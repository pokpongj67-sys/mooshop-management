<?php
require_once 'db.php';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_id')->fetchAll(PDO::FETCH_ASSOC);
$error = '';
$data = ['product_name'=>'','category_id'=>'','price'=>'','stock'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $value) { $data[$key] = trim((string)($_POST[$key] ?? '')); }
    $categoryValid = in_array((int)$data['category_id'], array_map(fn($c)=>(int)$c['category_id'], $categories), true);
    if ($data['product_name']==='' || mb_strlen($data['product_name'])>150 || !$categoryValid || !is_numeric($data['price']) || (float)$data['price']<0 || !ctype_digit($data['stock'])) {
        $error='กรุณากรอกข้อมูลให้ถูกต้อง (ราคาไม่ติดลบ และจำนวนเป็นจำนวนเต็มตั้งแต่ 0 ขึ้นไป)';
    } else {
        $stmt=$conn->prepare('INSERT INTO products (category_id, product_name, price, stock) VALUES (?, ?, ?, ?)');
        $stmt->execute([(int)$data['category_id'], $data['product_name'], $data['price'], (int)$data['stock']]);
        header('Location: index.php'); exit;
    }
}
?>
<!doctype html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MooShop | เพิ่มสินค้า</title><link rel="stylesheet" href="style.css?v=4"></head><body>
<div class="shell">
  <header class="site-header"><div class="header-inner"><a class="top-brand" href="index.php"><span class="top-brand-mark">◈</span><span><strong>MooShop</strong><small>SHOP MANAGEMENT</small></span></a><nav class="site-nav" aria-label="เมนูหลัก"><a href="index.php">ภาพรวมสินค้า</a><a href="orders.php">คำสั่งซื้อ</a><a href="add.php" class="current">＋ เพิ่มสินค้า</a></nav></div></header>
  <main class="main"><a class="backlink" href="index.php">← กลับไปภาพรวมร้านค้า</a><div class="page-header"><h1>เพิ่มสินค้าใหม่ ✨</h1><p class="subtitle">กรอกรายละเอียดสินค้าเพื่อบันทึกเข้าระบบ</p></div><section class="panel form-panel"><h2 class="form-title">ข้อมูลสินค้า</h2><p class="form-desc">ระบุชื่อสินค้า หมวดหมู่ ราคา และจำนวนคงคลัง</p>
<?php if($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
<form method="post"><div class="form-grid"><div class="field-group full"><label for="name">ชื่อสินค้า *</label><input class="field" type="text" name="product_name" id="name" maxlength="150" required placeholder="เช่น Wireless Mouse" value="<?= h($data['product_name']) ?>"></div><div class="field-group full"><label for="category">หมวดหมู่สินค้า *</label><select class="field" name="category_id" id="category" required><option value="">-- เลือกหมวดหมู่ --</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['category_id'] ?>" <?= (string)$cat['category_id']===$data['category_id']?'selected':'' ?>><?= h($cat['category_name']) ?></option><?php endforeach; ?></select></div><div class="field-group"><label for="price">ราคาสินค้า (บาท) *</label><input class="field" type="number" name="price" id="price" min="0" max="99999999" step="0.01" required placeholder="0.00" value="<?= h($data['price']) ?>"></div><div class="field-group"><label for="stock">จำนวนในสต็อก *</label><input class="field" type="number" name="stock" id="stock" min="0" step="1" required placeholder="0" value="<?= h($data['stock']) ?>"></div></div><div class="form-actions"><a class="btn btn-outline" href="index.php">ยกเลิก</a><button class="btn btn-primary" type="submit">＋ บันทึกสินค้า</button></div></form></section></main></div></body></html>
