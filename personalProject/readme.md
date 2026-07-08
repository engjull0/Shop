# Multi-Language Web Project

## Overview

This project is a full-stack web application built using a combination of frontend and backend technologies.
It integrates **5 different programming and scripting languages/technologies**:

* **HTML** – Structure and layout of the web pages
* **CSS** – Styling and responsive design
* **JavaScript** – Client-side interactivity and dynamic behavior
* **PHP** – Server-side logic and backend functionality
* **MySQL** – Database management and data storage

The project was initially generated and structured with the assistance of the AI model **Alibaba Group's** **Qwen**, then refined and organized into a more complete project format.

---

# Features

* Responsive frontend interface
* Interactive user experience using JavaScript
* PHP-powered backend processing
* MySQL database integration
* Multi-language full-stack architecture
* Organized project structure for scalability

---

# Technologies Used

| Technology | Purpose                         |
| ---------- | ------------------------------- |
| HTML       | Page structure                  |
| CSS        | Styling and layout              |
| JavaScript | Dynamic frontend functionality  |
| PHP        | Backend/server-side scripting   |
| MySQL      | Database storage and management |

---

# Project Structure

```plaintext
project-folder/
│
├── index.html
├── style.css
├── script.js
├── config.php
├── database.sql
│
├── assets/
│   ├── images/
│   └── icons/
│
├── includes/
│   ├── header.php
│   └── footer.php
│
└── README.md
```

---

# Installation & Setup

## Requirements

Before running the project, make sure you have:

* PHP installed
* MySQL installed
* A local server environment such as:

  * XAMPP
  * WAMP
  * Laragon

---

## Setup Instructions

### 1. Clone or Download the Project

```bash
git clone <repository-url>
```

Or download the ZIP file and extract it.

---

### 2. Move Project to Server Directory

Place the project folder inside your server directory:

* XAMPP → `htdocs`
* WAMP → `www`

---

### 3. Create the Database

1. Open **phpMyAdmin**
2. Create a new database
3. Import the provided `.sql` file

---

### 4. Configure Database Connection

Edit the PHP configuration file:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "your_database_name";
```

---

### 5. Start the Server

Start:

* Apache
* MySQL

from your local server control panel.

---

### 6. Run the Project

Open your browser and visit:

```plaintext
http://localhost/project-folder
```

---

# Purpose of the Project

This project was created as a practical demonstration of combining multiple web technologies into a single functional application.
It represents a foundational full-stack development project involving frontend design, backend logic, and database integration.

---

# AI Assistance Disclosure

Parts of this project were generated with the assistance of **Qwen**, an AI model developed by **Alibaba Group**.

The project was later reviewed, modified, and organized into a structured implementation.

---

# Future Improvements

* User authentication system
* Improved UI/UX
* API integration
* Better security practices
* Mobile-first optimization
* Deployment support

---

# License

This project is open for educational and personal use. Modify and distribute as needed.

---

# Author

Developed as a multi-language full-stack web development project with AI-assisted generation and manual refinement.
