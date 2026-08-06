# MITO IT Helpdesk

IT Helpdesk Ticketing System built with Laravel 12.

## Project Overview

MITO IT Helpdesk is an enterprise-grade IT helpdesk ticketing system designed to streamline the management of IT support requests within the organization. The system provides a centralized platform for employees to submit, track, and resolve IT issues efficiently.

## Technology Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 12 |
| **Language** | PHP 8.5 |
| **Database** | PostgreSQL 18 |
| **Frontend** | Vite + Node.js LTS |
| **Development** | Native Windows |
| **Production** | Ubuntu 24.04 + Nginx |

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
```

### 4. Create Database

```bash
psql -U postgres -c "CREATE DATABASE ticketing;"
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://127.0.0.1:8000`.

## Folder Structure

```
app/
├── app/                    # Application code
│   ├── Http/               # HTTP layer (Controllers, Middleware, Requests)
│   │   └── Controllers/    # Controllers
│   ├── Models/             # Eloquent models
│   └── Providers/          # Service providers
├── assets/                 # Static assets (images, fonts, etc.)
├── bootstrap/              # Framework bootstrap files
├── config/                 # Configuration files
├── database/               # Migrations, factories, seeders
│   ├── factories/          # Model factories
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── docs/                   # Project documentation
├── prompts/                # AI/LLM prompt templates
├── public/                 # Publicly accessible assets
├── resources/              # Views and frontend assets
│   ├── css/                # CSS source files
│   ├── js/                 # JavaScript source files
│   └── views/              # Blade templates
├── routes/                 # Route definitions
│   ├── console.php         # Console routes
│   └── web.php             # Web routes
├── scripts/                # Development and deployment scripts
├── storage/                # Application storage
├── tests/                  # Automated tests
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
├── ui/                     # UI design assets and mockups
├── CONTRIBUTING.md         # Contribution guidelines
├── CHANGELOG.md            # Version history
├── LICENSE.md              # MIT License
└── README.md               # Project documentation
```

## Documentation

- [Contributing Guidelines](CONTRIBUTING.md)
- [Changelog](CHANGELOG.md)
- [License](LICENSE.md)

## License

This project is open-sourced software licensed under the [MIT license](LICENSE.md).