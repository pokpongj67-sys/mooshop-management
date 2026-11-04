<?php
require_once 'db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('รหัสคำสั่งซื้อไม่ถูกต้อง');
}

$stmt = $conn->prepare('SELECT order_id, customer_name, order_date, total_price FROM orders WHERE order_id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    http_response_code(404);
    exit('ไม่พบคำสั่งซื้อนี้');
}

$stmt = $conn->prepare(<<<'SQL'
    SELECT oi.item_id, oi.product_id, oi.quantity, oi.price,
           p.product_name, c.category_name
    FROM order_items oi
    LEFT JOIN products p ON p.product_id = oi.product_id
    LEFT JOIN categories c ON c.category_id = p.category_id
    WHERE oi.order_id = ?
    ORDER BY oi.item_id
SQL);
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
foreach ($items as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MooShop | รายละเอียดคำสั่งซื้อ #<?= (int)$id ?></title>
  <link rel="stylesheet" href="style.css?v=5">
</head>
<body>
<div class="shell">
  <header class="site-header">
    <div class="header-inner">
      <a class="top-brand" href="index.php"><span class="top-brand-mark">◈</span><span><strong>MooShop</strong><small>SHOP MANAGEMENT</small></span></a>
      <nav class="site-nav" aria-label="เมนูหลัก">
        <a href="index.php">ภาพรวมสินค้า</a>
        <a href="orders.php" class="current" aria-current="page">คำสั่งซื้อ</a>
        <a href="add.php">＋ เพิ่มสินค้า</a>
      </nav>
    </div>
  </header>
  <main class="main">
    <a class="backlink" href="orders.php">← กลับไปรายการคำสั่งซื้อ</a>
    <div class="page-header"><a class="btn btn-primary" style="float:right" href="order_form.php?id=<?= (int)$id ?>">✎ แก้ไขคำสั่งซื้อ</a><h1>คำสั่งซื้อ #<?= (int)$order['order_id'] ?></h1><p class="subtitle">รายละเอียดสินค้าในคำสั่งซื้อ</p></div>
    <section class="intro-banner">
      <div><strong>ลูกค้า: <?= e($order['customer_name']) ?></strong><p>วันที่สั่งซื้อ: <?= e($order['order_date']) ?></p></div>
      <div><strong>฿<?= number_format($total, 2) ?></strong><p>ยอดรวมจากรายการสินค้า</p></div>
    </section>
    <section class="panel">
      <div class="panel-header"><div><h2>สินค้าในคำสั่งซื้อ</h2><p>ตาราง order_items เชื่อมกับ products และ categories</p></div></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>รหัสสินค้า</th><th>ชื่อสินค้า</th><th>หมวดหมู่</th><th>ราคาต่อชิ้น</th><th>จำนวน</th><th>รวม</th></tr></thead>
          <tbody>
          <?php if (!$items): ?>
            <tr><td colspan="6" class="empty">ไม่พบรายละเอียดสินค้า</td></tr>
          <?php else: foreach ($items as $item): ?>
            <tr>
              <td>#<?= (int)$item['product_id'] ?></td>
              <td><strong><?= e($item['product_name'] ?? 'สินค้าที่ถูกลบ') ?></strong></td>
              <td><span class="tag"><?= e($item['category_name'] ?? '-') ?></span></td>
              <td>฿<?= number_format((float)$item['price'], 2) ?></td>
              <td><?= (int)$item['quantity'] ?></td>
              <td><strong>฿<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></strong></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
          <tfoot><tr><td colspan="5" style="text-align:right;font-weight:600">ยอดรวมทั้งหมด</td><td><strong>฿<?= number_format($total, 2) ?></strong></td></tr></tfoot>
        </table>
      </div>
    </section>
    <p class="footer-note">หมายเหตุ: ยอดรวมคำนวณจากรายการสินค้าใน order_items เพื่อให้ตรงกับรายละเอียดจริง</p>
  </main>
</div>
</body>
</html>
