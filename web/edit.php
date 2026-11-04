<?php
require_once 'db.php';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$id||$id<1){http_response_code(400);exit('Invalid product ID');}
$stmt=$conn->prepare('SELECT * FROM products WHERE product_id = ?'); $stmt->execute([$id]);
$product=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$product){http_response_code(404);exit('ไม่พบสินค้า');}
$categories=$conn->query('SELECT category_id, category_name FROM categories ORDER BY category_id')->fetchAll(PDO::FETCH_ASSOC);
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $product['product_name']=trim((string)($_POST['product_name']??''));
    $product['category_id']=(int)($_POST['category_id']??0);
    $product['price']=trim((string)($_POST['price']??''));
    $product['stock']=trim((string)($_POST['stock']??''));
    $validCat=in_array((int)$product['category_id'],array_map(fn($c)=>(int)$c['category_id'],$categories),true);
    if($product['product_name']===''||mb_strlen($product['product_name'])>150||!$validCat||!is_numeric($product['price'])||(float)$product['price']<0||!ctype_digit($product['stock'])){
        $error='กรุณากรอกข้อมูลให้ถูกต้อง (ราคาไม่ติดลบ และจำนวนเป็นจำนวนเต็มตั้งแต่ 0 ขึ้นไป)';
    }else{
        $stmt=$conn->prepare('UPDATE products SET category_id=?, product_name=?, price=?, stock=? WHERE product_id=?');
        $stmt->execute([(int)$product['category_id'],$product['product_name'],$product['price'],(int)$product['stock'],$id]);
        header('Location: index.php');exit;
    }
}
?>
<!doctype html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MooShop | แก้ไขสินค้า</title><link rel="stylesheet" href="style.css?v=4"></head><body>
<div class="shell">
  <header class="site-header"><div class="header-inner"><a class="top-brand" href="index.php"><span class="top-brand-mark">◈</span><span><strong>MooShop</strong><small>SHOP MANAGEMENT</small></span></a><nav class="site-nav" aria-label="เมนูหลัก"><a href="index.php">ภาพรวมสินค้า</a><a href="orders.php">คำสั่งซื้อ</a><a href="add.php">＋ เพิ่มสินค้า</a></nav></div></header>
  <main class="main"><a class="backlink" href="index.php">← กลับไปภาพรวมร้านค้า</a><div class="page-header"><h1>แก้ไขข้อมูลสินค้า ✎</h1><p class="subtitle">อัปเดตรายละเอียดสินค้า #<?= (int)$id ?></p></div><section class="panel form-panel"><h2 class="form-title">รายละเอียดสินค้า</h2><p class="form-desc">แก้ไขข้อมูลด้านล่างแล้วกดบันทึกการเปลี่ยนแปลง</p>
<?php if($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
<form method="post"><div class="form-grid"><div class="field-group full"><label for="name">ชื่อสินค้า *</label><input class="field" type="text" name="product_name" id="name" maxlength="150" required value="<?= h($product['product_name']) ?>"></div><div class="field-group full"><label for="category">หมวดหมู่สินค้า *</label><select class="field" name="category_id" id="category" required><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['category_id'] ?>" <?= (int)$cat['category_id']===(int)$product['category_id']?'selected':'' ?>><?= h($cat['category_name']) ?></option><?php endforeach; ?></select></div><div class="field-group"><label for="price">ราคาสินค้า (บาท) *</label><input class="field" type="number" name="price" id="price" min="0" max="99999999" step="0.01" required value="<?= h($product['price']) ?>"></div><div class="field-group"><label for="stock">จำนวนในสต็อก *</label><input class="field" type="number" name="stock" id="stock" min="0" step="1" required value="<?= h($product['stock']) ?>"></div></div><div class="form-actions"><a class="btn btn-outline" href="index.php">ยกเลิก</a><button class="btn btn-primary" type="submit">✓ บันทึกการแก้ไข</button></div></form></section></main></div></body></html>
