#!/usr/bin/env python3

"""
Generate a UML Class Diagram for a Laravel project.

Run from the Laravel project root:

    python generate_class_diagram.py

Reads:
    app/Models/**/*.php
    database/migrations/**/*.php

Writes:
    school-management-class-diagram.drawio

No Laravel/PHP files are modified.
Only Python standard library is used.
"""

from __future__ import annotations

import html
import re
import uuid
from pathlib import Path
from collections import OrderedDict, defaultdict


# ============================================================
# Configuration
# ============================================================

ROOT = Path(__file__).resolve().parent

MODELS_DIR = ROOT / "app" / "Models"
MIGRATIONS_DIR = ROOT / "database" / "migrations"

OUT = ROOT / "school-management-class-diagram.drawio"


# ============================================================
# Utilities
# ============================================================

def gid() -> str:
    return uuid.uuid4().hex[:12]


def esc(value: str) -> str:
    return html.escape(str(value), quote=True)


def clean_php(text: str) -> str:
    """
    Remove PHP comments while preserving strings reasonably well
    for the type of static parsing performed here.
    """

    text = re.sub(r"/\*.*?\*/", "", text, flags=re.S)
    text = re.sub(r"//.*", "", text)

    return text


def snake(name: str) -> str:
    name = re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", name)
    return re.sub(r"[^a-zA-Z0-9_]", "_", name).lower()


def short_class_name(value: str) -> str:
    return value.split("\\")[-1]


def normalize_type(value: str) -> str:
    value = value.strip()

    value = value.replace("\\", "")

    value = re.sub(r"\s+", " ", value)

    return value


# ============================================================
# Model Parsing
# ============================================================

RELATION_METHODS = OrderedDict([
    ("belongsTo", "N:1"),
    ("hasOne", "1:1"),
    ("hasMany", "1:N"),
    ("belongsToMany", "N:N"),
    ("hasManyThrough", "1:N"),
    ("hasOneThrough", "1:1"),
    ("morphTo", "N:1"),
    ("morphOne", "1:1"),
    ("morphMany", "1:N"),
    ("morphToMany", "N:N"),
    ("morphedByMany", "N:N"),
])


def model_class(text: str, fallback: str) -> str:
    match = re.search(
        r"\bclass\s+([A-Za-z_][A-Za-z0-9_]*)",
        text
    )

    return match.group(1) if match else fallback


def model_parent(text: str) -> str | None:
    match = re.search(
        r"\bclass\s+[A-Za-z_][A-Za-z0-9_]*\s+extends\s+([A-Za-z_][A-Za-z0-9_\\]*)",
        text
    )

    if not match:
        return None

    return short_class_name(match.group(1))


def model_interfaces(text: str) -> list[str]:
    match = re.search(
        r"\bclass\s+[A-Za-z_][A-Za-z0-9_]*"
        r"(?:\s+extends\s+[A-Za-z_][A-Za-z0-9_\\]*)?"
        r"\s+implements\s+([^{]+)",
        text
    )

    if not match:
        return []

    return [
        short_class_name(x.strip())
        for x in match.group(1).split(",")
        if x.strip()
    ]


def model_traits(text: str) -> list[str]:
    traits = []

    for match in re.finditer(
        r"\buse\s+([^;{]+);",
        text
    ):
        value = match.group(1).strip()

        # Ignore relationship/model imports that are not traits.
        if "::" in value:
            continue

        for item in value.split(","):
            item = item.strip()

            if item:
                traits.append(short_class_name(item))

    return list(dict.fromkeys(traits))


def table_from_model(class_name: str, text: str) -> str:

    match = re.search(
        r"protected\s+\$table\s*=\s*['\"]([^'\"]+)['\"]",
        text
    )

    if match:
        return match.group(1)

    irregular = {
        "User": "users",
        "Person": "people",
    }

    if class_name in irregular:
        return irregular[class_name]

    return snake(class_name) + "s"


def parse_fillable(text: str) -> list[str]:

    match = re.search(
        r"protected\s+\$fillable\s*=\s*\[(.*?)\]",
        text,
        flags=re.S
    )

    if not match:
        return []

    return re.findall(
        r"['\"]([^'\"]+)['\"]",
        match.group(1)
    )


def parse_guarded(text: str) -> list[str]:

    match = re.search(
        r"protected\s+\$guarded\s*=\s*\[(.*?)\]",
        text,
        flags=re.S
    )

    if not match:
        return []

    return re.findall(
        r"['\"]([^'\"]+)['\"]",
        match.group(1)
    )


def parse_hidden(text: str) -> list[str]:

    match = re.search(
        r"protected\s+\$hidden\s*=\s*\[(.*?)\]",
        text,
        flags=re.S
    )

    if not match:
        return []

    return re.findall(
        r"['\"]([^'\"]+)['\"]",
        match.group(1)
    )


def parse_casts(text: str) -> dict[str, str]:

    casts = {}

    match = re.search(
        r"protected\s+\$casts\s*=\s*\[(.*?)\]",
        text,
        flags=re.S
    )

    if not match:
        return casts

    body = match.group(1)

    for item in re.finditer(
        r"['\"]([^'\"]+)['\"]\s*=>\s*['\"]([^'\"]+)['\"]",
        body
    ):
        casts[item.group(1)] = item.group(2)

    return casts


def parse_methods(text: str) -> list[str]:

    methods = []

    for match in re.finditer(
        r"(?:public|protected|private)?\s*function\s+"
        r"([A-Za-z_][A-Za-z0-9_]*)\s*\(",
        text
    ):
        name = match.group(1)

        if name not in methods:
            methods.append(name)

    return methods


def parse_relations(text: str):

    relations = []

    for method, cardinality in RELATION_METHODS.items():

        pattern = re.compile(
            rf"""
            function\s+
            ([A-Za-z_][A-Za-z0-9_]*)\s*
            \([^)]*\)
            .*?
            return\s+
            \$this\s*->\s*
            {method}
            \s*\(
            \s*([A-Za-z_][A-Za-z0-9_\\]*)::class
            """,
            re.S | re.X
        )

        for match in pattern.finditer(text):

            relation_name = match.group(1)

            target = short_class_name(match.group(2))

            relations.append({
                "name": relation_name,
                "target": target,
                "method": method,
                "cardinality": cardinality,
            })

    return relations


def parse_models():

    models = OrderedDict()

    if not MODELS_DIR.exists():
        return models

    for path in sorted(MODELS_DIR.rglob("*.php")):

        raw = path.read_text(
            encoding="utf-8",
            errors="ignore"
        )

        text = clean_php(raw)

        cls = model_class(
            text,
            path.stem
        )

        models[cls] = {
            "class": cls,
            "table": table_from_model(cls, text),
            "path": str(path.relative_to(ROOT)),

            "parent": model_parent(text),
            "interfaces": model_interfaces(text),
            "traits": model_traits(text),

            "fillable": parse_fillable(text),
            "guarded": parse_guarded(text),
            "hidden": parse_hidden(text),
            "casts": parse_casts(text),

            "methods": parse_methods(text),
            "relations": parse_relations(text),

            "columns": OrderedDict(),

            "group": "Other",
        }

    return models


# ============================================================
# Migration Parsing
# ============================================================

def parse_migrations():

    tables = OrderedDict()
    fks = []

    if not MIGRATIONS_DIR.exists():
        return tables, fks

    for path in sorted(MIGRATIONS_DIR.rglob("*.php")):

        text = clean_php(
            path.read_text(
                encoding="utf-8",
                errors="ignore"
            )
        )

        # Schema::create(...)
        for match in re.finditer(
            r"""
            Schema::create(?:IfNotExists)?
            \s*\(
            \s*['"]([^'"]+)['"]
            \s*,\s*
            function\s*\([^)]*\)\s*\{
            """,
            text,
            flags=re.X
        ):

            table = match.group(1)

            start = match.end()

            next_schema = re.search(
                r"\bSchema::(?:create|table|drop|rename)\b",
                text[start:]
            )

            if next_schema:
                block = text[
                    start:
                    start + next_schema.start()
                ]
            else:
                block = text[start:]

            if table not in tables:

                tables[table] = {
                    "columns": OrderedDict(),
                    "path": str(path.relative_to(ROOT)),
                }

            parse_table_block(
                table,
                block,
                tables,
                fks
            )

        # Schema::table(...)
        for match in re.finditer(
            r"""
            Schema::table
            \s*\(
            \s*['"]([^'"]+)['"]
            \s*,\s*
            function\s*\([^)]*\)\s*\{
            """,
            text,
            flags=re.X
        ):

            table = match.group(1)

            start = match.end()

            next_schema = re.search(
                r"\bSchema::(?:create|table|drop|rename)\b",
                text[start:]
            )

            if next_schema:
                block = text[
                    start:
                    start + next_schema.start()
                ]
            else:
                block = text[start:]

            if table not in tables:

                tables[table] = {
                    "columns": OrderedDict(),
                    "path": str(path.relative_to(ROOT)),
                }

            parse_table_block(
                table,
                block,
                tables,
                fks
            )

    unique = []

    seen = set()

    for fk in fks:

        key = tuple(fk)

        if key not in seen:

            seen.add(key)

            unique.append(fk)

    return tables, unique


def parse_table_block(
    table,
    block,
    tables,
    fks
):

    columns = tables[table]["columns"]

    # Primary keys
    special = [
        (
            r"\$table->id\s*\(\s*['\"]?([^'\")]*)['\"]?\s*\)",
            "BIGINT",
            "PK"
        ),
        (
            r"\$table->bigIncrements\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "BIGINT",
            "PK"
        ),
        (
            r"\$table->increments\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "INT",
            "PK"
        ),
        (
            r"\$table->uuid\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "UUID",
            ""
        ),
        (
            r"\$table->ulid\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "ULID",
            ""
        ),
    ]

    for rx, typ, flag in special:

        for match in re.finditer(rx, block):

            name = match.group(1) or "id"

            columns.setdefault(
                name,
                [typ, flag]
            )

    methods = {
        "string": "VARCHAR",
        "text": "TEXT",
        "mediumText": "MEDIUMTEXT",
        "longText": "LONGTEXT",

        "integer": "INT",
        "unsignedInteger": "INT UNSIGNED",

        "bigInteger": "BIGINT",
        "unsignedBigInteger": "BIGINT UNSIGNED",

        "tinyInteger": "TINYINT",
        "unsignedTinyInteger": "TINYINT UNSIGNED",

        "smallInteger": "SMALLINT",
        "unsignedSmallInteger": "SMALLINT UNSIGNED",

        "mediumInteger": "MEDIUMINT",
        "unsignedMediumInteger": "MEDIUMINT UNSIGNED",

        "decimal": "DECIMAL",
        "float": "FLOAT",
        "double": "DOUBLE",

        "boolean": "BOOLEAN",

        "date": "DATE",
        "dateTime": "DATETIME",
        "datetime": "DATETIME",

        "timestamp": "TIMESTAMP",
        "time": "TIME",

        "json": "JSON",
        "jsonb": "JSONB",

        "binary": "BLOB",

        "enum": "ENUM",

        "ipAddress": "IP",
        "macAddress": "MAC",
        "year": "YEAR",
    }

    for method, typ in methods.items():

        rx = re.compile(
            rf"\$table->{method}"
            rf"\s*\(\s*['\"]([^'\"]+)['\"]([^)]*)\)"
        )

        for match in rx.finditer(block):

            name = match.group(1)

            args = match.group(2)

            display_type = typ

            if method in (
                "string",
                "decimal",
                "float",
                "double",
                "enum"
            ):

                numbers = re.findall(
                    r"\d+",
                    args
                )

                if numbers:

                    display_type += (
                        "(" +
                        ",".join(numbers[:2]) +
                        ")"
                    )

            columns.setdefault(
                name,
                [display_type, ""]
            )

    # timestamps
    if re.search(
        r"\$table->timestamps\s*\(",
        block
    ):

        columns.setdefault(
            "created_at",
            ["TIMESTAMP", ""]
        )

        columns.setdefault(
            "updated_at",
            ["TIMESTAMP", ""]
        )

    # soft deletes
    if re.search(
        r"\$table->softDeletes\s*\(",
        block
    ):

        columns.setdefault(
            "deleted_at",
            ["TIMESTAMP", ""]
        )

    # foreignId()
    for match in re.finditer(
        r"""
        \$table->foreignId
        \s*\(
        \s*['"]([^'"]+)['"]
        \s*\)
        ([^;]*);
        """,
        block,
        flags=re.X
    ):

        column = match.group(1)

        tail = match.group(2)

        columns.setdefault(
            column,
            ["BIGINT UNSIGNED", "FK"]
        )

        constrained = re.search(
            r"->constrained"
            r"\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        if constrained:

            ref_table = constrained.group(1)

        else:

            ref_table = re.sub(
                r"_id$",
                "s",
                column
            )

        references = re.search(
            r"->references"
            r"\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        ref_column = (
            references.group(1)
            if references
            else "id"
        )

        fks.append(
            (
                table,
                column,
                ref_table,
                ref_column
            )
        )

    # foreign()
    for match in re.finditer(
        r"""
        \$table->foreign
        \s*\(
        \s*['"]([^'"]+)['"]
        \s*\)
        ([^;]*);
        """,
        block,
        flags=re.X
    ):

        column = match.group(1)

        tail = match.group(2)

        on_match = re.search(
            r"->on\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        references = re.search(
            r"->references"
            r"\s*\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        if on_match:

            columns.setdefault(
                column,
                ["BIGINT UNSIGNED", "FK"]
            )

            fks.append(
                (
                    table,
                    column,
                    on_match.group(1),
                    references.group(1)
                    if references
                    else "id"
                )
            )


# ============================================================
# Merge Model / Migration Information
# ============================================================

def model_for_table(models, table):

    for cls, info in models.items():

        if info["table"] == table:
            return cls

    return None


def merge_columns(models, tables):

    for cls, info in models.items():

        table = info["table"]

        if table in tables:

            info["columns"] = tables[
                table
            ]["columns"]

        else:

            # Model without migration
            # remains valid in the diagram.
            info["columns"] = OrderedDict()


# ============================================================
# Groups
# ============================================================

GROUP_MAP = {

    "users": "Auth & People",
    "roles": "Auth & People",
    "permissions": "Auth & People",

    "model_has_roles": "Auth & People",
    "model_has_permissions": "Auth & People",
    "role_has_permissions": "Auth & People",

    "students": "Students & Academic",
    "guardians": "Students & Academic",
    "enrollments": "Students & Academic",

    "academic_years": "Students & Academic",
    "academic_stages": "Students & Academic",
    "grade_levels": "Students & Academic",
    "class_rooms": "Students & Academic",
    "subjects": "Students & Academic",
    "grade_subjects": "Students & Academic",
    "semesters": "Students & Academic",

    "assessment_components": "Assessment & Learning",
    "student_marks": "Assessment & Learning",
    "practice_quizzes": "Assessment & Learning",
    "questions": "Assessment & Learning",
    "options": "Assessment & Learning",
    "student_quiz_attempts": "Assessment & Learning",
    "student_quiz_attempt_answers": "Assessment & Learning",
    "study_materials": "Assessment & Learning",
    "homeworks": "Assessment & Learning",

    "schedules": "Scheduling",
    "schedule_entries": "Scheduling",
    "schedule_time_slots": "Scheduling",
    "time_slots": "Scheduling",
    "days": "Scheduling",

    "teacher_assignments": "Staff & HR",
    "teacher_workloads": "Staff & HR",
    "teacher_evaluations": "Staff & HR",
    "teacher_period_attendances": "Staff & HR",

    "staff": "Staff & HR",
    "staff_attendances": "Staff & HR",
    "staff_leaves": "Staff & HR",
    "staff_leave_types": "Staff & HR",
    "advisers": "Staff & HR",
    "service_staff": "Staff & HR",

    "staff_financial_contracts": "Finance",
    "payrolls": "Finance",
    "financial_accounts": "Finance",
    "fee_plans": "Finance",
    "fee_plan_extra_services": "Finance",
    "extra_services": "Finance",
    "installment_policies": "Finance",
    "installment_policy_items": "Finance",
    "scheduled_installments": "Finance",
    "payment_transactions": "Finance",

    "complaints": "Communication",
    "complaint_categories": "Communication",
    "complaint_types": "Communication",
    "announcements": "Communication",
    "activities": "Communication",
    "alerts": "Communication",

    "contents": "Content",
}


def infer_groups(models):

    for cls, info in models.items():

        info["group"] = GROUP_MAP.get(
            info["table"],
            "Other"
        )


# ============================================================
# Class Box
# ============================================================

def class_cell(
    info,
    x,
    y,
    width,
    height
):

    cls = info["class"]

    table = info["table"]

    parent = info["parent"]

    interfaces = info["interfaces"]

    traits = info["traits"]

    columns = info["columns"]

    methods = info["methods"]

    fillable = info["fillable"]

    casts = info["casts"]

    # --------------------------------------------------------
    # Header
    # --------------------------------------------------------

    header = (
        f"<B>{esc(cls)}</B>"
    )

    subtitle = (
        f"<FONT POINT-SIZE='9'>"
        f"{esc(table)}"
        f"</FONT>"
    )

    if parent:

        subtitle += (
            f"<BR>"
            f"<FONT POINT-SIZE='8'>"
            f"extends {esc(parent)}"
            f"</FONT>"
        )

    if interfaces:

        subtitle += (
            f"<BR>"
            f"<FONT POINT-SIZE='8'>"
            f"implements "
            f"{esc(', '.join(interfaces))}"
            f"</FONT>"
        )

    # --------------------------------------------------------
    # Attributes
    # --------------------------------------------------------

    attribute_rows = []

    for name, data in columns.items():

        typ, flag = data

        visibility = "-"

        attribute_rows.append(
            "<TR>"
            f"<TD ALIGN='LEFT'>"
            f"<FONT POINT-SIZE='9'>"
            f"{visibility} {esc(name)}"
            f"</FONT>"
            f"</TD>"
            f"<TD ALIGN='LEFT'>"
            f"<FONT POINT-SIZE='9'>"
            f"{esc(typ)}"
            f"</FONT>"
            f"</TD>"
            "</TR>"
        )

    # If migration has no columns, use fillable/casts.
    if not attribute_rows:

        for name in fillable:

            typ = casts.get(
                name,
                "mixed"
            )

            attribute_rows.append(
                "<TR>"
                f"<TD ALIGN='LEFT'>"
                f"<FONT POINT-SIZE='9'>"
                f"- {esc(name)}"
                f"</FONT>"
                f"</TD>"
                f"<TD ALIGN='LEFT'>"
                f"<FONT POINT-SIZE='9'>"
                f"{esc(typ)}"
                f"</FONT>"
                f"</TD>"
                "</TR>"
            )

    # --------------------------------------------------------
    # Methods
    # --------------------------------------------------------

    method_rows = []

    relation_names = {
        r["name"]
        for r in info["relations"]
    }

    for method in methods:

        if method in relation_names:

            symbol = "+"

        else:

            symbol = "+"

        method_rows.append(
            "<TR>"
            f"<TD ALIGN='LEFT' COLSPAN='2'>"
            f"<FONT POINT-SIZE='9'>"
            f"{symbol} {esc(method)}()"
            f"</FONT>"
            f"</TD>"
            "</TR>"
        )

    if not method_rows:

        method_rows.append(
            "<TR>"
            "<TD COLSPAN='2'>"
            "<FONT POINT-SIZE='9'>"
            "No methods detected"
            "</FONT>"
            "</TD>"
            "</TR>"
        )

    # --------------------------------------------------------
    # Traits
    # --------------------------------------------------------

    trait_line = ""

    if traits:

        trait_line = (
            "<TR>"
            "<TD ALIGN='LEFT' COLSPAN='2'>"
            "<FONT POINT-SIZE='8'>"
            "<I>"
            "Traits: "
            f"{esc(', '.join(traits))}"
            "</I>"
            "</FONT>"
            "</TD>"
            "</TR>"
        )

    # --------------------------------------------------------
    # Complete UML label
    # --------------------------------------------------------

    label = (
        "<TABLE "
        "BORDER='0' "
        "CELLBORDER='0' "
        "CELLSPACING='0' "
        "CELLPADDING='5' "
        "WIDTH='100%'>"

        # Header
        "<TR>"
        "<TD "
        "ALIGN='CENTER' "
        "COLSPAN='2'>"
        f"{header}"
        "<BR>"
        f"{subtitle}"
        "</TD>"
        "</TR>"

        # Divider
        "<TR>"
        "<TD "
        "COLSPAN='2' "
        "HEIGHT='1' "
        "BGCOLOR='#64748b'>"
        "</TD>"
        "</TR>"

        # Attributes header
        "<TR>"
        "<TD "
        "ALIGN='LEFT' "
        "COLSPAN='2'>"
        "<FONT POINT-SIZE='8'>"
        "<B>ATTRIBUTES</B>"
        "</FONT>"
        "</TD>"
        "</TR>"

        + "".join(attribute_rows)

        # Divider
        + (
            "<TR>"
            "<TD "
            "COLSPAN='2' "
            "HEIGHT='1' "
            "BGCOLOR='#64748b'>"
            "</TD>"
            "</TR>"
        )

        # Methods header
        + (
            "<TR>"
            "<TD "
            "ALIGN='LEFT' "
            "COLSPAN='2'>"
            "<FONT POINT-SIZE='8'>"
            "<B>METHODS</B>"
            "</FONT>"
            "</TD>"
            "</TR>"
        )

        + "".join(method_rows)

        + trait_line

        + "</TABLE>"
    )

    style = (
        "shape=rectangle;"
        "rounded=0;"
        "whiteSpace=wrap;"
        "html=1;"
        "fillColor=#ffffff;"
        "strokeColor=#334155;"
        "strokeWidth=1;"
        "align=left;"
        "verticalAlign=top;"
        "spacing=8;"
        "fontSize=10;"
        "shadow=0;"
    )

    return (
        f'<mxCell '
        f'id="{gid()}" '
        f'value="{esc(label)}" '
        f'style="{style}" '
        f'vertex="1" '
        f'parent="1">'
        f'<mxGeometry '
        f'x="{x}" '
        f'y="{y}" '
        f'width="{width}" '
        f'height="{height}" '
        f'as="geometry"/>'
        f'</mxCell>'
    )


# ============================================================
# Build Diagram
# ============================================================

def build_drawio(models, tables, fks):

    # Only actual Models are represented as UML classes.
    classes = OrderedDict()

    for cls, info in models.items():

        classes[cls] = info

    grouped = defaultdict(list)

    for cls, info in classes.items():

        grouped[
            info["group"]
        ].append(cls)

    order = [
        "Auth & People",
        "Students & Academic",
        "Assessment & Learning",
        "Scheduling",
        "Staff & HR",
        "Finance",
        "Communication",
        "Content",
        "Other",
    ]

    # --------------------------------------------------------
    # Layout
    # --------------------------------------------------------

    margin_x = 70

    class_w = 360

    gap_x = 80

    gap_y = 90

    max_per_row = 3

    positions = {}

    class_ids = {}

    cells = []

    next_y = 70

    # --------------------------------------------------------
    # Estimate class height
    # --------------------------------------------------------

    def estimate_height(info):

        columns = len(info["columns"])

        if columns == 0:

            columns = len(
                info["fillable"]
            )

        methods = len(
            info["methods"]
        )

        traits = 1 if info["traits"] else 0

        height = (
            110
            + columns * 22
            + methods * 22
            + traits * 24
        )

        return max(
            220,
            min(
                height,
                900
            )
        )

    # --------------------------------------------------------
    # Group layout
    # --------------------------------------------------------

    for group in order:

        items = sorted(
            grouped.get(group, [])
        )

        if not items:
            continue

        rows = [
            items[i:i + max_per_row]
            for i in range(
                0,
                len(items),
                max_per_row
            )
        ]

        row_heights = []

        for row in rows:

            row_height = 250

            for cls in row:

                row_height = max(
                    row_height,
                    estimate_height(
                        classes[cls]
                    )
                )

            row_heights.append(
                row_height
            )

        group_header = 42

        group_h = (
            group_header
            + sum(row_heights)
            + (len(rows) - 1) * 40
            + 40
        )

        # Group container
        cells.append(
            f'<mxCell '
            f'id="{gid()}" '
            f'value="{esc(group)}" '
            f'style="'
            f'swimlane;'
            f'html=1;'
            f'rounded=0;'
            f'startSize={group_header};'
            f'fillColor=#ffffff;'
            f'strokeColor=#94a3b8;'
            f'strokeWidth=1;'
            f'fontStyle=1;'
            f'shadow=0;'
            f'" '
            f'vertex="1" '
            f'parent="1">'
            f'<mxGeometry '
            f'x="40" '
            f'y="{next_y}" '
            f'width="1500" '
            f'height="{group_h}" '
            f'as="geometry"/>'
            f'</mxCell>'
        )

        y = next_y + group_header + 20

        for row_index, row in enumerate(rows):

            for column_index, cls in enumerate(row):

                x = (
                    margin_x
                    + column_index
                    * (class_w + gap_x)
                )

                positions[cls] = (
                    x,
                    y
                )

                class_id = gid()

                class_ids[cls] = class_id

                info = classes[cls]

                cells.append(
                    class_cell(
                        info,
                        x,
                        y,
                        class_w,
                        estimate_height(info)
                    )
                )

            y += (
                row_heights[row_index]
                + 40
            )

        next_y += (
            group_h
            + gap_y
        )

    # ========================================================
    # Inheritance relationships
    # ========================================================

    for cls, info in classes.items():

        parent = info["parent"]

        if not parent:
            continue

        if (
            cls not in class_ids
            or parent not in class_ids
        ):
            continue

        cells.append(
            f'<mxCell '
            f'id="{gid()}" '
            f'value="extends" '
            f'style="'
            f'edgeStyle=orthogonalEdgeStyle;'
            f'rounded=0;'
            f'html=1;'
            f'endArrow=block;'
            f'endFill=0;'
            f'startArrow=none;'
            f'fontSize=9;'
            f'fontColor=#334155;'
            f'" '
            f'edge="1" '
            f'parent="1" '
            f'source="{class_ids[cls]}" '
            f'target="{class_ids[parent]}">'
            f'<mxGeometry '
            f'relative="1" '
            f'as="geometry"/>'
            f'</mxCell>'
        )

    # ========================================================
    # Model relationships
    # ========================================================

    for cls, info in classes.items():

        if cls not in class_ids:
            continue

        for relation in info["relations"]:

            target = relation["target"]

            if target not in class_ids:
                continue

            cardinality = relation["cardinality"]

            label = (
                relation["name"]
                + " : "
                + relation["method"]
            )

            cells.append(
                f'<mxCell '
                f'id="{gid()}" '
                f'value="{esc(label)}" '
                f'style="'
                f'edgeStyle=orthogonalEdgeStyle;'
                f'rounded=0;'
                f'orthogonalLoop=1;'
                f'jettySize=auto;'
                f'html=1;'
                f'endArrow=none;'
                f'startArrow=none;'
                f'fontSize=9;'
                f'fontColor=#475569;'
                f'labelBackgroundColor=#ffffff;'
                f'" '
                f'edge="1" '
                f'parent="1" '
                f'source="{class_ids[cls]}" '
                f'target="{class_ids[target]}">'
                f'<mxGeometry '
                f'relative="1" '
                f'as="geometry"/>'
                f'</mxCell>'
            )

    # ========================================================
    # Migration FK relationships
    # ========================================================

    table_to_class = {
        info["table"]: cls
        for cls, info in classes.items()
    }

    for src_table, src_col, dst_table, dst_col in fks:

        src_cls = table_to_class.get(
            src_table
        )

        dst_cls = table_to_class.get(
            dst_table
        )

        if not src_cls or not dst_cls:
            continue

        if (
            src_cls not in class_ids
            or dst_cls not in class_ids
        ):
            continue

        # Avoid duplicate relationship edges
        # where a Model relationship already describes
        # the same pair.
        has_relation = False

        for relation in classes[src_cls]["relations"]:

            if relation["target"] == dst_cls:

                has_relation = True

                break

        if has_relation:
            continue

        cells.append(
            f'<mxCell '
            f'id="{gid()}" '
            f'value="{esc(src_col + " → " + dst_col)}" '
            f'style="'
            f'edgeStyle=orthogonalEdgeStyle;'
            f'rounded=0;'
            f'dashed=1;'
            f'html=1;'
            f'endArrow=none;'
            f'startArrow=none;'
            f'fontSize=8;'
            f'fontColor=#64748b;'
            f'labelBackgroundColor=#ffffff;'
            f'" '
            f'edge="1" '
            f'parent="1" '
            f'source="{class_ids[src_cls]}" '
            f'target="{class_ids[dst_cls]}">'
            f'<mxGeometry '
            f'relative="1" '
            f'as="geometry"/>'
            f'</mxCell>'
        )

    # ========================================================
    # Draw.io XML
    # ========================================================

    return f'''<?xml version="1.0" encoding="UTF-8"?>
<mxfile
    host="app.diagrams.net"
    modified="2026-08-15T00:00:00.000Z"
    agent="Laravel-Class-Diagram"
    version="24.7.17"
    type="device">

    <diagram
        id="{gid()}"
        name="School Management Class Diagram">

        <mxGraphModel
            dx="1600"
            dy="1000"
            grid="1"
            gridSize="10"
            guides="1"
            tooltips="1"
            connect="1"
            arrows="1"
            fold="1"
            page="1"
            pageScale="1"
            pageWidth="1600"
            pageHeight="1200"
            math="0"
            shadow="0">

            <root>

                <mxCell id="0"/>

                <mxCell
                    id="1"
                    parent="0"/>

                {''.join(cells)}

            </root>

        </mxGraphModel>

    </diagram>

</mxfile>
'''


# ============================================================
# Main
# ============================================================

def main():

    if not MODELS_DIR.exists():

        raise SystemExit(
            "ERROR: app/Models was not found."
        )

    print()
    print("=" * 60)
    print(" Laravel → UML Class Diagram")
    print("=" * 60)
    print()

    print("Reading Laravel Models...")

    models = parse_models()

    print(
        f"Models found: {len(models)}"
    )

    print(
        "Reading Laravel migrations..."
    )

    tables, fks = parse_migrations()

    print(
        f"Tables found: {len(tables)}"
    )

    print(
        f"Foreign keys found: {len(fks)}"
    )

    merge_columns(
        models,
        tables
    )

    infer_groups(
        models
    )

    print(
        "Building draw.io diagram..."
    )

    xml = build_drawio(
        models,
        tables,
        fks
    )

    OUT.write_text(
        xml,
        encoding="utf-8"
    )

    print()
    print("=" * 60)
    print(" CLASS DIAGRAM GENERATED")
    print("=" * 60)
    print()
    print(
        f"Output: {OUT}"
    )
    print()
    print(
        "Open this file with diagrams.net / draw.io:"
    )
    print(
        "school-management-class-diagram.drawio"
    )
    print()
    print(
        "No Laravel backend files were modified."
    )
    print()


if __name__ == "__main__":
    main()