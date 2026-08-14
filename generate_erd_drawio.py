#!/usr/bin/env python3

"""
Generate a draw.io ERD from a Laravel project.

Run from the Laravel project root:

    python generate_erd_drawio.py

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
# Utilities
# ============================================================

def clean_php(s: str) -> str:
    """
    Remove PHP comments so regex parsing is cleaner.
    """

    s = re.sub(r"/\*.*?\*/", "", s, flags=re.S)
    s = re.sub(r"//.*", "", s)

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


def model_class(text: str, fallback: str) -> str:
    """
    Extract Laravel model class name.
    """

    m = re.search(
        r"\bclass\s+([A-Za-z_][A-Za-z0-9_]*)",
        text
    )

    return m.group(1) if m else fallback


def table_from_model(class_name: str, text: str) -> str:
    """
    Resolve database table name from Laravel model.
    """

    m = re.search(
        r"protected\s+\$table\s*=\s*['\"]([^'\"]+)['\"]",
        text
    )

    if m:
        return m.group(1)

    irregular = {
        "User": "users",
    }

    if class_name in irregular:
        return irregular[class_name]

    return snake(class_name) + "s"


def esc(s: str) -> str:
    """
    Escape XML / HTML content.
    """

    return html.escape(
        str(s),
        quote=True
    )


def gid() -> str:
    """
    Generate draw.io cell ID.
    """

    return uuid.uuid4().hex[:12]


# ============================================================
# Parse Laravel Models
# ============================================================

def parse_models():
    models = OrderedDict()

    if not MODELS_DIR.exists():
        return models

    for p in sorted(MODELS_DIR.rglob("*.php")):

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
            ("hasMany", "1:N"),
            ("hasOne", "1:1"),
            ("belongsTo", "N:1"),
            ("belongsToMany", "N:N"),
            ("morphMany", "1:N"),
            ("morphOne", "1:1"),
            ("morphTo", "N:1"),
            ("morphToMany", "N:N"),
            ("morphedByMany", "N:N"),
            ("hasManyThrough", "1:N"),
            ("hasOneThrough", "1:1"),
        ]

        for method, cardinality in patterns:

            rx = re.compile(
                rf"function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\([^)]*\).*?"
                rf"return\s+\$this\s*->\s*{method}\s*\(\s*"
                rf"([A-Za-z_][A-Za-z0-9_\\]*)::class",
                re.S,
            )

            for rm in rx.finditer(text):

                rel_name = rm.group(1)

                target = rm.group(2).split("\\")[-1]

                relations.append(
                    (
                        rel_name,
                        target,
                        method,
                        cardinality
                    )
                )

        models[cls] = {
            "class": cls,
            "table": table,
            "path": str(p.relative_to(ROOT)),
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

    for p in sorted(MIGRATIONS_DIR.rglob("*.php")):

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
                    "path": str(p.relative_to(ROOT))
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
                    "path": str(p.relative_to(ROOT))
                }

            parse_table_block(
                table,
                block,
                tables,
                fks
            )

    # --------------------------------------------------------
    # Remove duplicate foreign keys
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
    # Special column definitions
    # --------------------------------------------------------

    special = [

        (
            r"\$table->id\s*\(\s*['\"]?([^'\")]*)['\"]?\s*\)",
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

            name = m.group(1) or "id"

            cols.setdefault(
                name,
                [typ, flag]
            )

    # --------------------------------------------------------
    # Laravel column methods
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
            rf"\(\s*['\"]([^'\"]+)['\"]([^)]*)\)"
        )

        for m in rx.finditer(block):

            name = m.group(1)

            arg = m.group(2)

            display_type = typ

            if method in (
                "string",
                "decimal",
                "float",
                "double",
                "enum"
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
    # foreignId()
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

        if ref_table:

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
# Model / Table helpers
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
# Module grouping
# ============================================================

def infer_groups(models):

    group_map = {

        # ----------------------------------------------------
        # Auth
        # ----------------------------------------------------

        "users": "Auth & People",
        "roles": "Auth & People",
        "permissions": "Auth & People",
        "model_has_roles": "Auth & People",
        "model_has_permissions": "Auth & People",

        # ----------------------------------------------------
        # Students / Academic
        # ----------------------------------------------------

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

        # ----------------------------------------------------
        # Assessment
        # ----------------------------------------------------

        "assessment_components": "Assessment & Learning",
        "student_marks": "Assessment & Learning",

        "practice_quizzes": "Assessment & Learning",
        "questions": "Assessment & Learning",
        "options": "Assessment & Learning",

        "student_quiz_attempts": "Assessment & Learning",
        "student_quiz_attempt_answers": "Assessment & Learning",

        "study_materials": "Assessment & Learning",
        "homeworks": "Assessment & Learning",

        # ----------------------------------------------------
        # Scheduling
        # ----------------------------------------------------

        "schedules": "Scheduling",
        "schedule_entries": "Scheduling",
        "schedule_time_slots": "Scheduling",

        "time_slots": "Scheduling",
        "days": "Scheduling",

        # ----------------------------------------------------
        # Staff / HR
        # ----------------------------------------------------

        "teacher_assignments": "Staff & HR",
        "teacher_workloads": "Staff & HR",

        "teacher_evaluations": "Staff & HR",
        "teacher_period_attendances": "Staff & HR",

        "staff": "Staff & HR",
        "staff_attendances": "Staff & HR",

        "staff_leaves": "Staff & HR",
        "staff_leave_types": "Staff & HR",

        # ----------------------------------------------------
        # Finance
        # ----------------------------------------------------

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

        # ----------------------------------------------------
        # Communication
        # ----------------------------------------------------

        "complaints": "Communication",
        "complaint_categories": "Communication",
        "complaint_types": "Communication",

        "announcements": "Communication",
        "activities": "Communication",
        "alerts": "Communication",

        # ----------------------------------------------------
        # Content
        # ----------------------------------------------------

        "contents": "Content",
    }

    for cls, info in models.items():

        info["group"] = group_map.get(
            info["table"],
            "Other"
        )


# ============================================================
# Draw.io Table Cell
# ============================================================

def table_cell(
    title,
    columns,
    x,
    y,
    w=360
):

    rows = []

    # --------------------------------------------------------
    # Columns
    # --------------------------------------------------------

    for name, (typ, flag) in columns.items():

        key = (
            "PK"
            if "PK" in flag
            else (
                "FK"
                if "FK" in flag
                else ""
            )
        )

        rows.append(
            f"<TR>"

            f"<TD "
            f"ALIGN='LEFT' "
            f"VALIGN='MIDDLE' "
            f"WIDTH='215'>"
            f"<B>{esc(name)}</B>"
            f"</TD>"

            f"<TD "
            f"ALIGN='LEFT' "
            f"VALIGN='MIDDLE' "
            f"WIDTH='120'>"
            f"{esc(typ)}"
            f"</TD>"

            f"<TD "
            f"ALIGN='CENTER' "
            f"VALIGN='MIDDLE' "
            f"WIDTH='45'>"
            f"<B>{key}</B>"
            f"</TD>"

            f"</TR>"
        )

    # --------------------------------------------------------
    # Empty table
    # --------------------------------------------------------

    if not rows:

        rows.append(
            "<TR>"
            "<TD "
            "COLSPAN='3' "
            "ALIGN='LEFT'>"
            "No migration columns parsed"
            "</TD>"
            "</TR>"
        )

    # --------------------------------------------------------
    # HTML table
    #
    # Important:
    # No colored title background.
    # --------------------------------------------------------

    label = (

        "<TABLE "
        "BORDER='0' "
        "CELLBORDER='1' "
        "CELLSPACING='0' "
        "CELLPADDING='7' "
        "STYLE='font-family:Arial;font-size:12px;'>"

        # ----------------------------------------------------
        # Title
        # ----------------------------------------------------

        "<TR>"

        "<TD "
        "COLSPAN='3' "
        "ALIGN='LEFT' "
        "VALIGN='MIDDLE' "
        "HEIGHT='34'>"

        f"<B>{esc(title)}</B>"

        "</TD>"

        "</TR>"

        # ----------------------------------------------------
        # Column headers
        # ----------------------------------------------------

        "<TR>"

        "<TD ALIGN='LEFT'>"
        "<B>Column</B>"
        "</TD>"

        "<TD ALIGN='LEFT'>"
        "<B>Type</B>"
        "</TD>"

        "<TD ALIGN='CENTER'>"
        "<B>Key</B>"
        "</TD>"

        "</TR>"

        # ----------------------------------------------------
        # Rows
        # ----------------------------------------------------

        + "".join(rows)

        + "</TABLE>"
    )

    # --------------------------------------------------------
    # Dynamic height
    # --------------------------------------------------------

    row_height = 28

    header_height = 72

    h = max(
        110,
        header_height +
        len(rows) * row_height
    )

    # --------------------------------------------------------
    # Draw.io rectangle
    #
    # rounded=0 => sharp corners
    # overflow=hidden => content stays inside
    # --------------------------------------------------------

    return (

        f'<mxCell '

        f'id="{gid()}" '

        f'value="{esc(label)}" '

        f'style="'

        f'shape=rectangle;'

        f'whiteSpace=wrap;'

        f'html=1;'

        f'rounded=0;'

        f'fillColor=#ffffff;'

        f'strokeColor=#475569;'

        f'strokeWidth=1;'

        f'align=left;'

        f'verticalAlign=top;'

        f'spacing=6;'

        f'fontSize=12;'

        f'fontColor=#1e293b;'

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

        if info["table"] not in all_tables:

            all_tables[info["table"]] = {

                "columns": info["columns"],

                "path": info["path"],
            }

    # --------------------------------------------------------
    # Group tables
    # --------------------------------------------------------

    grouped = defaultdict(list)

    for table in all_tables:

        cls = model_for_table(
            models,
            table
        )

        group = (
            models[cls]["group"]
            if cls
            else "Other"
        )

        grouped[group].append(table)

    # --------------------------------------------------------
    # Group ordering
    # --------------------------------------------------------

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

    # ========================================================
    # Layout
    # ========================================================

    margin_x = 70

    table_w = 360

    gap_x = 60

    gap_y = 90

    group_header = 42

    # Three tables per row.
    max_per_row = 3

    positions = {}

    table_ids = {}

    cells = []

    next_y = 70

    # ========================================================
    # Generate Groups
    # ========================================================

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

        # ----------------------------------------------------
        # Calculate row heights
        # ----------------------------------------------------

        for row in rows:

            max_h = 120

            for table in row:

                max_h = max(
                    max_h,

                    72 +
                    len(
                        all_tables[table]["columns"]
                    ) * 28
                )

            row_heights.append(
                max_h
            )

        # ----------------------------------------------------
        # Group height
        # ----------------------------------------------------

        group_h = (

            group_header

            + sum(row_heights)

            + (len(rows) - 1) * 25

            + 30
        )

        # ----------------------------------------------------
        # Group Swimlane
        #
        # Very light background.
        # Tables remain white.
        # ----------------------------------------------------

        cells.append(

            f'<mxCell '

            f'id="{gid()}" '

            f'value="{esc(group)}" '

            f'style="'

            f'swimlane;'

            f'html=1;'

            f'rounded=0;'

            f'startSize={group_header};'

            f'fillColor=#f8fafc;'

            f'strokeColor=#cbd5e1;'

            f'fontStyle=1;'

            f'fontSize=14;'

            f'fontColor=#334155;'

            f'" '

            f'vertex="1" '

            f'parent="1">'

            f'<mxGeometry '

            f'x="40" '

            f'y="{next_y - 20}" '

            f'width="1680" '

            f'height="{group_h}" '

            f'as="geometry"/>'

            f'</mxCell>'
        )

        # ----------------------------------------------------
        # Tables inside group
        # ----------------------------------------------------

        y = next_y + group_header

        for ri, row in enumerate(rows):

            for ci, table in enumerate(row):

                x = (

                    margin_x

                    + ci *
                    (table_w + gap_x)
                )

                positions[table] = (
                    x,
                    y
                )

                tid = gid()

                table_ids[table] = tid

                cls = model_for_table(
                    models,
                    table
                )

                if cls:

                    title = (
                        f"{cls}  ·  {table}"
                    )

                else:

                    title = table

                cells.append(
                    table_cell(
                        title,
                        all_tables[table]["columns"],
                        x,
                        y,
                        table_w
                    )
                )

            y += (
                row_heights[ri]
                + 25
            )

        next_y += (
            group_h
            + gap_y
        )

    # ========================================================
    # Foreign Key Edges
    # ========================================================

    for src, src_col, dst, dst_col in fks:

        if (
            src not in table_ids
            or dst not in table_ids
        ):
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

            f'fontColor=#64748b;'

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
    # Model Relationships
    # ========================================================

    table_by_class = {

        cls: info["table"]

        for cls, info in models.items()
    }

    fk_pairs = {

        (a, c)

        for a, _, c, _ in fks
    }

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

            if (
                not dst
                or src not in table_ids
                or dst not in table_ids
            ):
                continue

            # Do not duplicate a relationship
            # already represented by an FK.
            if (
                src,
                dst
            ) in fk_pairs:
                continue

            cells.append(

                f'<mxCell '

                f'id="{gid()}" '

                f'value="{esc(rel_name + " [" + method + "]")}" '

                f'style="'

                f'edgeStyle=orthogonalEdgeStyle;'

                f'rounded=0;'

                f'dashed=1;'

                f'html=1;'

                f'endArrow=none;'

                f'startArrow=none;'

                f'fontSize=8;'

                f'fontColor=#64748b;'

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
    # Draw.io XML
    # ========================================================

    return f"""<?xml version="1.0" encoding="UTF-8"?>

<mxfile
    host="app.diagrams.net"
    modified="2026-08-15T00:00:00.000Z"
    agent="Laravel-to-drawio-ERD"
    version="24.7.17"
    type="device">

    <diagram
        id="{gid()}"
        name="School Management ERD">

        <mxGraphModel
            dx="1800"
            dy="1200"
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
"""


# ============================================================
# Main
# ============================================================

def main():

    if (
        not MODELS_DIR.exists()
        or not MIGRATIONS_DIR.exists()
    ):

        raise SystemExit(

            "Run this script from the root "
            "of your Laravel project "
            "(the folder containing app/ "
            "and database/)."
        )

    # --------------------------------------------------------
    # Read Models
    # --------------------------------------------------------

    models = parse_models()

    # --------------------------------------------------------
    # Read Migrations
    # --------------------------------------------------------

    tables, fks = parse_migrations()

    # --------------------------------------------------------
    # Assign groups
    # --------------------------------------------------------

    infer_groups(models)

    # --------------------------------------------------------
    # Generate XML
    # --------------------------------------------------------

    xml = build_drawio(
        models,
        tables,
        fks
    )

    # --------------------------------------------------------
    # Write output
    # --------------------------------------------------------

    OUT.write_text(
        xml,
        encoding="utf-8"
    )

    # --------------------------------------------------------
    # Console output
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


if __name__ == "__main__":
    main()