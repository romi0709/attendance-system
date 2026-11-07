# Attendance Management System

A web-based Attendance Management System developed to simplify and manage student attendance within an educational institution.

## Project Overview

The Attendance Management System is designed to facilitate attendance tracking and academic management by providing dedicated interfaces for administrators, professors, and students.

The system centralizes attendance information and helps users manage courses, students, professors, and attendance records efficiently.

## Features

### Administrator
- Manage students.
- Manage professors.
- Manage courses.
- Enroll students in courses.
- View attendance statistics.
- Monitor attendance records.

### Professor
- View assigned courses.
- Manage student attendance.
- Record attendance sessions.
- View enrolled students.
- Consult attendance summaries.
- Manage profile information.

### Student
- View personal attendance records.
- Consult enrolled courses.
- View attendance history.
- Manage profile information.

## Technologies Used

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Composer
- PhpSpreadsheet

## Project Structure

```text
attendance_system/
├── admin/
├── professor/
├── student/
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── config/
├── index.php
├── login.php
├── logout.php
├── composer.json
└── README.md
```

## Installation

1. Install a local PHP environment such as WAMP or XAMPP.
2. Clone or download this repository.
3. Place the project inside your web server directory.
4. Create a MySQL database.
5. Configure the database connection in `config/database.php`.
6. Install the required PHP dependencies using:

```bash
composer install
```

7. Start Apache and MySQL.
8. Open the application in your browser:

```text
http://localhost/attendance_system/
```

## Project Status

Developed as an academic project.

## Author

Developed as a student project.

---

*This project aims to provide a simple and efficient solution for managing student attendance.*
