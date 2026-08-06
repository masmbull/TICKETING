# MITO IT Helpdesk — Database Documentation

## Overview

This directory contains the complete database design documentation for the MITO IT Helpdesk system. The design follows **Third Normal Form (3NF)** and is optimized for PostgreSQL 18.

## Documentation Index

| # | Document | Description |
|---|----------|-------------|
| 1 | [01-requirements-analysis.md](01-requirements-analysis.md) | Business and functional requirements analysis |
| 2 | [02-entity-definitions.md](02-entity-definitions.md) | All entities with complete attribute definitions |
| 3 | [03-erd.md](03-erd.md) | Normalized ERD with Mermaid diagram and cardinality |
| 4 | [04-data-dictionary.md](04-data-dictionary.md) | Complete data dictionary for all tables |
| 5 | [05-indexes-and-foreign-keys.md](05-indexes-and-foreign-keys.md) | Index recommendations and foreign key constraints |
| 6 | [06-enum-values.md](06-enum-values.md) | All configurable values and lookup table seed data |
| 7 | [07-audit-strategy.md](07-audit-strategy.md) | Comprehensive audit and compliance strategy |
| 8 | [08-mvp-database-summary.md](08-mvp-database-summary.md) | MVP database summary, deferred modules, and expansion strategy |

## Database Summary (MVP)

| Aspect | Value |
|--------|-------|
| **Total MVP Tables** | 16 |
| **Master Data Tables** | 8 |
| **Transaction Data Tables** | 8 |
| **Total Columns** | 182 |
| **Total Foreign Keys** | 31 |
| **Normalization Level** | 3NF |
| **Database Engine** | PostgreSQL 18 |
| **Future Module Tables** | 6 (deferred) |

## Entity Groups

### Master Data (8 tables — MVP)
`roles`, `departments`, `categories`, `sub_categories`, `ticket_types`, `priorities`, `statuses`, `sla_policies`

### Transaction Data (8 tables — MVP)
`users`, `tickets`, `ticket_comments`, `attachments`, `ticket_assignments`, `ticket_status_history`, `ticket_escalations`, `audit_logs`

### Future Modules (6 tables — Deferred)
`asset_types`, `assets`, `locations`, `holidays`, `knowledge_base`, `ticket_ratings`

## Key Design Decisions

1. **Master tables** for all configurable business data — no PostgreSQL ENUM for roles, statuses, priorities, categories, or ticket types.
2. **`roles` master table** replaces the role ENUM for flexibility.
3. **`ticket_types` master table** added for Incident, Service Request, Problem, Change Request.
4. **Reusable `attachments` table** with polymorphic `module`/`module_id` design.
5. **Department hierarchy** via `parent_department_id` self-reference.
6. **Enhanced user profile** with `job_title`, `employee_number`, `extension`, `mobile`, `last_password_change_at`.
7. **Separate history tables** (`ticket_assignments`, `ticket_status_history`) for complete audit trails.
8. **Polymorphic `audit_logs`** table for unified audit across all entities.
9. **SLA policies** as a separate table for configurable service level agreements.
10. **Soft deletes** on all master data and ticket-related tables.
11. **Composite indexes** on high-volume tables for query performance.
12. **Partial indexes** on boolean flags to reduce index size.

## Next Steps

When ready to implement, the database design will be translated into:

1. Laravel migration files
2. Eloquent model definitions
3. Model relationships
4. Database seeders
5. Query optimization