<?php
require_once 'order_helpers.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$orders = $conn->query(<<<'SQL'
    SELECT o.order_id, o.customer_name, o.order_date, o.total_price,
           COUNT(oi.item_id) AS line_count,
           COALESCE(SUM(oi.quantity), 0) AS item_count,
           COALESCE(SUM(oi.quantity * oi.price), 0) AS calculated_total
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.order_id
    GROUP BY o.order_id, o.customer_name, o.order_date, o.total_price
    ORDER BY o.order_id DESC
SQL)->fetchAll(PDO::FETCH_ASSOC);

$orderCount = count($orders);
$totalSales = array_sum(array_map(fn($order) => (float)$order['calculated_total'], $orders));
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MooShop | คำสั่งซื้อ</title>
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
    <div class="page-header">
      <h1>รายการคำสั่งซื้อ 🧾</h1>
      <p class="subtitle">เพิ่ม ดู แก้ไข และลบคำสั่งซื้อได้</p>
    </div>
    <section class="stats" style="grid-template-columns:repeat(2,minmax(0,1fr))">
      <div class="stat"><div><p>คำสั่งซื้อทั้งหมด</p><h2><?= number_format($orderCount) ?></h2><span class="hint">รายการ</span></div><span class="stat-icon ic-blue">🛒</span></div>
      <div class="stat"><div><p>มูลค่ารายการสินค้าในคำสั่งซื้อ</p><h2>฿<?= number_format($totalSales, 2) ?></h2><span class="hint">คำนวณจากจำนวน × ราคาของแต่ละรายการ</span></div><span class="stat-icon ic-green">💰</span></div>
    </section>
    <section class="panel">
      <div class="panel-header"><div><h2>ประวัติคำสั่งซื้อ</h2><p>จัดการคำสั่งซื้อจากฐานข้อมูล</p></div><a class="btn btn-primary" href="order_form.php">＋ เพิ่มคำสั่งซื้อ</a></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>รหัสคำสั่งซื้อ</th><th>ชื่อลูกค้า</th><th>วันที่</th><th>จำนวนชิ้น</th><th>ยอดรายการสินค้า</th><th>จัดการ</th></tr></thead>
          <tbody>
          <?php if (!$orders): ?>
            <tr><td colspan="6" class="empty">ยังไม่มีคำสั่งซื้อ</td></tr>
          <?php else: foreach ($orders as $order): ?>
            <tr>
              <td><strong>#<?= (int)$order['order_id'] ?></strong></td>
              <td><?= e($order['customer_name']) ?></td>
              <td class="muted"><?= e($order['order_date']) ?></td>
              <td><span class="tag"><?= (int)$order['item_count'] ?> ชิ้น</span></td>
              <td><strong>฿<?= number_format((float)$order['calculated_total'], 2) ?></strong></td>
              <td><div class="actions" style="flex-wrap:wrap"><a class="btn btn-primary btn-sm" href="order_detail.php?id=<?= (int)$order['order_id'] ?>">ดูรายละเอียด</a><a class="btn btn-light btn-sm" href="order_form.php?id=<?= (int)$order['order_id'] ?>">แก้ไข</a><form action="order_delete.php" method="post" onsubmit="return confirm('ต้องการลบคำสั่งซื้อ #<?= (int)$order['order_id'] ?> ใช่หรือไม่?')"><input type="hidden" name="csrf" value="<?= oe(order_csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$order['order_id'] ?>"><button class="btn btn-danger btn-sm" type="submit">ลบ</button></form></div></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    <p class="footer-note">MooShop · Shop Management System</p>
  </main>
</div>
</body>
</html>
