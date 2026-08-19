# MITO IT Helpdesk

IT Helpdesk Ticketing System built with Laravel 12.

**Current Version:** `v1.0.0` ✅ **RELEASED**  
**Last Updated:** 19 Aug 2026

## Project Overview

MITO IT Helpdesk is an enterprise-grade IT helpdesk ticketing system designed to streamline the management of IT support requests within the organization. The system provides a centralized platform for employees to submit, track, and resolve IT issues efficiently.

## Release Status

### v1.0.0 - Production Ready ✅

**Release Date:** 19 Aug 2026  
**Status:** STABLE - Ready for production deployment

#### Core Features Implemented

✅ **User Management**
- Authentication (Login, Remember Me, Forgot Password)
- Role-based access control (Admin, Manager, Staff, User)
- User profile management
- Password management

✅ **Ticket Management**
- Create tickets with categories & subcategories
- Ticket assignment (Manager/Admin)
- Assign to Me (Staff self-assignment)
- Problem Analysis field (required for workflow)
- Resolution/Resolution field (required for completion)
- Ticket status workflow: Waiting → In Progress → Completed
- Comment system with attachments
- Full audit trail of all changes

✅ **SLA Management**
- 4 priority levels: Critical (1 day), High (2 days), Medium (3 days), Low (5 days)
- Automatic deadline calculation (Mon-Fri, 08:30-17:30 WIB)
- SLA performance tracking: Excellent/Normal/Poor
- Category-based SLA mapping
- Manual SLA override (Manager/Admin)

✅ **Dashboard & Reports**
- Role-based dashboards (Admin, Manager, Staff, User)
- KPI metrics and statistics
- KPI Reports with Excel export
- Audit Logs with full activity tracking
- Recent tickets timeline

✅ **Notifications**
- Real-time notification system
- Notification bell with unread count
- Event-based notifications:
  - Ticket assignment
  - Status changes
  - Comments
  - SLA changes
  - Ticket completion
- Mark as read / Mark all as read
- Direct navigation to ticket

✅ **User Interface**
- Light/Dark theme support
- MITO Electronic branding
- Splash screen with loading animation
- Responsive design (Mobile, Tablet, Desktop)
- Sidebar navigation
- Global footer with timezone info
- Professional dashboard layout

✅ **Localization**
- Timezone: Asia/Jakarta (WIB)
- Business hours: Mon-Fri, 08:30-17:30

#### Test Coverage

- 56+ automated tests passing
- Feature tests for all major workflows
- SLA calculation tests
- Notification tests
- Audit log tests
- Authentication tests

## Technology Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 12 |
| **Language** | PHP 8.5 |
| **Database** | PostgreSQL 18 |
| **Frontend** | Vite + Node.js LTS |
| **Development** | Native Windows |
| **Production** | Ubuntu 24.04 + Nginx |
| **Testing** | PHPUnit |
| **Build** | Vite v6 |

## Development Requirements

- **PHP** >= 8.2
- **Composer** 2.x
- **PostgreSQL** 18
- **Node.js** LTS
- **Git**

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/masmbull/TICKETING.git
cd TICKETING
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update the `.env` file with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ticketing
DB_USERNAME=postgres
DB_PASSWORD=your_password
APP_TIMEZONE=Asia/Jakarta
```

### 4. Create Database

```bash
psql -U postgres -c "CREATE DATABASE ticketing;"
```

### 5. Run Migrations

```bash
php artisan migrate
php artisan db:seed
```

### 6. Build Frontend Assets

```bash
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://127.0.0.1:8000`.

**Demo Credentials:** See [DEMO_USERS.md](DEMO_USERS.md)

## Folder Structure

```
app/
├── app/                           # Application code
│   ├── Http/                      # HTTP layer
│   │   ├── Controllers/           # Controllers (Ticket, Auth, etc.)
│   │   └── Middleware/            # Middleware
│   ├── Models/                    # Eloquent models
│   ├── Services/                  # Business logic services
│   ├── Notifications/             # Notification classes
│   ├── Observers/                 # Eloquent observers
│   └── Providers/                 # Service providers
├── assets/                        # Static assets
├── bootstrap/                     # Framework bootstrap
├── config/                        # Configuration files
├── database/                      # Migrations & seeders
│   ├── migrations/                # DB migrations
│   └── seeders/                   # Database seeders
├── docs/                          # Documentation
├── public/                        # Public assets (CSS, JS, images)
├── resources/                     # Views and assets
│   ├── css/                       # CSS (Tailwind)
│   ├── js/                        # JavaScript (Alpine.js)
│   └── views/                     # Blade templates
├── routes/                        # Route definitions
├── scripts/                       # Development scripts
├── storage/                       # Application storage
├── tests/                         # Automated tests
│   ├── Feature/                   # Feature tests
│   └── Unit/                      # Unit tests
├── CONTRIBUTING.md                # Contribution guidelines
├── CHANGELOG.md                   # Version history
├── DEMO_USERS.md                  # Demo credentials
├── LICENSE.md                     # MIT License
└── README.md                      # This file
```

## Usage Examples

### Create a Ticket (End User)

1. Log in as user
2. Click "Create Ticket"
3. Select Category & Subcategory
4. Enter Description & Priority
5. Submit - Ticket created with status "Waiting Confirmation"

### Assign a Ticket (Manager)

1. Log in as manager
2. Go to "All Tickets"
3. Click ticket → "Assign To"
4. Select staff member → Assign
5. Optional: Set SLA priority

### Resolve a Ticket (Staff)

1. Log in as staff
2. Go to "Assigned" tickets
3. Click ticket → "Take Ticket" (if unassigned)
4. Enter "Problem Analysis" → "In Progress"
5. Enter "Resolution" → "Completed"

### View Reports (Admin/Manager)

1. Log in as admin/manager
2. Go to "Reports" → "KPI Reports"
3. Select date range and detail level
4. Export to Excel if needed

## API Routes

Key endpoints available (all authenticated):

```
GET  /dashboard              # Dashboard
GET  /tickets                # My tickets
GET  /tickets/assigned       # Assigned tickets (Staff)
GET  /tickets/all            # All tickets (Manager/Admin)
GET  /tickets/create         # Create form
POST /tickets                # Store ticket
GET  /tickets/{id}           # Show ticket details
PATCH /tickets/{id}          # Update ticket status
POST /tickets/{id}/comments  # Add comment
GET  /reports                # KPI reports
GET  /audit-logs             # Audit logs
```

## Testing

Run automated tests:

```bash
php artisan test
```

Run specific test suite:

```bash
php artisan test --filter=Sprint40
php artisan test tests/Feature/TicketWorkflowTest
```

## Database Schema

Key tables:
- `users` - User accounts
- `tickets` - Support tickets
- `ticket_comments` - Comments on tickets
- `notifications` - User notifications
- `audit_logs` - Activity audit trail
- `sla_policies` - SLA configuration
- `sla_category_mappings` - Category to SLA mapping

## Security

- Password hashing with bcrypt
- CSRF protection on forms
- SQL injection protection
- XSS protection
- Role-based authorization
- Audit logging of sensitive operations
- No sensitive data in logs

## Performance

- Database query optimization with eager loading
- View caching for production
- Frontend asset minification (Vite)
- Lazy loading of heavy components
- Database indexes on frequently queried columns

## Known Issues

None reported in v1.0.0

## Roadmap (Future Versions)

Potential enhancements for v1.1+:
- Mobile app
- Integration with external ticketing systems
- Advanced reporting dashboards
- Automated ticket assignment
- Escalation workflows
- Custom fields
- API documentation

## Documentation

- [Contributing Guidelines](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)
- [Demo Users](DEMO_USERS.md)
- [License](LICENSE.md)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines.

## Support

For issues, questions, or suggestions, please open an issue on [GitHub](https://github.com/masmbull/TICKETING/issues).

## License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).

---

**Developed by:** MITO Team  
**Repository:** https://github.com/masmbull/TICKETING  
**Version:** v1.0.0  
**Last Updated:** 19 Aug 2026