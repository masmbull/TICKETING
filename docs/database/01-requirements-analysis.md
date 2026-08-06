# MITO IT Helpdesk — Requirements Analysis

## 1. System Overview

MITO IT Helpdesk is an enterprise-grade IT support ticketing system that enables employees to submit, track, and resolve IT issues. The system provides a centralized workflow for IT staff to manage support requests efficiently, with SLA tracking, escalation management, and knowledge base support.

## 2. Business Requirements

### 2.1 Ticket Management
- Employees can submit IT support tickets with detailed descriptions.
- Tickets can be categorized by type (hardware, software, network, access, etc.).
- Tickets follow a defined lifecycle: New → Assigned → In Progress → On Hold → Resolved → Closed.
- Tickets can be reopened if the issue persists.
- Tickets can be escalated based on priority and SLA breach.

### 2.2 User & Role Management
- Multiple user roles: Employee, IT Staff, IT Manager, Administrator.
- Users belong to departments.
- IT staff can be assigned to tickets.
- Role-based access control (RBAC) for system features.

### 2.3 Asset Management
- Track IT assets (computers, laptops, printers, monitors, etc.).
- Assets can be linked to tickets for reference.
- Assets are assigned to employees.
- Asset lifecycle tracking (procurement, deployment, maintenance, retirement).

### 2.4 SLA Management
- Define Service Level Agreements per category and priority.
- Track response time and resolution time.
- Automatic escalation on SLA breach.
- Business hours and holiday calendar for SLA calculation.

### 2.5 Knowledge Base
- IT staff can create knowledge base articles.
- Articles can be linked to ticket categories.
- Employees can search for self-service solutions.

### 2.6 Reporting & Analytics
- Ticket volume by category, priority, and status.
- Average response and resolution time.
- SLA compliance rate.
- Agent performance metrics.
- Customer satisfaction ratings.

## 3. Functional Requirements

### 3.1 Ticket Lifecycle
```
New → Assigned → In Progress → On Hold → Resolved → Closed
  ↑         ↑            ↑           ↑
  └─────────┴────────────┴───────────┘ (Reopened)
```

### 3.2 Ticket States
| State | Description |
|-------|-------------|
| `new` | Ticket submitted, awaiting assignment |
| `assigned` | Ticket assigned to IT staff |
| `in_progress` | IT staff actively working on ticket |
| `on_hold` | Waiting for user response or external dependency |
| `resolved` | Solution provided, awaiting user confirmation |
| `closed` | Ticket confirmed resolved and closed |
| `reopened` | Previously resolved/closed ticket reopened |

### 3.3 Priority Levels
| Priority | SLA Response | SLA Resolution |
|----------|-------------|----------------|
| `critical` | 15 minutes | 4 hours |
| `high` | 1 hour | 8 hours |
| `medium` | 4 hours | 24 hours |
| `low` | 8 hours | 72 hours |

## 4. Non-Functional Requirements

### 4.1 Performance
- Support up to 10,000 concurrent users.
- Ticket list queries must return in < 500ms.
- Dashboard queries must return in < 2 seconds.

### 4.2 Security
- All user passwords hashed with bcrypt.
- Role-based access control (RBAC).
- Audit trail for all critical operations.
- Data encryption at rest for sensitive fields.

### 4.3 Availability
- 99.9% uptime target.
- Automated database backups.
- Disaster recovery plan.

### 4.4 Audit & Compliance
- Track all ticket state changes.
- Track all user authentication events.
- Track all data modifications.
- Immutable audit log.

## 5. Data Requirements

### 5.1 Master Data
Master data represents the reference/configuration data that is relatively static and shared across the system.

| Entity | Description |
|--------|-------------|
| `users` | All system users (employees, IT staff, admins) |
| `departments` | Organizational departments |
| `categories` | Ticket categories (top-level) |
| `sub_categories` | Ticket sub-categories |
| `priorities` | Ticket priority levels |
| `statuses` | Ticket status workflow states |
| `asset_types` | Types of IT assets |
| `assets` | IT assets inventory |
| `locations` | Physical locations (offices, floors, rooms) |
| `sla_policies` | SLA definitions per category/priority |
| `holidays` | Company holidays for SLA calculation |
| `knowledge_base` | Knowledge base articles |

### 5.2 Transaction Data
Transaction data represents the operational records created during system usage.

| Entity | Description |
|--------|-------------|
| `tickets` | Main ticket records |
| `ticket_comments` | Comments/updates on tickets |
| `ticket_attachments` | Files attached to tickets |
| `ticket_assignments` | Assignment history |
| `ticket_status_history` | Status change history |
| `ticket_escalations` | Escalation records |
| `ticket_ratings` | Customer satisfaction ratings |
| `audit_logs` | System audit trail |

## 6. Business Rules

### 6.1 Ticket Creation
- Only authenticated users can create tickets.
- Ticket must have a category and description.
- Ticket priority is auto-assigned based on category defaults.
- Ticket status starts as `new`.

### 6.2 Ticket Assignment
- Only IT staff can be assigned to tickets.
- Assignment can be manual or automatic (round-robin).
- Assignment history must be preserved.

### 6.3 Ticket Resolution
- Only assigned IT staff can resolve tickets.
- Resolution requires a solution description.
- User must confirm resolution before ticket is closed.
- Unconfirmed resolutions auto-close after 72 hours.

### 6.4 Escalation
- Escalation triggers when SLA response/resolution time is breached.
- Escalation level increases with each breach.
- Escalation notifies IT Manager.

### 6.5 SLA Calculation
- SLA clock runs only during business hours (Mon-Fri, 08:00-17:00).
- Holidays are excluded from SLA calculation.
- SLA is calculated per category and priority.

## 7. Data Volume Estimates

| Entity | Year 1 | Year 3 | Year 5 |
|--------|--------|--------|--------|
| `users` | 500 | 1,500 | 3,000 |
| `tickets` | 12,000 | 50,000 | 120,000 |
| `ticket_comments` | 36,000 | 150,000 | 360,000 |
| `ticket_attachments` | 6,000 | 25,000 | 60,000 |
| `ticket_status_history` | 60,000 | 250,000 | 600,000 |
| `audit_logs` | 100,000 | 500,000 | 1,500,000 |
| `knowledge_base` | 100 | 500 | 1,000 |
| `assets` | 600 | 2,000 | 4,000 |

## 8. Assumptions

1. The system is used by a single organization (MITO).
2. The organization has multiple departments and physical locations.
3. IT staff are internal employees, not external vendors.
4. The system supports Indonesian business hours and holidays.
5. The primary language is English, with potential for localization.
6. The system will be accessed via web browsers on desktop and mobile.