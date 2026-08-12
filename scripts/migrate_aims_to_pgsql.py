#!/usr/bin/env python3
"""Copy data from MariaDB (aims) to PostgreSQL (cardiac), skipping sensitive/system tables.

Default mode is dry-run. Use --run --yes to execute data copy.
"""

from __future__ import annotations

import argparse
from dataclasses import dataclass
from decimal import Decimal
from typing import Any, Iterable

import pymysql
import psycopg2
from psycopg2 import sql
from psycopg2.extras import execute_values


DEFAULT_SKIP_TABLES = {
    "password_reset_tokens",
    "model_has_roles",
    "model_has_permissions",
    "migrations",
    "personal_access_tokens",
    "roles",
    "role_has_permissions",
    "sessions",
    "users",
    "patient_test_carts",
    "cache",
    "cache_locks",
    "permissions",
    "lab_tests",
    "team_invitations",
    "teams",
    "team_user",
}

PRIORITY_IMPORT_SEQUENCE = [
    "departments",
    "districts",
    "tehsils",
    "fee_categories",
    "fee_types",
    "government_departments",
    "patient_attendant_relations",
    "patients",
    "invoices",
    "patient_tests",
]

INTEGER_BOUNDS = {
    "smallint": (-32768, 32767),
    "integer": (-2147483648, 2147483647),
    "bigint": (-9223372036854775808, 9223372036854775807),
}


@dataclass
class DbConfig:
    host: str
    port: int
    database: str
    user: str
    password: str


@dataclass
class TableResult:
    table: str
    copied_rows: int
    skipped: bool
    reason: str = ""
    source_rows: int = 0
    destination_rows: int = 0
    row_count_matched: bool = False


@dataclass
class SequenceSyncResult:
    table: str
    column: str
    sequence_name: str
    next_value: int


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Migrate data from MariaDB (source) to PostgreSQL (destination)."
    )

    parser.add_argument("--source-host", default="127.0.0.1")
    parser.add_argument("--source-port", type=int, default=3306)
    parser.add_argument("--source-db", default="aims")
    parser.add_argument("--source-user", default="root")
    parser.add_argument("--source-password", default="")

    parser.add_argument("--dest-host", default="127.0.0.1")
    parser.add_argument("--dest-port", type=int, default=5432)
    parser.add_argument("--dest-db", default="cardiac")
    parser.add_argument("--dest-user", default="postgres")
    parser.add_argument("--dest-password", default="03008169924")

    parser.add_argument(
        "--exclude",
        nargs="*",
        default=sorted(DEFAULT_SKIP_TABLES),
        help="Table names to skip.",
    )
    parser.add_argument(
        "--batch-size",
        type=int,
        default=5000,
        help="Batch size for inserts.",
    )
    parser.add_argument(
        "--run",
        action="store_true",
        help="Actually copy data. Without this flag script runs in dry-run mode.",
    )
    parser.add_argument(
        "--yes",
        action="store_true",
        help="Confirm execution in run mode.",
    )

    return parser.parse_args()


def get_mariadb_connection(cfg: DbConfig):
    return pymysql.connect(
        host=cfg.host,
        port=cfg.port,
        user=cfg.user,
        password=cfg.password,
        database=cfg.database,
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True,
    )


def get_postgres_connection(cfg: DbConfig):
    return psycopg2.connect(
        host=cfg.host,
        port=cfg.port,
        user=cfg.user,
        password=cfg.password,
        dbname=cfg.database,
    )


def fetch_source_tables(source_conn) -> list[str]:
    with source_conn.cursor() as cursor:
        cursor.execute("SHOW TABLES")
        rows = cursor.fetchall()

    tables = []
    for row in rows:
        # SHOW TABLES returns one dynamic key like Tables_in_aims
        first_value = next(iter(row.values()))
        tables.append(str(first_value))
    return sorted(tables)


def destination_table_exists(dest_conn, table: str) -> bool:
    with dest_conn.cursor() as cursor:
        cursor.execute(
            """
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = %s
            LIMIT 1
            """,
            (table,),
        )
        return cursor.fetchone() is not None


def get_source_columns(source_conn, table: str) -> set[str]:
    with source_conn.cursor() as cursor:
        cursor.execute(f"SHOW COLUMNS FROM `{table}`")
        rows = cursor.fetchall()
    return {row["Field"] for row in rows}


def get_dest_columns_and_types(dest_conn, table: str) -> list[tuple[str, str]]:
    with dest_conn.cursor() as cursor:
        cursor.execute(
            """
            SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = %s
            ORDER BY ordinal_position
            """,
            (table,),
        )
        return [(row[0], row[1]) for row in cursor.fetchall()]


def get_source_row_count(source_conn, table: str) -> int:
    with source_conn.cursor() as cursor:
        cursor.execute(f"SELECT COUNT(*) AS total_rows FROM `{table}`")
        return int(cursor.fetchone()["total_rows"])


def get_destination_row_count(dest_conn, table: str) -> int:
    with dest_conn.cursor() as cursor:
        stmt = sql.SQL("SELECT COUNT(*) FROM {}") .format(sql.Identifier(table))
        cursor.execute(stmt)
        return int(cursor.fetchone()[0])


def build_import_order(source_tables: list[str], excluded: set[str]) -> list[str]:
    available = set(source_tables)
    ordered: list[str] = []

    for table in PRIORITY_IMPORT_SEQUENCE:
        if table in available and table not in excluded:
            ordered.append(table)

    for table in sorted(source_tables):
        if table not in excluded and table not in ordered:
            ordered.append(table)

    return ordered


def sync_postgres_sequences(dest_conn, table_names: list[str]) -> list[SequenceSyncResult]:
    synced: list[SequenceSyncResult] = []

    with dest_conn.cursor() as cursor:
        for table in table_names:
            cursor.execute(
                """
                SELECT column_name
                FROM information_schema.columns
                WHERE table_schema = 'public'
                  AND table_name = %s
                  AND (
                    identity_generation IS NOT NULL
                                        OR column_default LIKE 'nextval(%%'
                  )
                ORDER BY ordinal_position
                """,
                (table,),
            )
            auto_columns = [row[0] for row in cursor.fetchall()]

            for column in auto_columns:
                sequence_stmt = sql.SQL("SELECT pg_get_serial_sequence({}, {})").format(
                    sql.Literal(f"public.{table}"),
                    sql.Literal(column),
                )
                cursor.execute(sequence_stmt)
                sequence_name = cursor.fetchone()[0]
                if not sequence_name:
                    continue

                max_stmt = sql.SQL("SELECT COALESCE(MAX({}), 0) FROM {}") .format(
                    sql.Identifier(column),
                    sql.Identifier(table),
                )
                cursor.execute(max_stmt)
                next_value = int(cursor.fetchone()[0]) + 1

                cursor.execute("SELECT setval(%s, %s, false)", (sequence_name, next_value))

                synced.append(
                    SequenceSyncResult(
                        table=table,
                        column=column,
                        sequence_name=sequence_name,
                        next_value=next_value,
                    )
                )

    return synced


def sanitize_value(value: Any, dest_type: str) -> Any:
    if value is None:
        return None

    if dest_type == "boolean":
        if isinstance(value, bool):
            return value
        if isinstance(value, (int, Decimal)):
            return bool(value)
        if isinstance(value, str):
            lowered = value.strip().lower()
            if lowered in {"1", "t", "true", "yes", "y"}:
                return True
            if lowered in {"0", "f", "false", "no", "n"}:
                return False

    if isinstance(value, str) and dest_type in {"date", "timestamp without time zone", "timestamp with time zone"}:
        # MariaDB may contain invalid zero-dates that PostgreSQL rejects.
        if value.startswith("0000-00-00"):
            return None

    if dest_type in INTEGER_BOUNDS:
        if isinstance(value, bool):
            return int(value)

        if isinstance(value, (int, Decimal)):
            int_value = int(value)
        elif isinstance(value, str):
            cleaned = value.strip()
            if cleaned == "":
                return None
            try:
                int_value = int(cleaned)
            except ValueError:
                return None
        else:
            return None

        min_value, max_value = INTEGER_BOUNDS[dest_type]
        if int_value < min_value or int_value > max_value:
            return None

        return int_value

    return value


def quote_mysql_columns(columns: Iterable[str]) -> str:
    return ", ".join(f"`{col}`" for col in columns)


def copy_table(
    source_conn,
    dest_conn,
    table: str,
    common_columns: list[tuple[str, str]],
    source_row_count: int,
    batch_size: int,
    dry_run: bool,
) -> int:
    column_names = [name for name, _ in common_columns]
    dest_types = [dtype for _, dtype in common_columns]

    quoted_mysql_cols = quote_mysql_columns(column_names)

    if dry_run:
        return source_row_count

    with dest_conn.cursor() as dest_cursor:
        truncate_stmt = sql.SQL("TRUNCATE TABLE {} RESTART IDENTITY CASCADE").format(
            sql.Identifier(table)
        )
        dest_cursor.execute(truncate_stmt)

    insert_stmt = sql.SQL("INSERT INTO {} ({}) VALUES %s").format(
        sql.Identifier(table),
        sql.SQL(", ").join(sql.Identifier(col) for col in column_names),
    )

    copied = 0
    with source_conn.cursor() as src_cursor, dest_conn.cursor() as dest_cursor:
        src_cursor.execute(f"SELECT {quoted_mysql_cols} FROM `{table}`")

        while True:
            rows = src_cursor.fetchmany(batch_size)
            if not rows:
                break

            prepared_rows = []
            for row in rows:
                prepared_rows.append(
                    tuple(
                        sanitize_value(row[column_names[i]], dest_types[i])
                        for i in range(len(column_names))
                    )
                )

            execute_values(dest_cursor, insert_stmt.as_string(dest_conn), prepared_rows, page_size=batch_size)
            copied += len(prepared_rows)

    return copied


def main() -> None:
    args = parse_args()

    dry_run = not args.run
    if args.run and not args.yes:
        raise SystemExit("Run mode requires --yes confirmation. Example: --run --yes")

    source_cfg = DbConfig(
        host=args.source_host,
        port=args.source_port,
        database=args.source_db,
        user=args.source_user,
        password=args.source_password,
    )
    dest_cfg = DbConfig(
        host=args.dest_host,
        port=args.dest_port,
        database=args.dest_db,
        user=args.dest_user,
        password=args.dest_password,
    )

    excluded = {table.strip() for table in args.exclude if table.strip()}

    print("=" * 72)
    print("AIMS -> CARDIAC migration script")
    print(f"Mode: {'DRY RUN' if dry_run else 'EXECUTE'}")
    print(f"Source DB: {source_cfg.database} @ {source_cfg.host}:{source_cfg.port}")
    print(f"Destination DB: {dest_cfg.database} @ {dest_cfg.host}:{dest_cfg.port}")
    print(f"Excluded tables: {', '.join(sorted(excluded))}")
    print("=" * 72)

    source_conn = get_mariadb_connection(source_cfg)
    dest_conn = get_postgres_connection(dest_cfg)

    results: list[TableResult] = []
    synced_sequences: list[SequenceSyncResult] = []

    try:
        source_tables = fetch_source_tables(source_conn)
        ordered_tables = build_import_order(source_tables, excluded)

        for table in sorted(source_tables):
            if table in excluded:
                results.append(TableResult(table=table, copied_rows=0, skipped=True, reason="excluded"))
        
        for table in ordered_tables:

            if not destination_table_exists(dest_conn, table):
                results.append(TableResult(table=table, copied_rows=0, skipped=True, reason="missing_in_destination"))
                continue

            source_columns = get_source_columns(source_conn, table)
            dest_columns_and_types = get_dest_columns_and_types(dest_conn, table)
            common_columns = [(name, dtype) for name, dtype in dest_columns_and_types if name in source_columns]

            if not common_columns:
                results.append(TableResult(table=table, copied_rows=0, skipped=True, reason="no_common_columns"))
                continue

            source_row_count = get_source_row_count(source_conn, table)

            copied_rows = copy_table(
                source_conn=source_conn,
                dest_conn=dest_conn,
                table=table,
                common_columns=common_columns,
                source_row_count=source_row_count,
                batch_size=args.batch_size,
                dry_run=dry_run,
            )
            destination_rows = 0
            matched = False
            if not dry_run:
                destination_rows = get_destination_row_count(dest_conn, table)
                matched = destination_rows == source_row_count

            results.append(
                TableResult(
                    table=table,
                    copied_rows=copied_rows,
                    skipped=False,
                    source_rows=source_row_count,
                    destination_rows=destination_rows,
                    row_count_matched=matched,
                )
            )
            print(f"{table}: {copied_rows} row(s) {'would be copied' if dry_run else 'copied'}")

        if not dry_run:
            imported_tables = [item.table for item in results if not item.skipped]
            synced_sequences = sync_postgres_sequences(dest_conn, imported_tables)
            dest_conn.commit()
    except Exception:
        if not dry_run:
            dest_conn.rollback()
        raise
    finally:
        source_conn.close()
        dest_conn.close()

    copied_total = sum(item.copied_rows for item in results if not item.skipped)
    skipped_total = sum(1 for item in results if item.skipped)
    mismatched_total = sum(
        1 for item in results if not item.skipped and not dry_run and not item.row_count_matched
    )

    print("\nSummary")
    print("-" * 72)
    print(f"Tables processed: {len(results)}")
    print(f"Rows {'to copy' if dry_run else 'copied'}: {copied_total}")
    print(f"Tables skipped: {skipped_total}")
    if not dry_run:
        print(f"Row-count mismatches: {mismatched_total}")
        print(f"Sequences synced: {len(synced_sequences)}")

    if skipped_total:
        print("\nSkipped detail:")
        for item in results:
            if item.skipped:
                print(f"- {item.table}: {item.reason}")

    if not dry_run:
        print("\nReconciliation detail (source vs destination):")
        for item in results:
            if item.skipped:
                continue
            status = "OK" if item.row_count_matched else "MISMATCH"
            print(
                f"- {item.table}: source={item.source_rows}, destination={item.destination_rows}, status={status}"
            )

        print("\nSequence sync detail:")
        if synced_sequences:
            for item in synced_sequences:
                print(
                    f"- {item.table}.{item.column}: sequence={item.sequence_name}, next={item.next_value}"
                )
        else:
            print("- No auto-increment sequences found on imported tables.")


if __name__ == "__main__":
    main()
