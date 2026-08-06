# MITO IT Helpdesk — Indexes & Foreign Keys

## 1. Index Strategy

### 1.1 Index Types

| Type | Usage |
|------|-------|
| `PRIMARY KEY` | Unique identifier for each row |
| `UNIQUE` | Enforces uniqueness on non-PK columns |
| `BTREE` | Default index for equality and range queries |
| `GIN` | For JSON and full-text search |
| `COMPOSITE` | Multi-column index for combined queries |
| `PARTIAL` | Index on subset of rows (e.g., `WHERE is_active = true`) |

### 1.2 Index Design Principles

1. Index columns used in `WHERE`, `JOIN`, `ORDER BY`, and `GROUP BY` clauses.
2. Use **composite indexes** for frequently combined query conditions.
3. Order composite index columns by **selectivity** (most selective first).
4. Avoid over-indexing — each index adds write overhead.
5. Use **partial indexes** for boolean flags to reduce index size.
6. Use **covering indexes** for frequently accessed columns.

---

## 2. Index Recommendations

### 2.1 `departments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `departments_pkey` | PRIMARY KEY | `id` | Row identity |
| `departments_code_unique` | UNIQUE | `code` | Enforce unique code |
| `departments_manager_id_idx` | BTREE | `manager_id` | FK lookup |
| `departments_is_active_idx` | PARTIAL | `is_active` | Filter active departments |

---

### 2.2 `locations`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `locations_pkey` | PRIMARY KEY | `id` | Row identity |
| `locations_code_unique` | UNIQUE | `code` | Enforce unique code |
| `locations_city_idx` | BTREE | `city` | City-based queries |
| `locations_is_active_idx` | PARTIAL | `is_active` | Filter active locations |

---

### 2.3 `users`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `users_pkey` | PRIMARY KEY | `id` | Row identity |
| `users_employee_id_unique` | UNIQUE | `employee_id` | Enforce unique employee ID |
| `users_email_unique` | UNIQUE | `email` | Enforce unique email |
| `users_department_id_idx` | BTREE | `department_id` | FK lookup |
| `users_location_id_idx` | BTREE | `location_id` | FK lookup |
| `users_role_idx` | BTREE | `role` | Role-based queries |
| `users_is_active_idx` | PARTIAL | `is_active` | Filter active users |
| `users_name_idx` | BTREE | `name` | Name search |

---

### 2.4 `asset_types`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `asset_types_pkey` | PRIMARY KEY | `id` | Row identity |
| `asset_types_code_unique` | UNIQUE | `code` | Enforce unique code |
| `asset_types_is_active_idx` | PARTIAL | `is_active` | Filter active types |

---

### 2.5 `assets`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `assets_pkey` | PRIMARY KEY | `id` | Row identity |
| `assets_asset_tag_unique` | UNIQUE | `asset_tag` | Enforce unique asset tag |
| `assets_serial_number_unique` | UNIQUE | `serial_number` | Enforce unique serial |
| `assets_asset_type_id_idx` | BTREE | `asset_type_id` | FK lookup |
| `assets_status_idx` | BTREE | `status` | Status-based queries |
| `assets_assigned_to_idx` | BTREE | `assigned_to` | FK lookup |
| `assets_location_id_idx` | BTREE | `location_id` | FK lookup |
| `assets_type_status_idx` | COMPOSITE | `asset_type_id`, `status` | Filter by type and status |

---

### 2.6 `categories`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `categories_pkey` | PRIMARY KEY | `id` | Row identity |
| `categories_code_unique` | UNIQUE | `code` | Enforce unique code |
| `categories_default_priority_id_idx` | BTREE | `default_priority_id` | FK lookup |
| `categories_sla_policy_id_idx` | BTREE | `sla_policy_id` | FK lookup |
| `categories_is_active_idx` | PARTIAL | `is_active` | Filter active categories |

---

### 2.7 `sub_categories`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `sub_categories_pkey` | PRIMARY KEY | `id` | Row identity |
| `sub_categories_category_code_unique` | UNIQUE | `category_id`, `code` | Unique per category |
| `sub_categories_category_id_idx` | BTREE | `category_id` | FK lookup |
| `sub_categories_is_active_idx` | PARTIAL | `is_active` | Filter active sub-categories |

---

### 2.8 `priorities`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `priorities_pkey` | PRIMARY KEY | `id` | Row identity |
| `priorities_code_unique` | UNIQUE | `code` | Enforce unique code |
| `priorities_level_idx` | BTREE | `level` | Sort by priority level |
| `priorities_is_active_idx` | PARTIAL | `is_active` | Filter active priorities |

---

### 2.9 `statuses`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `statuses_pkey` | PRIMARY KEY | `id` | Row identity |
| `statuses_code_unique` | UNIQUE | `code` | Enforce unique code |
| `statuses_sort_order_idx` | BTREE | `sort_order` | Sort by display order |
| `statuses_is_active_idx` | PARTIAL | `is_active` | Filter active statuses |

---

### 2.10 `sla_policies`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `sla_policies_pkey` | PRIMARY KEY | `id` | Row identity |
| `sla_policies_category_priority_unique` | UNIQUE | `category_id`, `priority_id` | Unique per category/priority |
| `sla_policies_category_id_idx` | BTREE | `category_id` | FK lookup |
| `sla_policies_priority_id_idx` | BTREE | `priority_id` | FK lookup |
| `sla_policies_is_active_idx` | PARTIAL | `is_active` | Filter active policies |

---

### 2.11 `holidays`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `holidays_pkey` | PRIMARY KEY | `id` | Row identity |
| `holidays_holiday_date_unique` | UNIQUE | `holiday_date` | Enforce unique date |
| `holidays_is_recurring_idx` | BTREE | `is_recurring` | Filter recurring holidays |

---

### 2.12 `knowledge_base`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `knowledge_base_pkey` | PRIMARY KEY | `id` | Row identity |
| `knowledge_base_slug_unique` | UNIQUE | `slug` | Enforce unique slug |
| `knowledge_base_category_id_idx` | BTREE | `category_id` | FK lookup |
| `knowledge_base_author_id_idx` | BTREE | `author_id` | FK lookup |
| `knowledge_base_status_idx` | BTREE | `status` | Status-based queries |
| `knowledge_base_published_at_idx` | BTREE | `published_at` | Sort by publish date |
| `knowledge_base_search_idx` | GIN | `title`, `content` | Full-text search |

---

### 2.13 `tickets` (High-Volume Table)

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `tickets_pkey` | PRIMARY KEY | `id` | Row identity |
| `tickets_ticket_number_unique` | UNIQUE | `ticket_number` | Enforce unique ticket number |
| `tickets_requester_id_idx` | BTREE | `requester_id` | FK lookup |
| `tickets_assignee_id_idx` | BTREE | `assignee_id` | FK lookup |
| `tickets_category_id_idx` | BTREE | `category_id` | FK lookup |
| `tickets_sub_category_id_idx` | BTREE | `sub_category_id` | FK lookup |
| `tickets_priority_id_idx` | BTREE | `priority_id` | FK lookup |
| `tickets_status_id_idx` | BTREE | `status_id` | FK lookup |
| `tickets_asset_id_idx` | BTREE | `asset_id` | FK lookup |
| `tickets_location_id_idx` | BTREE | `location_id` | FK lookup |
| `tickets_sla_due_at_idx` | BTREE | `sla_due_at` | SLA monitoring |
| `tickets_resolution_due_at_idx` | BTREE | `resolution_due_at` | SLA monitoring |
| `tickets_created_at_idx` | BTREE | `created_at` | Date-range queries |
| `tickets_is_sla_breached_idx` | PARTIAL | `is_sla_breached` | SLA breach queries |
| `tickets_status_priority_idx` | COMPOSITE | `status_id`, `priority_id` | Dashboard queries |
| `tickets_assignee_status_idx` | COMPOSITE | `assignee_id`, `status_id` | Agent workload |
| `tickets_requester_created_idx` | COMPOSITE | `requester_id`, `created_at` | User ticket history |
| `tickets_category_created_idx` | COMPOSITE | `category_id`, `created_at` | Category reporting |

---

### 2.14 `ticket_comments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_comments_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_comments_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_comments_user_id_idx` | BTREE | `user_id` | FK lookup |
| `ticket_comments_is_internal_idx` | PARTIAL | `is_internal` | Filter internal notes |
| `ticket_comments_ticket_created_idx` | COMPOSITE | `ticket_id`, `created_at` | Timeline queries |

---

### 2.15 `ticket_attachments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_attachments_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_attachments_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_attachments_comment_id_idx` | BTREE | `comment_id` | FK lookup |
| `ticket_attachments_uploaded_by_idx` | BTREE | `uploaded_by` | FK lookup |

---

### 2.16 `ticket_assignments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_assignments_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_assignments_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_assignments_assigned_by_idx` | BTREE | `assigned_by` | FK lookup |
| `ticket_assignments_assigned_to_idx` | BTREE | `assigned_to` | FK lookup |
| `ticket_assignments_assigned_at_idx` | BTREE | `assigned_at` | Date-range queries |
| `ticket_assignments_ticket_assigned_idx` | COMPOSITE | `ticket_id`, `assigned_at` | Assignment timeline |

---

### 2.17 `ticket_status_history`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_status_history_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_status_history_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_status_history_from_status_id_idx` | BTREE | `from_status_id` | FK lookup |
| `ticket_status_history_to_status_id_idx` | BTREE | `to_status_id` | FK lookup |
| `ticket_status_history_changed_by_idx` | BTREE | `changed_by` | FK lookup |
| `ticket_status_history_changed_at_idx` | BTREE | `changed_at` | Date-range queries |
| `ticket_status_history_ticket_changed_idx` | COMPOSITE | `ticket_id`, `changed_at` | Status timeline |

---

### 2.18 `ticket_escalations`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_escalations_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_escalations_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_escalations_escalation_level_idx` | BTREE | `escalation_level` | Level-based queries |
| `ticket_escalations_escalated_by_idx` | BTREE | `escalated_by` | FK lookup |
| `ticket_escalations_escalated_to_idx` | BTREE | `escalated_to` | FK lookup |
| `ticket_escalations_escalated_at_idx` | BTREE | `escalated_at` | Date-range queries |

---

### 2.19 `ticket_ratings`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_ratings_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_ratings_ticket_id_unique` | UNIQUE | `ticket_id` | One rating per ticket |
| `ticket_ratings_rating_idx` | BTREE | `rating` | Rating distribution |
| `ticket_ratings_rated_by_idx` | BTREE | `rated_by` | FK lookup |
| `ticket_ratings_rated_at_idx` | BTREE | `rated_at` | Date-range queries |

---

### 2.20 `audit_logs` (High-Volume Table)

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `audit_logs_pkey` | PRIMARY KEY | `id` | Row identity |
| `audit_logs_user_id_idx` | BTREE | `user_id` | FK lookup |
| `audit_logs_event_idx` | BTREE | `event` | Event-based queries |
| `audit_logs_auditable_idx` | COMPOSITE | `auditable_type`, `auditable_id` | Entity audit history |
| `audit_logs_created_at_idx` | BTREE | `created_at` | Date-range queries |
| `audit_logs_user_created_idx` | COMPOSITE | `user_id`, `created_at` | User activity timeline |

---

## 3. Foreign Key Recommendations

### 3.1 Foreign Key Summary

| # | Table | Column | References | On Delete | On Update |
|---|-------|--------|------------|-----------|-----------|
| 1 | `users` | `department_id` | `departments.id` | `SET NULL` | `CASCADE` |
| 2 | `users` | `location_id` | `locations.id` | `SET NULL` | `CASCADE` |
| 3 | `departments` | `manager_id` | `users.id` | `SET NULL` | `CASCADE` |
| 4 | `assets` | `asset_type_id` | `asset_types.id` | `RESTRICT` | `CASCADE` |
| 5 | `assets` | `assigned_to` | `users.id` | `SET NULL` | `CASCADE` |
| 6 | `assets` | `location_id` | `locations.id` | `SET NULL` | `CASCADE` |
| 7 | `categories` | `default_priority_id` | `priorities.id` | `SET NULL` | `CASCADE` |
| 8 | `categories` | `sla_policy_id` | `sla_policies.id` | `SET NULL` | `CASCADE` |
| 9 | `sub_categories` | `category_id` | `categories.id` | `CASCADE` | `CASCADE` |
| 10 | `sla_policies` | `category_id` | `categories.id` | `CASCADE` | `CASCADE` |
| 11 | `sla_policies` | `priority_id` | `priorities.id` | `CASCADE` | `CASCADE` |
| 12 | `knowledge_base` | `category_id` | `categories.id` | `SET NULL` | `CASCADE` |
| 13 | `knowledge_base` | `author_id` | `users.id` | `RESTRICT` | `CASCADE` |
| 14 | `tickets` | `requester_id` | `users.id` | `RESTRICT` | `CASCADE` |
| 15 | `tickets` | `assignee_id` | `users.id` | `SET NULL` | `CASCADE` |
| 16 | `tickets` | `category_id` | `categories.id` | `RESTRICT` | `CASCADE` |
| 17 | `tickets` | `sub_category_id` | `sub_categories.id` | `SET NULL` | `CASCADE` |
| 18 | `tickets` | `priority_id` | `priorities.id` | `RESTRICT` | `CASCADE` |
| 19 | `tickets` | `status_id` | `statuses.id` | `RESTRICT` | `CASCADE` |
| 20 | `tickets` | `asset_id` | `assets.id` | `SET NULL` | `CASCADE` |
| 21 | `tickets` | `location_id` | `locations.id` | `SET NULL` | `CASCADE` |
| 22 | `tickets` | `sla_policy_id` | `sla_policies.id` | `SET NULL` | `CASCADE` |
| 23 | `ticket_comments` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 24 | `ticket_comments` | `user_id` | `users.id` | `RESTRICT` | `CASCADE` |
| 25 | `ticket_attachments` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 26 | `ticket_attachments` | `comment_id` | `ticket_comments.id` | `CASCADE` | `CASCADE` |
| 27 | `ticket_attachments` | `uploaded_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 28 | `ticket_assignments` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 29 | `ticket_assignments` | `assigned_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 30 | `ticket_assignments` | `assigned_to` | `users.id` | `RESTRICT` | `CASCADE` |
| 31 | `ticket_status_history` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 32 | `ticket_status_history` | `from_status_id` | `statuses.id` | `SET NULL` | `CASCADE` |
| 33 | `ticket_status_history` | `to_status_id` | `statuses.id` | `RESTRICT` | `CASCADE` |
| 34 | `ticket_status_history` | `changed_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 35 | `ticket_escalations` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 36 | `ticket_escalations` | `escalated_by` | `users.id` | `SET NULL` | `CASCADE` |
| 37 | `ticket_escalations` | `escalated_to` | `users.id` | `SET NULL` | `CASCADE` |
| 38 | `ticket_ratings` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 39 | `ticket_ratings` | `rated_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 40 | `audit_logs` | `user_id` | `users.id` | `SET NULL` | `CASCADE` |

### 3.2 On Delete Behavior Rationale

| Behavior | Used For | Rationale |
|----------|----------|-----------|
| `CASCADE` | Child records (comments, attachments, history) | Delete child records when parent is deleted |
| `RESTRICT` | Critical references (requester, category, priority) | Prevent deletion of referenced records |
| `SET NULL` | Optional references (assignee, location, asset) | Preserve parent record, nullify reference |

---

## 4. Index Maintenance

### 4.1 Monitoring

- Run `pg_stat_user_indexes` to monitor index usage.
- Identify unused indexes with `pg_stat_all_indexes`.
- Rebuild indexes periodically with `REINDEX`.

### 4.2 High-Volume Table Strategy

For `tickets` and `audit_logs` tables:

1. **Partitioning:** Consider partitioning `audit_logs` by month/year after 1M+ rows.
2. **Archiving:** Archive closed tickets older than 2 years to a separate archive table.
3. **Vacuum:** Run `VACUUM ANALYZE` regularly on high-volume tables.
4. **Autovacuum:** Ensure autovacuum is enabled and tuned for high-volume tables.

### 4.3 Query Optimization

- Use `EXPLAIN ANALYZE` to verify index usage.
- Avoid `SELECT *` on high-volume tables.
- Use pagination (`LIMIT`/`OFFSET` or cursor-based) for large result sets.
- Use covering indexes for frequently accessed columns.