# Gym Tracker

A personal workout tracking web application built with **Laravel**, **PHP**, **SQLite**, and **Tailwind CSS**.

Gym Tracker helps users organise their workouts, record exercises and sets, and monitor their training progress in one place.

## Features

- **User accounts:** Register, log in, and log out.
- **Workout management:** Create, view, edit, and delete workout records.
- **Exercise and set tracking:** Record exercises and their individual sets rather than storing everything in one text field.
- **Workout completion:** Mark workouts as completed to keep progress tracking meaningful.
- **Progress overview:** Review workout activity and completed sessions.
- **File attachments:** Attach relevant files to workout records, with access controlled on the server.
- **Validation and authorisation:** Validate submitted data and restrict access to records owned by other users.

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | PHP, Laravel |
| Database | SQLite |
| Frontend | Blade templates, HTML, Tailwind CSS |
| Dependency management | Composer, npm |
| Testing | Laravel's testing tools / PHPUnit |

## Getting started

### Prerequisites

- PHP and the PHP extensions required by your Laravel version (including SQLite support)
- Composer
- Node.js and npm (for frontend assets)

### 1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/gym-tracker.git
cd gym-tracker
```

Replace `YOUR_USERNAME` with your actual GitHub username or use the clone URL shown on GitHub.

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Set up the environment

Copy `.env.example` to `.env`:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS/Linux:**

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

In `.env`, configure the local SQLite database:

```dotenv
DB_CONNECTION=sqlite
```

If your version of Laravel does not create the SQLite file automatically, create an empty file at `database/database.sqlite` before migrating.

### 4. Set up the database

```bash
php artisan migrate
```

The database file is local to your machine and is **not committed** to GitHub.

### 5. Run the application

Start the Laravel server:

```bash
php artisan serve
```

Then open the local URL printed by Artisan (usually `http://127.0.0.1:8000`).

If your project builds its frontend assets with npm, run the configured development script in another terminal:

```bash
npm run dev
```

> The npm command depends on the scripts defined in `package.json`.

## Project structure

```text
app/                  # Models, controllers, and application logic
bootstrap/            # Laravel bootstrapping and generated framework cache
config/               # Application configuration
database/             # Database migrations, factories, and seeders
public/               # Public entry point and static assets
resources/views/      # Blade templates for the user interface
routes/web.php        # Web routes
tests/                # Automated tests
storage/              # Runtime files, logs, and uploaded attachments
```

## Security and data handling

Authentication identifies the logged-in user. Authorisation checks whether that user is allowed to access a particular workout or attachment; logging in alone is not sufficient. Form data is validated on the server before it is saved.

Do not commit `.env`, SQLite database contents, or users' uploaded files. **Private attachments should only be served through authorisation-protected routes**, not through a publicly accessible storage link.

## Running tests

```bash
php artisan test
```

## Web demo
![log in page](image.png)
![workouts](image-2.png)
![exercises](image-1.png)
![progress](image-3.png)


## About

Built as a Web Application Development project, with an emphasis on Laravel CRUD operations, relational database design, authentication, validation, and access control.