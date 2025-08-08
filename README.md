# Laravel Project Setup Guide

This guide explains how to set up the Laravel 12 project, run database migrations and seeders, and start the development server with frontend assets.

---

## Setup & Run Laravel Application

1. **Clone repository and install dependencies**

```bash
git clone https://github.com/Arfiandimas/perpustakaan.git
cd perpustakaan
composer install
cp .env.example .env
php artisan key:generate
```

2. **Edit .env to set your database connection**

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

3. **Run database migrations and seeders**

```bash
php artisan migrate --seed
```

4. **Install frontend dependencies and build assets**

```bash
npm install
npm run dev
```

5. **Start Laravel development server**

```bash
php artisan serve
```
Open your browser and visit: http://localhost:8000

Sample User Credentials:

-   Email: `test@example.com`
-   Password: `password`