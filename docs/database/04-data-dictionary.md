# MITO IT Helpdesk — Data Dictionary

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
| Enum columns | `snake_case` | `role` |

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
| `DECIMAL(p,s)` | Precise decimal numbers |
| `DATE` | Date only (no time) |
| `TIMESTAMP` | Date and time |
| `JSON` | JSON data |
| `ENUM` | PostgreSQL enum type |

---

## 2. Master Data Tables

### 2.1 `departments`

**Purpose:** Stores organizational departments.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(10) | NO | — | UNIQUE, NOT NULL | Department code (e.g., `IT`, `HR`, `FIN`) |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Department name |
| `description` | TEXT | YES | NULL | — | Department description |
| `manager_id` | BIGINT | YES | NULL | FK → `users.id` | Department manager |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.2 `locations`

**Purpose:** Stores physical locations (offices, floors, rooms).

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Location code (e.g., `JKT-01`) |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Location name |
| `address` | TEXT | YES | NULL | — | Physical address |
| `city` | VARCHAR(50) | YES | NULL | — | City |
| `country` | VARCHAR(50) | NO | `Indonesia` | NOT NULL | Country |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.3 `users`

**Purpose:** Stores all system users.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `employee_id` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Employee number |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Full name |
| `email` | VARCHAR(100) | NO | — | UNIQUE, NOT NULL | Email address |
| `password` | VARCHAR(255) | NO | — | NOT NULL | Bcrypt hashed password |
| `phone` | VARCHAR(20) | YES | NULL | — | Contact phone |
| `department_id` | BIGINT | YES | NULL | FK → `departments.id` | Department |
| `location_id` | BIGINT | YES | NULL | FK → `locations.id` | Location |
| `role` | ENUM | NO | `employee` | NOT NULL | User role |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Account active |
| `email_verified_at` | TIMESTAMP | YES | NULL | — | Email verification |
| `last_login_at` | TIMESTAMP | YES | NULL | — | Last login |
| `remember_token` | VARCHAR(100) | YES | NULL | — | Laravel remember token |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

**Enum Values (`role`):** `employee`, `it_staff`, `it_manager`, `admin`

---

### 2.4 `asset_types`

**Purpose:** Stores types of IT assets.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `code` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Asset type code |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Asset type name |
| `description` | TEXT | YES | NULL | — | Description |
| `is_active` | BOOLEAN | NO | `true` | NOT NULL | Active status |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.5 `assets`

**Purpose:** Stores IT assets inventory.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `asset_tag` | VARCHAR(50) | NO | — | UNIQUE, NOT NULL | Asset tag number |
| `asset_type_id` | BIGINT | NO | — | FK → `asset_types.id`, NOT NULL | Asset type |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Asset name |
| `brand` | VARCHAR(50) | YES | NULL | — | Brand |
| `model` | VARCHAR(50) | YES | NULL | — | Model |
| `serial_number` | VARCHAR(50) | YES | NULL | UNIQUE | Serial number |
| `purchase_date` | DATE | YES | NULL | — | Purchase date |
| `purchase_cost` | DECIMAL(12,2) | YES | NULL | CHECK ≥ 0 | Purchase cost |
| `warranty_expiry` | DATE | YES | NULL | — | Warranty expiry |
| `status` | ENUM | NO | `available` | NOT NULL | Asset status |
| `assigned_to` | BIGINT | YES | NULL | FK → `users.id` | Current assignee |
| `location_id` | BIGINT | YES | NULL | FK → `locations.id` | Location |
| `notes` | TEXT | YES | NULL | — | Additional notes |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

**Enum Values (`status`):** `available`, `assigned`, `in_repair`, `retired`, `lost`

---

### 2.6 `categories`

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

### 2.7 `sub_categories`

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

### 2.8 `priorities`

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

### 2.9 `statuses`

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

### 2.10 `sla_policies`

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

### 2.11 `holidays`

**Purpose:** Stores company holidays for SLA calculation.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `name` | VARCHAR(100) | NO | — | NOT NULL | Holiday name |
| `holiday_date` | DATE | NO | — | UNIQUE, NOT NULL | Holiday date |
| `is_recurring` | BOOLEAN | NO | `false` | NOT NULL | Recurring annually |
| `description` | TEXT | YES | NULL | — | Description |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 2.12 `knowledge_base`

**Purpose:** Stores knowledge base articles.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `title` | VARCHAR(200) | NO | — | NOT NULL | Article title |
| `slug` | VARCHAR(220) | NO | — | UNIQUE, NOT NULL | URL slug |
| `content` | TEXT | NO | — | NOT NULL | Article content (Markdown) |
| `category_id` | BIGINT | YES | NULL | FK → `categories.id` | Category |
| `author_id` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Author |
| `status` | ENUM | NO | `draft` | NOT NULL | Article status |
| `views_count` | BIGINT | NO | `0` | NOT NULL, CHECK ≥ 0 | View counter |
| `helpful_count` | BIGINT | NO | `0` | NOT NULL, CHECK ≥ 0 | Helpful count |
| `not_helpful_count` | BIGINT | NO | `0` | NOT NULL, CHECK ≥ 0 | Not helpful count |
| `published_at` | TIMESTAMP | YES | NULL | — | Publish time |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

**Enum Values (`status`):** `draft`, `published`, `archived`

---

## 3. Transaction Data Tables

### 3.1 `tickets`

**Purpose:** Stores main ticket records.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_number` | VARCHAR(20) | NO | — | UNIQUE, NOT NULL | Human-readable ticket number |
| `subject` | VARCHAR(200) | NO | — | NOT NULL | Ticket subject |
| `description` | TEXT | NO | — | NOT NULL | Issue description |
| `requester_id` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Ticket creator |
| `assignee_id` | BIGINT | YES | NULL | FK → `users.id` | Current assignee |
| `category_id` | BIGINT | NO | — | FK → `categories.id`, NOT NULL | Category |
| `sub_category_id` | BIGINT | YES | NULL | FK → `sub_categories.id` | Sub-category |
| `priority_id` | BIGINT | NO | — | FK → `priorities.id`, NOT NULL | Priority |
| `status_id` | BIGINT | NO | — | FK → `statuses.id`, NOT NULL | Status |
| `asset_id` | BIGINT | YES | NULL | FK → `assets.id` | Related asset |
| `location_id` | BIGINT | YES | NULL | FK → `locations.id` | Location |
| `sla_policy_id` | BIGINT | YES | NULL | FK → `sla_policies.id` | SLA policy |
| `sla_due_at` | TIMESTAMP | YES | NULL | — | SLA response due |
| `resolution_due_at` | TIMESTAMP | YES | NULL | — | SLA resolution due |
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

### 3.2 `ticket_comments`

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

### 3.3 `ticket_attachments`

**Purpose:** Stores files attached to tickets.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_id` | BIGINT | NO | — | FK → `tickets.id`, NOT NULL | Ticket |
| `comment_id` | BIGINT | YES | NULL | FK → `ticket_comments.id` | Comment |
| `uploaded_by` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Uploader |
| `original_name` | VARCHAR(255) | NO | — | NOT NULL | Original filename |
| `stored_name` | VARCHAR(255) | NO | — | NOT NULL | Stored filename |
| `path` | VARCHAR(500) | NO | — | NOT NULL | Storage path |
| `mime_type` | VARCHAR(100) | NO | — | NOT NULL | MIME type |
| `size_bytes` | BIGINT | NO | — | NOT NULL, CHECK > 0 | File size |
| `created_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Creation time |
| `updated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Update time |
| `deleted_at` | TIMESTAMP | YES | NULL | — | Soft delete timestamp |

---

### 3.4 `ticket_assignments`

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

### 3.5 `ticket_status_history`

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

### 3.6 `ticket_escalations`

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

### 3.7 `ticket_ratings`

**Purpose:** Stores customer satisfaction ratings.

| Column | Type | Nullable | Default | Constraints | Description |
|--------|------|----------|---------|-------------|-------------|
| `id` | BIGSERIAL | NO | — | PRIMARY KEY | Auto-increment ID |
| `ticket_id` | BIGINT | NO | — | FK → `tickets.id`, UNIQUE, NOT NULL | Ticket |
| `rating` | SMALLINT | NO | — | NOT NULL, CHECK 1-5 | Rating (1-5) |
| `comment` | TEXT | YES | NULL | — | Feedback comment |
| `rated_by` | BIGINT | NO | — | FK → `users.id`, NOT NULL | Rater |
| `rated_at` | TIMESTAMP | NO | `CURRENT_TIMESTAMP` | NOT NULL | Rating time |
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
| 1 | `departments` | Master | 9 | Organizational departments |
| 2 | `locations` | Master | 10 | Physical locations |
| 3 | `users` | Master | 16 | System users |
| 4 | `asset_types` | Master | 7 | Asset types |
| 5 | `assets` | Master | 17 | IT assets inventory |
| 6 | `categories` | Master | 10 | Ticket categories |
| 7 | `sub_categories` | Master | 9 | Ticket sub-categories |
| 8 | `priorities` | Master | 11 | Priority levels |
| 9 | `statuses` | Master | 12 | Status workflow states |
| 10 | `sla_policies` | Master | 14 | SLA definitions |
| 11 | `holidays` | Master | 8 | Company holidays |
| 12 | `knowledge_base` | Master | 15 | KB articles |
| 13 | `tickets` | Transaction | 25 | Main ticket records |
| 14 | `ticket_comments` | Transaction | 9 | Ticket comments |
| 15 | `ticket_attachments` | Transaction | 12 | Ticket attachments |
| 16 | `ticket_assignments` | Transaction | 9 | Assignment history |
| 17 | `ticket_status_history` | Transaction | 9 | Status change history |
| 18 | `ticket_escalations` | Transaction | 10 | Escalation records |
| 19 | `ticket_ratings` | Transaction | 8 | Satisfaction ratings |
| 20 | `audit_logs` | Transaction | 10 | System audit trail |

**Total: 20 tables, 230 columns**