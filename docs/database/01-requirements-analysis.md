# MITO IT Helpdesk — Requirements Analysis

## 1. System Overview

MITO IT Helpdesk is an enterprise-grade IT support ticketing system that enables employees to submit, track, and resolve IT issues. The system provides a centralized workflow for IT staff to manage support requests efficiently, with SLA tracking and escalation management.

## 2. Business Requirements

### 2.1 Ticket Management
- Employees can submit IT support tickets with detailed descriptions.
- Tickets can be categorized by type (Incident, Service Request, Problem, Change Request).
- Tickets can be categorized by category and sub-category (hardware, software, network, access, etc.).
- Tickets follow a defined lifecycle: New → Assigned → In Progress → On Hold → Resolved → Closed.
- Tickets can be reopened if the issue persists.
- Tickets can be escalated based on priority and SLA breach.

### 2.2 User & Role Management
- Multiple user roles: Employee, IT Staff, IT Manager, Administrator.
- Roles are stored in a master table for flexibility.
- Users belong to departments.
- IT staff can be assigned to tickets.
- Role-based access control (RBAC) for system features.

### 2.3 SLA Management
- Define Service Level Agreements per category and priority.
- Track response time and resolution time.
- Automatic escalation on SLA breach.

### 2.4 Reporting & Analytics
- Ticket volume by category, priority, and status.
- Average response and resolution time.
- SLA compliance rate.
- Agent performance metrics.

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

### 3.3 Ticket Types
| Type | Description |
|------|-------------|
| `incident` | Unplanned interruption or reduction in quality of IT service |
| `service_request` | Request for new service or information |
| `problem` | Root cause of one or more incidents |
| `change_request` | Request for a change to IT infrastructure |

### 3.4 Priority Levels
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

### 5.1 MVP Master Data
Master data represents the reference/configuration data that is relatively static and shared across the system.

| Entity | Description |
|--------|-------------|
| `roles` | User roles (employee, IT staff, IT manager, admin) |
| `departments` | Organizational departments with hierarchy |
| `categories` | Ticket categories (top-level) |
| `sub_categories` | Ticket sub-categories |
| `ticket_types` | Ticket types (incident, service request, problem, change request) |
| `priorities` | Ticket priority levels |
| `statuses` | Ticket status workflow states |
| `sla_policies` | SLA definitions per category/priority |

### 5.2 MVP Transaction Data
Transaction data represents the operational records created during system usage.

| Entity | Description |
|--------|-------------|
| `users` | All system users |
| `tickets` | Main ticket records |
| `ticket_comments` | Comments/updates on tickets |
| `attachments` | Reusable file attachments |
| `ticket_assignments` | Assignment history |
| `ticket_status_history` | Status change history |
| `ticket_escalations` | Escalation records |
| `audit_logs` | System audit trail |

### 5.3 Future Modules (Deferred from MVP)
These modules are designed but will be implemented in future phases.

| Entity | Description | Phase |
|--------|-------------|-------|
| `assets` | IT assets inventory | Future |
| `asset_types` | Types of IT assets | Future |
| `knowledge_base` | Knowledge base articles | Future |
| `locations` | Physical locations | Future |
| `holidays` | Company holidays for SLA calculation | Future |
| `ticket_ratings` | Customer satisfaction ratings | Future |

## 6. Business Rules

### 6.1 Ticket Creation
- Only authenticated users can create tickets.
- Ticket must have a type, category, and description.
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
- SLA is calculated per category and priority.

## 7. Data Volume Estimates

| Entity | Year 1 | Year 3 | Year 5 |
|--------|--------|--------|--------|
| `users` | 500 | 1,500 | 3,000 |
| `tickets` | 12,000 | 50,000 | 120,000 |
| `ticket_comments` | 36,000 | 150,000 | 360,000 |
| `attachments` | 6,000 | 25,000 | 60,000 |
| `ticket_status_history` | 60,000 | 250,000 | 600,000 |
| `audit_logs` | 100,000 | 500,000 | 1,500,000 |

## 8. Assumptions

1. The system is used by a single organization (MITO).
2. The organization has multiple departments.
3. IT staff are internal employees, not external vendors.
4. The system supports Indonesian business hours.
5. The primary language is English, with potential for localization.
6. The system will be accessed via web browsers on desktop and mobile.
7. Asset management, knowledge base, and ratings are future enhancements.