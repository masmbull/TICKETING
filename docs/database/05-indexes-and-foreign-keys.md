# MITO IT Helpdesk — Indexes & Foreign Keys (MVP)

## 1. Index Strategy

### 1.1 Index Types

| Type | Usage |
|------|-------|
| `PRIMARY KEY` | Unique identifier for each row |
| `UNIQUE` | Enforces uniqueness on non-PK columns |
| `BTREE` | Default index for equality and range queries |
| `COMPOSITE` | Multi-column index for combined queries |
| `PARTIAL` | Index on subset of rows (e.g., `WHERE is_active = true`) |

### 1.2 Index Design Principles

1. Index columns used in `WHERE`, `JOIN`, `ORDER BY`, and `GROUP BY` clauses.
2. Use **composite indexes** for frequently combined query conditions.
3. Order composite index columns by **selectivity** (most selective first).
4. Avoid over-indexing — each index adds write overhead.
5. Use **partial indexes** for boolean flags to reduce index size.

---

## 2. Index Recommendations

### 2.1 `roles`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `roles_pkey` | PRIMARY KEY | `id` | Row identity |
| `roles_name_unique` | UNIQUE | `name` | Enforce unique role name |

---

### 2.2 `departments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `departments_pkey` | PRIMARY KEY | `id` | Row identity |
| `departments_code_unique` | UNIQUE | `code` | Enforce unique code |
| `departments_parent_department_id_idx` | BTREE | `parent_department_id` | FK lookup |
| `departments_manager_id_idx` | BTREE | `manager_id` | FK lookup |
| `departments_is_active_idx` | PARTIAL | `is_active` | Filter active departments |

---

### 2.3 `categories`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `categories_pkey` | PRIMARY KEY | `id` | Row identity |
| `categories_code_unique` | UNIQUE | `code` | Enforce unique code |
| `categories_default_priority_id_idx` | BTREE | `default_priority_id` | FK lookup |
| `categories_sla_policy_id_idx` | BTREE | `sla_policy_id` | FK lookup |
| `categories_is_active_idx` | PARTIAL | `is_active` | Filter active categories |

---

### 2.4 `sub_categories`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `sub_categories_pkey` | PRIMARY KEY | `id` | Row identity |
| `sub_categories_category_code_unique` | UNIQUE | `category_id`, `code` | Unique per category |
| `sub_categories_category_id_idx` | BTREE | `category_id` | FK lookup |
| `sub_categories_is_active_idx` | PARTIAL | `is_active` | Filter active sub-categories |

---

### 2.5 `ticket_types`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_types_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_types_code_unique` | UNIQUE | `code` | Enforce unique code |
| `ticket_types_is_active_idx` | PARTIAL | `is_active` | Filter active types |

---

### 2.6 `priorities`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `priorities_pkey` | PRIMARY KEY | `id` | Row identity |
| `priorities_code_unique` | UNIQUE | `code` | Enforce unique code |
| `priorities_level_idx` | BTREE | `level` | Sort by priority level |
| `priorities_is_active_idx` | PARTIAL | `is_active` | Filter active priorities |

---

### 2.7 `statuses`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `statuses_pkey` | PRIMARY KEY | `id` | Row identity |
| `statuses_code_unique` | UNIQUE | `code` | Enforce unique code |
| `statuses_sort_order_idx` | BTREE | `sort_order` | Sort by display order |
| `statuses_is_active_idx` | PARTIAL | `is_active` | Filter active statuses |

---

### 2.8 `sla_policies`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `sla_policies_pkey` | PRIMARY KEY | `id` | Row identity |
| `sla_policies_category_priority_unique` | UNIQUE | `category_id`, `priority_id` | Unique per category/priority |
| `sla_policies_category_id_idx` | BTREE | `category_id` | FK lookup |
| `sla_policies_priority_id_idx` | BTREE | `priority_id` | FK lookup |
| `sla_policies_is_active_idx` | PARTIAL | `is_active` | Filter active policies |

---

### 2.9 `users`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `users_pkey` | PRIMARY KEY | `id` | Row identity |
| `users_employee_number_unique` | UNIQUE | `employee_number` | Enforce unique employee number |
| `users_email_unique` | UNIQUE | `email` | Enforce unique email |
| `users_department_id_idx` | BTREE | `department_id` | FK lookup |
| `users_role_id_idx` | BTREE | `role_id` | FK lookup |
| `users_is_active_idx` | PARTIAL | `is_active` | Filter active users |
| `users_name_idx` | BTREE | `name` | Name search |

---

### 2.10 `tickets` (High-Volume Table)

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `tickets_pkey` | PRIMARY KEY | `id` | Row identity |
| `tickets_ticket_number_unique` | UNIQUE | `ticket_number` | Enforce unique ticket number |
| `tickets_ticket_type_id_idx` | BTREE | `ticket_type_id` | FK lookup |
| `tickets_category_id_idx` | BTREE | `category_id` | FK lookup |
| `tickets_sub_category_id_idx` | BTREE | `sub_category_id` | FK lookup |
| `tickets_priority_id_idx` | BTREE | `priority_id` | FK lookup |
| `tickets_status_id_idx` | BTREE | `status_id` | FK lookup |
| `tickets_requester_id_idx` | BTREE | `requester_id` | FK lookup |
| `tickets_assigned_to_idx` | BTREE | `assigned_to` | FK lookup |
| `tickets_due_response_at_idx` | BTREE | `due_response_at` | SLA monitoring |
| `tickets_due_resolve_at_idx` | BTREE | `due_resolve_at` | SLA monitoring |
| `tickets_created_at_idx` | BTREE | `created_at` | Date-range queries |
| `tickets_is_sla_breached_idx` | PARTIAL | `is_sla_breached` | SLA breach queries |
| `tickets_status_priority_idx` | COMPOSITE | `status_id`, `priority_id` | Dashboard queries |
| `tickets_assigned_status_idx` | COMPOSITE | `assigned_to`, `status_id` | Agent workload |
| `tickets_requester_created_idx` | COMPOSITE | `requester_id`, `created_at` | User ticket history |
| `tickets_category_created_idx` | COMPOSITE | `category_id`, `created_at` | Category reporting |

---

### 2.11 `ticket_comments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_comments_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_comments_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_comments_user_id_idx` | BTREE | `user_id` | FK lookup |
| `ticket_comments_is_internal_idx` | PARTIAL | `is_internal` | Filter internal notes |
| `ticket_comments_ticket_created_idx` | COMPOSITE | `ticket_id`, `created_at` | Timeline queries |

---

### 2.12 `attachments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `attachments_pkey` | PRIMARY KEY | `id` | Row identity |
| `attachments_module_module_id_idx` | COMPOSITE | `module`, `module_id` | Module lookup |
| `attachments_uploaded_by_idx` | BTREE | `uploaded_by` | FK lookup |

---

### 2.13 `ticket_assignments`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_assignments_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_assignments_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_assignments_assigned_by_idx` | BTREE | `assigned_by` | FK lookup |
| `ticket_assignments_assigned_to_idx` | BTREE | `assigned_to` | FK lookup |
| `ticket_assignments_assigned_at_idx` | BTREE | `assigned_at` | Date-range queries |
| `ticket_assignments_ticket_assigned_idx` | COMPOSITE | `ticket_id`, `assigned_at` | Assignment timeline |

---

### 2.14 `ticket_status_history`

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

### 2.15 `ticket_escalations`

| Index Name | Type | Columns | Purpose |
|-----------|------|---------|---------|
| `ticket_escalations_pkey` | PRIMARY KEY | `id` | Row identity |
| `ticket_escalations_ticket_id_idx` | BTREE | `ticket_id` | FK lookup |
| `ticket_escalations_escalation_level_idx` | BTREE | `escalation_level` | Level-based queries |
| `ticket_escalations_escalated_by_idx` | BTREE | `escalated_by` | FK lookup |
| `ticket_escalations_escalated_to_idx` | BTREE | `escalated_to` | FK lookup |
| `ticket_escalations_escalated_at_idx` | BTREE | `escalated_at` | Date-range queries |

---

### 2.16 `audit_logs` (High-Volume Table)

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
| 1 | `users` | `role_id` | `roles.id` | `RESTRICT` | `CASCADE` |
| 2 | `users` | `department_id` | `departments.id` | `SET NULL` | `CASCADE` |
| 3 | `departments` | `parent_department_id` | `departments.id` | `SET NULL` | `CASCADE` |
| 4 | `departments` | `manager_id` | `users.id` | `SET NULL` | `CASCADE` |
| 5 | `categories` | `default_priority_id` | `priorities.id` | `SET NULL` | `CASCADE` |
| 6 | `categories` | `sla_policy_id` | `sla_policies.id` | `SET NULL` | `CASCADE` |
| 7 | `sub_categories` | `category_id` | `categories.id` | `CASCADE` | `CASCADE` |
| 8 | `sla_policies` | `category_id` | `categories.id` | `CASCADE` | `CASCADE` |
| 9 | `sla_policies` | `priority_id` | `priorities.id` | `CASCADE` | `CASCADE` |
| 10 | `tickets` | `ticket_type_id` | `ticket_types.id` | `RESTRICT` | `CASCADE` |
| 11 | `tickets` | `category_id` | `categories.id` | `RESTRICT` | `CASCADE` |
| 12 | `tickets` | `sub_category_id` | `sub_categories.id` | `SET NULL` | `CASCADE` |
| 13 | `tickets` | `priority_id` | `priorities.id` | `RESTRICT` | `CASCADE` |
| 14 | `tickets` | `status_id` | `statuses.id` | `RESTRICT` | `CASCADE` |
| 15 | `tickets` | `requester_id` | `users.id` | `RESTRICT` | `CASCADE` |
| 16 | `tickets` | `assigned_to` | `users.id` | `SET NULL` | `CASCADE` |
| 17 | `tickets` | `sla_policy_id` | `sla_policies.id` | `SET NULL` | `CASCADE` |
| 18 | `ticket_comments` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 19 | `ticket_comments` | `user_id` | `users.id` | `RESTRICT` | `CASCADE` |
| 20 | `attachments` | `uploaded_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 21 | `ticket_assignments` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 22 | `ticket_assignments` | `assigned_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 23 | `ticket_assignments` | `assigned_to` | `users.id` | `RESTRICT` | `CASCADE` |
| 24 | `ticket_status_history` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 25 | `ticket_status_history` | `from_status_id` | `statuses.id` | `SET NULL` | `CASCADE` |
| 26 | `ticket_status_history` | `to_status_id` | `statuses.id` | `RESTRICT` | `CASCADE` |
| 27 | `ticket_status_history` | `changed_by` | `users.id` | `RESTRICT` | `CASCADE` |
| 28 | `ticket_escalations` | `ticket_id` | `tickets.id` | `CASCADE` | `CASCADE` |
| 29 | `ticket_escalations` | `escalated_by` | `users.id` | `SET NULL` | `CASCADE` |
| 30 | `ticket_escalations` | `escalated_to` | `users.id` | `SET NULL` | `CASCADE` |
| 31 | `audit_logs` | `user_id` | `users.id` | `SET NULL` | `CASCADE` |

### 3.2 On Delete Behavior Rationale

| Behavior | Used For | Rationale |
|----------|----------|-----------|
| `CASCADE` | Child records (comments, attachments, history) | Delete child records when parent is deleted |
| `RESTRICT` | Critical references (requester, category, priority, type) | Prevent deletion of referenced records |
| `SET NULL` | Optional references (assignee, department, manager) | Preserve parent record, nullify reference |

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