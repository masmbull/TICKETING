# MITO IT Helpdesk — Entity Definitions (MVP)

## 1. Entity Overview

### 1.1 MVP Master Data Entities

| # | Entity | Type | Description |
|---|--------|------|-------------|
| 1 | `roles` | Master | User roles |
| 2 | `departments` | Master | Organizational departments with hierarchy |
| 3 | `categories` | Master | Ticket categories (top-level) |
| 4 | `sub_categories` | Master | Ticket sub-categories |
| 5 | `ticket_types` | Master | Ticket types |
| 6 | `priorities` | Master | Ticket priority levels |
| 7 | `statuses` | Master | Ticket status workflow states |
| 8 | `sla_policies` | Master | SLA definitions |

### 1.2 MVP Transaction Data Entities

| # | Entity | Type | Description |
|---|--------|------|-------------|
| 9 | `users` | Transaction | All system users |
| 10 | `tickets` | Transaction | Main ticket records |
| 11 | `ticket_comments` | Transaction | Comments/updates on tickets |
| 12 | `attachments` | Transaction | Reusable file attachments |
| 13 | `ticket_assignments` | Transaction | Assignment history |
| 14 | `ticket_status_history` | Transaction | Status change history |
| 15 | `ticket_escalations` | Transaction | Escalation records |
| 16 | `audit_logs` | Transaction | System audit trail |

### 1.3 Future Modules (Deferred from MVP)

| # | Entity | Type | Description | Phase |
|---|--------|------|-------------|-------|
| 17 | `asset_types` | Master | Types of IT assets | Future |
| 18 | `assets` | Master | IT assets inventory | Future |
| 19 | `locations` | Master | Physical locations | Future |
| 20 | `holidays` | Master | Company holidays | Future |
| 21 | `knowledge_base` | Master | Knowledge base articles | Future |
| 22 | `ticket_ratings` | Transaction | Customer satisfaction ratings | Future |

---

## 2. Entity Definitions

### 2.1 `roles`

**Description:** User roles for role-based access control (RBAC). Stored as a master table for flexibility.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR(50) | NO | — | Role name (unique) |
| `description` | TEXT | YES | NULL | Role description |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |

**Indexes:** `id` (PK), `name` (UNIQUE)

---

### 2.2 `departments`

**Description:** Organizational departments with hierarchical structure.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(10) | NO | — | Department code (unique) |
| `name` | VARCHAR(100) | NO | — | Department name |
| `parent_department_id` | BIGINT UNSIGNED | YES | NULL | FK → `departments.id` (parent department) |
| `description` | TEXT | YES | NULL | Department description |
| `manager_id` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (department manager) |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `code` (UNIQUE), `parent_department_id`, `manager_id`, `is_active`

---

### 2.3 `categories`

**Description:** Top-level ticket categories (e.g., Hardware, Software, Network, Access).

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(20) | NO | — | Category code (unique) |
| `name` | VARCHAR(100) | NO | — | Category name |
| `description` | TEXT | YES | NULL | Category description |
| `default_priority_id` | BIGINT UNSIGNED | YES | NULL | FK → `priorities.id` |
| `sla_policy_id` | BIGINT UNSIGNED | YES | NULL | FK → `sla_policies.id` |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `code` (UNIQUE), `default_priority_id`, `sla_policy_id`, `is_active`

---

### 2.4 `sub_categories`

**Description:** Sub-categories under top-level categories.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `category_id` | BIGINT UNSIGNED | NO | — | FK → `categories.id` |
| `code` | VARCHAR(20) | NO | — | Sub-category code (unique per category) |
| `name` | VARCHAR(100) | NO | — | Sub-category name |
| `description` | TEXT | YES | NULL | Sub-category description |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `category_id`, `code`, `is_active`
**Unique Constraint:** `(category_id, code)`

---

### 2.5 `ticket_types`

**Description:** Types of tickets (Incident, Service Request, Problem, Change Request).

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(20) | NO | — | Ticket type code (unique) |
| `name` | VARCHAR(50) | NO | — | Ticket type name |
| `description` | TEXT | YES | NULL | Ticket type description |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `code` (UNIQUE), `is_active`

---

### 2.6 `priorities`

**Description:** Ticket priority levels with SLA targets.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(20) | NO | — | Priority code (unique) |
| `name` | VARCHAR(50) | NO | — | Priority name |
| `level` | SMALLINT | NO | — | Sort order (1=highest) |
| `color` | VARCHAR(7) | NO | `#000000` | Hex color for UI |
| `response_time_minutes` | INTEGER | NO | — | SLA response target (minutes) |
| `resolution_time_minutes` | INTEGER | NO | — | SLA resolution target (minutes) |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `code` (UNIQUE), `level`, `is_active`

---

### 2.7 `statuses`

**Description:** Ticket status workflow states.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(20) | NO | — | Status code (unique) |
| `name` | VARCHAR(50) | NO | — | Status name |
| `description` | TEXT | YES | NULL | Status description |
| `is_closed` | BOOLEAN | NO | `false` | Is this a closed state? |
| `is_resolved` | BOOLEAN | NO | `false` | Is this a resolved state? |
| `sort_order` | SMALLINT | NO | `0` | Display order |
| `color` | VARCHAR(7) | NO | `#000000` | Hex color for UI |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `code` (UNIQUE), `is_closed`, `is_resolved`, `sort_order`, `is_active`

---

### 2.8 `sla_policies`

**Description:** Service Level Agreement definitions per category and priority.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR(100) | NO | — | SLA policy name |
| `category_id` | BIGINT UNSIGNED | YES | NULL | FK → `categories.id` (NULL = all) |
| `priority_id` | BIGINT UNSIGNED | NO | — | FK → `priorities.id` |
| `response_time_minutes` | INTEGER | NO | — | Response time target (minutes) |
| `resolution_time_minutes` | INTEGER | NO | — | Resolution time target (minutes) |
| `escalation_level_1_minutes` | INTEGER | NO | — | Escalation level 1 threshold |
| `escalation_level_2_minutes` | INTEGER | NO | — | Escalation level 2 threshold |
| `escalation_level_3_minutes` | INTEGER | NO | — | Escalation level 3 threshold |
| `business_hours_only` | BOOLEAN | NO | `true` | Count only business hours |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `category_id`, `priority_id`, `is_active`
**Unique Constraint:** `(category_id, priority_id)`

---

### 2.9 `users`

**Description:** All system users including employees, IT staff, IT managers, and administrators.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `employee_number` | VARCHAR(20) | NO | — | Employee number (unique) |
| `name` | VARCHAR(100) | NO | — | Full name |
| `email` | VARCHAR(100) | NO | — | Email address (unique) |
| `password` | VARCHAR(255) | NO | — | Bcrypt hashed password |
| `job_title` | VARCHAR(100) | YES | NULL | Job title |
| `phone` | VARCHAR(20) | YES | NULL | Contact phone number |
| `extension` | VARCHAR(10) | YES | NULL | Phone extension |
| `mobile` | VARCHAR(20) | YES | NULL | Mobile phone number |
| `department_id` | BIGINT UNSIGNED | YES | NULL | FK → `departments.id` |
| `role_id` | BIGINT UNSIGNED | NO | — | FK → `roles.id` |
| `is_active` | BOOLEAN | NO | `true` | Account active status |
| `email_verified_at` | TIMESTAMP | YES | NULL | Email verification timestamp |
| `last_login_at` | TIMESTAMP | YES | NULL | Last login timestamp |
| `last_password_change_at` | TIMESTAMP | YES | NULL | Last password change timestamp |
| `remember_token` | VARCHAR(100) | YES | NULL | Laravel remember token |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `employee_number` (UNIQUE), `email` (UNIQUE), `department_id`, `role_id`, `is_active`

---

### 2.10 `tickets`

**Description:** Main ticket records.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_number` | VARCHAR(20) | NO | — | Human-readable ticket number (unique) |
| `ticket_type_id` | BIGINT UNSIGNED | NO | — | FK → `ticket_types.id` |
| `category_id` | BIGINT UNSIGNED | NO | — | FK → `categories.id` |
| `sub_category_id` | BIGINT UNSIGNED | YES | NULL | FK → `sub_categories.id` |
| `priority_id` | BIGINT UNSIGNED | NO | — | FK → `priorities.id` |
| `status_id` | BIGINT UNSIGNED | NO | — | FK → `statuses.id` |
| `subject` | VARCHAR(200) | NO | — | Ticket subject |
| `description` | TEXT | NO | — | Detailed issue description |
| `requester_id` | BIGINT UNSIGNED | NO | — | FK → `users.id` (ticket creator) |
| `assigned_to` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (current assignee) |
| `sla_policy_id` | BIGINT UNSIGNED | YES | NULL | FK → `sla_policies.id` |
| `due_response_at` | TIMESTAMP | YES | NULL | SLA response due timestamp |
| `due_resolve_at` | TIMESTAMP | YES | NULL | SLA resolution due timestamp |
| `first_response_at` | TIMESTAMP | YES | NULL | First response timestamp |
| `resolved_at` | TIMESTAMP | YES | NULL | Resolution timestamp |
| `closed_at` | TIMESTAMP | YES | NULL | Closure timestamp |
| `reopened_count` | SMALLINT | NO | `0` | Number of times reopened |
| `escalation_level` | SMALLINT | NO | `0` | Current escalation level |
| `is_sla_breached` | BOOLEAN | NO | `false` | SLA breach flag |
| `resolution_summary` | TEXT | YES | NULL | Resolution summary |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `ticket_number` (UNIQUE), `ticket_type_id`, `category_id`, `sub_category_id`, `priority_id`, `status_id`, `requester_id`, `assigned_to`, `due_response_at`, `due_resolve_at`, `created_at`, `is_sla_breached`

---

### 2.11 `ticket_comments`

**Description:** Comments and updates on tickets.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` |
| `user_id` | BIGINT UNSIGNED | NO | — | FK → `users.id` (comment author) |
| `comment` | TEXT | NO | — | Comment content |
| `is_internal` | BOOLEAN | NO | `false` | Internal note (not visible to requester) |
| `is_system` | BOOLEAN | NO | `false` | System-generated comment |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `ticket_id`, `user_id`, `is_internal`, `created_at`

---

### 2.12 `attachments`

**Description:** Reusable file attachments supporting multiple modules via polymorphic design.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `module` | VARCHAR(50) | NO | — | Module name (e.g., `ticket`, `comment`) |
| `module_id` | BIGINT UNSIGNED | NO | — | Record ID in the module |
| `filename` | VARCHAR(255) | NO | — | Stored file name |
| `original_filename` | VARCHAR(255) | NO | — | Original file name |
| `mime_type` | VARCHAR(100) | NO | — | MIME type |
| `file_size` | BIGINT UNSIGNED | NO | — | File size in bytes |
| `storage_path` | VARCHAR(500) | NO | — | Storage path |
| `uploaded_by` | BIGINT UNSIGNED | NO | — | FK → `users.id` |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `module`, `module_id`, `uploaded_by`
**Composite Index:** `(module, module_id)`

---

### 2.13 `ticket_assignments`

**Description:** Ticket assignment history.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` |
| `assigned_by` | BIGINT UNSIGNED | NO | — | FK → `users.id` (who assigned) |
| `assigned_to` | BIGINT UNSIGNED | NO | — | FK → `users.id` (who was assigned) |
| `assigned_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Assignment timestamp |
| `unassigned_at` | TIMESTAMP | YES | NULL | Unassignment timestamp |
| `reason` | TEXT | YES | NULL | Assignment reason/note |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |

**Indexes:** `id` (PK), `ticket_id`, `assigned_by`, `assigned_to`, `assigned_at`

---

### 2.14 `ticket_status_history`

**Description:** Ticket status change history.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` |
| `from_status_id` | BIGINT UNSIGNED | YES | NULL | FK → `statuses.id` (previous status) |
| `to_status_id` | BIGINT UNSIGNED | NO | — | FK → `statuses.id` (new status) |
| `changed_by` | BIGINT UNSIGNED | NO | — | FK → `users.id` (who changed) |
| `changed_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Change timestamp |
| `note` | TEXT | YES | NULL | Change note |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |

**Indexes:** `id` (PK), `ticket_id`, `from_status_id`, `to_status_id`, `changed_by`, `changed_at`

---

### 2.15 `ticket_escalations`

**Description:** Ticket escalation records.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` |
| `escalation_level` | SMALLINT | NO | — | Escalation level (1, 2, 3) |
| `escalated_by` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (who escalated) |
| `escalated_to` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (escalated to) |
| `reason` | TEXT | NO | — | Escalation reason |
| `escalated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Escalation timestamp |
| `resolved_at` | TIMESTAMP | YES | NULL | Escalation resolution timestamp |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |

**Indexes:** `id` (PK), `ticket_id`, `escalation_level`, `escalated_by`, `escalated_to`, `escalated_at`

---

### 2.16 `audit_logs`

**Description:** System audit trail for all critical operations.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `user_id` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (actor, NULL = system) |
| `event` | VARCHAR(50) | NO | — | Event type (created, updated, deleted, etc.) |
| `auditable_type` | VARCHAR(100) | NO | — | Model class name |
| `auditable_id` | BIGINT UNSIGNED | NO | — | Model record ID |
| `old_values` | JSON | YES | NULL | Previous values |
| `new_values` | JSON | YES | NULL | New values |
| `ip_address` | VARCHAR(45) | YES | NULL | Client IP address |
| `user_agent` | VARCHAR(500) | YES | NULL | Client user agent |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |

**Indexes:** `id` (PK), `user_id`, `event`, `auditable_type`, `auditable_id`, `created_at`

---

## 3. Future Module Entity Definitions

### 3.1 `asset_types` (Future)

**Description:** Types of IT assets. Deferred to future phase.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(20) | NO | — | Asset type code (unique) |
| `name` | VARCHAR(100) | NO | — | Asset type name |
| `description` | TEXT | YES | NULL | Asset type description |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

---

### 3.2 `assets` (Future)

**Description:** IT assets inventory. Deferred to future phase.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `asset_tag` | VARCHAR(50) | NO | — | Asset tag number (unique) |
| `asset_type_id` | BIGINT UNSIGNED | NO | — | FK → `asset_types.id` |
| `name` | VARCHAR(100) | NO | — | Asset name |
| `brand` | VARCHAR(50) | YES | NULL | Brand/manufacturer |
| `model` | VARCHAR(50) | YES | NULL | Model number |
| `serial_number` | VARCHAR(50) | YES | NULL | Serial number (unique) |
| `purchase_date` | DATE | YES | NULL | Purchase date |
| `purchase_cost` | DECIMAL(12,2) | YES | NULL | Purchase cost |
| `warranty_expiry` | DATE | YES | NULL | Warranty expiry date |
| `status` | VARCHAR(20) | NO | `available` | Asset status |
| `assigned_to` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` |
| `location_id` | BIGINT UNSIGNED | YES | NULL | FK → `locations.id` |
| `notes` | TEXT | YES | NULL | Additional notes |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

---

### 3.3 `locations` (Future)

**Description:** Physical locations. Deferred to future phase.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(20) | NO | — | Location code (unique) |
| `name` | VARCHAR(100) | NO | — | Location name |
| `address` | TEXT | YES | NULL | Physical address |
| `city` | VARCHAR(50) | YES | NULL | City |
| `country` | VARCHAR(50) | NO | `Indonesia` | Country |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

---

### 3.4 `holidays` (Future)

**Description:** Company holidays for SLA calculation. Deferred to future phase.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `name` | VARCHAR(100) | NO | — | Holiday name |
| `holiday_date` | DATE | NO | — | Holiday date (unique) |
| `is_recurring` | BOOLEAN | NO | `false` | Recurring annually? |
| `description` | TEXT | YES | NULL | Holiday description |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

---

### 3.5 `knowledge_base` (Future)

**Description:** Knowledge base articles. Deferred to future phase.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `title` | VARCHAR(200) | NO | — | Article title |
| `slug` | VARCHAR(220) | NO | — | URL slug (unique) |
| `content` | TEXT | NO | — | Article content (Markdown) |
| `category_id` | BIGINT UNSIGNED | YES | NULL | FK → `categories.id` |
| `author_id` | BIGINT UNSIGNED | NO | — | FK → `users.id` |
| `status` | VARCHAR(20) | NO | `draft` | Article status |
| `views_count` | BIGINT UNSIGNED | NO | `0` | View counter |
| `helpful_count` | BIGINT UNSIGNED | NO | `0` | Helpful feedback count |
| `not_helpful_count` | BIGINT UNSIGNED | NO | `0` | Not helpful feedback count |
| `published_at` | TIMESTAMP | YES | NULL | Publish timestamp |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

---

### 3.6 `ticket_ratings` (Future)

**Description:** Customer satisfaction ratings. Deferred to future phase.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` (unique) |
| `rating` | SMALLINT | NO | — | Rating 1-5 |
| `comment` | TEXT | YES | NULL | Feedback comment |
| `rated_by` | BIGINT UNSIGNED | NO | — | FK → `users.id` |
| `rated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Rating timestamp |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |

---

## 4. Ticket Numbering Strategy

### 4.1 Format

```
HD-YYYYMMDD-000001
```

### 4.2 Components

| Component | Description | Example |
|-----------|-------------|---------|
| `HD` | Prefix (Helpdesk) | `HD` |
| `YYYYMMDD` | Date of ticket creation | `20260806` |
| `000001` | Sequential number (zero-padded, 6 digits) | `000001` |

### 4.3 Examples

| Ticket Number | Date | Sequence |
|---------------|------|----------|
| `HD-20260806-000001` | 2026-08-06 | 1 |
| `HD-20260806-000002` | 2026-08-06 | 2 |
| `HD-20260807-000001` | 2026-08-07 | 1 (resets daily) |

### 4.4 Numbering Rules

1. The sequence resets **daily** (per `YYYYMMDD`).
2. The sequence is **zero-padded** to 6 digits (supports 999,999 tickets per day).
3. The prefix `HD` identifies the system (Helpdesk).
4. The ticket number is **unique** across the entire system.
5. The ticket number is **immutable** — never changes after creation.
6. The ticket number is **human-readable** for easy reference in communications.

### 4.5 Implementation Notes

- The sequence should be generated atomically to prevent race conditions.
- Use a database sequence or a dedicated counter table per date.
- The format can be extended with additional prefixes for future modules (e.g., `AST-` for assets).

---

## 5. Relationship Summary

### 5.1 One-to-Many Relationships

| From | To | Description |
|------|-----|-------------|
| `roles` | `users` | A role has many users |
| `departments` | `users` | A department has many users |
| `departments` | `departments` | A department has many child departments |
| `categories` | `sub_categories` | A category has many sub-categories |
| `categories` | `tickets` | A category has many tickets |
| `sub_categories` | `tickets` | A sub-category has many tickets |
| `ticket_types` | `tickets` | A ticket type has many tickets |
| `priorities` | `tickets` | A priority has many tickets |
| `statuses` | `tickets` | A status has many tickets |
| `users` | `tickets` (requester) | A user has many tickets created |
| `users` | `tickets` (assigned_to) | A user has many tickets assigned |
| `users` | `ticket_comments` | A user has many comments |
| `users` | `attachments` | A user has many attachments uploaded |
| `tickets` | `ticket_comments` | A ticket has many comments |
| `tickets` | `attachments` | A ticket has many attachments |
| `tickets` | `ticket_assignments` | A ticket has many assignments |
| `tickets` | `ticket_status_history` | A ticket has many status changes |
| `tickets` | `ticket_escalations` | A ticket has many escalations |

### 5.2 Many-to-One Relationships

| From | To | Description |
|------|-----|-------------|
| `users` | `roles` | A user belongs to a role |
| `users` | `departments` | A user belongs to a department |
| `departments` | `departments` | A department belongs to a parent department |
| `tickets` | `ticket_types` | A ticket has a type |
| `tickets` | `categories` | A ticket belongs to a category |
| `tickets` | `sub_categories` | A ticket belongs to a sub-category |
| `tickets` | `priorities` | A ticket has a priority |
| `tickets` | `statuses` | A ticket has a status |
| `tickets` | `users` | A ticket belongs to a requester |
| `tickets` | `users` | A ticket is assigned to a user |
| `tickets` | `sla_policies` | A ticket follows an SLA policy |
| `ticket_comments` | `tickets` | A comment belongs to a ticket |
| `ticket_comments` | `users` | A comment belongs to a user |
| `attachments` | `users` | An attachment was uploaded by a user |
| `ticket_assignments` | `tickets` | An assignment belongs to a ticket |
| `ticket_assignments` | `users` | An assignment was made by a user |
| `ticket_assignments` | `users` | An assignment was made to a user |
| `ticket_status_history` | `tickets` | A status change belongs to a ticket |
| `ticket_status_history` | `statuses` | A status change has a from-status |
| `ticket_status_history` | `statuses` | A status change has a to-status |
| `ticket_status_history` | `users` | A status change was made by a user |
| `ticket_escalations` | `tickets` | An escalation belongs to a ticket |
| `ticket_escalations` | `users` | An escalation was made by a user |
| `ticket_escalations` | `users` | An escalation was made to a user |
| `sla_policies` | `categories` | An SLA policy belongs to a category |
| `sla_policies` | `priorities` | An SLA policy belongs to a priority |
| `categories` | `priorities` | A category has a default priority |
| `categories` | `sla_policies` | A category has a default SLA policy |
| `audit_logs` | `users` | An audit log belongs to a user |

### 5.3 Self-Referencing Relationships

| Entity | Description |
|--------|-------------|
| `departments.parent_department_id` | A department can have a parent department |
| `departments.manager_id` | A department manager is a user |
| `categories.default_priority_id` | A category references a priority |
| `categories.sla_policy_id` | A category references an SLA policy |