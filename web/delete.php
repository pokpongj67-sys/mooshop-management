<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if(!$id||$id<1){http_response_code(400);exit('Invalid product ID');}
try{
    $stmt=$conn->prepare('DELETE FROM products WHERE product_id=?');
    $stmt->execute([$id]);
    header('Location: index.php');exit;
}catch(PDOException $e){
    http_response_code(409);
    echo '<!doctype html><html lang="th"><head><meta charset="UTF-8"><title>ไม่สามารถลบสินค้าได้</title><link rel="stylesheet" href="style.css"></head><body><main style="max-width:540px;margin:15vh auto;padding:24px" class="panel"><h2>ไม่สามารถลบสินค้าได้</h2><p>สินค้านี้อาจมีรายการสั่งซื้ออ้างอิงอยู่ จึงไม่สามารถลบออกจากระบบได้</p><a class="btn btn-primary" href="index.php">กลับหน้าหลัก</a></main></body></html>';
}
