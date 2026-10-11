<?php
require 'config.php';
header('Content-Type: application/json');
session_start();

// Simple auth check (for demo)
if (!isset($_SESSION['user_id'])) {
    // Allow read-only for demo; in production, uncomment:
    // http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

switch ($path) {
    case 'products':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT * FROM products WHERE is_deleted=0 ORDER BY id DESC");
            echo json_encode($stmt->fetchAll());
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO products (name, sku, barcode, price, cost, stock) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$data['name'], $data['sku'], $data['barcode'], $data['price'], $data['cost'], $data['stock']]);
            echo json_encode(['id' => $pdo->lastInsertId()]);
        }
        break;

    case 'sales':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT s.*, c.name as customer_name FROM sales s LEFT JOIN customers c ON s.customer_id=c.id ORDER BY s.id DESC");
            echo json_encode($stmt->fetchAll());
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $pdo->beginTransaction();
            try {
                $invoiceNo = 'RST-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $subtotal = 0;
                foreach ($data['items'] as $item) {
                    $subtotal += $item['price'] * $item['qty'];
                }
                $vat = $subtotal * 0.05;
                $grand = $subtotal + $vat - ($data['discount'] ?? 0);

                $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_id, subtotal, discount, vat, grand_total, payment_method) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$invoiceNo, $data['customer_id'], $subtotal, $data['discount'] ?? 0, $vat, $grand, $data['payment_method'] ?? 'Cash']);
                $saleId = $pdo->lastInsertId();

                $itemStmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, qty, price, cost) VALUES (?,?,?,?,?)");
                $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                foreach ($data['items'] as $item) {
                    $itemStmt->execute([$saleId, $item['product_id'], $item['qty'], $item['price'], $item['cost']]);
                    $stockStmt->execute([$item['qty'], $item['product_id']]);
                }
                $pdo->commit();
                echo json_encode(['success' => true, 'invoice_no' => $invoiceNo, 'grand_total' => $grand]);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
        break;

    case 'customers':
        $stmt = $pdo->query("SELECT * FROM customers ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'telecom':
        $stmt = $pdo->query("SELECT * FROM telecom_operators WHERE is_active=1");
        echo json_encode($stmt->fetchAll());
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}
?>