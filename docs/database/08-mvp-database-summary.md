# MITO IT Helpdesk — MVP Database Summary

## 1. Final MVP Tables

### 1.1 Master Data Tables (8)

| # | Table | Purpose | Key Columns |
|---|-------|---------|-------------|
| 1 | `roles` | User roles for RBAC | `id`, `name`, `description` |
| 2 | `departments` | Organizational departments with hierarchy | `id`, `code`, `name`, `parent_department_id`, `manager_id` |
| 3 | `categories` | Top-level ticket categories | `id`, `code`, `name`, `default_priority_id`, `sla_policy_id` |
| 4 | `sub_categories` | Ticket sub-categories | `id`, `category_id`, `code`, `name` |
| 5 | `ticket_types` | Ticket types | `id`, `code`, `name`, `description` |
| 6 | `priorities` | Priority levels with SLA targets | `id`, `code`, `name`, `level`, `color`, `response_time_minutes`, `resolution_time_minutes` |
| 7 | `statuses` | Ticket status workflow states | `id`, `code`, `name`, `is_closed`, `is_resolved`, `sort_order`, `color` |
| 8 | `sla_policies` | SLA definitions per category/priority | `id`, `name`, `category_id`, `priority_id`, `response_time_minutes`, `resolution_time_minutes`, `escalation_level_1_minutes`, `escalation_level_2_minutes`, `escalation_level_3_minutes` |

### 1.2 Transaction Data Tables (8)

| # | Table | Purpose | Key Columns |
|---|-------|---------|-------------|
| 9 | `users` | All system users | `id`, `employee_number`, `name`, `email`, `password`, `job_title`, `phone`, `extension`, `mobile`, `department_id`, `role_id`, `last_password_change_at` |
| 10 | `tickets` | Main ticket records | `id`, `ticket_number`, `ticket_type_id`, `category_id`, `sub_category_id`, `priority_id`, `status_id`, `subject`, `description`, `requester_id`, `assigned_to`, `due_response_at`, `due_resolve_at`, `resolved_at`, `closed_at` |
| 11 | `ticket_comments` | Comments on tickets | `id`, `ticket_id`, `user_id`, `comment`, `is_internal`, `is_system` |
| 12 | `attachments` | Reusable file attachments | `id`, `module`, `module_id`, `filename`, `original_filename`, `mime_type`, `file_size`, `storage_path`, `uploaded_by` |
| 13 | `ticket_assignments` | Assignment history | `id`, `ticket_id`, `assigned_by`, `assigned_to`, `assigned_at`, `unassigned_at` |
| 14 | `ticket_status_history` | Status change history | `id`, `ticket_id`, `from_status_id`, `to_status_id`, `changed_by`, `changed_at` |
| 15 | `ticket_escalations` | Escalation records | `id`, `ticket_id`, `escalation_level`, `escalated_by`, `escalated_to`, `reason`, `escalated_at` |
| 16 | `audit_logs` | System audit trail | `id`, `user_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent` |

### 1.3 MVP Database Statistics

| Aspect | Value |
|--------|-------|
| **Total MVP Tables** | 16 |
| **Master Data Tables** | 8 |
| **Transaction Data Tables** | 8 |
| **Total Columns** | 182 |
| **Total Foreign Keys** | 31 |
| **Normalization Level** | 3NF |

---

## 2. Deferred Modules (Future Phases)

### 2.1 Deferred Tables

| # | Table | Type | Description | Reason for Deferral |
|---|-------|------|-------------|---------------------|
| 17 | `asset_types` | Master | Types of IT assets | Asset management is a post-MVP enhancement |
| 18 | `assets` | Master | IT assets inventory | Requires asset procurement and lifecycle workflows |
| 19 | `locations` | Master | Physical locations | Not required for core ticketing workflow |
| 20 | `holidays` | Master | Company holidays for SLA | Can be managed ad-hoc in MVP; needs calendar integration |
| 21 | `knowledge_base` | Master | Knowledge base articles | Self-service portal is a future phase |
| 22 | `ticket_ratings` | Transaction | Customer satisfaction ratings | Survey functionality is a future phase |

### 2.2 Impact of Deferral

| Deferred Module | MVP Impact | Future Integration |
|-----------------|------------|-------------------|
| `assets` / `asset_types` | Tickets don't reference assets | Add `asset_id` FK to `tickets` table |
| `locations` | Users don't have location | Add `location_id` FK to `users` table |
| `holidays` | SLA uses fixed business hours only | Add holiday lookup to SLA calculation |
| `knowledge_base` | No self-service articles | New module with `categories` FK |
| `ticket_ratings` | No satisfaction surveys | Add `ticket_ratings` table with `tickets` FK |

---

## 3. Design Decisions

### 3.1 Master Tables Instead of ENUM

**Decision:** All configurable business data uses master tables instead of PostgreSQL ENUM types.

**Affected:** `roles`, `statuses`, `priorities`, `categories`, `sub_categories`, `ticket_types`

**Rationale:**
- Allows adding/removing values without schema changes
- Supports metadata (color, sort_order, description)
- Maintains referential integrity via foreign keys
- Facilitates audit tracking of configuration changes

### 3.2 `roles` Master Table

**Decision:** Replace the `users.role` ENUM with a `roles` master table.

**Design:**
- `roles` table: `id`, `name`, `description`, `created_at`, `updated_at`
- `users.role_id` FK references `roles.id`
- Seed data: `employee`, `it_staff`, `it_manager`, `admin`

**Rationale:**
- Future roles can be added without migration
- Supports role descriptions and metadata
- Enables role-based permissions mapping in future phases

### 3.3 `ticket_types` Master Table

**Decision:** Add a `ticket_types` master table.

**Design:**
- `ticket_types` table: `id`, `code`, `name`, `description`, `is_active`
- `tickets.ticket_type_id` FK references `ticket_types.id`
- Seed data: `INCIDENT`, `SERVICE_REQUEST`, `PROBLEM`, `CHANGE_REQUEST`

**Rationale:**
- ITIL-aligned ticket classification
- Future ticket types can be added without schema changes

### 3.4 Reusable `attachments` Table

**Decision:** Replace `ticket_attachments` with a reusable `attachments` table.

**Design:**
- Polymorphic design using `module` and `module_id`
- `module` values: `ticket`, `comment`, and future modules
- Composite index on `(module, module_id)`

**Rationale:**
- Supports file attachments for any future module
- Avoids creating separate attachment tables per module

### 3.5 Department Hierarchy

**Decision:** Add `parent_department_id` to `departments`.

**Design:**
- Self-referencing FK: `departments.parent_department_id → departments.id`
- Supports unlimited organizational hierarchy levels
- Also added `code` for department identification

**Rationale:**
- Real-world organizations have nested departmental structures
- Enables hierarchical reporting and management

### 3.6 Enhanced User Profile

**Decision:** Extend `users` with additional profile fields.

**Added Fields:**
- `job_title` — Professional title
- `employee_number` — Unique employee identifier
- `extension` — Phone extension
- `mobile` — Mobile phone number
- `last_password_change_at` — Password change tracking

**Rationale:**
- Complete employee profile for support context
- `last_password_change_at` supports password policy enforcement

### 3.7 Ticket Numbering Format

**Decision:** Use `HD-YYYYMMDD-000001` format.

**Design:**
- Prefix: `HD` (Helpdesk)
- Date: `YYYYMMDD` (creation date)
- Sequence: zero-padded 6 digits, resets daily

**Rationale:**
- Human-readable and memorable
- Contains creation date for quick reference
- Daily reset keeps numbers short and manageable

### 3.8 SLA Timestamps

**Decision:** Rename and standardize SLA timestamp fields on `tickets`.

**Design:**
- `due_response_at` — SLA response due time
- `due_resolve_at` — SLA resolution due time
- `first_response_at` — Actual first response time
- `resolved_at` — Actual resolution time
- `closed_at` — Closure time

**Rationale:**
- Clear naming distinguishes due vs. actual times
- Supports SLA compliance calculation and reporting

---

## 4. Future Expansion Strategy

### 4.1 Phase Roadmap

| Phase | Module | Tables Added |
|-------|--------|--------------|
| **MVP** | Authentication, Ticketing, SLA, Audit, Attachments | 16 tables |
| **Phase 5+** | Asset Management | `asset_types`, `assets` (+ add `asset_id` FK to `tickets`) |
| **Phase 6+** | Location Management | `locations` (+ add `location_id` FK to `users` and `assets`) |
| **Phase 7+** | Knowledge Base | `knowledge_base` |
| **Phase 8+** | Customer Satisfaction | `ticket_ratings` |
| **Phase 9+** | Holiday Calendar | `holidays` |

### 4.2 Expansion Principles

1. **Additive migration** — All future expansions add new tables or columns without breaking existing schema.
2. **FK extension** — Add FKs to existing tables (e.g., `tickets.asset_id`, `users.location_id`) as NULL-able columns.
3. **Reusable design** — The `attachments` polymorphic design supports new modules without changes.
4. **Audit first** — All future modules must integrate with `audit_logs`.
5. **Master tables** — All future configurable data will use master tables, never ENUM.

### 4.3 Migration Impact Assessment

| Deferred Module | Migration Type | Impact |
|-----------------|---------------|--------|
| Asset Management | Add tables + add FK to `tickets` | Low — additive only |
| Location Management | Add table + add FK to `users` | Low — additive only |
| Knowledge Base | Add table | Low — additive only |
| Ratings | Add table | Low — additive only |
| Holidays | Add table | Low — additive only |

---

## 5. Implementation Checklist

### 5.1 MVP Migration Order

| Order | Table | Dependencies |
|-------|-------|--------------|
| 1 | `roles` | None |
| 2 | `departments` | `users` (for `manager_id`, circular — resolve with `SET NULL`) |
| 3 | `users` | `roles`, `departments` |
| 4 | `categories` | None |
| 5 | `sub_categories` | `categories` |
| 6 | `ticket_types` | None |
| 7 | `priorities` | None |
| 8 | `statuses` | None |
| 9 | `sla_policies` | `categories`, `priorities` |
| 10 | `tickets` | `ticket_types`, `categories`, `sub_categories`, `priorities`, `statuses`, `users`, `sla_policies` |
| 11 | `ticket_comments` | `tickets`, `users` |
| 12 | `attachments` | `users` |
| 13 | `ticket_assignments` | `tickets`, `users` |
| 14 | `ticket_status_history` | `tickets`, `statuses`, `users` |
| 15 | `ticket_escalations` | `tickets`, `users` |
| 16 | `audit_logs` | `users` |

### 5.2 Seed Data Order

| Order | Table | Records |
|-------|-------|---------|
| 1 | `roles` | 4 |
| 2 | `ticket_types` | 4 |
| 3 | `priorities` | 4 |
| 4 | `statuses` | 7 |
| 5 | `categories` | 7 |
| 6 | `sub_categories` | 17 |
| 7 | `sla_policies` | 4 |
| 8 | `departments` | Initial org structure |
| 9 | `users` | Admin account + IT staff |