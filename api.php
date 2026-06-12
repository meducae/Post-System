<?php
// UPDATED POS SYSTEM API
// Based on the new roadmap requirements

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

// Helper function to send JSON response
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Helper function to validate barcode (13 digits)
function validateBarcode($barcode) {
    return (strlen($barcode) === 13 && ctype_digit($barcode));
}

// Helper function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action);
            break;
        case 'POST':
            handlePostRequest($action);
            break;
        case 'PUT':
            handlePutRequest($action);
            break;
        case 'DELETE':
            handleDeleteRequest($action);
            break;
        default:
            sendResponse(['error' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    sendResponse(['error' => $e->getMessage()], 500);
}

// Handle GET requests
function handleGetRequest($action) {
    global $conn;
    
    switch ($action) {
        case 'products':
            // GET /api/products
            $stmt = $conn->prepare("
                SELECT 
                    p.*,
                    GROUP_CONCAT(pb.barcode) as barcodes
                FROM products p
                LEFT JOIN product_barcodes pb ON p.id = pb.product_id
                GROUP BY p.id
                ORDER BY p.name
            ");
            $stmt->execute();
            $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Format barcodes
            foreach ($products as &$product) {
                $product['barcodes'] = $product['barcodes'] ? explode(',', $product['barcodes']) : [];
                $product['low_stock'] = $product['quantity'] < 5;
            }
            
            sendResponse(['success' => true, 'data' => $products]);
            break;
            
        case 'product':
            // GET /api/product?barcode=1234567890123 or GET /api/product?name=Pepsi
            $barcode = $_GET['barcode'] ?? '';
            $name = $_GET['name'] ?? '';
            
            if ($barcode) {
                $stmt = $conn->prepare("
                    SELECT p.* FROM products p
                    INNER JOIN product_barcodes pb ON p.id = pb.product_id
                    WHERE pb.barcode = ?
                ");
                $stmt->bind_param("s", $barcode);
            } elseif ($name) {
                $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
                $searchTerm = "%$name%";
                $stmt->bind_param("s", $searchTerm);
            } else {
                sendResponse(['error' => 'Barcode or name required'], 400);
            }
            
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            
            if ($product) {
                // Get barcodes for this product
                $stmt2 = $conn->prepare("SELECT barcode FROM product_barcodes WHERE product_id = ?");
                $stmt2->bind_param("i", $product['id']);
                $stmt2->execute();
                $barcodes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                $product['barcodes'] = array_column($barcodes, 'barcode');
                $product['low_stock'] = $product['quantity'] < 5;
                
                sendResponse(['success' => true, 'data' => $product]);
            } else {
                sendResponse(['error' => 'Product not found'], 404);
            }
            break;
            
        case 'sales':
            // GET /api/sales?month=2025-12
            $month = $_GET['month'] ?? '';
            
            if ($month) {
                $stmt = $conn->prepare("
                    SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as total_sales,
                        SUM(total_amount) as total_revenue,
                        SUM(profit_amount) as total_profit
                    FROM sales 
                    WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
                    GROUP BY DATE(created_at)
                    ORDER BY date
                ");
                $stmt->bind_param("s", $month);
            } else {
                $stmt = $conn->prepare("
                    SELECT 
                        s.*,
                        COUNT(si.id) as item_count
                    FROM sales s
                    LEFT JOIN sale_items si ON s.id = si.sale_id
                    GROUP BY s.id
                    ORDER BY s.created_at DESC
                    LIMIT 100
                ");
            }
            
            $stmt->execute();
            $sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            sendResponse(['success' => true, 'data' => $sales]);
            break;
            
        case 'analytics':
            // GET /api/analytics
            $analytics = [];
            
            // Top profitable products
            $stmt = $conn->prepare("
                SELECT 
                    p.name,
                    SUM(si.profit) as total_profit,
                    SUM(si.quantity) as total_quantity
                FROM sale_items si
                INNER JOIN products p ON si.product_id = p.id
                GROUP BY p.id, p.name
                ORDER BY total_profit DESC
                LIMIT 10
            ");
            $stmt->execute();
            $analytics['top_products'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Daily revenue (last 30 days)
            $stmt = $conn->prepare("
                SELECT 
                    DATE(created_at) as date,
                    SUM(total_amount) as revenue,
                    SUM(profit_amount) as profit
                FROM sales
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute();
            $analytics['daily_revenue'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Monthly breakdown
            $stmt = $conn->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(total_amount) as revenue,
                    SUM(profit_amount) as profit,
                    COUNT(*) as sales_count
                FROM sales
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            $stmt->execute();
            $analytics['monthly_breakdown'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            sendResponse(['success' => true, 'data' => $analytics]);
            break;
            
        case 'sale_details':
            // GET /api.php?action=sale_details&id=123
            try {
                $sale_id = intval($_GET['id'] ?? 0);
                if (!$sale_id) {
                    sendResponse(['error' => 'ID is required'], 400);
                }
                // Savdo asosiy ma'lumotlari
                $stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
                $stmt->bind_param("i", $sale_id);
                $stmt->execute();
                $sale = $stmt->get_result()->fetch_assoc();
                if (!$sale) {
                    sendResponse(['error' => 'Sale not found'], 404);
                }
                // Savdo itemlari
                $stmt = $conn->prepare("
                    SELECT si.*, p.name as product_name
                    FROM sale_items si
                    INNER JOIN products p ON si.product_id = p.id
                    WHERE si.sale_id = ?
                ");
                $stmt->bind_param("i", $sale_id);
                $stmt->execute();
                $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $sale['items'] = $items;
                sendResponse(['success' => true, 'data' => $sale]);
            } catch (Exception $e) {
                sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;
            
        case 'daily_sales':
            // GET /api.php?action=daily_sales&date=2025-01-15
            try {
                $date = $_GET['date'] ?? '';
                if (!$date) {
                    sendResponse(['error' => 'Date is required'], 400);
                }
                
                // Ma'lum kun uchun savdolar
                $stmt = $conn->prepare("
                    SELECT 
                        s.*,
                        COUNT(si.id) as item_count
                    FROM sales s
                    LEFT JOIN sale_items si ON s.id = si.sale_id
                    WHERE DATE(s.created_at) = ?
                    GROUP BY s.id
                    ORDER BY s.created_at DESC
                ");
                $stmt->bind_param("s", $date);
                $stmt->execute();
                $sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                // Kunlik statistikalar
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as total_sales,
                        SUM(total_amount) as total_revenue,
                        SUM(profit_amount) as total_profit
                    FROM sales 
                    WHERE DATE(created_at) = ?
                ");
                $stmt->bind_param("s", $date);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();
                
                sendResponse([
                    'success' => true, 
                    'data' => [
                        'sales' => $sales,
                        'stats' => $stats
                    ]
                ]);
            } catch (Exception $e) {
                sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;
            
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

// Handle POST requests
function handlePostRequest($action) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'products':
            // POST /api/products
            $name = sanitizeInput($input['name'] ?? '');
            $buy_price = floatval($input['buy_price'] ?? 0);
            $sell_price = floatval($input['sell_price'] ?? 0);
            $quantity = intval($input['quantity'] ?? 0);
            $barcodes = $input['barcodes'] ?? [];
            
            // Debug logging
            error_log("Received product data: " . json_encode($input));
            error_log("Barcodes received: " . json_encode($barcodes));
            
            if (empty($name) || $buy_price <= 0 || $sell_price <= 0) {
                sendResponse(['error' => 'Mahsulot nomi va narxlari to\'ldirilishi shart'], 400);
            }
            
            $conn->begin_transaction();
            try {
                // Insert product
                $stmt = $conn->prepare("INSERT INTO products (name, buy_price, sell_price, quantity) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sddi", $name, $buy_price, $sell_price, $quantity);
                $stmt->execute();
                $product_id = $conn->insert_id;
                
                // Insert barcodes (optional)
                $barcodes_inserted = 0;
                if (!empty($barcodes)) {
                    foreach ($barcodes as $barcode) {
                        if (validateBarcode($barcode)) {
                            $stmt = $conn->prepare("INSERT INTO product_barcodes (product_id, barcode) VALUES (?, ?)");
                            $stmt->bind_param("is", $product_id, $barcode);
                            $stmt->execute();
                            $barcodes_inserted++;
                        } else {
                            error_log("Invalid barcode skipped: " . $barcode);
                        }
                    }
                    error_log("Barcodes inserted: " . $barcodes_inserted);
                }
                
                $conn->commit();
                sendResponse(['success' => true, 'message' => 'Product created successfully', 'id' => $product_id]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        case 'sales':
            // POST /api/sales
            $items = $input['items'] ?? [];
            $payment_type = sanitizeInput($input['payment_type'] ?? 'cash');
            
            if (empty($items)) {
                sendResponse(['error' => 'No items in sale'], 400);
            }
            
            $conn->begin_transaction();
            try {
                $total_amount = 0;
                $total_profit = 0;
                
                // Calculate totals
                foreach ($items as $item) {
                    $product_id = intval($item['product_id']);
                    $quantity = intval($item['quantity']);
                    
                    // Get product details
                    $stmt = $conn->prepare("SELECT buy_price, sell_price, quantity FROM products WHERE id = ?");
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $product = $stmt->get_result()->fetch_assoc();
                    
                    if (!$product) {
                        throw new Exception("Product not found");
                    }
                    
                    if ($product['quantity'] < $quantity) {
                        throw new Exception("Insufficient stock for product ID: $product_id");
                    }
                    
                    $item_total = $product['sell_price'] * $quantity;
                    $item_profit = ($product['sell_price'] - $product['buy_price']) * $quantity;
                    
                    $total_amount += $item_total;
                    $total_profit += $item_profit;
                }
                
                // Create sale
                $stmt = $conn->prepare("INSERT INTO sales (total_amount, payment_type, profit_amount) VALUES (?, ?, ?)");
                $stmt->bind_param("dsd", $total_amount, $payment_type, $total_profit);
                $stmt->execute();
                $sale_id = $conn->insert_id;
                
                // Create sale items and update inventory
                foreach ($items as $item) {
                    $product_id = intval($item['product_id']);
                    $quantity = intval($item['quantity']);
                    
                    // Get product details
                    $stmt = $conn->prepare("SELECT buy_price, sell_price FROM products WHERE id = ?");
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $product = $stmt->get_result()->fetch_assoc();
                    
                    $item_profit = ($product['sell_price'] - $product['buy_price']) * $quantity;
                    
                    // Insert sale item
                    $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, sell_price, buy_price, profit) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iidddd", $sale_id, $product_id, $quantity, $product['sell_price'], $product['buy_price'], $item_profit);
                    $stmt->execute();
                    
                    // Update inventory
                    $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                    $stmt->bind_param("ii", $quantity, $product_id);
                    $stmt->execute();
                }
                
                $conn->commit();
                sendResponse(['success' => true, 'message' => 'Sale completed successfully', 'sale_id' => $sale_id]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

// Handle PUT requests
function handlePutRequest($action) {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'products':
            // PUT /api/products/{id}
            $id = intval($_GET['id'] ?? 0);
            $name = sanitizeInput($input['name'] ?? '');
            $buy_price = floatval($input['buy_price'] ?? 0);
            $sell_price = floatval($input['sell_price'] ?? 0);
            $quantity = intval($input['quantity'] ?? 0);
            $barcodes = $input['barcodes'] ?? [];
            
            // Debug logging
            error_log("PUT - Received product data: " . json_encode($input));
            error_log("PUT - Barcodes received: " . json_encode($barcodes));
            
            if ($id <= 0 || empty($name) || $buy_price <= 0 || $sell_price <= 0) {
                sendResponse(['error' => 'Invalid product data'], 400);
            }
            
            $conn->begin_transaction();
            try {
                // Update product
                $stmt = $conn->prepare("UPDATE products SET name = ?, buy_price = ?, sell_price = ?, quantity = ? WHERE id = ?");
                $stmt->bind_param("sddii", $name, $buy_price, $sell_price, $quantity, $id);
                $stmt->execute();
                
                // Update barcodes (delete old ones, insert new ones if provided)
                $stmt = $conn->prepare("DELETE FROM product_barcodes WHERE product_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $barcodes_inserted = 0;
                if (!empty($barcodes)) {
                    foreach ($barcodes as $barcode) {
                        if (validateBarcode($barcode)) {
                            $stmt = $conn->prepare("INSERT INTO product_barcodes (product_id, barcode) VALUES (?, ?)");
                            $stmt->bind_param("is", $id, $barcode);
                            $stmt->execute();
                            $barcodes_inserted++;
                        } else {
                            error_log("PUT - Invalid barcode skipped: " . $barcode);
                        }
                    }
                    error_log("PUT - Barcodes inserted: " . $barcodes_inserted);
                }
                
                $conn->commit();
                sendResponse(['success' => true, 'message' => 'Product updated successfully']);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}

// Handle DELETE requests
function handleDeleteRequest($action) {
    global $conn;
    
    switch ($action) {
        case 'products':
            // DELETE /api/products/{id}
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                sendResponse(['error' => 'Invalid product ID'], 400);
            }
            
            // Check if product is used in sales
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                sendResponse(['error' => 'Cannot delete product that has been sold'], 400);
            }
            
            // Delete product (barcodes will be deleted automatically due to CASCADE)
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                sendResponse(['success' => true, 'message' => 'Product deleted successfully']);
            } else {
                sendResponse(['error' => 'Product not found'], 404);
            }
            break;
            
        default:
            sendResponse(['error' => 'Invalid action'], 400);
    }
}
?> 