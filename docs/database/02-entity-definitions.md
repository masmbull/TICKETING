# MITO IT Helpdesk — Entity Definitions

## 1. Entity Overview

### 1.1 Master Data Entities

| # | Entity | Type | Description |
|---|--------|------|-------------|
| 1 | `users` | Master | All system users |
| 2 | `departments` | Master | Organizational departments |
| 3 | `categories` | Master | Ticket categories (top-level) |
| 4 | `sub_categories` | Master | Ticket sub-categories |
| 5 | `priorities` | Master | Ticket priority levels |
| 6 | `statuses` | Master | Ticket status workflow states |
| 7 | `asset_types` | Master | Types of IT assets |
| 8 | `assets` | Master | IT assets inventory |
| 9 | `locations` | Master | Physical locations |
| 10 | `sla_policies` | Master | SLA definitions |
| 11 | `holidays` | Master | Company holidays |
| 12 | `knowledge_base` | Master | Knowledge base articles |

### 1.2 Transaction Data Entities

| # | Entity | Type | Description |
|---|--------|------|-------------|
| 13 | `tickets` | Transaction | Main ticket records |
| 14 | `ticket_comments` | Transaction | Comments/updates on tickets |
| 15 | `ticket_attachments` | Transaction | Files attached to tickets |
| 16 | `ticket_assignments` | Transaction | Assignment history |
| 17 | `ticket_status_history` | Transaction | Status change history |
| 18 | `ticket_escalations` | Transaction | Escalation records |
| 19 | `ticket_ratings` | Transaction | Customer satisfaction ratings |
| 20 | `audit_logs` | Transaction | System audit trail |

---

## 2. Entity Definitions

### 2.1 `users`

**Description:** All system users including employees, IT staff, IT managers, and administrators.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `employee_id` | VARCHAR(20) | NO | — | Employee number (unique) |
| `name` | VARCHAR(100) | NO | — | Full name |
| `email` | VARCHAR(100) | NO | — | Email address (unique) |
| `password` | VARCHAR(255) | NO | — | Bcrypt hashed password |
| `phone` | VARCHAR(20) | YES | NULL | Contact phone number |
| `department_id` | BIGINT UNSIGNED | YES | NULL | FK → `departments.id` |
| `location_id` | BIGINT UNSIGNED | YES | NULL | FK → `locations.id` |
| `role` | ENUM | NO | `employee` | User role |
| `is_active` | BOOLEAN | NO | `true` | Account active status |
| `email_verified_at` | TIMESTAMP | YES | NULL | Email verification timestamp |
| `last_login_at` | TIMESTAMP | YES | NULL | Last login timestamp |
| `remember_token` | VARCHAR(100) | YES | NULL | Laravel remember token |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `employee_id` (UNIQUE), `email` (UNIQUE), `department_id`, `location_id`, `role`, `is_active`

---

### 2.2 `departments`

**Description:** Organizational departments within the company.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `code` | VARCHAR(10) | NO | — | Department code (unique) |
| `name` | VARCHAR(100) | NO | — | Department name |
| `description` | TEXT | YES | NULL | Department description |
| `manager_id` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (department manager) |
| `is_active` | BOOLEAN | NO | `true` | Active status |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `code` (UNIQUE), `manager_id`, `is_active`

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

**Description:** Sub-categories under top-level categories (e.g., Hardware → Laptop, Printer).

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

### 2.5 `priorities`

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

### 2.6 `statuses`

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

### 2.7 `asset_types`

**Description:** Types of IT assets (e.g., Laptop, Desktop, Printer, Monitor, Server).

**Attributes:**

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

**Indexes:** `id` (PK), `code` (UNIQUE), `is_active`

---

### 2.8 `assets`

**Description:** IT assets inventory.

**Attributes:**

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
| `status` | ENUM | NO | `available` | Asset status |
| `assigned_to` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (current assignee) |
| `location_id` | BIGINT UNSIGNED | YES | NULL | FK → `locations.id` |
| `notes` | TEXT | YES | NULL | Additional notes |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `asset_tag` (UNIQUE), `serial_number` (UNIQUE), `asset_type_id`, `status`, `assigned_to`, `location_id`

---

### 2.9 `locations`

**Description:** Physical locations (offices, floors, rooms).

**Attributes:**

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

**Indexes:** `id` (PK), `code` (UNIQUE), `city`, `is_active`

---

### 2.10 `sla_policies`

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

### 2.11 `holidays`

**Description:** Company holidays for SLA calculation.

**Attributes:**

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

**Indexes:** `id` (PK), `holiday_date` (UNIQUE), `is_recurring`

---

### 2.12 `knowledge_base`

**Description:** Knowledge base articles for self-service support.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `title` | VARCHAR(200) | NO | — | Article title |
| `slug` | VARCHAR(220) | NO | — | URL slug (unique) |
| `content` | TEXT | NO | — | Article content (Markdown) |
| `category_id` | BIGINT UNSIGNED | YES | NULL | FK → `categories.id` |
| `author_id` | BIGINT UNSIGNED | NO | — | FK → `users.id` (author) |
| `status` | ENUM | NO | `draft` | Article status |
| `views_count` | BIGINT UNSIGNED | NO | `0` | View counter |
| `helpful_count` | BIGINT UNSIGNED | NO | `0` | Helpful feedback count |
| `not_helpful_count` | BIGINT UNSIGNED | NO | `0` | Not helpful feedback count |
| `published_at` | TIMESTAMP | YES | NULL | Publish timestamp |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `slug` (UNIQUE), `category_id`, `author_id`, `status`, `published_at`

---

### 2.13 `tickets`

**Description:** Main ticket records.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_number` | VARCHAR(20) | NO | — | Human-readable ticket number (unique) |
| `subject` | VARCHAR(200) | NO | — | Ticket subject |
| `description` | TEXT | NO | — | Detailed issue description |
| `requester_id` | BIGINT UNSIGNED | NO | — | FK → `users.id` (ticket creator) |
| `assignee_id` | BIGINT UNSIGNED | YES | NULL | FK → `users.id` (current assignee) |
| `category_id` | BIGINT UNSIGNED | NO | — | FK → `categories.id` |
| `sub_category_id` | BIGINT UNSIGNED | YES | NULL | FK → `sub_categories.id` |
| `priority_id` | BIGINT UNSIGNED | NO | — | FK → `priorities.id` |
| `status_id` | BIGINT UNSIGNED | NO | — | FK → `statuses.id` |
| `asset_id` | BIGINT UNSIGNED | YES | NULL | FK → `assets.id` (related asset) |
| `location_id` | BIGINT UNSIGNED | YES | NULL | FK → `locations.id` |
| `sla_policy_id` | BIGINT UNSIGNED | YES | NULL | FK → `sla_policies.id` |
| `sla_due_at` | TIMESTAMP | YES | NULL | SLA response due timestamp |
| `resolution_due_at` | TIMESTAMP | YES | NULL | SLA resolution due timestamp |
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

**Indexes:** `id` (PK), `ticket_number` (UNIQUE), `requester_id`, `assignee_id`, `category_id`, `sub_category_id`, `priority_id`, `status_id`, `asset_id`, `location_id`, `sla_due_at`, `resolution_due_at`, `created_at`, `is_sla_breached`

---

### 2.14 `ticket_comments`

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

### 2.15 `ticket_attachments`

**Description:** Files attached to tickets.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` |
| `comment_id` | BIGINT UNSIGNED | YES | NULL | FK → `ticket_comments.id` |
| `uploaded_by` | BIGINT UNSIGNED | NO | — | FK → `users.id` |
| `original_name` | VARCHAR(255) | NO | — | Original file name |
| `stored_name` | VARCHAR(255) | NO | — | Stored file name |
| `path` | VARCHAR(500) | NO | — | Storage path |
| `mime_type` | VARCHAR(100) | NO | — | MIME type |
| `size_bytes` | BIGINT UNSIGNED | NO | — | File size in bytes |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete timestamp |

**Indexes:** `id` (PK), `ticket_id`, `comment_id`, `uploaded_by`

---

### 2.16 `ticket_assignments`

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

### 2.17 `ticket_status_history`

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

### 2.18 `ticket_escalations`

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

### 2.19 `ticket_ratings`

**Description:** Customer satisfaction ratings for resolved tickets.

**Attributes:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary key |
| `ticket_id` | BIGINT UNSIGNED | NO | — | FK → `tickets.id` (unique) |
| `rating` | SMALLINT | NO | — | Rating 1-5 |
| `comment` | TEXT | YES | NULL | Feedback comment |
| `rated_by` | BIGINT UNSIGNED | NO | — | FK → `users.id` (requester) |
| `rated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Rating timestamp |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Record update time |

**Indexes:** `id` (PK), `ticket_id` (UNIQUE), `rating`, `rated_by`, `rated_at`

---

### 2.20 `audit_logs`

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

## 3. Relationship Summary

### 3.1 One-to-Many Relationships

| From | To | Description |
|------|-----|-------------|
| `departments` | `users` | A department has many users |
| `locations` | `users` | A location has many users |
| `locations` | `assets` | A location has many assets |
| `categories` | `sub_categories` | A category has many sub-categories |
| `categories` | `tickets` | A category has many tickets |
| `sub_categories` | `tickets` | A sub-category has many tickets |
| `priorities` | `tickets` | A priority has many tickets |
| `statuses` | `tickets` | A status has many tickets |
| `asset_types` | `assets` | An asset type has many assets |
| `users` | `tickets` (requester) | A user has many tickets created |
| `users` | `tickets` (assignee) | A user has many tickets assigned |
| `users` | `ticket_comments` | A user has many comments |
| `users` | `knowledge_base` | A user has many KB articles |
| `tickets` | `ticket_comments` | A ticket has many comments |
| `tickets` | `ticket_attachments` | A ticket has many attachments |
| `tickets` | `ticket_assignments` | A ticket has many assignments |
| `tickets` | `ticket_status_history` | A ticket has many status changes |
| `tickets` | `ticket_escalations` | A ticket has many escalations |
| `tickets` | `ticket_ratings` | A ticket has one rating |

### 3.2 Many-to-One Relationships

| From | To | Description |
|------|-----|-------------|
| `users` | `departments` | A user belongs to a department |
| `users` | `locations` | A user belongs to a location |
| `assets` | `asset_types` | An asset belongs to an asset type |
| `assets` | `users` | An asset is assigned to a user |
| `assets` | `locations` | An asset is located at a location |
| `tickets` | `users` | A ticket belongs to a requester |
| `tickets` | `users` | A ticket is assigned to an assignee |
| `tickets` | `categories` | A ticket belongs to a category |
| `tickets` | `sub_categories` | A ticket belongs to a sub-category |
| `tickets` | `priorities` | A ticket has a priority |
| `tickets` | `statuses` | A ticket has a status |
| `tickets` | `assets` | A ticket references an asset |
| `tickets` | `locations` | A ticket is at a location |
| `tickets` | `sla_policies` | A ticket follows an SLA policy |
| `ticket_comments` | `tickets` | A comment belongs to a ticket |
| `ticket_comments` | `users` | A comment belongs to a user |
| `ticket_attachments` | `tickets` | An attachment belongs to a ticket |
| `ticket_attachments` | `ticket_comments` | An attachment belongs to a comment |
| `ticket_attachments` | `users` | An attachment was uploaded by a user |
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
| `ticket_ratings` | `tickets` | A rating belongs to a ticket |
| `ticket_ratings` | `users` | A rating was made by a user |
| `knowledge_base` | `categories` | A KB article belongs to a category |
| `knowledge_base` | `users` | A KB article was authored by a user |
| `sla_policies` | `categories` | An SLA policy belongs to a category |
| `sla_policies` | `priorities` | An SLA policy belongs to a priority |
| `departments` | `users` | A department has a manager |
| `categories` | `priorities` | A category has a default priority |
| `categories` | `sla_policies` | A category has a default SLA policy |
| `audit_logs` | `users` | An audit log belongs to a user |

### 3.3 One-to-One Relationships

| From | To | Description |
|------|-----|-------------|
| `tickets` | `ticket_ratings` | A ticket has one rating |

### 3.4 Self-Referencing Relationships

| Entity | Description |
|--------|-------------|
| `departments.manager_id` | A department manager is a user |
| `categories.default_priority_id` | A category references a priority |
| `categories.sla_policy_id` | A category references an SLA policy |