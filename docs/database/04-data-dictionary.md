# MITO IT Helpdesk — Data Dictionary (MVP)

## 1. Conventions

### 1.1 Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Table names | `snake_case`, plural | `ticket_comments` |
| Column names | `snake_case` | `ticket_number` |
| Primary keys | `id` | `id` |
| Foreign keys | `{referenced_table}_id` | `ticket_id` |
| Boolean flags | `is_` prefix | `is_active` |
| Timestamps | `created_at`, `updated_at` | `created_at` |
| Soft delete | `deleted_at` | `deleted_at` |

### 1.2 Data Types (PostgreSQL)

| Type | Usage |
|------|-------|
| `BIGSERIAL` | Auto-increment primary key |
| `BIGINT` | Foreign key references |
| `VARCHAR(n)` | Variable-length strings |
| `TEXT` | Long text content |
| `BOOLEAN` | True/false flags |
| `SMALLINT` | Small integers (0-32767) |
| `INTEGER` | Standard integers |
| `TIMESTAMP` | Date and time |
| `JSON` | JSON data |

---

## 2. Master Data Tables

### 2.1 `roles`

**Purpose:** Stores user roles for RBAC.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `name` | VARCHAR(50) | NO | — | UNIQUE, NOT NULL | Role name |
| `description` | TEXT | YES | NULL | — | Role description |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |

---

### 2.2 `departments`

**Purpose:** Stores organizational departments with hierarchy.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(10) | NO | — | UNIQUE, NOT NULL | Department code |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Department name |
| `parent_department_id` | BIGINT | YES | NULL | FK → `departments.id` | Parent department |
| `description` | TEXT | YES | NULL | — | Department description |
| `manager_id` | BIGINT | YES | NULL | FK → `users.id` | Department manager |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.3 `categories`

**Purpose:** Stores top-level ticket categories.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Category code |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Category name |
| `description` | TEXT | YES | NULL | — | Description |
| `default_priority_id` | BIGINT | YES | NULL | FK → `priorities.id` | Default priority |
| `sla_policy_id` | BIGINT | YES | NULL | FK → `sla_policies.id` | Default SLA policy |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.4 `sub_categories`

**Purpose:** Stores sub-categories under top-level categories.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `category_id` | BIGINT | NO | — | FK → `categories.id`, NOT NULL | Parent category |
| `code` | VARCHAR(20) | NO | — | NOT NULL | Sub-category code |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Sub-category name |
| `description` | TEXT | YES | NULL | — | Description |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

**Unique Constraint:** `(category_id, code)`

---

### 2.5 `ticket_types`

**Purpose:** Stores ticket types.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Ticket type code |
| `name` | VARCHAR(50) | NO | — | NOT NULL | Ticket type name |
| `description` | TEXT | YES | NULL | — | Description |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.6 `priorities`

**Purpose:** Stores ticket priority levels with SLA targets.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Priority code |
| `name` | VARCHAR(50) | NO | — | NOT NULL | Priority name |
| `level` | SMALLINT | NO | — | NOT NULL, CHECK 1-10 | Sort order (1=highest) |
| `color` | VARCHAR(7) | NO | `#000000` | NOT NULL | Hex color |
| `response_time_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | SLA response target |
| `resolution_time_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | SLA resolution target |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.7 `statuses`

**Purpose:** Stores ticket status workflow states.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Status code |
| `name` | VARCHAR(50) | NO | — | NOT NULL | Status name |
| `description` | TEXT | YES | NULL | — | Description |
| `is_closed` | BOOLEAN | NO | `false` | NOT NULL | Closed state flag |
| `is_resolved` | BOOLEAN | NO | `false` | NOT NULL | Resolved state flag |
| `sort_order` | SMALLINT | NO | `0` | NOT NULL | Display order |
| `color` | VARCHAR(7) | NO | `#000000` | NOT NULL | Hex color |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.8 `sla_policies`

**Purpose:** Stores SLA definitions per category and priority.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `name` | VARCHAR(100) | NO | — | NOT NULL | SLA policy name |
| `category_id` | BIGINT | YES | NULL | FK → `categories.id` | Category (NULL = all) |
| `priority_id` | BIGINT | NO | — | FK → `priorities.id`, NOT NULL | Priority |
| `response_time_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | Response target |
| `resolution_time_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | Resolution target |
| `escalation_level_1_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | Escalation L1 threshold |
| `escalation_level_2_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | Escalation L2 threshold |
| `escalation_level_3_minutes` | INTEGER | NO | — | NOT NULL, CHECK > 0 | Escalation L3 threshold |
| `business_hours_only` | BOOLEAN | NO | `true` | NOT NULL | Business hours only |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

**Unique Constraint:** `(category_id, priority_id)`

---

## 3. Transaction Data Tables

### 3.1 `users`

**Purpose:** Stores all system users.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `employee_number` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Employee number |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Full name |
| `email` | VARCHAR(100) | NO | — | UNIQUE, NOT NULL | Email address |
| `password` | VARCHAR(255) | NO | — | NOT NULL | Bcrypt hashed password |
| `job_title` | VARCHAR(100) | YES | NULL | — | Job title |
| `phone` | VARCHAR(20) | YES | NULL | — | Contact phone |
| `extension` | VARCHAR(10) | YES | NULL | — | Phone extension |
| `mobile` | VARCHAR(20) | YES | NULL | — | Mobile phone |
| `department_id` | BIGINT | YES | NULL | FK → `departments.id` | Department |
| `role_id` | BIGINT | NO | — | FK → `roles.id`, NOT NULL | Role |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Account active |
| `email_verified_at` | TIMESTAMP | YES | NULL | — | Email verification |
| `last_login_at` | TIMESTAMP | YES | NULL | — | Last login |
| `last_password_change_at` | TIMESTAMP | YES | NULL | — | Last password change |
| `remember_token` | VARCHAR(100) | YES | NULL | — | Laravel remember token |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 3.2 `tickets`

**Purpose:** Stores main ticket records.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_number` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Human-readable ticket number |
| `ticket_type_id` | BIGINT | NO | — | FK → `ticket_types.id`, NOT NULL | Ticket type |
| `category_id` | BIGINT | NO | — | FK → `categories.id`, NOT NULL | Category |
| `sub_category_id` | BIGINT | YES | NULL | FK → `sub_categories.id` | Sub-category |
| `priority_id` | BIGINT | NO | — | FK → `priorities.id`, NOT NULL | Priority |
| `status_id` | BIGINT | NO | — | FK → `statuses.id`, NOT NULL | Status |
| `subject` | VARCHAR(200) | NO | — | NOT NULL | Ticket subject |
| `description` | TEXT | NO | — | NOT NULL | Issue description |
| `requester_id` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Ticket creator |
| `assigned_to` | BIGINT | YES | NULL | FK → `users.id` | Current assignee |
| `sla_policy_id` | BIGINT | YES | NULL | FK → `sla_policies.id` | SLA policy |
| `due_response_at` | TIMESTAMP | YES | NULL | — | SLA response due |
| `due_resolve_at` | TIMESTAMP | YES | NULL | — | SLA resolution due |
| `first_response_at` | TIMESTAMP | YES | NULL | — | First response time |
| `resolved_at` | TIMESTAMP | YES | NULL | — | Resolution time |
| `closed_at` | TIMESTAMP | YES | NULL | — | Closure time |
| `reopened_count` | SMALLINT | NO | `0` | NOT NULL, CHECK ≥ 0 | Reopen count |
| `escalation_level` | SMALLINT | NO | `0` | NOT NULL, CHECK 0-3 | Escalation level |
| `is_sla_breached` | BOOLEAN | NO | `false` | NOT NULL | SLA breach flag |
| `resolution_summary` | TEXT | YES | NULL | — | Resolution summary |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 3.3 `ticket_comments`

**Purpose:** Stores comments and updates on tickets.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_id` | BIGINT | NO | — | FK → `tickets.id`, NOT NULL | Ticket |
| `user_id` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Comment author |
| `comment` | TEXT | NO | — | NOT NULL | Comment content |
| `is_internal` | BOOLEAN | NO | `false` | NOT NULL | Internal note |
| `is_system` | BOOLEAN | NO | `false` | NOT NULL | System-generated |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 3.4 `attachments`

**Purpose:** Stores reusable file attachments supporting multiple modules.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `module` | VARCHAR(50) | NO | — | NOT NULL | Module name |
| `module_id` | BIGINT | NO | — | NOT NULL | Record ID in module |
| `filename` | VARCHAR(255) | NO | — | NOT NULL | Stored filename |
| `original_filename` | VARCHAR(255) | NO | — | NOT NULL | Original filename |
| `mime_type` | VARCHAR(100) | NO | — | NOT NULL | MIME type |
| `file_size` | BIGINT | NO | — | NOT NULL, CHECK > 0 | File size |
| `storage_path` | VARCHAR(500) | NO | — | NOT NULL | Storage path |
| `uploaded_by` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Uploader |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

**Composite Index:** `(module, module_id)`

---

### 3.5 `ticket_assignments`

**Purpose:** Stores ticket assignment history.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_id` | BIGINT | NO | — | FK → `tickets.id`, NOT NULL | Ticket |
| `assigned_by` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Assigner |
| `assigned_to` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Assignee |
| `assigned_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Assignment time |
| `unassigned_at` | TIMESTAMP | YES | NULL | — | Unassignment time |
| `reason` | TEXT | YES | NULL | — | Assignment reason |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |

---

### 3.6 `ticket_status_history`

**Purpose:** Stores ticket status change history.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_id` | BIGINT | NO | — | FK → `tickets.id`, NOT NULL | Ticket |
| `from_status_id` | BIGINT | YES | NULL | FK → `statuses.id` | Previous status |
| `to_status_id` | BIGINT | NO | — | FK → `statuses.id`, NOT NULL | New status |
| `changed_by` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Who changed |
| `changed_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Change time |
| `note` | TEXT | YES | NULL | — | Change note |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |

---

### 3.7 `ticket_escalations`

**Purpose:** Stores ticket escalation records.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_id` | BIGINT | NO | — | FK → `tickets.id`, NOT NULL | Ticket |
| `escalation_level` | SMALLINT | NO | — | NOT NULL, CHECK 1-3 | Escalation level |
| `escalated_by` | BIGINT | YES | NULL | FK → `users.id` | Who escalated |
| `escalated_to` | BIGINT | YES | NULL | FK → `users.id` | Escalated to |
| `reason` | TEXT | NO | — | NOT NULL | Escalation reason |
| `escalated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Escalation time |
| `resolved_at` | TIMESTAMP | YES | NULL | — | Resolution time |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |

---

### 3.8 `audit_logs`

**Purpose:** Stores system audit trail.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `user_id` | BIGINT | YES | NULL | FK → `users.id` | Actor (NULL = system) |
| `event` | VARCHAR(50) | NO | — | NOT NULL | Event type |
| `auditable_type` | VARCHAR(100) | NO | — | NOT NULL | Model class |
| `auditable_id` | BIGINT | NO | — | NOT NULL | Record ID |
| `old_values` | JSON | YES | NULL | — | Previous values |
| `new_values` | JSON | YES | NULL | — | New values |
| `ip_address` | VARCHAR(45) | YES | NULL | — | Client IP |
| `user_agent` | VARCHAR(500) | YES | NULL | — | User agent |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |

---

## 4. Table Summary

| # | Table | Type | Columns | Description |
|---|-------|------|---------|-------------|
| 1 | `roles` | Master | 5 | User roles |
| 2 | `departments` | Master | 10 | Organizational departments with hierarchy |
| 3 | `categories` | Master | 10 | Ticket categories |
| 4 | `sub_categories` | Master | 9 | Ticket sub-categories |
| 5 | `ticket_types` | Master | 8 | Ticket types |
| 6 | `priorities` | Master | 11 | Priority levels |
| 7 | `statuses` | Master | 12 | Status workflow states |
| 8 | `sla_policies` | Master | 14 | SLA definitions |
| 9 | `users` | Transaction | 19 | System users |
| 10 | `tickets` | Transaction | 25 | Main ticket records |
| 11 | `ticket_comments` | Transaction | 9 | Ticket comments |
| 12 | `attachments` | Transaction | 12 | Reusable file attachments |
| 13 | `ticket_assignments` | Transaction | 9 | Assignment history |
| 14 | `ticket_status_history` | Transaction | 9 | Status change history |
| 15 | `ticket_escalations` | Transaction | 10 | Escalation records |
| 16 | `audit_logs` | Transaction | 10 | System audit trail |

**Total: 16 MVP tables, 182 columns**

---

## 5. Future Module Tables (Deferred)

| # | Table | Type | Columns | Description |
|---|-------|------|---------|-------------|
| 17 | `asset_types` | Master | 7 | Asset types |
| 18 | `assets` | Master | 17 | IT assets inventory |
| 19 | `locations` | Master | 10 | Physical locations |
| 20 | `holidays` | Master | 8 | Company holidays |
| 21 | `knowledge_base` | Master | 15 | KB articles |
| 22 | `ticket_ratings` | Transaction | 8 | Satisfaction ratings |