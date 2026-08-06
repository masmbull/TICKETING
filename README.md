# MITO IT Helpdesk

IT Helpdesk Ticketing System built with Laravel 12.

## Tech Stack

- **Framework:** Laravel 12
- **PHP:** 8.5
- **Database:** PostgreSQL 18
- **Frontend:** Vite + Node.js
- **Server (Production):** Ubuntu 24.04 + Nginx

## Requirements

- PHP >= 8.2
- Composer 2.x
- PostgreSQL 18
- Node.js LTS
- Git

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

## Project Structure

```
app/
├── app/           # Application code
├── bootstrap/     # Framework bootstrap files
├── config/        # Configuration files
├── database/      # Migrations and seeders
├── public/        # Publicly accessible assets
├── resources/     # Views and frontend assets
├── routes/        # Route definitions
├── storage/       # Application storage
└── tests/         # Automated tests
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).