# MITO IT Helpdesk — Enum Values (MVP)

## 1. Enum Overview

This document defines all configurable values used in the database design.

### 1.1 Implementation Strategy

| Approach | When to Use | Example |
|----------|-------------|---------|
| **Master table** | Configurable business data that may change | `roles`, `statuses`, `priorities`, `categories`, `ticket_types` |
| `VARCHAR` + `CHECK` constraint | Values that may evolve but aren't master-data tables | Future: `assets.status`, `knowledge_base.status` |

### 1.2 Key Decision: No ENUM for Configurable Business Data

PostgreSQL `ENUM` types are **NOT used** for configurable business data. This decision was made because:

1. **Flexibility** — Master tables allow adding/removing values without schema changes.
2. **Metadata** — Master tables can store additional attributes (color, sort_order, description).
3. **Referential integrity** — Foreign keys enforce valid values.
4. **Audit** — Changes to value definitions are tracked in `audit_logs`.

---

## 2. Master Table Seed Data

### 2.1 `roles` — Seed Data

**Description:** User roles for RBAC.

| name | description |
|------|-------------|
| `employee` | Regular employee — create tickets, view own tickets |
| `it_staff` | IT support staff — manage assigned tickets, comment, resolve, escalate |
| `it_manager` | IT department manager — manage all tickets, view reports, manage SLA policies |
| `admin` | System administrator — full system access, user management, configuration |

---

### 2.2 `ticket_types` — Seed Data

**Description:** Ticket types.

| code | name | description |
|------|------|-------------|
| `INCIDENT` | Incident | Unplanned interruption or reduction in quality of IT service |
| `SERVICE_REQUEST` | Service Request | Request for new service or information |
| `PROBLEM` | Problem | Root cause of one or more incidents |
| `CHANGE_REQUEST` | Change Request | Request for a change to IT infrastructure |

---

### 2.3 `priorities` — Seed Data

**Description:** Ticket priority levels with SLA targets.

| code | name | level | color | response_time_minutes | resolution_time_minutes |
|------|------|-------|-------|----------------------|------------------------|
| `critical` | Critical | 1 | `#DC2626` | 15 | 240 |
| `high` | High | 2 | `#EA580C` | 60 | 480 |
| `medium` | Medium | 3 | `#D97706` | 240 | 1440 |
| `low` | Low | 4 | `#2563EB` | 480 | 4320 |

---

### 2.4 `statuses` — Seed Data

**Description:** Ticket status workflow states.

| code | name | is_closed | is_resolved | sort_order | color |
|------|------|-----------|-------------|------------|-------|
| `new` | New | false | false | 1 | `#6B7280` |
| `assigned` | Assigned | false | false | 2 | `#2563EB` |
| `in_progress` | In Progress | false | false | 3 | `#D97706` |
| `on_hold` | On Hold | false | false | 4 | `#7C3AED` |
| `resolved` | Resolved | false | true | 5 | `#059669` |
| `closed` | Closed | true | true | 6 | `#374151` |
| `reopened` | Reopened | false | false | 7 | `#DC2626` |

---

### 2.5 `categories` — Seed Data

**Description:** Top-level ticket categories.

| code | name | description |
|------|------|-------------|
| `HARDWARE` | Hardware | Hardware-related issues (computers, printers, peripherals) |
| `SOFTWARE` | Software | Software-related issues (applications, OS, licenses) |
| `NETWORK` | Network | Network connectivity issues (WiFi, LAN, VPN) |
| `ACCESS` | Access | Access and permission issues (accounts, credentials) |
| `EMAIL` | Email | Email-related issues (Outlook, Exchange, spam) |
| `PHONE` | Phone | Phone system issues (VoIP, desk phones) |
| `OTHER` | Other | Other IT-related issues |

---

### 2.6 `sub_categories` — Seed Data

**Description:** Sub-categories under top-level categories.

| category_code | code | name |
|---------------|------|------|
| `HARDWARE` | `LAPTOP` | Laptop |
| `HARDWARE` | `DESKTOP` | Desktop Computer |
| `HARDWARE` | `PRINTER` | Printer |
| `HARDWARE` | `MONITOR` | Monitor |
| `HARDWARE` | `PERIPHERAL` | Peripheral (keyboard, mouse, etc.) |
| `SOFTWARE` | `OS` | Operating System |
| `SOFTWARE` | `APPLICATION` | Application |
| `SOFTWARE` | `LICENSE` | License |
| `NETWORK` | `WIFI` | WiFi |
| `NETWORK` | `LAN` | Wired Network |
| `NETWORK` | `VPN` | VPN |
| `ACCESS` | `ACCOUNT` | Account |
| `ACCESS` | `PERMISSION` | Permission |
| `EMAIL` | `OUTLOOK` | Outlook |
| `EMAIL` | `SPAM` | Spam |
| `PHONE` | `VOIP` | VoIP |
| `PHONE` | `DESK_PHONE` | Desk Phone |

---

### 2.7 `sla_policies` — Seed Data

**Description:** SLA policy definitions per category and priority.

| name | category_id | priority_id | response_time_minutes | resolution_time_minutes | escalation_level_1_minutes | escalation_level_2_minutes | escalation_level_3_minutes |
|------|-------------|-------------|----------------------|------------------------|---------------------------|---------------------------|---------------------------|
| Default Critical | NULL | critical | 15 | 240 | 30 | 60 | 120 |
| Default High | NULL | high | 60 | 480 | 120 | 240 | 480 |
| Default Medium | NULL | medium | 240 | 1440 | 480 | 960 | 1440 |
| Default Low | NULL | low | 480 | 4320 | 960 | 1920 | 2880 |

---

## 3. Future Module Values (Deferred from MVP)

These values are used by future modules and are documented for completeness. They will NOT be implemented in the MVP.

### 3.1 `assets.status` (Future)

**Type:** VARCHAR + CHECK constraint

**Description:** Lifecycle status of an IT asset.

| Value | Description |
|-------|-------------|
| `available` | Asset is available for assignment |
| `assigned` | Asset is currently assigned to a user |
| `in_repair` | Asset is being repaired |
| `retired` | Asset has been decommissioned |
| `lost` | Asset is lost or missing |

**Default:** `available`

---

### 3.2 `knowledge_base.status` (Future)

**Type:** VARCHAR + CHECK constraint

**Description:** Publication status of knowledge base articles.

| Value | Description |
|-------|-------------|
| `draft` | Article is in draft, not visible to users |
| `published` | Article is published and visible to users |
| `archived` | Article is archived, no longer visible |

**Default:** `draft`

---

### 3.3 `asset_types` — Seed Data (Future)

**Description:** Types of IT assets.

| code | name | description |
|------|------|-------------|
| `LAPTOP` | Laptop | Portable computers |
| `DESKTOP` | Desktop | Desktop computers |
| `MONITOR` | Monitor | Display monitors |
| `PRINTER` | Printer | Printers and multifunction devices |
| `SCANNER` | Scanner | Document scanners |
| `PHONE` | Phone | Desk phones and VoIP devices |
| `MOBILE` | Mobile Device | Smartphones and tablets |
| `SERVER` | Server | Server hardware |
| `NETWORK_DEVICE` | Network Device | Routers, switches, access points |
| `PERIPHERAL` | Peripheral | Keyboard, mouse, docking station, etc. |

---

## 4. Configurable Values Summary

| Table | Column | Type | Values |
|-------|--------|------|--------|
| `roles` | `name` | Master table | `employee`, `it_staff`, `it_manager`, `admin` |
| `ticket_types` | `code` | Master table | `INCIDENT`, `SERVICE_REQUEST`, `PROBLEM`, `CHANGE_REQUEST` |
| `priorities` | `code` | Master table | `critical`, `high`, `medium`, `low` |
| `statuses` | `code` | Master table | `new`, `assigned`, `in_progress`, `on_hold`, `resolved`, `closed`, `reopened` |
| `categories` | `code` | Master table | `HARDWARE`, `SOFTWARE`, `NETWORK`, `ACCESS`, `EMAIL`, `PHONE`, `OTHER` |

---

## 5. Value Management Guidelines

### 5.1 Adding New Values

- For **master tables**, insert a new row.
- For `VARCHAR + CHECK`, update the CHECK constraint.

### 5.2 Removing Values

- **Never delete** values that are referenced by existing records.
- Mark values as inactive (`is_active = false`) instead of deleting.

### 5.3 Renaming Values

- For **master tables**, update the row and cascade changes to referencing records.
- Document all changes in this file.

### 5.4 Best Practices

1. Use **master tables** for all configurable business data.
2. Do **NOT use PostgreSQL ENUM** for configurable values.
3. Always provide a **default value** for new columns.
4. Document all configurable values in this file.