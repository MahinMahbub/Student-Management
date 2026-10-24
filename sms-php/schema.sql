-- ============================================================
-- Student Management System - Database Schema (MySQL)
-- ============================================================
-- Demonstrates: primary keys, foreign keys, relational design
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE student_management;

DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS departments;

-- Departments table (parent / lookup table)
CREATE TABLE departments (
    dept_id     INT AUTO_INCREMENT PRIMARY KEY,
    dept_name   VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Students table (child table, references departments)
CREATE TABLE students (
    student_id   VARCHAR(20) PRIMARY KEY,           -- e.g. "CSE-2024-001"
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    dept_id      INT NOT NULL,
    semester     TINYINT NOT NULL,
    cgpa         DECIMAL(3,2) NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_semester CHECK (semester BETWEEN 1 AND 12),
    CONSTRAINT chk_cgpa CHECK (cgpa >= 0.00 AND cgpa <= 4.00),
    CONSTRAINT fk_student_department
        FOREIGN KEY (dept_id) REFERENCES departments(dept_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Helpful index for department-based searches/joins
CREATE INDEX idx_students_dept ON students(dept_id);

-- Seed departments
INSERT INTO departments (dept_name) VALUES
    ('Computer Science & Engineering'),
    ('Electrical & Electronic Engineering'),
    ('Business Administration'),
    ('Civil Engineering'),
    ('Mathematics');

-- Seed sample students
INSERT INTO students (student_id, name, email, dept_id, semester, cgpa) VALUES
    ('CSE-2024-001', 'Ayesha Rahman',  'ayesha.rahman@example.com',  1, 4, 3.85),
    ('CSE-2024-002', 'Tanvir Ahmed',   'tanvir.ahmed@example.com',   1, 6, 3.42),
    ('EEE-2024-001', 'Farhana Akter',  'farhana.akter@example.com',  2, 2, 3.91),
    ('BBA-2023-010', 'Sadia Islam',    'sadia.islam@example.com',    3, 8, 3.20),
    ('CE-2023-005',  'Rakibul Hasan',  'rakibul.hasan@example.com',  4, 5, 2.95),
    ('MATH-2024-002','Nusrat Jahan',   'nusrat.jahan@example.com',   5, 3, 3.66);
