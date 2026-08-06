# MITO IT Helpdesk — Enum Values

## 1. Enum Overview

This document defines all enum values used in the database design. Enums are implemented as PostgreSQL `ENUM` types or `VARCHAR` with `CHECK` constraints depending on the use case.

### 1.1 Implementation Strategy

| Approach | When to Use | Example |
|----------|-------------|---------|
| PostgreSQL `ENUM` type | Fixed, rarely changing values | `users.role` |
| `VARCHAR` + `CHECK` constraint | Values that may evolve | `assets.status` |
| Lookup table | Values that need metadata (color, sort order) | `priorities`, `statuses` |

---

## 2. Enum Definitions

### 2.1 `users.role`

**Type:** PostgreSQL ENUM

**Description:** User role for role-based access control (RBAC).

| Value | Description | Permissions |
|-------|-------------|-------------|
| `employee` | Regular employee | Create tickets, view own tickets, rate resolved tickets |
| `it_staff` | IT support staff | Manage assigned tickets, comment, resolve, escalate |
| `it_manager` | IT department manager | Manage all tickets, view reports, manage SLA policies |
| `admin` | System administrator | Full system access, user management, configuration |

**Default:** `employee`

---

### 2.2 `assets.status`

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

### 2.3 `knowledge_base.status`

**Type:** VARCHAR + CHECK constraint

**Description:** Publication status of knowledge base articles.

| Value | Description |
|-------|-------------|
| `draft` | Article is in draft, not visible to users |
| `published` | Article is published and visible to users |
| `archived` | Article is archived, no longer visible |

**Default:** `draft`

---

## 3. Lookup Table Values

### 3.1 `priorities` — Seed Data

**Description:** Ticket priority levels with SLA targets.

| code | name | level | color | response_time_minutes | resolution_time_minutes |
|------|------|-------|-------|----------------------|------------------------|
| `critical` | Critical | 1 | `#DC2626` | 15 | 240 |
| `high` | High | 2 | `#EA580C` | 60 | 480 |
| `medium` | Medium | 3 | `#D97706` | 240 | 1440 |
| `low` | Low | 4 | `#2563EB` | 480 | 4320 |

---

### 3.2 `statuses` — Seed Data

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

### 3.3 `categories` — Seed Data

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

### 3.4 `sub_categories` — Seed Data

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

### 3.5 `asset_types` — Seed Data

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

### 3.6 `sla_policies` — Seed Data

**Description:** SLA policy definitions per category and priority.

| name | category_id | priority_id | response_time_minutes | resolution_time_minutes | escalation_level_1_minutes | escalation_level_2_minutes | escalation_level_3_minutes |
|------|-------------|-------------|----------------------|------------------------|---------------------------|---------------------------|---------------------------|
| Default Critical | NULL | critical | 15 | 240 | 30 | 60 | 120 |
| Default High | NULL | high | 60 | 480 | 120 | 240 | 480 |
| Default Medium | NULL | medium | 240 | 1440 | 480 | 960 | 1440 |
| Default Low | NULL | low | 480 | 4320 | 960 | 1920 | 2880 |

---

## 4. Enum Usage Summary

| Table | Column | Type | Values |
|-------|--------|------|--------|
| `users` | `role` | ENUM | `employee`, `it_staff`, `it_manager`, `admin` |
| `assets` | `status` | VARCHAR + CHECK | `available`, `assigned`, `in_repair`, `retired`, `lost` |
| `knowledge_base` | `status` | VARCHAR + CHECK | `draft`, `published`, `archived` |
| `priorities` | `code` | Lookup table | `critical`, `high`, `medium`, `low` |
| `statuses` | `code` | Lookup table | `new`, `assigned`, `in_progress`, `on_hold`, `resolved`, `closed`, `reopened` |
| `categories` | `code` | Lookup table | `HARDWARE`, `SOFTWARE`, `NETWORK`, `ACCESS`, `EMAIL`, `PHONE`, `OTHER` |
| `asset_types` | `code` | Lookup table | `LAPTOP`, `DESKTOP`, `MONITOR`, `PRINTER`, `SCANNER`, `PHONE`, `MOBILE`, `SERVER`, `NETWORK_DEVICE`, `PERIPHERAL` |

---

## 5. Enum Management Guidelines

### 5.1 Adding New Values

- For PostgreSQL `ENUM` types, use `ALTER TYPE ... ADD VALUE`.
- For `VARCHAR + CHECK`, update the CHECK constraint.
- For lookup tables, insert a new row.

### 5.2 Removing Values

- **Never delete** enum values that are referenced by existing records.
- Mark values as inactive (`is_active = false`) instead of deleting.
- For PostgreSQL `ENUM`, removal requires recreating the type.

### 5.3 Renaming Values

- For PostgreSQL `ENUM`, use `ALTER TYPE ... RENAME VALUE`.
- For lookup tables, update the row and cascade changes.

### 5.4 Best Practices

1. Use **lookup tables** for values that need UI metadata (color, sort order).
2. Use **PostgreSQL ENUM** for values that are truly fixed.
3. Use **VARCHAR + CHECK** for values that may evolve over time.
4. Always provide a **default value** for enum columns.
5. Document all enum values in this file.