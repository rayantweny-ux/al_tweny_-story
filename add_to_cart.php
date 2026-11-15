<?php
session_start();

require_once "database.php";

/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: shop.php");
    exit;
}

$productId = (int)($_POST["product_id"] ?? 0);
$quantity = (int)($_POST["quantity"] ?? 1);

if ($productId <= 0 || $quantity <= 0) {
    die("بيانات المنتج غير صحيحة");
}

// التأكد من وجود المنتج والمخزون
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, stock FROM products WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    die("المنتج غير موجود");
}

if ($product["stock"] <= 0) {
    die("عذراً، المنتج غير متوفر حالياً");
}

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$currentQuantity = (int)($_SESSION["cart"][$productId] ?? 0);

if ($currentQuantity + $quantity > (int)$product["stock"]) {
    die("الكمية المطلوبة أكبر من المخزون المتوفر");
}

// إضافة المنتج إلى السلة
$_SESSION["cart"][$productId] = $currentQuantity + $quantity;

header("Location: cart.php");
exit;
?>