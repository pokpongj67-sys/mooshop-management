<?php
require_once 'db.php';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$totalProducts = (int)$conn->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int)$conn->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalStock = (int)$conn->query('SELECT COALESCE(SUM(stock), 0) FROM products')->fetchColumn();
$totalValue = (float)$conn->query('SELECT COALESCE(SUM(price * stock), 0) FROM products')->fetchColumn();
$products = $conn->query('SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MooShop | ภาพรวมร้านค้า</title><link rel="stylesheet" href="style.css?v=4">
</head>
<body>
<div class="shell">
  <header class="site-header"><div class="header-inner"><a class="top-brand" href="index.php"><span class="top-brand-mark">◈</span><span><strong>MooShop</strong><small>SHOP MANAGEMENT</small></span></a><nav class="site-nav" aria-label="เมนูหลัก"><a href="index.php" class="current">ภาพรวมสินค้า</a><a href="add.php">＋ เพิ่มสินค้า</a></nav></div></header>
  <main class="main">
    
    <div class="welcome"><div><h1>ภาพรวมร้านค้า <span aria-hidden="true">✨</span></h1><p class="subtitle">ยินดีต้อนรับกลับมา! นี่คือสรุปข้อมูลสินค้าในร้านของคุณ</p></div><a href="add.php" class="btn btn-primary">＋ เพิ่มสินค้าใหม่</a></div>
    <section class="stats">
      <article class="stat"><div><p>สินค้าทั้งหมด</p><h2><?= number_format($totalProducts) ?></h2><span class="hint">รายการในระบบ</span></div><div class="stat-icon ic-blue">📦</div></article>
      <article class="stat"><div><p>หมวดหมู่สินค้า</p><h2><?= number_format($totalCategories) ?></h2><span class="hint">หมวดหมู่ทั้งหมด</span></div><div class="stat-icon ic-purple">▦</div></article>
      <article class="stat"><div><p>จำนวนสินค้าในคลัง</p><h2><?= number_format($totalStock) ?></h2><span class="hint">ชิ้นรวมทุกสินค้า</span></div><div class="stat-icon ic-green">▤</div></article>
      <article class="stat"><div><p>มูลค่าสินค้าคงคลัง</p><h2>฿<?= number_format($totalValue, 2) ?></h2><span class="hint">มูลค่ารวมโดยประมาณ</span></div><div class="stat-icon ic-amber">฿</div></article>
    </section>
    <section class="intro-banner"><div><strong>จัดการสินค้าของคุณได้ในที่เดียว</strong><p>ค้นหา แก้ไข และติดตามจำนวนสินค้าคงเหลือได้อย่างรวดเร็ว</p></div><span style="font-size:30px">🛍️</span></section>
    <section class="panel"><div class="panel-header"><div><h2>รายการสินค้าทั้งหมด</h2><p>สินค้า <?= number_format($totalProducts) ?> รายการในระบบ</p></div><div class="tools"><label class="search"><span>⌕</span><input type="search" id="productSearch" placeholder="ค้นหาชื่อสินค้า / หมวดหมู่" aria-label="ค้นหาสินค้า"></label><a class="btn btn-outline btn-sm" href="add.php">＋ เพิ่มสินค้า</a></div></div>
      <div class="table-wrap"><table id="productsTable"><thead><tr><th>สินค้า</th><th>หมวดหมู่</th><th>ราคา</th><th>สต็อก</th><th>จัดการ</th></tr></thead><tbody>
      <?php foreach ($products as $product): ?>
        <?php $stock=(int)$product['stock']; ?>
        <tr><td><div class="product"><span class="product-icon">📦</span><div><?= h($product['product_name']) ?><div class="muted" style="font-weight:400;font-size:10px">ID #<?= (int)$product['product_id'] ?></div></div></div></td><td><span class="tag"><?= h($product['category_name']) ?></span></td><td><strong>฿<?= number_format((float)$product['price'],2) ?></strong></td><td><span class="stock <?= $stock<=0?'zero':($stock<=5?'low':'') ?>"><?= $stock<=0?'หมดสต็อก':number_format($stock).' ชิ้น' ?></span></td><td><div class="actions"><a class="btn btn-sm btn-light" href="edit.php?id=<?= (int)$product['product_id'] ?>">✎ แก้ไข</a><form method="post" action="delete.php" onsubmit="return confirm('ยืนยันการลบสินค้า <?= h($product['product_name']) ?> ?');"><input type="hidden" name="id" value="<?= (int)$product['product_id'] ?>"><button class="btn btn-sm btn-danger" type="submit">ลบ</button></form></div></td></tr>
      <?php endforeach; ?>
      </tbody></table></div><div class="empty" id="noResults" hidden>ไม่พบสินค้าที่ค้นหา</div><?php if (!$products): ?><div class="empty">ยังไม่มีสินค้าในระบบ เริ่มต้นโดยกด “เพิ่มสินค้าใหม่”</div><?php endif; ?>
    </section><div class="footer-note">© <?= date('Y') ?> MooShop Management · จัดการร้านค้าอย่างเป็นระบบ</div>
  </main>
</div>
<script>
const search=document.getElementById('productSearch'),rows=Array.from(document.querySelectorAll('#productsTable tbody tr')),empty=document.getElementById('noResults');
search.addEventListener('input',()=>{let n=0;const q=search.value.trim().toLocaleLowerCase();rows.forEach(row=>{const match=row.textContent.toLocaleLowerCase().includes(q);row.hidden=!match;if(match)n++});empty.hidden=n!==0||rows.length===0;});
</script>
</body></html>
