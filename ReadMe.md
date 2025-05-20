# Personal Finance Tracker

A simple web-based personal finance tracker built with **HTML**, **CSS**, **PHP**, and **MySQL**. This tool helps users manage their income and expenses, track financial history, and maintain budget clarity through a clean and user-friendly dashboard interface.

---

## 🚀 Features

- 🔐 User Authentication (Login & Register)
- 📥 Income & Expense Entry
- 📊 Expense History Dashboard
- 📂 Category-wise Breakdown
- 💾 Data Storage in MySQL Database
- 🎨 Clean and Modern UI (Blue-Green Theme)

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS
- **Backend:** PHP
- **Database:** MySQL
- **Server:** XAMPP / LAMP / WAMP

## 📊 Database Schema

### `users` Table:
| Column     | Type         |
|------------|--------------|
| id         | INT (PK)     |
| name       | VARCHAR      |
| email      | VARCHAR      |
| password   | VARCHAR      |

### `finance` Table:
| Column     | Type         |
|------------|--------------|
| id         | INT (PK)     |
| user_id    | INT (FK)     |
| type       | ENUM('income','expense') |
| amount     | DECIMAL      |
| category   | VARCHAR      |
| reason     | TEXT         |
| created_at | TIMESTAMP    |

---

## ⚙️ Setup Instructions

1. Clone this repository:
```bash
git clone https://github.com/ranjeethagunasekaran/expense-tracker.git
```
	1.	Import finance.sql into your MySQL database using phpMyAdmin or CLI.
	2.	Configure database credentials in PHP files (e.g., dashboard.php, login.php, etc.)
	3.	Start Apache & MySQL via XAMPP/WAMP/LAMP
	4.	Access in browser:

http://localhost/expense_tracker/pages




✍️ Author

Ranjeetha
Third-year Software Engineering Student | VIT Vellore
Passionate about Web Development and Building Real-world Projects
