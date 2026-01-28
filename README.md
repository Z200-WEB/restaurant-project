# SmartOrder

A table-based restaurant ordering web application that allows customers to place orders directly from tablets at their tables. Includes a management dashboard for staff to monitor orders.

## Features

### Customer Interface
- Menu display organized by categories (appetizers, mains, drinks, etc.)
- Table-based ordering with unique table numbers
- Quantity selection (1-4 items) with confirmation modal
- Real-time running total for each table
- Responsive design for tablets and mobile devices

### Management Dashboard
- View all orders sorted by most recent
- Quick navigation to specific table orders
- Detailed order view with itemized bills
- Total amount calculation

## Technologies

- **Backend:** PHP 7.0+, MySQL/MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Database:** PDO for secure database operations

## Project Structure

```
smartorder/
├── index.php        # Customer ordering interface
├── logic.php        # Order processing backend (API endpoint)
├── order.php        # Order details view
├── management.php   # Admin dashboard
├── pdo.php          # Database connection configuration
├── script.js        # Frontend JavaScript (modal, AJAX)
└── style.css        # Responsive styling
```

## Database Schema

The application uses four tables:

- **sCategory** - Menu categories (id, categoryName, state)
- **sItem** - Menu items (id, name, price, category, state)
- **sManagement** - Order headers (orderNo, tableNo, state, dateA, dateB)
- **sOrder** - Order line items (orderNo, itemNo, amount)

## Setup

### Prerequisites
- PHP 7.0+ with PDO extension
- MySQL 5.7+ or MariaDB 10.0+
- Web server (Apache, Nginx, or PHP built-in server)

### Installation

1. **Create the database:**
   ```sql
   CREATE DATABASE smart_order;
   ```

2. **Configure database connection:**

   Edit `pdo.php` and update the following values:
   ```php
   $dbHost = "localhost";
   $dbName = "smart_order";
   $user = "your_username";
   $password = "your_password";
   ```

3. **Create database tables and add menu data**

4. **Deploy to web server or run locally:**
   ```bash
   php -S localhost:8000
   ```

## Usage

- **Customer ordering:** Access `index.php?tableNo=1` (replace 1 with table number)
- **Management dashboard:** Access `management.php`

## Security Features

- Prepared statements with parameterized queries (SQL injection prevention)
- Input validation and sanitization
- HTML escaping for output
- Database transaction rollback on errors

## License

This project is for educational/personal use.
