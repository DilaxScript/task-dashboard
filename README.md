![image alt](https://github.com/DilaxScript/CodeAlpha_EcommerceStore_F/blob/d18c183a159291ae8e6faa60126540b1a19450a5/without%20login.png)


# Taskflow — Task Management Dashboard

A single-page daily task dashboard built for the Lunivo Labs Intern Software Engineer technical assessment.

## Features

- Create tasks without reloading the page
- Edit existing task details
- Delete tasks with confirmation
- Change task status dynamically from the dashboard
- Search tasks by title
- Filter tasks by status
- Live status totals
- Responsive mobile and desktop interface
- Server-side validation with inline feedback
- Loading, success, error, and empty states
- Feature tests for the core task workflow

## Tech stack

- Laravel 13
- PHP 8.3+
- Alpine.js 3
- Tailwind CSS 4
- MySQL 8+
- Vite

## Local installation

### 1. Clone the repository

```bash
git clone <your-repository-url>
cd lunivo-task-dashboard
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Create a MySQL database:

```sql
CREATE DATABASE lunivo_task_dashboard
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Update these values in `.env` if your local credentials differ:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lunivo_task_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Start the application

Run the Laravel and Vite development servers in separate terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

Open `http://127.0.0.1:8000`.

Alternatively, use the combined development command:

```bash
composer run dev
```

## Testing

The test suite uses SQLite in memory, so it does not change local MySQL data. Ensure the PHP `pdo_sqlite` extension is enabled.

```bash
php artisan test
```

Build production assets with:

```bash
npm run build
```

## Project structure

```text
app/
├── Enums/TaskStatus.php
├── Http/Controllers/TaskController.php
└── Models/Task.php

database/migrations/
└── *_create_tasks_table.php

resources/
├── css/app.css
├── js/app.js
└── views/dashboard.blade.php

routes/
└── web.php

tests/Feature/
└── TaskManagementTest.php
```

## Implementation notes

- Laravel handles routing, validation, database persistence, and JSON responses.
- The `TaskStatus` enum keeps allowed status values consistent in the model and controller.
- Alpine.js owns the dashboard state and sends `fetch` requests for task creation and status changes.
- The UI updates optimistically when a status changes and rolls back if the request fails.
- Blade provides the initial task data, while Alpine.js handles search, filters, counters, modal state, and notifications.

## Video walkthrough checklist

For the required 3–5 minute recording:

1. Create a task and show it appearing without a page reload.
2. Edit the task, then change its status and show the live counter update.
3. Delete a task and show the success notification.
4. Demonstrate search, filtering, and the responsive mobile layout.
5. Explain the migration, `Task` model, controller, and routes.
6. Explain the Alpine component in `resources/js/app.js`.
7. Briefly show the Tailwind dashboard view and feature tests.
