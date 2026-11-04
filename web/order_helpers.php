<?php
require_once 'db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
function oe($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function order_csrf() { if (empty($_SESSION['order_csrf'])) { $_SESSION['order_csrf'] = bin2hex(random_bytes(32)); } return $_SESSION['order_csrf']; }
function order_check_csrf() { if (!hash_equals(order_csrf(), (string)($_POST['csrf'] ?? ''))) { throw new RuntimeException('แบบฟอร์มหมดอายุ กรุณาโหลดหน้าใหม่'); } }
function order_products(PDO $conn) {
    return $conn->query('SELECT product_id, product_name, price, stock FROM products ORDER BY product_id')->fetchAll(PDO::FETCH_ASSOC);
}
function order_validate_lines($ids, $qtys) {
    if (!is_array($ids) || !is_array($qtys) || count($ids) !== count($qtys) || count($ids) < 1 || count($ids) > 30) { throw new RuntimeException('กรุณาเลือกสินค้าอย่างน้อยหนึ่งรายการ'); }
    $lines = [];
    foreach ($ids as $i => $id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $qty = filter_var($qtys[$i], FILTER_VALIDATE_INT);
        if (!$id || $id < 1 || !$qty || $qty < 1 || $qty > 100000) { throw new RuntimeException('สินค้าและจำนวนต้องถูกต้อง'); }
        $lines[$id] = ($lines[$id] ?? 0) + $qty;
        if ($lines[$id] > 100000) { throw new RuntimeException('จำนวนสินค้ามากเกินไป'); }
    }
    return $lines;
}
function order_save(PDO $conn, ?int $id, string $customer, array $requested) {
    if ($customer === '' || mb_strlen($customer, 'UTF-8') > 150) { throw new RuntimeException('กรุณากรอกชื่อลูกค้าไม่เกิน 150 ตัวอักษร'); }
    $conn->beginTransaction();
    try {
        $old = [];
        if ($id !== null) {
            $q = $conn->prepare('SELECT order_id FROM orders WHERE order_id = ? FOR UPDATE');
            $q->execute([$id]);
            if (!$q->fetchColumn()) { throw new RuntimeException('ไม่พบคำสั่งซื้อ'); }
            $q = $conn->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
            $q->execute([$id]);
            foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) { $pid=(int)$row['product_id']; $old[$pid] = ($old[$pid] ?? 0) + (int)$row['quantity']; }
        }
        $allIds = array_unique(array_merge(array_keys($old), array_keys($requested)));
        sort($allIds, SORT_NUMERIC);
        $q = $conn->prepare('SELECT product_id, price, stock FROM products WHERE product_id = ? FOR UPDATE');
        $products = [];
        foreach ($allIds as $pid) {
            $q->execute([$pid]);
            $p = $q->fetch(PDO::FETCH_ASSOC);
            if (!$p) { throw new RuntimeException('ไม่พบสินค้ารหัส #' . $pid); }
            $products[$pid] = $p;
        }
        $total = 0;
        foreach ($requested as $pid => $qty) {
            $available = (int)$products[$pid]['stock'] + ($old[$pid] ?? 0);
            if ($qty > $available) { throw new RuntimeException('สินค้า #' . $pid . ' มีสต็อกไม่พอ (สูงสุด ' . $available . ' ชิ้น)'); }
            $total += (float)$products[$pid]['price'] * $qty;
        }
        if ($total > 99999999.99) { throw new RuntimeException('ยอดรวมเกินขนาดที่ระบบรองรับ'); }
        if ($id === null) {
            $q = $conn->prepare('INSERT INTO orders (customer_name,total_price) VALUES (?,?)');
            $q->execute([$customer, round($total, 2)]);
            $id = (int)$conn->lastInsertId();
        } else {
            $q = $conn->prepare('UPDATE orders SET customer_name=?, total_price=? WHERE order_id=?');
            $q->execute([$customer, round($total, 2), $id]);
            $q = $conn->prepare('DELETE FROM order_items WHERE order_id=?');
            $q->execute([$id]);
        }
        $q = $conn->prepare('INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)');
        foreach ($requested as $pid => $qty) { $q->execute([$id,$pid,$qty,$products[$pid]['price']]); }
        $q = $conn->prepare('UPDATE products SET stock = stock + ? WHERE product_id = ?');
        foreach ($allIds as $pid) {
            $change = ($old[$pid] ?? 0) - ($requested[$pid] ?? 0);
            if ($change !== 0) { $q->execute([$change,$pid]); }
        }
        $conn->commit();
        return $id;
    } catch (Throwable $e) { if ($conn->inTransaction()) $conn->rollBack(); throw $e; }
}
