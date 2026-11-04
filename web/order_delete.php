<?php
require_once 'order_helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
try {
    order_check_csrf();
    $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
    if (!$id || $id < 1) { throw new RuntimeException('รหัสคำสั่งซื้อไม่ถูกต้อง'); }
    $conn->beginTransaction();
    $q=$conn->prepare('SELECT order_id FROM orders WHERE order_id=? FOR UPDATE');
    $q->execute([$id]);
    if (!$q->fetchColumn()) { throw new RuntimeException('ไม่พบคำสั่งซื้อ'); }
    $q=$conn->prepare('SELECT product_id,quantity FROM order_items WHERE order_id=?');
    $q->execute([$id]);
    $old=[];
    foreach($q->fetchAll(PDO::FETCH_ASSOC) as $item){$pid=(int)$item['product_id'];$old[$pid]=($old[$pid]??0)+(int)$item['quantity'];}
    $q=$conn->prepare('DELETE FROM order_items WHERE order_id=?');$q->execute([$id]);
    $q=$conn->prepare('DELETE FROM orders WHERE order_id=?');$q->execute([$id]);
    $q=$conn->prepare('UPDATE products SET stock=stock+? WHERE product_id=?');
    foreach($old as $pid=>$qty){$q->execute([$qty,$pid]);}
    $conn->commit();header('Location: orders.php');exit;
} catch(Throwable $e){if($conn->inTransaction())$conn->rollBack();http_response_code(400);echo '<!doctype html><html lang="th"><head><meta charset="UTF-8"><title>ลบคำสั่งซื้อไม่ได้</title></head><body><p>ลบคำสั่งซื้อไม่สำเร็จ</p><a href="orders.php">กลับรายการคำสั่งซื้อ</a></body></html>';}
