# MITO IT Helpdesk — Entity Relationship Diagram (ERD)

## 1. Normalization Level

The database design follows **Third Normal Form (3NF)**:

- **1NF:** All attributes are atomic; no repeating groups.
- **2NF:** All non-key attributes are fully dependent on the primary key.
- **3NF:** No transitive dependencies; non-key attributes depend only on the primary key.

### Normalization Decisions

| Decision | Rationale |
|----------|-----------|
| `categories` and `sub_categories` are separate tables | Avoids repeating category names in tickets; supports hierarchical categorization |
| `priorities` and `statuses` are separate tables | Enables dynamic configuration without code changes; supports UI color coding |
| `ticket_assignments` and `ticket_status_history` are separate tables | Preserves full audit trail of ticket lifecycle |
| `sla_policies` is a separate table | SLA targets can be configured per category/priority without modifying tickets |
| `ticket_ratings` is a separate table | One-to-one with tickets but keeps rating data isolated for reporting |
| `audit_logs` uses polymorphic `auditable_type`/`auditable_id` | Single audit table for all entities |

---

## 2. ERD Diagram (Mermaid)

```mermaid
erDiagram
    %% ==================== MASTER DATA ====================
    DEPARTMENTS ||--o{ USERS : "has"
    LOCATIONS ||--o{ USERS : "has"
    LOCATIONS ||--o{ ASSETS : "located at"
    ASSET_TYPES ||--o{ ASSETS : "classifies"
    USERS ||--o{ ASSETS : "assigned to"
    
    CATEGORIES ||--o{ SUB_CATEGORIES : "has"
    CATEGORIES ||--o{ TICKETS : "categorizes"
    SUB_CATEGORIES ||--o{ TICKETS : "sub-categorizes"
    PRIORITIES ||--o{ TICKETS : "prioritizes"
    STATUSES ||--o{ TICKETS : "tracks"
    SLA_POLICIES ||--o{ TICKETS : "governs"
    
    CATEGORIES }o--|| PRIORITIES : "default priority"
    CATEGORIES }o--|| SLA_POLICIES : "default SLA"
    SLA_POLICIES }o--|| PRIORITIES : "applies to"
    
    USERS ||--o{ KNOWLEDGE_BASE : "authors"
    CATEGORIES ||--o{ KNOWLEDGE_BASE : "categorizes"
    
    %% ==================== TRANSACTION DATA ====================
    USERS ||--o{ TICKETS : "requests (requester_id)"
    USERS ||--o{ TICKETS : "handles (assignee_id)"
    ASSETS ||--o{ TICKETS : "referenced in"
    LOCATIONS ||--o{ TICKETS : "reported at"
    
    TICKETS ||--o{ TICKET_COMMENTS : "has"
    TICKETS ||--o{ TICKET_ATTACHMENTS : "has"
    TICKETS ||--o{ TICKET_ASSIGNMENTS : "has"
    TICKETS ||--o{ TICKET_STATUS_HISTORY : "has"
    TICKETS ||--o{ TICKET_ESCALATIONS : "has"
    TICKETS ||--|| TICKET_RATINGS : "has one"
    
    USERS ||--o{ TICKET_COMMENTS : "writes"
    USERS ||--o{ TICKET_ATTACHMENTS : "uploads"
    USERS ||--o{ TICKET_ASSIGNMENTS : "assigns (assigned_by)"
    USERS ||--o{ TICKET_ASSIGNMENTS : "receives (assigned_to)"
    USERS ||--o{ TICKET_STATUS_HISTORY : "changes"
    USERS ||--o{ TICKET_ESCALATIONS : "escalates (escalated_by)"
    USERS ||--o{ TICKET_ESCALATIONS : "receives (escalated_to)"
    USERS ||--o{ TICKET_RATINGS : "rates"
    
    TICKET_COMMENTS ||--o{ TICKET_ATTACHMENTS : "has"
    
    USERS ||--o{ AUDIT_LOGS : "performs"
    
    %% ==================== ENTITY DEFINITIONS ====================
    DEPARTMENTS {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        TEXT description
        BIGINT manager_id FK
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    LOCATIONS {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        TEXT address
        VARCHAR city
        VARCHAR country
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    USERS {
        BIGINT id PK
        VARCHAR employee_id UK
        VARCHAR name
        VARCHAR email UK
        VARCHAR password
        VARCHAR phone
        BIGINT department_id FK
        BIGINT location_id FK
        ENUM role
        BOOLEAN is_active
        TIMESTAMP email_verified_at
        TIMESTAMP last_login_at
        VARCHAR remember_token
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    ASSET_TYPES {
        BIGINT id PK
        VARCHAR code UK
        VARCHAR name
        TEXT description
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    ASSETS {
        BIGINT id PK
        VARCHAR asset_tag UK
        BIGINT asset_type_id FK
        VARCHAR name
        VARCHAR brand
        VARCHAR model
        VARCHAR serial_number UK
        DATE purchase_date
        DECIMAL purchase_cost
        DATE warranty_expiry
        ENUM status
        BIGINT assigned_to FK
        BIGINT location_id FK
        TEXT notes
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
    
    HOLIDAYS {
        BIGINT id PK
        VARCHAR name
        DATE holiday_date UK
        BOOLEAN is_recurring
        TEXT description
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    KNOWLEDGE_BASE {
        BIGINT id PK
        VARCHAR title
        VARCHAR slug UK
        TEXT content
        BIGINT category_id FK
        BIGINT author_id FK
        ENUM status
        BIGINT views_count
        BIGINT helpful_count
        BIGINT not_helpful_count
        TIMESTAMP published_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    
    TICKETS {
        BIGINT id PK
        VARCHAR ticket_number UK
        VARCHAR subject
        TEXT description
        BIGINT requester_id FK
        BIGINT assignee_id FK
        BIGINT category_id FK
        BIGINT sub_category_id FK
        BIGINT priority_id FK
        BIGINT status_id FK
        BIGINT asset_id FK
        BIGINT location_id FK
        BIGINT sla_policy_id FK
        TIMESTAMP sla_due_at
        TIMESTAMP resolution_due_at
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
    
    TICKET_ATTACHMENTS {
        BIGINT id PK
        BIGINT ticket_id FK
        BIGINT comment_id FK
        BIGINT uploaded_by FK
        VARCHAR original_name
        VARCHAR stored_name
        VARCHAR path
        VARCHAR mime_type
        BIGINT size_bytes
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
    
    TICKET_RATINGS {
        BIGINT id PK
        BIGINT ticket_id FK UK
        SMALLINT rating
        TEXT comment
        BIGINT rated_by FK
        TIMESTAMP rated_at
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
| `||--||` | One-to-One |
| `}o--||` | Many-to-One |
| `PK` | Primary Key |
| `FK` | Foreign Key |
| `UK` | Unique Key |

---

## 4. Entity Grouping

### 4.1 Master Data Group
```
departments ──┐
locations ────┼── users
              │
asset_types ──┼── assets
              │
categories ───┼── sub_categories
priorities ───┼── sla_policies
statuses ─────┘
holidays
knowledge_base
```

### 4.2 Transaction Data Group
```
tickets ────────┬── ticket_comments
                ├── ticket_attachments
                ├── ticket_assignments
                ├── ticket_status_history
                ├── ticket_escalations
                └── ticket_ratings

audit_logs (standalone)
```

---

## 5. Cardinality Summary

| Relationship | Cardinality | Description |
|-------------|-------------|-------------|
| `departments` → `users` | 1:N | One department has many users |
| `locations` → `users` | 1:N | One location has many users |
| `locations` → `assets` | 1:N | One location has many assets |
| `asset_types` → `assets` | 1:N | One asset type has many assets |
| `users` → `assets` | 1:N | One user can be assigned many assets |
| `categories` → `sub_categories` | 1:N | One category has many sub-categories |
| `categories` → `tickets` | 1:N | One category has many tickets |
| `sub_categories` → `tickets` | 1:N | One sub-category has many tickets |
| `priorities` → `tickets` | 1:N | One priority has many tickets |
| `statuses` → `tickets` | 1:N | One status has many tickets |
| `sla_policies` → `tickets` | 1:N | One SLA policy governs many tickets |
| `users` → `tickets` (requester) | 1:N | One user requests many tickets |
| `users` → `tickets` (assignee) | 1:N | One user handles many tickets |
| `assets` → `tickets` | 1:N | One asset is referenced in many tickets |
| `locations` → `tickets` | 1:N | One location has many tickets |
| `tickets` → `ticket_comments` | 1:N | One ticket has many comments |
| `tickets` → `ticket_attachments` | 1:N | One ticket has many attachments |
| `tickets` → `ticket_assignments` | 1:N | One ticket has many assignments |
| `tickets` → `ticket_status_history` | 1:N | One ticket has many status changes |
| `tickets` → `ticket_escalations` | 1:N | One ticket has many escalations |
| `tickets` → `ticket_ratings` | 1:1 | One ticket has one rating |
| `ticket_comments` → `ticket_attachments` | 1:N | One comment has many attachments |
| `users` → `knowledge_base` | 1:N | One user authors many KB articles |
| `categories` → `knowledge_base` | 1:N | One category has many KB articles |
| `users` → `audit_logs` | 1:N | One user performs many audit events |