<?php
require_once 'order_helpers.php';
$editing = isset($_GET['id']);
$id = $editing ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
if ($editing && (!$id || $id < 1)) { http_response_code(400); exit('รหัสคำสั่งซื้อไม่ถูกต้อง'); }
$order = null;
$lines = [];
if ($editing) {
    $q = $conn->prepare('SELECT * FROM orders WHERE order_id=?');
    $q->execute([$id]);
    $order = $q->fetch(PDO::FETCH_ASSOC);
    if (!$order) { http_response_code(404); exit('ไม่พบคำสั่งซื้อ'); }
    $q = $conn->prepare('SELECT product_id,quantity FROM order_items WHERE order_id=? ORDER BY item_id');
    $q->execute([$id]);
    $lines = $q->fetchAll(PDO::FETCH_ASSOC);
}
$products = order_products($conn);
$error = '';
$customer = $order['customer_name'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = trim((string)($_POST['customer_name'] ?? ''));
    $lines = [];
    foreach ((array)($_POST['product_id'] ?? []) as $k => $pid) { $lines[] = ['product_id'=>$pid, 'quantity'=>$_POST['quantity'][$k] ?? '']; }
    try {
        order_check_csrf();
        $selected = order_validate_lines($_POST['product_id'] ?? null, $_POST['quantity'] ?? null);
        $savedId = order_save($conn, $id, $customer, $selected);
        header('Location: order_detail.php?id=' . $savedId); exit;
    } catch (Throwable $e) { $error = $e instanceof PDOException ? 'บันทึกข้อมูลไม่สำเร็จ กรุณาลองอีกครั้ง' : $e->getMessage(); }
}
if (!$lines) { $lines = [['product_id'=>'', 'quantity'=>1]]; }
$title = $editing ? 'แก้ไขคำสั่งซื้อ #'.$id : 'เพิ่มคำสั่งซื้อ';
?>
<!doctype html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MooShop | <?= oe($title) ?></title><link rel="stylesheet" href="style.css?v=6"></head><body>
<div class="shell"><header class="site-header"><div class="header-inner"><a class="top-brand" href="index.php"><span class="top-brand-mark">◈</span><span><strong>MooShop</strong><small>SHOP MANAGEMENT</small></span></a><nav class="site-nav"><a href="index.php">ภาพรวมสินค้า</a><a href="orders.php" class="current">คำสั่งซื้อ</a><a href="add.php">＋ เพิ่มสินค้า</a></nav></div></header>
<main class="main"><a class="backlink" href="orders.php">← กลับไปรายการคำสั่งซื้อ</a><div class="page-header"><h1><?= oe($title) ?></h1><p class="subtitle">เลือกรายการสินค้าและจำนวน ระบบจะคำนวณยอดรวมและปรับสต็อกเมื่อบันทึก</p></div>
<section class="panel form-panel" style="max-width:920px"><h2 class="form-title">ข้อมูลคำสั่งซื้อ</h2>
<?php if($error): ?><div class="alert" role="alert"><?= oe($error) ?></div><?php endif; ?>
<form method="post" id="orderForm"><input type="hidden" name="csrf" value="<?= oe(order_csrf()) ?>">
<div class="field-group" style="margin-bottom:25px"><label for="customer_name">ชื่อลูกค้า *</label><input id="customer_name" class="field" name="customer_name" maxlength="150" required value="<?= oe($customer) ?>"></div>
<h2 class="form-title">สินค้าในคำสั่งซื้อ</h2><div id="lines">
<?php foreach($lines as $line): ?><div class="order-line" style="display:grid;grid-template-columns:minmax(0,2fr) minmax(90px,1fr) auto;gap:12px;margin:12px 0;align-items:end"><div class="field-group"><label>สินค้า</label><select name="product_id[]" class="field product-choice" required><option value="">-- เลือกสินค้า --</option><?php foreach($products as $p): ?><option value="<?= (int)$p['product_id'] ?>" data-price="<?= oe($p['price']) ?>" <?= (string)$p['product_id']===(string)$line['product_id']?'selected':'' ?>><?= oe($p['product_name']) ?> (฿<?= number_format((float)$p['price'],2) ?> / คงเหลือ <?= (int)$p['stock'] ?>)</option><?php endforeach; ?></select></div><div class="field-group"><label>จำนวน</label><input class="field item-qty" name="quantity[]" type="number" min="1" step="1" max="100000" required value="<?= oe($line['quantity']) ?>"></div><button type="button" class="btn btn-danger remove-line">ลบ</button></div><?php endforeach; ?>
</div><button type="button" id="addLine" class="btn btn-outline">＋ เพิ่มรายการสินค้า</button>
<div class="intro-banner" style="margin-top:24px"><div><strong>ยอดรวมโดยประมาณ</strong><p>คำนวณจากราคาสินค้าปัจจุบัน</p></div><strong id="previewTotal" style="font-size:22px">฿0.00</strong></div>
<div class="form-actions"><a href="orders.php" class="btn btn-outline">ยกเลิก</a><button type="submit" class="btn btn-primary">บันทึกคำสั่งซื้อ</button></div>
</form></section></main></div>
<script>
const lines=document.getElementById('lines');
const template=lines.querySelector('.order-line').cloneNode(true);
function updateTotal(){let total=0;lines.querySelectorAll('.order-line').forEach(row=>{const sel=row.querySelector('select');const price=Number(sel.selectedOptions[0]?.dataset.price||0);const qty=Number(row.querySelector('.item-qty').value||0);total+=price*Math.max(0,qty)});document.getElementById('previewTotal').textContent='฿'+total.toLocaleString('th-TH',{minimumFractionDigits:2,maximumFractionDigits:2})}
document.getElementById('addLine').addEventListener('click',()=>{const row=template.cloneNode(true);row.querySelector('select').value='';row.querySelector('input').value='1';lines.appendChild(row);updateTotal()});
lines.addEventListener('click',e=>{if(e.target.closest('.remove-line')){if(lines.querySelectorAll('.order-line').length===1){alert('ต้องมีสินค้าอย่างน้อยหนึ่งรายการ');return;}e.target.closest('.order-line').remove();updateTotal()}});
lines.addEventListener('input',updateTotal);lines.addEventListener('change',updateTotal);updateTotal();
</script></body></html>
