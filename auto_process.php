<?php
require_once "config.php";

// Auto-process old monthly data
echo "🔄 Auto-processing old monthly data...\n";

// Check if any month is older than 1 month and process it
$old_months = $conn->query("
    SELECT DISTINCT month_year 
    FROM monthly_sales 
    WHERE month_year < DATE_FORMAT(NOW(), '%Y-%m')
")->fetch_all(MYSQLI_ASSOC);

if (empty($old_months)) {
    echo "✅ No old months to process.\n";
    exit;
}

$conn->begin_transaction();
try {
    foreach ($old_months as $old_month) {
        echo "📅 Processing month: {$old_month['month_year']}\n";
        
        $monthly_sales = $conn->query("
            SELECT 
                month_year,
                SUM(total_amount) as total_amount,
                SUM(total_orders) as total_orders,
                payment_type
            FROM monthly_sales 
            WHERE month_year = '{$old_month['month_year']}'
            GROUP BY payment_type
        ")->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($monthly_sales)) {
            foreach ($monthly_sales as $sale) {
                $stmt = $conn->prepare("
                    INSERT INTO yearly_sales (sale_date, total_amount, total_orders, payment_type, year, month_name)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $year = substr($old_month['month_year'], 0, 4);
                $month_name = date('F', strtotime($old_month['month_year'] . '-01'));
                $stmt->bind_param("sdssss", $old_month['month_year'] . '-01', $sale['total_amount'], $sale['total_orders'], $sale['payment_type'], $year, $month_name);
                $stmt->execute();
            }
            $conn->query("DELETE FROM monthly_sales WHERE month_year = '{$old_month['month_year']}'");
            echo "✅ Month {$old_month['month_year']} processed and moved to annual reports.\n";
        }
    }
    
    $conn->commit();
    echo "✅ All old months processed successfully!\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 