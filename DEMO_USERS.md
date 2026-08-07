# MITO Ticketing System — Demo User Accounts

> All accounts use password: **`Admin@123`**  
> Application URL: `http://127.0.0.1:8000`  
> Demo Mode: **Enabled** (`DEMO_MODE=true` in `.env`)

---

## Role-Based Login (from Welcome Page)

| Role | Login URL | Email | Password |
|------|-----------|-------|----------|
| 👑 Administrator | `/login/admin` | `admin@mito.local` | `Admin@123` |
| 👨‍💼 IT Manager | `/login/manager` | `manager@mito.local` | `Admin@123` |
| 🛠 IT Support (Staff) | `/login/staff` | `support@mito.local` | `Admin@123` |
| 👤 Employee (User) | `/login/user` | `employee@mito.local` | `Admin@123` |

---

## All Test Accounts

### Administrator

| Name | Email | Role | Department |
|------|-------|------|------------|
| Admin MITO | `admin@mito.local` | Admin | — |

### IT Manager

| Name | Email | Role | Department |
|------|-------|------|------------|
| Rina Sari | `rina.sari@mito.local` | Manager | IT Support |

### IT Support (Staff)

| Name | Email | Role | Department |
|------|-------|------|------------|
| Ahmad Hidayat | `ahmad.hidayat@mito.local` | Staff | IT Support |
| Dewi Lestari | `dewi.lestari@mito.local` | Staff | Network |
| Firmansyah | `firmansyah@mito.local` | Staff | IT Support |

### Employee (User)

| Name | Email | Role | Department |
|------|-------|------|------------|
| Siti Nurhaliza | `siti.nurhaliza@mito.local` | User | — |
| Budi Prasetyo | `budi.prasetyo@mito.local` | User | — |
| Maya Putri | `maya.putri@mito.local` | User | — |

---

## Quick Reference

```
admin@mito.local       / Admin@123    — Full access, user management, SLA settings
rina.sari@mito.local   / Admin@123    — Manager, view all tickets, assign
ahmad.hidayat@mito.local / Admin@123  — Staff, handle assigned tickets
dewi.lestari@mito.local / Admin@123   — Staff, network tickets
firmansyah@mito.local  / Admin@123    — Staff, handle assigned tickets
siti.nurhaliza@mito.local / Admin@123  — User, create & view own tickets
budi.prasetyo@mito.local / Admin@123   — User, create & view own tickets
maya.putri@mito.local  / Admin@123    — User, create & view own tickets
```

---

## Ticket Statuses

| Status | Color | Description |
|--------|-------|-------------|
| Open | Blue | New ticket, not yet assigned |
| In Progress | Yellow | Being worked on |
| Waiting User | Orange | Waiting for user response |
| Resolved | Green | Issue resolved |
| Closed | Gray | Ticket closed |

## SLA Policies

| Priority | Response Time | Resolution Time |
|----------|--------------|-----------------|
| Critical | 1 hour | 4 hours |
| High | 2 hours | 8 hours |
| Medium | 4 hours | 24 hours |
| Low | 8 hours | 72 hours |