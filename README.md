# Training Enrollment System

A multi-table PHP PDO web application for managing training courses, class schedules, students, and enrollments.

**Author:** Santos, Kyra Lois G. (BSIT 3A)
**Subject:** IPT — Integrative Programming and Technology

## Project Description

An organization offers training programs. Each course can have several scheduled classes, each with a schedule, an instructor, and a limited number of slots. A single Administrator registers courses, manages class schedules, records and enrolls students, cancels enrollments, and views reports.

The system prevents over-enrollment and duplicate enrollments. Multi-step operations run inside database transactions, so the `students`, `enrollments`, and `classes` tables always stay consistent.

## Features

- Course management (add, edit, delete) with server-side validation
- Class management showing course, schedule, instructor, and remaining slots (JOIN)
- Student recording form: inserts the student, inserts the enrollment, and decrements the class slot in **one transaction**
- Enrollment of an existing student, with a slot check
- Enrollment list built with multi-table JOINs (enrollments, students, classes, courses)
- Cancel enrollment: updates the status and restores the slot in **one transaction**
- Search, filter, sorting, and pagination using parameterized queries
- Summary reports
- All database access uses PDO prepared statements only

## Technologies

- PHP 8 with PDO
- MySQL / MariaDB
- HTML and CSS
- XAMPP (Apache + MySQL)

## Setup Instructions

### 1. Install and start the server

1. Install [XAMPP](https://www.apachefriends.org/).
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 2. Add the project

Copy or clone this project into:

```
C:\xampp\htdocs\training_enrollment\
```

### 3. Create the database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click the **SQL** tab at the top.
3. Copy and paste the script below into the box, then click **Go**.

```sql
CREATE DATABASE IF NOT EXISTS training_db;
USE training_db;

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    class_code VARCHAR(20) NOT NULL,
    schedule VARCHAR(100),
    instructor VARCHAR(100),
    slots INT NOT NULL DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'active',
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);
```

4. After clicking **Go**, `training_db` and its four tables (`students`, `courses`, `classes`, `enrollments`) appear in the left sidebar.


### 5. Run the app

Open `http://localhost/training_enrollment/` in your browser.

## Folder Structure

```
training_enrollment/
|-- config/
|   `-- db.php                    # Single PDO connection
|-- classes/
|   |-- Database.php              # PDO wrapper (Singleton)
|   |-- Student.php
|   |-- Course.php
|   |-- ClassSection.php
|   `-- EnrollmentRepository.php  # Transactions: record, enroll, cancel
|-- admin/
|   |-- courses.php
|   |-- classes.php
|   |-- students.php
|   |-- enroll.php
|   |-- enrollments.php
|   `-- reports.php
|-- includes/
|   |-- header.php
|   `-- footer.php
|-- css/
|   `-- style.css
|-- sql/
|   `-- schema.sql
|-- index.php
`-- README.md
```

Pages in `admin/` handle only presentation and requests. All data access lives in the classes, and all database work goes through the single `Database` instance.
