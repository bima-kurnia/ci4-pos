# 🏪 SwiftPOS — CodeIgniter 4 Point of Sale System

A full-featured, production-ready POS web application built with **CodeIgniter 4**, **MySQL**, **Tailwind CSS**, and **DaisyUI**.

---

## 📦 Tech Stack

| Layer     | Technology                   |
|-----------|------------------------------|
| Backend   | CodeIgniter 4 (MVC)          |
| Database  | MySQL 8.x                    |
| Frontend  | Tailwind CSS + DaisyUI 4.x   |
| Auth      | Session-based, role-protected |
| Fonts     | Plus Jakarta Sans, JetBrains Mono |

---

## 🗄️ Database Schema (9 Tables)

```
users              — System users (admin / cashier)
categories         — Product categories
products           — Product catalog with stock tracking
customers          — Customer registry
transactions       — Sale transaction headers
transaction_items  — Line items per transaction
payments           — Payment records (cash/card/transfer/ewallet)
stock_movements    — Full stock in/out audit trail
discounts          — Reusable discount definitions
```

---

## ⚙️ Setup Instructions

### 1. Install CodeIgniter 4

```bash
composer create-project codeigniter4/appstarter ci4-pos
cd ci4-pos
```

### 2. Copy All Generated Files

Copy the entire `app/` directory contents from this project into your CI4 installation.

### 3. Configure Environment

```bash
cp .env.example .env
```

Edit `.env`:
```ini
CI_ENVIRONMENT = development
app.baseURL    = 'http://localhost:8080/'

database.default.hostname = 127.0.0.1
database.default.database = pos_db
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
```

### 4. Create MySQL Database

```sql
CREATE DATABASE pos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Migrations

```bash
php spark migrate
```

This will create all 9 tables in the correct order with proper foreign keys.

### 6. Seed Dummy Data

```bash
php spark db:seed MainSeeder
```

Seeds:
- 2 users (admin + cashier)
- 5 categories
- 12 products
- 5 customers
- 7 discount types

### 7. Start Development Server

```bash
php spark serve
```

Visit: **http://localhost:8080**

---

## 🔑 Default Login Credentials

| Role    | Email              | Password     |
|---------|--------------------|--------------|
| Admin   | admin@pos.com      | admin123     |
| Cashier | cashier@pos.com    | cashier123   |

---

## 🗂️ File Structure

```
app/
├── Config/
│   ├── Database.php
│   ├── Filters.php          ← registers auth + admin filters
│   └── Routes.php           ← all application routes
│
├── Controllers/
│   ├── AuthController.php       login/logout
│   ├── DashboardController.php  home stats
│   ├── PosController.php        ★ checkout + DB transaction
│   ├── ProductController.php    CRUD + stock log
│   ├── CategoryController.php   CRUD
│   ├── CustomerController.php   CRUD + AJAX search
│   ├── TransactionController.php list + detail + cancel
│   └── UserController.php       admin user management
│
├── Filters/
│   ├── AuthFilter.php       redirect if not logged in
│   └── AdminFilter.php      redirect if not admin
│
├── Models/
│   ├── UserModel.php
│   ├── CategoryModel.php
│   ├── ProductModel.php         stock management helpers
│   ├── CustomerModel.php
│   ├── TransactionModel.php     invoice generator, filters
│   ├── TransactionItemModel.php
│   ├── PaymentModel.php
│   ├── StockMovementModel.php   record() helper
│   └── DiscountModel.php        calculate() helper
│
├── Database/
│   ├── Migrations/
│   │   ├── ..._CreateUsersTable.php
│   │   ├── ..._CreateCategoriesTable.php
│   │   ├── ..._CreateProductsTable.php
│   │   ├── ..._CreateCustomersTable.php
│   │   ├── ..._CreateTransactionsTable.php
│   │   ├── ..._CreateTransactionItemsTable.php
│   │   ├── ..._CreatePaymentsTable.php
│   │   ├── ..._CreateStockMovementsTable.php
│   │   └── ..._CreateDiscountsTable.php
│   └── Seeds/
│       └── MainSeeder.php
│
└── Views/
    ├── layouts/
    │   └── main.php             sidebar layout
    ├── auth/
    │   └── login.php
    ├── dashboard/
    │   └── index.php
    ├── pos/
    │   └── index.php            ★ full POS interface
    ├── products/
    │   ├── index.php
    │   ├── form.php             create + edit
    │   └── stock_log.php
    ├── categories/
    │   └── index.php
    ├── customers/
    │   └── index.php
    ├── transactions/
    │   ├── index.php
    │   └── show.php             receipt view
    └── users/
        └── index.php
```

---

## 💳 Checkout Flow (Database Transaction)

```
POST /pos/checkout  (JSON payload from POS frontend)
│
├── 1. Validate cart is not empty
├── 2. For each cart item:
│   ├── Fetch product row from DB
│   └── Check stock >= requested qty  ← throws if insufficient
│
├── 3. Calculate:
│   ├── total_amount (sum of line items)
│   ├── discount_amount (via DiscountModel::calculate)
│   ├── tax_amount (11% of after-discount)
│   └── grand_total
│
├── 4. Validate: amount_paid >= grand_total
├── 5. Generate unique invoice number (INV-YYYYMMDD-XXXXX)
│
├── DB::transBegin()
│   ├── INSERT transactions
│   ├── For each item:
│   │   ├── INSERT transaction_items
│   │   ├── UPDATE products SET stock = stock - qty  ← prevents negative
│   │   └── INSERT stock_movements (type=out, reference=invoice)
│   └── INSERT payments
│
├── DB::transCommit()   ← all or nothing
│   └── return JSON { success, invoice_number, change_amount, redirect }
│
└── On Exception:
    ├── DB::transRollback()
    └── return JSON { success: false, message: error }
```

---

## 🔐 Role-Based Access Control

| Feature              | Admin | Cashier |
|----------------------|-------|---------|
| POS Checkout         | ✅    | ✅      |
| View Transactions    | ✅    | ✅      |
| Cancel Transaction   | ✅    | ❌      |
| Product CRUD         | ✅    | View only |
| Category CRUD        | ✅    | ❌      |
| Customer CRUD        | ✅    | View only |
| User Management      | ✅    | ❌      |
| Stock Log            | ✅    | ✅      |

---

## 🚀 Key Features

- ✅ Full DB transaction on checkout (atomic, rollback on error)
- ✅ Prevents negative stock with guard checks
- ✅ Auto-generated invoice numbers (INV-YYYYMMDD-XXXXX)
- ✅ Real-time cart with quantity controls
- ✅ Tax (11%) + flexible discounts (% or flat)
- ✅ Quick-amount payment buttons
- ✅ Role-based access (admin vs cashier)
- ✅ Stock movement audit trail
- ✅ Print-ready transaction receipts
- ✅ Date/status filtering for transactions
- ✅ Low-stock dashboard alerts
- ✅ DaisyUI modals for all CRUD operations
- ✅ AJAX product search on POS screen
- ✅ Responsive layout (sidebar + top nav)

---

## 🛠️ Spark Commands Reference

```bash
# Run all migrations
php spark migrate

# Rollback migrations
php spark migrate:rollback

# Seed dummy data
php spark db:seed MainSeeder

# Create new migration
php spark make:migration CreateXxxTable

# Start dev server
php spark serve

# Start on custom port
php spark serve --port=8888
```

---

## 📝 Notes

- CSRF protection is configured via `.env` — enabled in production
- All monetary values stored as `DECIMAL(15,2)` for accuracy
- Stock movements record every change (initial, sale, manual adjustment)
- Invoice numbers reset daily by prefix (INV-YYYYMMDD-)
- Session expires after 2 hours (configurable in `.env`)
