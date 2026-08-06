# Contributing to MITO IT Helpdesk

Thank you for considering contributing to the MITO IT Helpdesk project. This document outlines the standards and conventions that all contributors must follow.

## Table of Contents

- [Coding Standards](#coding-standards)
- [Naming Conventions](#naming-conventions)
- [Git Commit Message Convention](#git-commit-message-convention)
- [Branch Naming Convention](#branch-naming-convention)
- [Development Workflow](#development-workflow)
- [Pull Request Process](#pull-request-process)

## Coding Standards

### PHP

- Follow **PSR-12** coding standard.
- Use **strict types** declaration (`declare(strict_types=1);`) at the top of every PHP file.
- Use **type hints** for all method parameters and return types.
- Use **named arguments** where they improve readability.
- Use **early returns** to reduce nesting.
- Keep methods **short and focused** — a method should do one thing.
- Use **dependency injection** over facades where possible.
- Use **Eloquent** for database interactions.
- Use **Laravel Pint** for code style enforcement.

### Database

- Use **migrations** for all schema changes.
- Use **snake_case** for table names and column names.
- Use **plural** table names (e.g., `tickets`, `users`).
- Always define **foreign key constraints** with `onDelete` behavior.
- Use **timestamps** (`created_at`, `updated_at`) on all tables.
- Use **soft deletes** where appropriate.

### Frontend

- Follow **PSR-12** for Blade templates.
- Use **Tailwind CSS** utility classes.
- Use **Vite** for asset compilation.
- Keep Blade templates **simple** — move logic to controllers or view composers.

## Naming Conventions

### PHP Classes

| Type | Convention | Example |
|------|-----------|---------|
| **Models** | Singular, PascalCase | `Ticket`, `User` |
| **Controllers** | Singular, PascalCase, `Controller` suffix | `TicketController` |
| **Requests** | Singular, PascalCase, `Request` suffix | `StoreTicketRequest` |
| **Resources** | Singular, PascalCase, `Resource` suffix | `TicketResource` |
| **Migrations** | `YYYY_MM_DD_HHMMSS_create_table_name_table` | `2026_08_06_000000_create_tickets_table` |
| **Factories** | Singular, PascalCase, `Factory` suffix | `TicketFactory` |
| **Seeders** | PascalCase, `Seeder` suffix | `TicketSeeder` |
| **Services** | Singular, PascalCase, `Service` suffix | `TicketService` |
| **Repositories** | Singular, PascalCase, `Repository` suffix | `TicketRepository` |
| **Events** | Past tense, PascalCase | `TicketCreated` |
| **Listeners** | PascalCase, `Listener` suffix | `SendTicketNotification` |
| **Jobs** | PascalCase, `Job` suffix | `ProcessTicket` |
| **Mail** | PascalCase, `Mail` suffix | `TicketAssignedMail` |
| **Notifications** | PascalCase, `Notification` suffix | `TicketAssignedNotification` |
| **Middleware** | PascalCase, `Middleware` suffix | `EnsureUserIsAdmin` |
| **Policies** | PascalCase, `Policy` suffix | `TicketPolicy` |

### Variables & Methods

- **Variables:** `camelCase` — `$ticketStatus`, `$userName`
- **Methods:** `camelCase` — `getTicketById()`, `assignToUser()`
- **Constants:** `UPPER_SNAKE_CASE` — `MAX_TICKETS_PER_DAY`
- **Database Columns:** `snake_case` — `ticket_status`, `created_at`
- **Route Names:** `snake_case` with dots — `tickets.index`, `tickets.store`
- **Route URIs:** `kebab-case` — `/ticket-categories`, `/user-profiles`

## Git Commit Message Convention

We follow the **Conventional Commits** specification.

### Format

```
<type>(<scope>): <description>
```

### Types

| Type | Description | Example |
|------|-------------|---------|
| `feat` | New feature | `feat(tickets): add ticket creation form` |
| `fix` | Bug fix | `fix(auth): resolve session timeout issue` |
| `docs` | Documentation only | `docs: update installation guide` |
| `style` | Formatting, no code change | `style: apply pint formatting` |
| `refactor` | Code change, no fix/feature | `refactor(users): simplify user service` |
| `perf` | Performance improvement | `perf: optimize ticket list query` |
| `test` | Adding/updating tests | `test(tickets): add feature tests` |
| `build` | Build system changes | `build: update vite config` |
| `ci` | CI configuration changes | `ci: add github actions workflow` |
| `chore` | Maintenance tasks | `chore: update composer dependencies` |
| `revert` | Revert a previous commit | `revert: revert ticket status change` |

### Rules

- Use **imperative mood** in the description (e.g., "add", "fix", "update" — not "added", "fixed", "updated").
- Keep the subject line **under 72 characters**.
- Use **lowercase** for the type and description.
- Add a **body** for complex changes explaining the "why" and "what".
- Reference **issue numbers** in the body when applicable: `Closes #123`.

### Examples

```
feat(tickets): add ticket priority field

Add priority selection (low, medium, high, urgent) to the ticket
creation form. Store the value in the tickets table.

Closes #42
```

```
fix(auth): resolve session timeout issue

Increase session lifetime from 60 to 120 minutes to prevent
premature logout during long support sessions.

Closes #87
```

## Branch Naming Convention

### Format

```
<type>/<short-description>
```

### Types

| Type | Description | Example |
|------|-------------|---------|
| `feature` | New feature | `feature/ticket-priority` |
| `bugfix` | Bug fix | `bugfix/session-timeout` |
| `hotfix` | Urgent production fix | `hotfix/security-patch` |
| `docs` | Documentation | `docs/update-readme` |
| `refactor` | Code refactoring | `refactor/user-service` |
| `test` | Testing | `test/ticket-feature-tests` |
| `chore` | Maintenance | `chore/update-dependencies` |

### Rules

- Use **lowercase** for the entire branch name.
- Use **hyphens** (`-`) as word separators.
- Keep the description **short and descriptive**.
- Optionally include the **issue number**: `feature/ticket-priority-42`

### Examples

```
feature/ticket-priority
bugfix/session-timeout
hotfix/security-patch
docs/update-readme
refactor/user-service
test/ticket-feature-tests
chore/update-dependencies
```

## Development Workflow

1. **Create a branch** from `main` using the branch naming convention.
2. **Write code** following the coding standards and naming conventions.
3. **Write tests** for all new functionality.
4. **Run tests** locally before committing:
   ```bash
   php artisan test
   ```
5. **Run code style** check:
   ```bash
   ./vendor/bin/pint --test
   ```
6. **Commit** using the commit message convention.
7. **Push** the branch to the remote repository.
8. **Create a pull request** to merge into `main`.

## Pull Request Process

1. Ensure the PR title follows the commit message convention.
2. Provide a **clear description** of the changes.
3. Reference **related issues** in the PR description.
4. Ensure **all tests pass** and the **code style** is clean.
5. Request **review** from at least one team member.
6. Address all **review feedback** before merging.
7. Squash and merge when the PR is approved.