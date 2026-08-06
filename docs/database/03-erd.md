# MITO IT Helpdesk — Entity Relationship Diagram (ERD) — MVP

## 1. Normalization Level

The database design follows **Third Normal Form (3NF)**:

- **1NF:** All attributes are atomic; no repeating groups.
- **2NF:** All non-key attributes are fully dependent on the primary key.
- **3NF:** No transitive dependencies; non-key attributes depend only on the primary key.

### Normalization Decisions

| Decision | Rationale |
|----------|-----------|
| `roles` is a master table | Roles may change; avoids ENUM limitations |
| `categories` and `sub_categories` are separate tables | Avoids repeating category names in tickets; supports hierarchical categorization |
| `ticket_types` is a master table | Ticket types may evolve; supports future types |
| `priorities` and `statuses` are separate tables | Enables dynamic configuration without code changes; supports UI color coding |
| `ticket_assignments` and `ticket_status_history` are separate tables | Preserves full audit trail of ticket lifecycle |
| `sla_policies` is a separate table | SLA targets can be configured per category/priority without modifying tickets |
| `attachments` uses polymorphic `module`/`module_id` | Reusable attachment design for future modules |
| `audit_logs` uses polymorphic `auditable_type`/`auditable_id` | Single audit table for all entities |

---

## 2. ERD Diagram (Mermaid)

```mermaid
erDiagram
    %% ==================== MASTER DATA ====================
    ROLES ||--o{ USERS : "has"
    DEPARTMENTS ||--o{ USERS : "has"
    DEPARTMENTS ||--o{ DEPARTMENTS : "parent of"
    
    CATEGORIES ||--o{ SUB_CATEGORIES : "has"
    CATEGORIES ||--o{ TICKETS : "categorizes"
    SUB_CATEGORIES ||--o{ TICKETS : "sub-categorizes"
    TICKET_TYPES ||--o{ TICKETS : "types"
    PRIORITIES ||--o{ TICKETS : "prioritizes"
    STATUSES ||--o{ TICKETS : "tracks"
    SLA_POLICIES ||--o{ TICKETS : "governs"
    
    CATEGORIES }o--|| PRIORITIES : "default priority"
    CATEGORIES }o--|| SLA_POLICIES : "default SLA"
    SLA_POLICIES }o--|| PRIORITIES : "applies to"
    
    %% ==================== TRANSACTION DATA ====================
    USERS ||--o{ TICKETS : "requests (requester_id)"
    USERS ||--o{ TICKETS : "handles (assigned_to)"
    
    TICKETS ||--o{ TICKET_COMMENTS : "has"
    TICKETS ||--o{ ATTACHMENTS : "has"
    TICKETS ||--o{ TICKET_ASSIGNMENTS : "has"
    TICKETS ||--o{ TICKET_STATUS_HISTORY : "has"
    TICKETS ||--o{ TICKET_ESCALATIONS : "has"
    
    USERS ||--o{ TICKET_COMMENTS : "writes"
    USERS ||--o{ ATTACHMENTS : "uploads"
    USERS ||--o{ TICKET_ASSIGNMENTS : "assigns (assigned_by)"
    USERS ||--o{ TICKET_ASSIGNMENTS : "receives (assigned_to)"
    USERS ||--o{ TICKET_STATUS_HISTORY : "changes"
    USERS ||--o{ TICKET_ESCALATIONS : "escalates (escalated_by)"
    USERS ||--o{ TICKET_ESCALATIONS : "receives (escalated_to)"
    
    USERS ||--o{ AUDIT_LOGS : "performs"
    
    %% ==================== ENTITY DEFINITIONS ====================
    ROLES {
        BIGINT id PK
        VARCHAR name UK
        TEXT description
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    
    DEPARTMENTS {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        BIGINT parent_department_id FK
        TEXT description
        BIGINT manager_id FK
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    USERS {
        BIGINT id PK
        VARCHAR employee_number UK
        VARCHAR name
        VARCHAR email UK
        VARCHAR password
        VARCHAR job_title
        VARCHAR phone
        VARCHAR extension
        VARCHAR mobile
        BIGINT department_id FK
        BIGINT role_id FK
        BOOLEAN is_active
        TIMESTAMP email_verified_at
        TIMESTAMP last_login_at
        TIMESTAMP last_password_change_at
        VARCHAR remember_token
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    CATEGORIES {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        TEXT description
        BIGINT default_priority_id FK
        BIGINT sla_policy_id FK
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    SUB_CATEGORIES {
        BIGINT id PK
        BIGINT category_id FK
        VARCHAR code
        VARCHAR name
        TEXT description
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    TICKET_TYPES {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        TEXT description
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    PRIORITIES {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        SMALLINT level
        VARCHAR color
        INTEGER response_time_minutes
        INTEGER resolution_time_minutes
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    STATUSES {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        TEXT description
        BOOLEAN is_closed
        BOOLEAN is_resolved
        SMALLINT sort_order
        VARCHAR color
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    SLA_POLICIES {
        BIGINT id PK
        VARCHAR name
        BIGINT category_id FK
        BIGINT priority_id FK
        INTEGER response_time_minutes
        INTEGER resolution_time_minutes
        INTEGER escalation_level_1_minutes
        INTEGER escalation_level_2_minutes
        INTEGER escalation_level_3_minutes
        BOOLEAN business_hours_only
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    TICKETS {
        BIGINT id PK
        VARCHAR ticket_number UK
        BIGINT ticket_type_id FK
        BIGINT category_id FK
        BIGINT sub_category_id FK
        BIGINT priority_id FK
        BIGINT status_id FK
        VARCHAR subject
        TEXT description
        BIGINT requester_id FK
        BIGINT assigned_to FK
        BIGINT sla_policy_id FK
        TIMESTAMP due_response_at
        TIMESTAMP due_resolve_at
        TIMESTAMP first_response_at
        TIMESTAMP resolved_at
        TIMESTAMP closed_at
        SMALLINT reopened_count
        SMALLINT escalation_level
        BOOLEAN is_sla_breached
        TEXT resolution_summary
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    TICKET_COMMENTS {
        BIGINT id PK
        BIGINT ticket_id FK
        BIGINT user_id FK
        TEXT comment
        BOOLEAN is_internal
        BOOLEAN is_system
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    ATTACHMENTS {
        BIGINT id PK
        VARCHAR module
        BIGINT module_id
        VARCHAR filename
        VARCHAR original_filename
        VARCHAR mime_type
        BIGINT file_size
        VARCHAR storage_path
        BIGINT uploaded_by FK
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    TICKET_ASSIGNMENTS {
        BIGINT id PK
        BIGINT ticket_id FK
        BIGINT assigned_by FK
        BIGINT assigned_to FK
        TIMESTAMP assigned_at
        TIMESTAMP unassigned_at
        TEXT reason
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    
    TICKET_STATUS_HISTORY {
        BIGINT id PK
        BIGINT ticket_id FK
        BIGINT from_status_id FK
        BIGINT to_status_id FK
        BIGINT changed_by FK
        TIMESTAMP changed_at
        TEXT note
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    
    TICKET_ESCALATIONS {
        BIGINT id PK
        BIGINT ticket_id FK
        SMALLINT escalation_level
        BIGINT escalated_by FK
        BIGINT escalated_to FK
        TEXT reason
        TIMESTAMP escalated_at
        TIMESTAMP resolved_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }
    
    AUDIT_LOGS {
        BIGINT id PK
        BIGINT user_id FK
        VARCHAR event
        VARCHAR auditable_type
        BIGINT auditable_id
        JSON old_values
        JSON new_values
        VARCHAR ip_address
        VARCHAR user_agent
        TIMESTAMP created_at
    }
```

---

## 3. ERD Legend

| Symbol | Meaning |
|--------|---------|
| `||--o{` | One-to-Many (one entity has many) |
| `}o--||` | Many-to-One |
| `PK` | Primary Key |
| `FK` | Foreign Key |
| `UK` | Unique Key |

---

## 4. Entity Grouping

### 4.1 Master Data Group (MVP)
```
roles ────────┐
departments ──┼── users
              │
categories ───┼── sub_categories
ticket_types ─┼── tickets
priorities ───┼── sla_policies
statuses ─────┘
```

### 4.2 Transaction Data Group (MVP)
```
tickets ────────┬── ticket_comments
                ├── attachments
                ├── ticket_assignments
                ├── ticket_status_history
                └── ticket_escalations

audit_logs (standalone)
```

### 4.3 Future Modules (Deferred)
```
asset_types ──┐
locations ────┼── assets
              │
holidays ─────┘
knowledge_base
ticket_ratings
```

---

## 5. Cardinality Summary

| Relationship | Cardinality | Description |
|-------------|-------------|-------------|
| `roles` → `users` | 1:N | One role has many users |
| `departments` → `users` | 1:N | One department has many users |
| `departments` → `departments` | 1:N | One department has many child departments |
| `categories` → `sub_categories` | 1:N | One category has many sub-categories |
| `categories` → `tickets` | 1:N | One category has many tickets |
| `sub_categories` → `tickets` | 1:N | One sub-category has many tickets |
| `ticket_types` → `tickets` | 1:N | One ticket type has many tickets |
| `priorities` → `tickets` | 1:N | One priority has many tickets |
| `statuses` → `tickets` | 1:N | One status has many tickets |
| `sla_policies` → `tickets` | 1:N | One SLA policy governs many tickets |
| `users` → `tickets` (requester) | 1:N | One user requests many tickets |
| `users` → `tickets` (assigned_to) | 1:N | One user handles many tickets |
| `tickets` → `ticket_comments` | 1:N | One ticket has many comments |
| `tickets` → `attachments` | 1:N | One ticket has many attachments |
| `tickets` → `ticket_assignments` | 1:N | One ticket has many assignments |
| `tickets` → `ticket_status_history` | 1:N | One ticket has many status changes |
| `tickets` → `ticket_escalations` | 1:N | One ticket has many escalations |
| `users` → `attachments` | 1:N | One user uploads many attachments |
| `users` → `audit_logs` | 1:N | One user performs many audit events |