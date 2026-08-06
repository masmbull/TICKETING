# MITO IT Helpdesk — Database Documentation

## Overview

This directory contains the complete database design documentation for the MITO IT Helpdesk system. The design follows **Third Normal Form (3NF)** and is optimized for PostgreSQL 18.

## Documentation Index

| # | Document | Description |
|---|----------|-------------|
| 1 | [01-requirements-analysis.md](01-requirements-analysis.md) | Business and functional requirements analysis |
| 2 | [02-entity-definitions.md](02-entity-definitions.md) | All 20 entities with complete attribute definitions |
| 3 | [03-erd.md](03-erd.md) | Normalized ERD with Mermaid diagram and cardinality |
| 4 | [04-data-dictionary.md](04-data-dictionary.md) | Complete data dictionary for all 20 tables (230 columns) |
| 5 | [05-indexes-and-foreign-keys.md](05-indexes-and-foreign-keys.md) | Index recommendations and foreign key constraints |
| 6 | [06-enum-values.md](06-enum-values.md) | All enum values and lookup table seed data |
| 7 | [07-audit-strategy.md](07-audit-strategy.md) | Comprehensive audit and compliance strategy |

## Database Summary

| Aspect | Value |
|--------|-------|
| **Total Tables** | 20 |
| **Master Data Tables** | 12 |
| **Transaction Data Tables** | 8 |
| **Total Columns** | 230 |
| **Total Foreign Keys** | 40 |
| **Normalization Level** | 3NF |
| **Database Engine** | PostgreSQL 18 |

## Entity Groups

### Master Data (12 tables)
`users`, `departments`, `locations`, `categories`, `sub_categories`, `priorities`, `statuses`, `asset_types`, `assets`, `sla_policies`, `holidays`, `knowledge_base`

### Transaction Data (8 tables)
`tickets`, `ticket_comments`, `ticket_attachments`, `ticket_assignments`, `ticket_status_history`, `ticket_escalations`, `ticket_ratings`, `audit_logs`

## Key Design Decisions

1. **Lookup tables** for `priorities` and `statuses` to support dynamic configuration and UI metadata.
2. **Separate history tables** (`ticket_assignments`, `ticket_status_history`) for complete audit trails.
3. **Polymorphic `audit_logs`** table for unified audit across all entities.
4. **SLA policies** as a separate table for configurable service level agreements.
5. **Soft deletes** on all master data and ticket-related tables.
6. **Composite indexes** on high-volume tables for query performance.
7. **Partial indexes** on boolean flags to reduce index size.
8. **GIN index** on `knowledge_base` for full-text search.

## Next Steps

When ready to implement, the database design will be translated into:

1. Laravel migration files
2. Eloquent model definitions
3. Model relationships
4. Database seeders
5. Query optimization