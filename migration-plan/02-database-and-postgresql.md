# 02 Database and PostgreSQL

## Source Surface

- `install/db/mysql.sql`
- `install/db/update.sql`
- `install/install.php`
- `class/player_class.php`
- inline ADOdb usage across root PHP files and `includes/*.php`

## Goal

Translate the legacy MySQL schema and ad hoc data access into explicit PostgreSQL structures that support an incremental rewrite.

## Tasks

### MP-02-01: Produce a table ownership map

- Description: Assign each legacy table to one migration module and record which routes read and write it.
- Estimated time: 1.5h
- Dependencies: MP-01-01.
- Acceptance criteria:
  - Every table from `install/db/mysql.sql` is assigned to a module owner.
  - Shared tables such as `players`, `settings`, and `log` have named coordinating modules.
  - Cross-module write hotspots are explicitly called out.
- Technical notes: This prevents accidental schema design by whichever feature is ported first.
- In scope: Ownership matrix and coupling notes.
- Out of scope: Final schema DDL.

### MP-02-02: Create initial PostgreSQL migrations for core tables

- Description: Port the highest-leverage tables first: `players`, `settings`, auth-related tables, logs, and basic catalog tables needed for bootstrap.
- Estimated time: 1.5h
- Dependencies: MP-02-01.
- Acceptance criteria:
  - `sqlx migrate run` creates the initial PostgreSQL schema.
  - Primary keys, unique constraints, and obvious indexes are present.
  - MyISAM-only quirks are removed rather than preserved blindly.
- Technical notes: Do not carry over legacy defaults such as zero dates unless a compatibility need is proven.
- In scope: Initial migration files and core schema.
- Out of scope: Every gameplay table.

### MP-02-03: Design the legacy player field normalization strategy

- Description: Define how `players.settings`, `players.stats`, `players.skills`, and `players.bonuses` move from delimited strings into PostgreSQL-friendly structures.
- Estimated time: 1h
- Dependencies: MP-02-01.
- Acceptance criteria:
  - The plan chooses normalized tables or JSONB per field group and explains why.
  - Compatibility import logic is documented.
  - Derived-stat calculations still have access to all needed data.
- Technical notes: Avoid premature over-normalization for settings, but do normalize values that participate in formulas and filtering.
- In scope: Target data model and import rules.
- Out of scope: Full import implementation.

### MP-02-04: Add PostgreSQL repository scaffolding with explicit SQL

- Description: Create the `data` crate structure, connection pool, transaction helpers, and first query modules using `sqlx`.
- Estimated time: 1h
- Dependencies: MP-02-02.
- Acceptance criteria:
  - The server can open a PostgreSQL pool.
  - Query modules are grouped by domain, not by generic CRUD abstraction.
  - Transaction helpers are available for multi-step workflows.
- Technical notes: Keep row structs close to their SQL and separate from domain entities.
- In scope: Pooling and query module structure.
- Out of scope: Full module repositories.

### MP-02-05: Build a reference-data import command

- Description: Implement the first import path for mostly static or catalog-like data from MySQL SQL dumps into PostgreSQL.
- Estimated time: 1.5h
- Dependencies: MP-01-06, MP-02-02, MP-02-03.
- Acceptance criteria:
  - The import command loads selected reference tables such as item catalogs, monsters, and settings.
  - The command is idempotent or clearly guarded against duplicate runs.
  - Import logs show row counts per table.
- Technical notes: Start with stable tables before mutable player-owned data.
- In scope: Reference-data import path.
- Out of scope: Full production cutover import.

### MP-02-06: Add data reconciliation reporting

- Description: Create a CLI report that compares MySQL and PostgreSQL row counts and critical aggregates for migrated modules.
- Estimated time: 1.5h
- Dependencies: MP-02-04, MP-02-05.
- Acceptance criteria:
  - The report can compare selected tables between legacy and target stores.
  - Mismatches are grouped by severity.
  - The output is usable as a pre-cutover gate.
- Technical notes: Start with counts and sums before attempting full row diffing.
- In scope: Reconciliation command and report format.
- Out of scope: Automated repair.