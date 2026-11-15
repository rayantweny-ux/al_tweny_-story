<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["role"] === "admin") {
    header("Location: admin/dashboard.php");
    exit;
}

$cart_count = 0;

if (isset($_SESSION["cart"]) && is_array($_SESSION["cart"])) {
    $cart_count = array_sum($_SESSION["cart"]);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>حسابي - AL TAWENY</title>

<style>
body {
    margin: 0;
    background: #EDE7DE;
    color: #34272A;
    font-family: Arial, sans-serif;
}

.account {
    max-width: 600px;
    margin: 70px auto;
    padding: 35px;
    background: #F5F1EB;
    border-radius: 12px;
    text-align: center;
}

h2 {
    color: #642D3D;
}

a {
    display: inline-block;
    margin: 10px;
    padding: 13px 25px;
    background: #642D3D;
    color: white;
    text-decoration: none;
    border-radius: 6px;
}
</style>
</head>

<body>

<div class="account">

<h2>مرحباً بك في حسابك</h2>

<p>
أهلاً
<?php echo htmlspecialchars($_SESSION["username"]); ?>
</p>

<p>يسعدنا وجودك في متجر AL TAWENY</p>

<a href="shop.php">تصفح المنتجات</a>

<a href="cart.php">
سلة المشتريات (<?php echo $cart_count; ?>)
</a>

<a href="logout.php">تسجيل الخروج</a>

</div>

</body>
</html>