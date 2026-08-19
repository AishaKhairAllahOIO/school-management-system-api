#!/usr/bin/env python3

"""
Generate a draw.io ERD from a Laravel project.

Run from the Laravel project root:

    py generate_erd_drawio.py

Reads:
    app/Models/**/*.php
    database/migrations/**/*.php

Writes:
    school-management-erd.drawio

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
# Paths
# ============================================================

ROOT = Path(__file__).resolve().parent

MODELS_DIR = ROOT / "app" / "Models"
MIGRATIONS_DIR = ROOT / "database" / "migrations"

OUT = ROOT / "school-management-erd.drawio"


# ============================================================
# Layout Configuration
# ============================================================

# Horizontal margin around the whole ERD
MARGIN_X = 80

# Vertical margin
MARGIN_Y = 70

# Width of each table
TABLE_W = 320

# Horizontal distance between tables
GAP_X = 80

# Vertical distance between tables
GAP_Y = 70

# Distance between groups
GROUP_GAP_Y = 110

# Group title/header height
GROUP_HEADER = 44

# Number of tables in one row
MAX_PER_ROW = 4


# ============================================================
# Utilities
# ============================================================

def clean_php(s: str) -> str:
    """
    Remove PHP comments.
    """

    s = re.sub(
        r"/\*.*?\*/",
        "",
        s,
        flags=re.S
    )

    s = re.sub(
        r"//.*",
        "",
        s
    )

    return s


def snake(name: str) -> str:
    """
    Convert PascalCase / camelCase to snake_case.
    """

    name = re.sub(
        r"([a-z0-9])([A-Z])",
        r"\1_\2",
        name
    )

    return re.sub(
        r"[^a-zA-Z0-9_]",
        "_",
        name
    ).lower()


def model_class(
    text: str,
    fallback: str
) -> str:

    m = re.search(
        r"\bclass\s+([A-Za-z_][A-Za-z0-9_]*)",
        text
    )

    return (
        m.group(1)
        if m
        else fallback
    )


def table_from_model(
    class_name: str,
    text: str
) -> str:

    m = re.search(
        r"protected\s+\$table\s*=\s*['\"]([^'\"]+)['\"]",
        text
    )

    if m:
        return m.group(1)

    irregular = {
        "User": "users",
        "Person": "people",
    }

    if class_name in irregular:
        return irregular[class_name]

    return snake(class_name) + "s"


def esc(s: str) -> str:
    """
    Escape XML / HTML.
    """

    return html.escape(
        str(s),
        quote=True
    )


def gid() -> str:
    """
    Generate a draw.io ID.
    """

    return uuid.uuid4().hex[:12]


# ============================================================
# Parse Laravel Models
# ============================================================

def parse_models():

    models = OrderedDict()

    if not MODELS_DIR.exists():
        return models

    for p in sorted(
        MODELS_DIR.rglob("*.php")
    ):

        raw = p.read_text(
            encoding="utf-8",
            errors="ignore"
        )

        text = clean_php(raw)

        cls = model_class(
            text,
            p.stem
        )

        table = table_from_model(
            cls,
            text
        )

        relations = []

        patterns = [

            ("belongsToMany", "N:N"),
            ("morphToMany", "N:N"),
            ("morphedByMany", "N:N"),

            ("hasManyThrough", "1:N"),
            ("hasOneThrough", "1:1"),

            ("hasMany", "1:N"),
            ("hasOne", "1:1"),
            ("belongsTo", "N:1"),

            ("morphMany", "1:N"),
            ("morphOne", "1:1"),
            ("morphTo", "N:1"),
        ]

        for method, cardinality in patterns:

            rx = re.compile(
                rf"function\s+"
                rf"([A-Za-z_][A-Za-z0-9_]*)"
                rf"\s*\([^)]*\).*?"
                rf"return\s+\$this\s*->\s*"
                rf"{method}\s*\(\s*"
                rf"([A-Za-z_][A-Za-z0-9_\\]*)::class",
                re.S,
            )

            for rm in rx.finditer(text):

                rel_name = rm.group(1)

                target = (
                    rm.group(2)
                    .split("\\")[-1]
                )

                relation = (
                    rel_name,
                    target,
                    method,
                    cardinality
                )

                if relation not in relations:
                    relations.append(relation)

        models[cls] = {

            "class": cls,

            "table": table,

            "path": str(
                p.relative_to(ROOT)
            ),

            "relations": relations,

            "columns": OrderedDict(),

            "group": "Other",
        }

    return models


# ============================================================
# Parse Laravel Migrations
# ============================================================

def parse_migrations():

    tables = OrderedDict()

    fks = []

    if not MIGRATIONS_DIR.exists():
        return tables, fks

    for p in sorted(
        MIGRATIONS_DIR.rglob("*.php")
    ):

        text = clean_php(
            p.read_text(
                encoding="utf-8",
                errors="ignore"
            )
        )

        # ----------------------------------------------------
        # Schema::create
        # ----------------------------------------------------

        for cm in re.finditer(
            r"Schema::create(?:IfNotExists)?\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*,\s*"
            r"function\s*\([^)]*\)\s*\{",
            text,
        ):

            table = cm.group(1)

            start = cm.end()

            nxt = re.search(
                r"\bSchema::(?:create|table|drop|rename)\b",
                text[start:]
            )

            block = text[
                start:
                start + (
                    nxt.start()
                    if nxt
                    else len(text[start:])
                )
            ]

            if table not in tables:

                tables[table] = {

                    "columns": OrderedDict(),

                    "path": str(
                        p.relative_to(ROOT)
                    ),
                }

            parse_table_block(
                table,
                block,
                tables,
                fks
            )

        # ----------------------------------------------------
        # Schema::table
        # ----------------------------------------------------

        for tm in re.finditer(
            r"Schema::table\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*,\s*"
            r"function\s*\([^)]*\)\s*\{",
            text,
        ):

            table = tm.group(1)

            start = tm.end()

            nxt = re.search(
                r"\bSchema::(?:create|table|drop|rename)\b",
                text[start:]
            )

            block = text[
                start:
                start + (
                    nxt.start()
                    if nxt
                    else len(text[start:])
                )
            ]

            if table not in tables:

                tables[table] = {

                    "columns": OrderedDict(),

                    "path": str(
                        p.relative_to(ROOT)
                    ),
                }

            parse_table_block(
                table,
                block,
                tables,
                fks
            )

    # --------------------------------------------------------
    # Remove duplicate FKs
    # --------------------------------------------------------

    unique = []

    seen = set()

    for fk in fks:

        key = tuple(fk)

        if key not in seen:

            seen.add(key)

            unique.append(fk)

    return tables, unique


# ============================================================
# Parse Migration Table Block
# ============================================================

def parse_table_block(
    table,
    block,
    tables,
    fks
):

    cols = tables[table]["columns"]

    # --------------------------------------------------------
    # Special definitions
    # --------------------------------------------------------

    special = [

        (
            r"\$table->id\s*\(\s*"
            r"['\"]?([^'\")}]*)['\"]?\s*\)",
            "BIGINT",
            "PK"
        ),

        (
            r"\$table->bigIncrements\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "BIGINT",
            "PK"
        ),

        (
            r"\$table->increments\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "INT",
            "PK"
        ),

        (
            r"\$table->uuid\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "UUID",
            ""
        ),

        (
            r"\$table->ulid\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            "ULID",
            ""
        ),
    ]

    for rx, typ, flag in special:

        for m in re.finditer(
            rx,
            block
        ):

            name = (
                m.group(1).strip()
                or "id"
            )

            cols.setdefault(
                name,
                [typ, flag]
            )

    # --------------------------------------------------------
    # Laravel methods
    # --------------------------------------------------------

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
            rf"\$table->{method}\s*"
            rf"\(\s*['\"]([^'\"]+)['\"]"
            rf"([^)]*)\)"
        )

        for m in rx.finditer(block):

            name = m.group(1)

            arg = m.group(2)

            display_type = typ

            if method in (
                "string",
                "decimal",
                "float",
                "double"
            ):

                nums = re.findall(
                    r"\d+",
                    arg
                )

                if nums:

                    display_type += (
                        "(" +
                        ",".join(nums[:2]) +
                        ")"
                    )

            cols.setdefault(
                name,
                [display_type, ""]
            )

    # --------------------------------------------------------
    # timestamps
    # --------------------------------------------------------

    if re.search(
        r"\$table->timestamps\s*\(",
        block
    ):

        cols.setdefault(
            "created_at",
            ["TIMESTAMP", ""]
        )

        cols.setdefault(
            "updated_at",
            ["TIMESTAMP", ""]
        )

    # --------------------------------------------------------
    # soft deletes
    # --------------------------------------------------------

    if re.search(
        r"\$table->softDeletes\s*\(",
        block
    ):

        cols.setdefault(
            "deleted_at",
            ["TIMESTAMP", ""]
        )

    # --------------------------------------------------------
    # foreignId
    # --------------------------------------------------------

    for m in re.finditer(
        r"\$table->foreignId\s*"
        r"\(\s*['\"]([^'\"]+)['\"]\s*\)"
        r"([^;]*);",
        block,
    ):

        col = m.group(1)

        tail = m.group(2)

        cols.setdefault(
            col,
            ["BIGINT UNSIGNED", "FK"]
        )

        cm = re.search(
            r"->constrained\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        if cm:

            ref_table = cm.group(1)

        else:

            ref_table = re.sub(
                r"_id$",
                "s",
                col
            )

        rm = re.search(
            r"->references\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        ref_col = (
            rm.group(1)
            if rm
            else "id"
        )

        fks.append(
            (
                table,
                col,
                ref_table,
                ref_col
            )
        )

    # --------------------------------------------------------
    # foreign()
    # --------------------------------------------------------

    for m in re.finditer(
        r"\$table->foreign\s*"
        r"\(\s*['\"]([^'\"]+)['\"]\s*\)"
        r"([^;]*);",
        block,
    ):

        col = m.group(1)

        tail = m.group(2)

        om = re.search(
            r"->on\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        rm = re.search(
            r"->references\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        if om:

            cols.setdefault(
                col,
                ["BIGINT UNSIGNED", "FK"]
            )

            fks.append(
                (
                    table,
                    col,
                    om.group(1),
                    rm.group(1)
                    if rm
                    else "id"
                )
            )

    # --------------------------------------------------------
    # integer/bigInteger + constrained
    # --------------------------------------------------------

    for m in re.finditer(
        r"\$table->"
        r"(?:unsignedBigInteger|bigInteger|integer)"
        r"\s*\(\s*['\"]([^'\"]+_id)['\"]\s*\)"
        r"([^;]*->constrained[^;]*);",
        block,
    ):

        col = m.group(1)

        tail = m.group(2)

        cm = re.search(
            r"->constrained\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        ref_table = (
            cm.group(1)
            if cm
            else re.sub(
                r"_id$",
                "s",
                col
            )
        )

        rm = re.search(
            r"->references\s*"
            r"\(\s*['\"]([^'\"]+)['\"]\s*\)",
            tail
        )

        cols.setdefault(
            col,
            ["BIGINT UNSIGNED", "FK"]
        )

        fks.append(
            (
                table,
                col,
                ref_table,
                rm.group(1)
                if rm
                else "id"
            )
        )


# ============================================================
# Helpers
# ============================================================

def model_for_table(
    models,
    table
):

    for cls, info in models.items():

        if info["table"] == table:
            return cls

    return None


# ============================================================
# Group Configuration
# ============================================================

GROUP_MAP = {

    # Auth
    "users": "Auth & People",
    "roles": "Auth & People",
    "permissions": "Auth & People",
    "model_has_roles": "Auth & People",
    "model_has_permissions": "Auth & People",
    "role_has_permissions": "Auth & People",

    # Academic
    "students": "Students & Academic",
    "guardians": "Students & Academic",
    "enrollments": "Students & Academic",

    "academic_years": "Students & Academic",
    "academic_stages": "Students & Academic",

    "grade_levels": "Students & Academic",
    "grades": "Students & Academic",

    "class_rooms": "Students & Academic",
    "classes": "Students & Academic",

    "subjects": "Students & Academic",
    "grade_subjects": "Students & Academic",

    "semesters": "Students & Academic",

    "student_profiles": "Students & Academic",
    "academic_profiles": "Students & Academic",

    # Assessment
    "assessment_components": "Assessment & Learning",
    "student_marks": "Assessment & Learning",

    "practice_quizzes": "Assessment & Learning",
    "questions": "Assessment & Learning",
    "options": "Assessment & Learning",

    "student_quiz_attempts": "Assessment & Learning",
    "student_quiz_attempt_answers": "Assessment & Learning",

    "study_materials": "Assessment & Learning",
    "homeworks": "Assessment & Learning",

    "assignments": "Assessment & Learning",

    # Scheduling
    "schedules": "Scheduling",
    "schedule_entries": "Scheduling",
    "schedule_time_slots": "Scheduling",

    "time_slots": "Scheduling",
    "schedule_slots": "Scheduling",

    "days": "Scheduling",
    "working_days": "Scheduling",

    "counselor_availabilities": "Scheduling",
    "counselor_available_slots": "Scheduling",

    # Staff
    "teachers": "Staff & HR",
    "teacher_assignments": "Staff & HR",
    "teacher_workloads": "Staff & HR",

    "teacher_evaluations": "Staff & HR",
    "teacher_period_attendances": "Staff & HR",

    "staff": "Staff & HR",
    "staff_attendances": "Staff & HR",

    "staff_leaves": "Staff & HR",
    "staff_leave_types": "Staff & HR",

    "counselors": "Staff & HR",

    # Finance
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

    # Communication
    "complaints": "Communication",
    "complaint_categories": "Communication",
    "complaint_types": "Communication",

    "announcements": "Communication",
    "activities": "Communication",
    "alerts": "Communication",

    "counseling_sessions": "Communication",
    "appointments": "Communication",

    # Content
    "contents": "Content",
}


def infer_groups(models):

    for cls, info in models.items():

        info["group"] = GROUP_MAP.get(
            info["table"],
            "Other"
        )


# ============================================================
# Table Cell
# ============================================================

def table_cell(
    title,
    columns,
    x,
    y,
    w=TABLE_W,
    cell_id=None
):
    """
    Current table rendering.

    NOTE:
    The visual styling will be refined separately.
    This function currently keeps the existing table
    appearance while the main focus is fixing the layout.
    """

    rows = []

    key_w = 50

    name_w = w - key_w

    for name, (typ, flag) in columns.items():

        if "PK" in flag:
            key = "PK"

        elif "FK" in flag:
            key = "FK"

        else:
            key = ""

        if "PK" in flag:

            field_name = (
                f"<U><B>{esc(name)}</B></U>"
            )

        elif "FK" in flag:

            field_name = (
                f"<B>{esc(name)}</B>"
            )

        else:

            field_name = esc(name)

        key_cell = (

            f"<TD "
            f"ALIGN='CENTER' "
            f"VALIGN='MIDDLE' "
            f"WIDTH='{key_w}'>"

            f"<FONT "
            f"COLOR='#f5f5f5' "
            f"SIZE='12'>"

            f"<B>{esc(key)}</B>"

            f"</FONT>"

            f"</TD>"
        )

        name_cell = (

            f"<TD "
            f"ALIGN='LEFT' "
            f"VALIGN='MIDDLE' "
            f"WIDTH='{name_w}'>"

            f"<FONT "
            f"COLOR='#f5f5f5' "
            f"SIZE='12'>"

            f"{field_name}"

            f"</FONT>"

            f"</TD>"
        )

        rows.append(
            "<TR>"
            + key_cell
            + name_cell
            + "</TR>"
        )

    if not rows:

        rows.append(

            "<TR>"

            f"<TD WIDTH='{key_w}'></TD>"

            f"<TD WIDTH='{name_w}'>"

            f"<FONT COLOR='#9ca3af'>"

            "No columns"

            "</FONT>"

            "</TD>"

            "</TR>"
        )

    label = (

        "<TABLE "
        "BORDER='0' "
        "CELLBORDER='1' "
        "CELLSPACING='0' "
        "CELLPADDING='7' "
        "STYLE='"
        "font-family:Arial;"
        "font-size:12px;"
        "background-color:#1b1d1f;"
        "'>"

        "<TR>"

        "<TD "
        "COLSPAN='2' "
        "ALIGN='CENTER' "
        "VALIGN='MIDDLE' "
        "HEIGHT='38'>"

        f"<FONT "
        f"COLOR='#ffffff' "
        f"SIZE='14'>"

        f"<B>{esc(title)}</B>"

        "</FONT>"

        "</TD>"

        "</TR>"

        + "".join(rows)

        + "</TABLE>"
    )

    title_height = 38

    row_height = 34

    h = max(
        80,
        title_height +
        len(rows) * row_height
    )

    if cell_id is None:
        cell_id = gid()

    return (

        f'<mxCell '

        f'id="{cell_id}" '

        f'value="{esc(label)}" '

        f'style="'

        f'shape=rectangle;'

        f'whiteSpace=wrap;'

        f'html=1;'

        f'rounded=0;'

        f'fillColor=#1b1d1f;'

        f'strokeColor=#f1f1f1;'

        f'strokeWidth=1;'

        f'align=left;'

        f'verticalAlign=top;'

        f'spacing=0;'

        f'fontSize=12;'

        f'fontColor=#ffffff;'

        f'overflow=hidden;'

        f'" '

        f'vertex="1" '

        f'parent="1">'

        f'<mxGeometry '

        f'x="{x}" '

        f'y="{y}" '

        f'width="{w}" '

        f'height="{h}" '

        f'as="geometry"/>'

        f'</mxCell>'
    )


# ============================================================
# Build Draw.io XML
# ============================================================

def build_drawio(
    models,
    tables,
    fks
):

    # --------------------------------------------------------
    # Merge migration tables + model tables
    # --------------------------------------------------------

    all_tables = OrderedDict(tables)

    for cls, info in models.items():

        table = info["table"]

        if table not in all_tables:

            all_tables[table] = {

                "columns": info["columns"],

                "path": info["path"],
            }

    # --------------------------------------------------------
    # Assign groups
    # --------------------------------------------------------

    grouped = defaultdict(list)

    for table in all_tables:

        cls = model_for_table(
            models,
            table
        )

        if cls:

            group = models[cls]["group"]

        else:

            group = GROUP_MAP.get(
                table,
                "Other"
            )

        grouped[group].append(table)

    # --------------------------------------------------------
    # Group order
    # --------------------------------------------------------

    group_order = [

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

    # ========================================================
    # Dynamic canvas width
    # ========================================================

    max_group_tables = 0

    for group in group_order:

        count = len(
            grouped.get(
                group,
                []
            )
        )

        max_group_tables = max(
            max_group_tables,
            count
        )

    actual_columns = min(
        MAX_PER_ROW,
        max(
            1,
            max_group_tables
        )
    )

    canvas_width = (

        MARGIN_X * 2

        + actual_columns * TABLE_W

        + (actual_columns - 1) * GAP_X

        + 120
    )

    # --------------------------------------------------------
    # Minimum comfortable width
    # --------------------------------------------------------

    canvas_width = max(
        canvas_width,
        1500
    )

    # ========================================================
    # Layout state
    # ========================================================

    cells = []

    table_ids = {}

    next_y = MARGIN_Y

    total_height = MARGIN_Y

    # ========================================================
    # Build groups
    # ========================================================

    for group in group_order:

        items = sorted(
            grouped.get(
                group,
                []
            )
        )

        if not items:
            continue

        # ----------------------------------------------------
        # Split into rows
        # ----------------------------------------------------

        rows = [

            items[i:i + MAX_PER_ROW]

            for i in range(
                0,
                len(items),
                MAX_PER_ROW
            )
        ]

        row_heights = []

        # ----------------------------------------------------
        # Calculate row heights
        # ----------------------------------------------------

        for row in rows:

            max_h = 100

            for table in row:

                column_count = len(
                    all_tables[table]["columns"]
                )

                table_h = (

                    38

                    + column_count * 34
                )

                max_h = max(
                    max_h,
                    table_h
                )

            row_heights.append(
                max_h
            )

        # ----------------------------------------------------
        # Group height
        # ----------------------------------------------------

        group_height = (

            GROUP_HEADER

            + sum(row_heights)

            + (len(rows) - 1) * GAP_Y

            + 45
        )

        # ----------------------------------------------------
        # Group cell
        # ----------------------------------------------------

        group_id = gid()

        cells.append(

            f'<mxCell '

            f'id="{group_id}" '

            f'value="{esc(group)}" '

            f'style="'

            f'swimlane;'

            f'html=1;'

            f'rounded=0;'

            f'startSize={GROUP_HEADER};'

            f'fillColor=#141618;'

            f'strokeColor=#3f4448;'

            f'fontStyle=1;'

            f'fontSize=14;'

            f'fontColor=#e5e7eb;'

            f'" '

            f'vertex="1" '

            f'parent="1">'

            f'<mxGeometry '

            f'x="40" '

            f'y="{next_y}" '

            f'width="{canvas_width - 80}" '

            f'height="{group_height}" '

            f'as="geometry"/>'

            f'</mxCell>'
        )

        # ----------------------------------------------------
        # Tables inside group
        # ----------------------------------------------------

        current_y = (
            next_y
            + GROUP_HEADER
            + 20
        )

        for row_index, row in enumerate(rows):

            row_y = current_y

            for column_index, table in enumerate(row):

                x = (

                    MARGIN_X

                    + column_index
                    * (
                        TABLE_W
                        + GAP_X
                    )
                )

                table_id = gid()

                table_ids[table] = table_id

                cls = model_for_table(
                    models,
                    table
                )

                if cls:

                    title = (
                        f"{cls} · {table}"
                    )

                else:

                    title = table

                cells.append(

                    table_cell(

                        title,

                        all_tables[table][
                            "columns"
                        ],

                        x,

                        row_y,

                        TABLE_W,

                        table_id
                    )
                )

            current_y += (
                row_heights[row_index]
                + GAP_Y
            )

        next_y += (
            group_height
            + GROUP_GAP_Y
        )

        total_height = next_y

    # ========================================================
    # Dynamic canvas height
    # ========================================================

    canvas_height = max(
        total_height + MARGIN_Y,
        1200
    )

    # ========================================================
    # Foreign Key relationships
    # ========================================================

    for (
        src,
        src_col,
        dst,
        dst_col
    ) in fks:

        if src not in table_ids:
            continue

        if dst not in table_ids:
            continue

        cells.append(

            f'<mxCell '

            f'id="{gid()}" '

            f'value="{esc(src_col + " → " + dst_col)}" '

            f'style="'

            f'edgeStyle=orthogonalEdgeStyle;'

            f'rounded=0;'

            f'orthogonalLoop=1;'

            f'jettySize=auto;'

            f'html=1;'

            f'endArrow=ERone;'

            f'startArrow=ERmany;'

            f'fontSize=9;'

            f'fontColor=#7d858d;'

            f'strokeColor=#69727b;'

            f'" '

            f'edge="1" '

            f'parent="1" '

            f'source="{table_ids[src]}" '

            f'target="{table_ids[dst]}">'

            f'<mxGeometry '

            f'relative="1" '

            f'as="geometry"/>'

            f'</mxCell>'
        )

    # ========================================================
    # Model relationships
    # ========================================================

    table_by_class = {

        cls: info["table"]

        for cls, info in models.items()
    }

    fk_pairs = {

        (src, dst)

        for src, _, dst, _ in fks
    }

    relation_pairs = set()

    for cls, info in models.items():

        src = info["table"]

        for (
            rel_name,
            target_cls,
            method,
            cardinality
        ) in info["relations"]:

            dst = table_by_class.get(
                target_cls
            )

            if not dst:
                continue

            if src not in table_ids:
                continue

            if dst not in table_ids:
                continue

            # ------------------------------------------------
            # Skip FK already represented
            # ------------------------------------------------

            if (
                src,
                dst
            ) in fk_pairs:

                continue

            relation_key = (
                src,
                dst,
                rel_name,
                method
            )

            if relation_key in relation_pairs:
                continue

            relation_pairs.add(
                relation_key
            )

            cells.append(

                f'<mxCell '

                f'id="{gid()}" '

                f'value="{esc(rel_name + " [" + method + "]")}" '

                f'style="'

                f'edgeStyle=orthogonalEdgeStyle;'

                f'rounded=0;'

                f'dashed=1;'

                f'orthogonalLoop=1;'

                f'jettySize=auto;'

                f'html=1;'

                f'endArrow=none;'

                f'startArrow=none;'

                f'fontSize=8;'

                f'fontColor=#687079;'

                f'strokeColor=#596169;'

                f'" '

                f'edge="1" '

                f'parent="1" '

                f'source="{table_ids[src]}" '

                f'target="{table_ids[dst]}">'

                f'<mxGeometry '

                f'relative="1" '

                f'as="geometry"/>'

                f'</mxCell>'
            )

    # ========================================================
    # Final XML
    # ========================================================

    return f"""<?xml version="1.0" encoding="UTF-8"?>

<mxfile
    host="app.diagrams.net"
    modified="2026-08-19T00:00:00.000Z"
    agent="Laravel-to-drawio-ERD"
    version="24.7.17"
    type="device">

    <diagram
        id="{gid()}"
        name="School Management ERD">

        <mxGraphModel
            dx="{canvas_width}"
            dy="{canvas_height}"
            grid="1"
            gridSize="10"
            guides="1"
            tooltips="1"
            connect="1"
            arrows="1"
            fold="1"
            page="1"
            pageScale="1"
            pageWidth="{canvas_width}"
            pageHeight="{canvas_height}"
            math="0"
            shadow="0"
            background="#0f1113">

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
"""


# ============================================================
# Main
# ============================================================

def main():

    if not MODELS_DIR.exists():

        raise SystemExit(
            "Models directory not found: "
            f"{MODELS_DIR}"
        )

    if not MIGRATIONS_DIR.exists():

        raise SystemExit(
            "Migrations directory not found: "
            f"{MIGRATIONS_DIR}"
        )

    # --------------------------------------------------------
    # Models
    # --------------------------------------------------------

    models = parse_models()

    # --------------------------------------------------------
    # Migrations
    # --------------------------------------------------------

    tables, fks = parse_migrations()

    # --------------------------------------------------------
    # Groups
    # --------------------------------------------------------

    infer_groups(
        models
    )

    # --------------------------------------------------------
    # Build XML
    # --------------------------------------------------------

    xml = build_drawio(
        models,
        tables,
        fks
    )

    # --------------------------------------------------------
    # Write
    # --------------------------------------------------------

    OUT.write_text(
        xml,
        encoding="utf-8"
    )

    # --------------------------------------------------------
    # Console
    # --------------------------------------------------------

    print()

    print(
        "=========================================="
    )

    print(
        " Laravel -> draw.io ERD generated"
    )

    print(
        "=========================================="
    )

    print(
        f"Models found : {len(models)}"
    )

    print(
        f"Tables found : {len(tables)}"
    )

    print(
        f"FKs found    : {len(fks)}"
    )

    print(
        f"Canvas width : {build_canvas_width(tables, models)}"
    )

    print(
        f"Output       : {OUT}"
    )

    print()

    print(
        "Open school-management-erd.drawio "
        "in diagrams.net."
    )

    print(
        "No Laravel backend files were modified."
    )

    print()


# ============================================================
# Canvas helper for console only
# ============================================================

def build_canvas_width(
    tables,
    models
):
    """
    Calculate the same dynamic width used by the ERD.
    """

    grouped = defaultdict(int)

    for table in tables:

        cls = model_for_table(
            models,
            table
        )

        if cls:

            group = models[cls]["group"]

        else:

            group = GROUP_MAP.get(
                table,
                "Other"
            )

        grouped[group] += 1

    max_group_tables = max(
        grouped.values(),
        default=1
    )

    actual_columns = min(
        MAX_PER_ROW,
        max_group_tables
    )

    width = (

        MARGIN_X * 2

        + actual_columns * TABLE_W

        + (actual_columns - 1) * GAP_X

        + 120
    )

    return max(
        width,
        1500
    )


# ============================================================
# Entry Point
# ============================================================

if __name__ == "__main__":
    main()
