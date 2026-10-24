# Student Management System (PHP + MySQL)

A web-based Student Management System built with **PHP** and **MySQL**, demonstrating core DBMS concepts: database connectivity, CRUD operations, searching, primary/foreign keys, joins, and aggregate queries.

## Tech Stack

- **Backend:** PHP 8+ (procedural, using PDO for database access)
- **Database:** MySQL / MariaDB
- **Frontend:** Server-rendered HTML (PHP templates) + vanilla CSS — no build step, no framework

## Project Structure

```
sms-php/
├── schema.sql                       # DDL: tables, PK/FK constraints, seed data
├── config/
│   └── database.php                 # PDO connection settings
├── includes/
│   ├── student_functions.php        # ALL SQL queries live here
│   ├── header.php                   # Shared layout top + nav
│   ├── footer.php                   # Shared layout bottom
│   └── flash.php                    # Flash-message helper
├── assets/
│   └── css/style.css
├── index.php                        # Dashboard module
├── students.php                     # View all students
├── add_student.php                  # Add student (form + handler)
├── edit_student.php                 # Update student (form + handler)
├── delete_student.php               # Delete handler (POST only)
└── search.php                       # Search module
```

## Database Schema

Two related tables demonstrating a proper relational design:

```sql
departments (
    dept_id     INT AUTO_INCREMENT PRIMARY KEY,
    dept_name   VARCHAR(100) NOT NULL UNIQUE
)

students (
    student_id   VARCHAR(20) PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    dept_id      INT NOT NULL,
    semester     TINYINT NOT NULL CHECK (semester BETWEEN 1 AND 12),
    cgpa         DECIMAL(3,2) NOT NULL CHECK (cgpa BETWEEN 0.00 AND 4.00),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
)
```

`students.dept_id` is a **foreign key** referencing `departments.dept_id`. Deleting a department that still has students is blocked by the database itself (`ON DELETE RESTRICT`) — this was verified directly against MySQL, not just enforced in the app.

The full DDL and seed data live in `schema.sql`.

## Setup & Run

### Option A — XAMPP / WAMP / MAMP (recommended for coursework)

!!! change the port number to 3306 if your port is default on the database.php file !!!

1. Install [XAMPP](https://www.apachefriends.org/) (or WAMP/MAMP) and start **Apache** + **MySQL**.
2. Copy the `sms-php` folder into your server's web root:
   - XAMPP: `htdocs/sms-php`
   - WAMP: `www/sms-php`
3. Import the schema:
   - Open **phpMyAdmin** → **Import** → choose `schema.sql` → Go
   - *or* via terminal: `mysql -u root -p < schema.sql`
4. If your MySQL root user has a password, set it in `config/database.php`:
   ```php
   define('DB_PASS', 'your_password_here');
   ```
5. Visit `http://localhost/sms-php/index.php` in your browser.

### Option B — PHP's built-in server + any MySQL install

1. Make sure MySQL/MariaDB is running locally.
2. Load the schema:
   ```bash
   mysql -u root -p < schema.sql
   ```
3. Update credentials in `config/database.php` if needed.
4. From the `sms-php` folder, run:
   ```bash
   php -S localhost:8000
   ```
5. Visit `http://localhost:8000/index.php`.

The schema pre-loads 5 departments and 6 sample students. To reset, re-run `schema.sql` — it drops and recreates both tables.

## Features & Where to Find Them

| Requirement | Implementation |
|---|---|
| Database connectivity | `config/database.php` → `getConnection()` (PDO, MySQL) |
| Data insertion | `includes/student_functions.php` → `addStudent()` — prepared `INSERT` |
| Data retrieval | `includes/student_functions.php` → `getAllStudents()` — `SELECT ... JOIN` |
| Data updating | `includes/student_functions.php` → `updateStudent()` — prepared `UPDATE` |
| Data deletion | `includes/student_functions.php` → `deleteStudent()` — prepared `DELETE` |
| Searching | `includes/student_functions.php` → `searchStudents()` — `WHERE ... LIKE` by ID / name / department |
| Primary key | `students.student_id`, `departments.dept_id` |
| Foreign key | `students.dept_id → departments.dept_id` |
| Dashboard (3+ queries) | `includes/student_functions.php` → `getDashboardStats()` runs 5 distinct queries: `COUNT(*)`, `GROUP BY` + `JOIN` per department, `AVG/MAX/MIN`, `GROUP BY` per semester, `ORDER BY ... LIMIT` for top performers |

Every SQL statement uses **PDO prepared statements** with bound parameters (`:placeholder` syntax) — no string-concatenated SQL anywhere in the codebase, so the app is protected against SQL injection by construction.

The Dashboard page also prints the literal SQL used for each chart directly in the UI, so the underlying queries are visible while using the app.

## Notes for Grading / Review

- `schema.sql` is the single source of truth for the database structure — open it directly to review the DDL, constraints, and seed data.
- `includes/student_functions.php` contains **every** SQL statement in the application; the page files (`index.php`, `students.php`, etc.) never touch SQL directly, keeping data-access logic isolated (a simple MVC-style separation).
- Constraints (`UNIQUE`, `CHECK`, `FOREIGN KEY ... ON DELETE RESTRICT`) are enforced at the database level, not just in PHP form validation — confirmed by testing directly against MySQL with `mysql` CLI, independent of the web app.
- All output is escaped with `htmlspecialchars()` to prevent XSS.
