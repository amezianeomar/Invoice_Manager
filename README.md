
# Ameziane Tours Invoice System

A custom web-based invoicing and management system designed for **Ameziane Tours**, a transport and tourism service provider. This application handles client management, service listings, and invoice generation.

## 🚀 Features

- **Dashboard**: Quick overview of recent invoices.
- **Invoice Management**: 
    - Create, Edit, View, and Delete invoices.
    - Add multiple services per invoice dynamically.
    - Automatic calculation of totals.
- **Service Management**:
    - Pre-defined services (Transport, Excursions, etc.).
    - Special fields based on service type (e.g., "From/To" for transport).
- **PDF Generation**: Native browser-based print layout for saving high-quality PDFs.
- **Authentication**: Simple admin login system.

## 🛠️ Technology Stack

- **Backend**: PHP (Native/Vanilla)
- **Database**: MySQL (PDO)
- **Frontend**: HTML5, Bootstrap 5, Vanilla JavaScript
- **Styling**: Custom CSS + FontAwesome Icons

## ⚙️ Installation & Setup

1.  **Prerequisites**:
    - A local web server (WAMP, XAMPP, or similar) with PHP and MySQL.
    
2.  **Database Configuration**:
    - Create a database named `ameziane_tours`.
    - Import the provided schema file: `ameziane_tours (1).sql`.
    - Configure the connection in `config/database.php`:
      ```php
      private $host = 'localhost';
      private $dbname = 'ameziane_tours';
      private $username = 'root';
      private $password = '';
      ```

3.  **Run the Application**:
    - Place the project folder in your web server's root directory (e.g., `www/` or `htdocs/`).
    - Navigate to `http://localhost/AMEZIANE_TOURS/`.

## 📂 Project Structure

- **`classes/`**: Contains core logic classes (e.g., `InvoiceManager.php` for DB operations).
- **`config/`**: Database configuration details.
- **`auth.php`**: Authentication logic class.
- **`index.php`**: Main dashboard and invoice creation interface.
- **`generate-pdf.php`**: Printable view of the invoice.
- **`admin-login.php`**: Admin login page.

## 🔒 Security Note

This application is intended for internal use. Please review the `INSIGHTS.md` file for important security considerations before deploying to a public server.
