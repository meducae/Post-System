<?php
require_once "config.php";

echo "<h2>🔧 Database Setup & Repair Tool</h2>";

// Test connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

echo "✅ Database connection successful<br><br>";

// Step 1: Create all required tables
echo "<h3>Step 1: Creating all required tables</h3>";

$tables = [
    "products" => "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        retail_price DECIMAL(10,2) NOT NULL,
        selling_price DECIMAL(10,2) NOT NULL,
        quantity INT DEFAULT 0,
        barcode VARCHAR(50) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "sales" => "CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_type ENUM('cash', 'card') DEFAULT 'cash',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "sale_items" => "CREATE TABLE IF NOT EXISTS sale_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        selling_price DECIMAL(10,2) NOT NULL,
        retail_price DECIMAL(10,2) NOT NULL,
        profit DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    
    "sales_archive" => "CREATE TABLE IF NOT EXISTS sales_archive (
        id INT AUTO_INCREMENT PRIMARY KEY,
        original_sale_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_type ENUM('cash', 'card') DEFAULT 'cash',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "sale_items_archive" => "CREATE TABLE IF NOT EXISTS sale_items_archive (
        id INT AUTO_INCREMENT PRIMARY KEY,
        original_sale_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        selling_price DECIMAL(10,2) NOT NULL,
        retail_price DECIMAL(10,2) NOT NULL,
        profit DECIMAL(10,2) NOT NULL,
        product_name VARCHAR(255),
        archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "monthly_sales" => "CREATE TABLE IF NOT EXISTS monthly_sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_date DATE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        total_profit DECIMAL(10,2) NOT NULL DEFAULT 0,
        total_orders INT NOT NULL,
        payment_type VARCHAR(255) NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "yearly_sales" => "CREATE TABLE IF NOT EXISTS yearly_sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_date DATE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        total_profit DECIMAL(10,2) NOT NULL DEFAULT 0,
        total_orders INT NOT NULL,
        payment_type VARCHAR(255) NOT NULL,
        year INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $table_name => $sql) {
    if ($conn->query($sql)) {
        echo "✅ Table '$table_name' created/verified successfully<br>";
    } else {
        echo "❌ Error with table '$table_name': " . $conn->error . "<br>";
    }
}

// Step 2: Add missing columns to existing tables
echo "<br><h3>Step 2: Adding missing columns</h3>";

$column_fixes = [
    "products" => [
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS retail_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER name",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS selling_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER retail_price",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ],
    "sale_items" => [
        "ALTER TABLE sale_items ADD COLUMN IF NOT EXISTS selling_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER quantity",
        "ALTER TABLE sale_items ADD COLUMN IF NOT EXISTS retail_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER selling_price",
        "ALTER TABLE sale_items ADD COLUMN IF NOT EXISTS profit DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER retail_price"
    ],
    "monthly_sales" => [
        "ALTER TABLE monthly_sales ADD COLUMN IF NOT EXISTS total_profit DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total_amount"
    ],
    "yearly_sales" => [
        "ALTER TABLE yearly_sales ADD COLUMN IF NOT EXISTS total_profit DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total_amount"
    ]
];

$existing_tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $existing_tables[] = $row[0];
}

foreach ($column_fixes as $table => $fixes) {
    if (in_array($table, $existing_tables)) {
        echo "Fixing columns in table '$table':<br>";
        foreach ($fixes as $fix) {
            if ($conn->query($fix)) {
                echo "  ✅ Applied: " . substr($fix, 0, 50) . "...<br>";
            } else {
                echo "  ❌ Error: " . $conn->error . "<br>";
            }
        }
    }
}

// Step 3: Handle old price column migration
echo "<br><h3>Step 3: Migrating old price columns</h3>";

// Check if old 'price' column exists in products table
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'price'");
if ($result->num_rows > 0) {
    echo "Found old 'price' column in products table. Migrating data...<br>";
    
    // Copy price data to selling_price if selling_price is 0
    $conn->query("UPDATE products SET selling_price = price WHERE selling_price = 0 OR selling_price IS NULL");
    $conn->query("UPDATE products SET retail_price = price * 0.8 WHERE retail_price = 0 OR retail_price IS NULL");
    
    // Remove old price column
    $conn->query("ALTER TABLE products DROP COLUMN price");
    echo "✅ Migration completed<br>";
}

// Check if old 'price' column exists in sale_items table
$result = $conn->query("SHOW COLUMNS FROM sale_items LIKE 'price'");
if ($result->num_rows > 0) {
    echo "Found old 'price' column in sale_items table. Migrating data...<br>";
    
    // Copy price data to selling_price if selling_price is 0
    $conn->query("UPDATE sale_items SET selling_price = price WHERE selling_price = 0 OR selling_price IS NULL");
    $conn->query("UPDATE sale_items SET retail_price = price * 0.8 WHERE retail_price = 0 OR retail_price IS NULL");
    $conn->query("UPDATE sale_items SET profit = selling_price - retail_price WHERE profit = 0 OR profit IS NULL");
    
    // Remove old price column
    $conn->query("ALTER TABLE sale_items DROP COLUMN price");
    echo "✅ Migration completed<br>";
}

// Step 4: Add sample data if products table is empty
echo "<br><h3>Step 4: Adding sample data</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "Products table is empty. Adding sample data...<br>";
    
    $sample_products = [
        ['Coca Cola', 4000, 5000, 100, '123456789'],
        ['Pepsi', 3500, 4500, 80, '987654321'],
        ['Bread', 2500, 3000, 50, '111222333'],
        ['Milk', 6000, 8000, 30, '444555666']
    ];

    foreach ($sample_products as $product) {
        $stmt = $conn->prepare("INSERT IGNORE INTO products (name, retail_price, selling_price, quantity, barcode) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sddss", $product[0], $product[1], $product[2], $product[3], $product[4]);
        if ($stmt->execute()) {
            echo "✅ Added product: {$product[0]}<br>";
        } else {
            echo "❌ Error adding product {$product[0]}: " . $stmt->error . "<br>";
        }
    }
} else {
    echo "Products table already has {$row['count']} records<br>";
}

// Step 5: Final verification
echo "<br><h3>Step 5: Final verification</h3>";
$tables_to_verify = ['products', 'sales', 'sale_items', 'sales_archive', 'sale_items_archive', 'monthly_sales', 'yearly_sales'];

foreach ($tables_to_verify as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Table '$table': {$row['count']} records<br>";
    } else {
        echo "❌ Error checking table '$table': " . $conn->error . "<br>";
    }
}

$conn->close();

echo "<br><h3>🎉 Database setup completed!</h3>";
echo "<a href='admin.php'>Go to Admin Panel</a> | ";
echo "<a href='public/index.html'>Go to POS</a>";
?> 