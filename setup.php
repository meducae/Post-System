<?php
// POS System Setup Script
// This script will create all necessary database tables and sample data

require_once 'config.php';

echo "<h1>POS Sistema O'rnatish</h1>";

try {
    // Read and execute the database schema
    $schema = file_get_contents('database_schema.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $result = $conn->query($statement);
            if ($result === false) {
                echo "<p style='color: red;'>Xatolik: " . $conn->error . "</p>";
                echo "<p>SQL: " . htmlspecialchars($statement) . "</p>";
            } else {
                echo "<p style='color: green;'>✅ Bajarildi: " . substr($statement, 0, 50) . "...</p>";
            }
        }
    }
    
    echo "<h2>✅ O'rnatish muvaffaqiyatli yakunlandi!</h2>";
    echo "<p>Endi siz quyidagi sahifalarga kirishingiz mumkin:</p>";
    echo "<ul>";
    echo "<li><a href='login.php'>Kirish sahifasi</a></li>";
    echo "<li><a href='admin.php'>Admin panel</a></li>";
    echo "<li><a href='cashier.php'>Kassir sahifasi</a></li>";
    echo "</ul>";
    
    echo "<h3>Demo ma'lumotlar:</h3>";
    echo "<p><strong>Admin:</strong> admin / admin123</p>";
    echo "<p><strong>Kassir:</strong> cashier / admin123</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Xatolik: " . $e->getMessage() . "</p>";
}
?> 