# Student Management System 🎓

[![PHP](https://img.shields.io/badge/Language-PHP-blue.svg)](https://www.php.net/)
[![Status](https://img.shields.io/badge/Status-Active-success.svg)]()

A lightweight, web-based Student Management System built with PHP and MySQL to manage student records, registrations, courses, and administrative operations efficiently.

---

## 🚀 Features

* **Student Records:** Add, view, edit, and delete student information seamlessly.
* **Authentication & Admin Setup:** Secure login system (`login.php`, `logout.php`, and `setup_admin.php`) to restrict access to authorized personnel.
* **File Management:** Upload and manage student documents or related files directly through the platform (`upload.php`, `file.php`).
* **Database Integration:** Reliable MySQL database connection (`db.php`) for data storage and management.

---

## 🛠️ Tech Stack

* **Backend & Logic:** PHP
* **Database:** MySQL / MySQLi
* **Server Environment:** Apache / Nginx (e.g., XAMPP, WAMP, or MAMP)

---

## 📁 Project Structure

```text
Student-Management/
├── add.php           # Add new student records
├── db.php            # Database connection configuration
├── delete.php        # Delete student records
├── edit.php          # Edit existing records
├── file.php          # Handle file viewing/management
├── index.php         # Main dashboard / home page
├── login.php         # Admin/User login portal
├── logout.php        # Session termination
├── setup_admin.php   # Initial admin account setup script
├── upload.php        # File upload processing
└── view.php          # View student lists and details
