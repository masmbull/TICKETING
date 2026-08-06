# MITO IT Helpdesk — Audit Strategy (MVP)

## 1. Audit Objectives

The audit strategy ensures:

1. **Accountability** — Every critical action can be traced to a specific user.
2. **Compliance** — Meet regulatory and organizational compliance requirements.
3. **Forensics** — Support incident investigation and root cause analysis.
4. **Integrity** — Detect unauthorized or accidental data modifications.
5. **Reporting** — Provide audit data for management and compliance reports.

---

## 2. Audit Architecture

### 2.1 Two-Layer Audit Approach

The system uses a **two-layer audit approach**:

| Layer | Mechanism | Purpose |
|-------|-----------|---------|
| **Application Layer** | `audit_logs` table | Structured audit trail for business events |
| **Database Layer** | PostgreSQL triggers | Immutable audit trail for data changes |

### 2.2 Audit Data Flow

```
User Action
    │
    ▼
┌─────────────────┐
│  Application    │
│  (Laravel)      │
└────────┬────────┘
         │
         ├──→ Business Event → audit_logs table
         │
         └──→ Data Change → PostgreSQL trigger → audit_logs table
```

---

## 3. Audit Log Table Design

### 3.1 `audit_logs` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGSERIAL | Primary key |
| `user_id` | BIGINT | Actor (NULL = system/cron) |
| `event` | VARCHAR(50) | Event type |
| `auditable_type` | VARCHAR(100) | Model class name |
| `auditable_id` | BIGINT | Record ID |
| `old_values` | JSON | Previous values (for updates) |
| `new_values` | JSON | New values |
| `ip_address` | VARCHAR(45) | Client IP |
| `user_agent` | VARCHAR(500) | Client user agent |
| `created_at` | TIMESTAMP | Event timestamp |

### 3.2 Event Types

| Event | Description | Example |
|-------|-------------|---------|
| `created` | Record created | Ticket created |
| `updated` | Record updated | Ticket priority changed |
| `deleted` | Record soft-deleted | Comment deleted |
| `restored` | Record restored | Ticket restored |
| `force_deleted` | Record permanently deleted | Attachment purged |
| `login` | User logged in | Authentication event |
| `logout` | User logged out | Authentication event |
| `login_failed` | Login attempt failed | Security event |
| `password_changed` | Password changed | Security event |
| `assigned` | Ticket assigned | Assignment event |
| `unassigned` | Ticket unassigned | Assignment event |
| `status_changed` | Ticket status changed | Workflow event |
| `escalated` | Ticket escalated | Escalation event |
| `exported` | Data exported | Compliance event |

---

## 4. Audit Scope

### 4.1 Entities to Audit (MVP)

| Entity | Create | Update | Delete | Notes |
|--------|--------|--------|--------|-------|
| `roles` | ✅ | ✅ | ✅ | |
| `departments` | ✅ | ✅ | ✅ | |
| `categories` | ✅ | ✅ | ✅ | |
| `sub_categories` | ✅ | ✅ | ✅ | |
| `ticket_types` | ✅ | ✅ | ✅ | |
| `priorities` | ✅ | ✅ | ✅ | |
| `statuses` | ✅ | ✅ | ✅ | |
| `sla_policies` | ✅ | ✅ | ✅ | |
| `users` | ✅ | ✅ | ✅ | Exclude password field from values |
| `tickets` | ✅ | ✅ | ✅ | High priority audit |
| `ticket_comments` | ✅ | ✅ | ✅ | |
| `attachments` | ✅ | ✅ | ✅ | |
| `ticket_assignments` | ✅ | ✅ | ✅ | |
| `ticket_status_history` | ✅ | ✅ | ✅ | |
| `ticket_escalations` | ✅ | ✅ | ✅ | |
| `audit_logs` | — | — | — | Append-only, no audit of audit |

### 4.2 Fields to Exclude from Audit

| Field | Reason |
|-------|--------|
| `password` | Security — never store hashed passwords in audit logs |
| `remember_token` | Security — session token |
| `old_values` / `new_values` (nested) | Prevent recursive audit |
| Large binary data | Performance — store file metadata only |

---

## 5. Implementation Strategy

### 5.1 Application-Level Auditing (Laravel)

Use Laravel **model events** and **observers**:

```php
// Example: TicketObserver
class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'created',
            'auditable_type' => Ticket::class,
            'auditable_id' => $ticket->id,
            'new_values' => $ticket->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

### 5.2 Database-Level Auditing (PostgreSQL Triggers)

For critical tables, use PostgreSQL triggers to ensure audit even if application bypasses Laravel:

```sql
-- Example: tickets audit trigger
CREATE OR REPLACE FUNCTION audit_tickets() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO audit_logs (
        user_id, event, auditable_type, auditable_id,
        old_values, new_values, created_at
    ) VALUES (
        NULL, TG_OP, 'App\\Models\\Ticket', NEW.id,
        CASE WHEN TG_OP = 'UPDATE' THEN row_to_json(OLD)::jsonb ELSE NULL END,
        row_to_json(NEW)::jsonb,
        NOW()
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_audit_tickets
    AFTER INSERT OR UPDATE OR DELETE ON tickets
    FOR EACH ROW EXECUTE FUNCTION audit_tickets();
```

### 5.3 Recommended Approach

| Aspect | Recommendation |
|--------|---------------|
| **Primary** | Application-level auditing (Laravel observers) |
| **Secondary** | Database triggers for `tickets` and `users` tables |
| **Authentication** | Application-level audit for login/logout events |
| **File changes** | Application-level audit for file uploads/deletions |

---

## 6. Audit Data Retention

### 6.1 Retention Policy

| Data Type | Retention Period | Action |
|-----------|-----------------|--------|
| Active audit logs | 2 years | Keep in `audit_logs` table |
| Archived audit logs | 5 years | Move to archive table/partition |
| Deleted audit logs | 7 years | Permanent deletion after 7 years |

### 6.2 Archiving Strategy

1. **Monthly archiving** — Move audit logs older than 2 years to `audit_logs_archive` table.
2. **Partitioning** — Partition `audit_logs` by month for efficient archiving.
3. **Compression** — Compress archived data to reduce storage.
4. **Backup** — Include audit logs in regular database backups.

---

## 7. Audit Integrity

### 7.1 Immutability

- Audit log records are **append-only** — no UPDATE or DELETE operations.
- Database-level `REVOKE` on `audit_logs` table for all users except system.
- Application-level middleware prevents audit log modification.

### 7.2 Hash Chaining

For high-security environments, implement hash chaining:

```sql
-- Add hash column to audit_logs
ALTER TABLE audit_logs ADD COLUMN record_hash VARCHAR(64);
ALTER TABLE audit_logs ADD COLUMN prev_hash VARCHAR(64);

-- Hash = SHA256(prev_hash + event + auditable + values + timestamp)
```

This creates an immutable chain where tampering with any record breaks the chain.

### 7.3 Monitoring

- Daily job to verify audit log integrity.
- Alert on missing or unexpected audit log entries.
- Monitor for audit log gaps (missing sequence numbers).

---

## 8. Audit Queries

### 8.1 Common Audit Queries

```sql
-- Ticket history for a specific ticket
SELECT * FROM audit_logs
WHERE auditable_type = 'App\\Models\\Ticket'
  AND auditable_id = 123
ORDER BY created_at DESC;

-- User activity for a specific user
SELECT * FROM audit_logs
WHERE user_id = 456
ORDER BY created_at DESC;

-- All events in a date range
SELECT * FROM audit_logs
WHERE created_at BETWEEN '2026-01-01' AND '2026-01-31'
ORDER BY created_at DESC;

-- Failed login attempts
SELECT * FROM audit_logs
WHERE event = 'login_failed'
ORDER BY created_at DESC;
```

### 8.2 Reporting

| Report | Query Pattern |
|--------|---------------|
| User activity report | Group by `user_id`, count by `event` |
| Entity change report | Filter by `auditable_type`, `auditable_id` |
| Security report | Filter by `event` in (`login`, `login_failed`, `password_changed`) |
| Compliance report | Date range + entity type filter |

---

## 9. Audit Best Practices

1. **Always audit** — Never skip audit for critical operations.
2. **Audit the actor** — Always record `user_id` (NULL for system/cron).
3. **Audit the context** — Record IP address and user agent.
4. **Audit before/after** — Store both `old_values` and `new_values` for updates.
5. **Exclude sensitive data** — Never store passwords or tokens in audit logs.
6. **Use JSON** — Store values as JSON for flexibility.
7. **Index for queries** — Index `user_id`, `event`, `auditable_type`, `auditable_id`, `created_at`.
8. **Retain appropriately** — Follow the retention policy.
9. **Protect integrity** — Make audit logs append-only.
10. **Monitor** — Regularly check audit log integrity and completeness.