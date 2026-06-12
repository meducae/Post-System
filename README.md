# 🛒 Post System - Advanced POS Solution

**A modern, feature-rich Point of Sale (POS) system built with PHP and MySQL. Designed for retail businesses to manage products, sales, inventory, and generate comprehensive reports.**

[🇺🇿 Uzbek Version](#uzbek-version) | [English](#english-version)

---

## English Version

### Overview
Post System is a comprehensive Point of Sale (POS) application that streamlines retail operations. It provides real-time inventory management, sales tracking, barcode support, and advanced analytics with an intuitive user interface.

### 🎯 Key Features

#### 📦 Product Management
- ✅ Add, edit, delete products
- ✅ Multiple barcodes per product (13-digit support)
- ✅ Cost and selling prices
- ✅ Automatic inventory reduction
- ✅ Low stock alerts (< 5 items)

#### 💰 Sales Processing
- ✅ Search by barcode or product name
- ✅ Shopping cart functionality
- ✅ Cash/Card payment support
- ✅ Automatic profit calculation
- ✅ PDF receipt generation and printing

#### 📊 Reports & Analytics
- ✅ Top profit-generating products
- ✅ Daily and monthly sales reports
- ✅ Calendar view of monthly sales
- ✅ Excel and PDF export options

#### 🔐 Security & Access Control
- ✅ Admin and cashier roles
- ✅ Session management
- ✅ Secure RESTful API
- ✅ Password protection

### 🚀 Installation

#### 1. System Requirements
- PHP 7.4+ or 8.0+
- MySQL 5.7+ or 8.0+
- Web server (Apache/Nginx)
- Modern web browser

#### 2. Database Setup
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3. Configuration
Edit `config.php`:
```php
$host = "localhost";
$user = "your_username";     
$pass = "your_password";        
$db   = "pos_system";
```

#### 4. Installation
```bash
# Access setup via web server
http://localhost/pos_system/setup.php
```

### 📁 File Structure
```
pos_system/
├── config.php              # Database configuration
├── database_schema.sql     # Database schema
├── setup.php              # Installation script
├── setup_database.php     # Database initialization
├── login.php              # Login page
├── logout.php             # Logout script
├── api.php                # REST API endpoints
├── admin.php              # Admin dashboard
├── cashier.php            # Cashier interface
├── password.php           # Password management
├── auto_process.php       # Background processes
├── index.html             # Home page
├── db/                    # Database backup folder
├── logs/                  # Log files
└── README.md              # This file
```

### 🔑 Demo Credentials

**Admin Account:**
- Login: `admin`
- Password: `admin123`

**Cashier Account:**
- Login: `cashier`
- Password: `admin123`

### 📱 Usage Guide

#### 1. Login
- Navigate to `login.php`
- Use demo credentials

#### 2. Admin Panel
- Product management
- Sales analysis
- Reports and charts
- Calendar view

#### 3. Cashier Interface
- Search products
- Add to cart
- Complete transactions
- Generate receipts

### 🔧 API Endpoints

**Products:**
- `GET /api.php?action=products` - List all products
- `GET /api.php?action=product&barcode=1234567890123` - Get by barcode
- `POST /api.php?action=products` - Create new product
- `PUT /api.php?action=products&id=1` - Update product
- `DELETE /api.php?action=products&id=1` - Delete product

**Sales:**
- `GET /api.php?action=sales` - List all sales
- `GET /api.php?action=sales&month=2025-01` - Monthly sales
- `POST /api.php?action=sales` - Record new sale

**Analytics:**
- `GET /api.php?action=analytics` - Get analytics data

### 🎨 Technology Stack

**Frontend:**
- HTML5
- Tailwind CSS
- jQuery
- Chart.js
- FullCalendar
- jsPDF

**Backend:**
- PHP 7.4+
- MySQL 5.7+
- RESTful API

### 📊 Database Schema

**Main Tables:**
- `products` - Product inventory
- `product_barcodes` - Barcode mappings
- `sales` - Transaction records
- `sale_items` - Individual items in sales
- `users` - User accounts

### 🔄 Version History

**v2.0 (Current)**
- ✅ Modern UI/UX design
- ✅ Multi-barcode support
- ✅ Real-time analytics
- ✅ Calendar view
- ✅ PDF receipt generation
- ✅ Enhanced security

### 🛠 Troubleshooting

**PHP Issues:**
```bash
php -v                    # Check PHP version
phpinfo()                 # Display PHP info
```

**Database Connection:**
```bash
mysql -u username -p database_name    # Test connection
SHOW TABLES;                          # List tables
```

### 🤝 Contributing
Pull requests are welcome. For major changes, please open an issue first.

### 📄 License
MIT License - See LICENSE file for details

---

# 🇺🇿 Uzbek Version

## 🛒 POS Sistema - To'liq Yangilangan

Zamonaviy va funksionalli POS (Point of Sale) sistema, barcha talablaringizga mos keladi.

## 🎯 Asosiy Xususiyatlar

### 📦 Mahsulotlar Boshqaruvi
- ✅ Mahsulot qo'shish, tahrirlash, o'chirish
- ✅ Bir mahsulot uchun 4 ta shtrix-kod (13 xonali)
- ✅ Olingan narx va sotuv narxi
- ✅ Avtomatik son kamayish
- ✅ Kam son ogohlantirish (< 5 dona)

### 💰 Savdo
- ✅ Shtrix-kod yoki nom orqali qidirish
- ✅ Savatchaga qo'shish
- ✅ Naqd/karta to'lov
- ✅ Avtomatik foyda hisoblash
- ✅ PDF chek yaratish va chop etish

### 📊 Hisobotlar va Tahlil
- ✅ Eng ko'p foyda beruvchi mahsulotlar
- ✅ Kunlik va oylik savdo
- ✅ Kalendar ko'rinishida oylik savdolar
- ✅ Excel va PDF eksport

### 🔐 Xavfsizlik
- ✅ Admin va kassir rollari
- ✅ Sessiya boshqaruvi
- ✅ Xavfsiz API

## 🚀 O'rnatish

### 1. Tizim talablari
- PHP 7.4+ yoki 8.0+
- MySQL 5.7+ yoki 8.0+
- Web server (Apache/Nginx)

### 2. Ma'lumotlar bazasini sozlash
```bash
# MySQL'ga ulaning
mysql -u root -p

# Ma'lumotlar bazasini yarating
CREATE DATABASE pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Konfiguratsiya
`config.php` faylini o'zgartiring:
```php
$host = "localhost";
$user = "your_username";     
$pass = "your_password";        
$db   = "pos_system";
```

### 4. O'rnatish
```bash
# Web server orqali setup.php'ni ishga tushiring
http://localhost/pos_system/setup.php
```

## 📁 Fayl tuzilishi

```
pos_system/
├── config.php              # Ma'lumotlar bazasi konfiguratsiyasi
├── database_schema.sql     # Ma'lumotlar bazasi sxemasi
├── setup.php              # O'rnatish skripti
├── login.php              # Kirish sahifasi
├── logout.php             # Chiqish skripti
├── api.php                # API endpoint'lar
├── admin.php              # Admin panel
├── cashier.php            # Kassir sahifasi
└── README.md              # Bu fayl
```

## 🔑 Demo ma'lumotlar

### Admin
- **Login:** admin
- **Parol:** admin123

### Kassir
- **Login:** cashier
- **Parol:** admin123

## 📱 Foydalanish

### 1. Kirish
- `login.php` sahifasiga kiring
- Demo ma'lumotlar bilan tizimga kiring

### 2. Admin Panel
- Mahsulotlar boshqaruvi
- Savdolar tahlili
- Hisobotlar va grafiklar
- Kalendar ko'rinish

### 3. Kassir Sahifasi
- Mahsulot qidirish
- Savatchaga qo'shish
- Savdoni yakunlash
- Chek yaratish

## 🔧 API Endpoint'lar

### Mahsulotlar
- `GET /api.php?action=products` - Barcha mahsulotlar
- `GET /api.php?action=product&barcode=1234567890123` - Shtrix-kod orqali
- `POST /api.php?action=products` - Yangi mahsulot
- `PUT /api.php?action=products&id=1` - Mahsulotni tahrirlash
- `DELETE /api.php?action=products&id=1` - Mahsulotni o'chirish

### Savdolar
- `GET /api.php?action=sales` - Barcha savdolar
- `GET /api.php?action=sales&month=2025-01` - Oylik savdolar
- `POST /api.php?action=sales` - Yangi savdo

### Tahlil
- `GET /api.php?action=analytics` - Barcha tahlil ma'lumotlari

## 🎨 Texnologiyalar

### Frontend
- **HTML5** - Struktura
- **Tailwind CSS** - Stillar
- **jQuery** - JavaScript kutubxonasi
- **Chart.js** - Grafiklar
- **FullCalendar** - Kalendar
- **jsPDF** - PDF yaratish

### Backend
- **PHP** - Server-side logika
- **MySQL** - Ma'lumotlar bazasi
- **RESTful API** - Ma'lumotlar almashinuvi

## 📊 Ma'lumotlar bazasi sxemasi

### Asosiy jadvallar
- `products` - Mahsulotlar
- `product_barcodes` - Mahsulot shtrix-kodlari
- `sales` - Savdolar
- `sale_items` - Savdo elementlari
- `users` - Foydalanuvchilar

## 🔄 Yangilanishlar

### v2.0 (Hozirgi versiya)
- ✅ Zamonaviy UI/UX
- ✅ Ko'p shtrix-kod qo'llab-quvvatlash
- ✅ Real-time tahlil
- ✅ Kalendar ko'rinish
- ✅ PDF chek yaratish
- ✅ Xavfsizlik yaxshilanishlari

## 🛠 Xatoliklarni tuzatish

### PHP xatoliklari
```bash
# PHP versiyasini tekshiring
php -v

# PHP xatoliklarini ko'rsatish
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Ma'lumotlar bazasi xatoliklari
```bash
# MySQL ulanishini tekshiring
mysql -u your_user -p your_database

# Jadvallarni tekshiring
SHOW TABLES;
```

## 📞 Yordam

Agar muammolar bo'lsa:
1. `setup.php` ni qayta ishga tushiring
2. Ma'lumotlar bazasi ulanishini tekshiring
3. PHP xatoliklarini ko'ring
4. Web server log'larini tekshiring

## 📄 Litsenziya

Bu loyiha MIT litsenziyasi ostida tarqatiladi.

---

**🎉 POS Sistema tayyor! Endi savdolaringizni boshqarish oson!** 