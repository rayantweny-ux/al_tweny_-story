<?php
session_start();

if (!isset($_SESSION["last_order_code"])) {
    header("Location: index.php");
    exit;
}

$order_code = $_SESSION["last_order_code"];

unset($_SESSION["last_order_code"]);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>تم تأكيد الطلب | AL TAWENY</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #eeeae5;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

.box {
    background: white;
    padding: 40px;
    border-radius: 15px;
    text-align: center;
    width: 400px;
    max-width: 85%;
}

h2 {
    color: #463a32;
}

p {
    color: #555;
    line-height: 1.8;
}

.order {
    background: #eeeae5;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    font-weight: bold;
}

a {
    display: inline-block;
    background: #463a32;
    color: white;
    padding: 13px 25px;
    text-decoration: none;
    border-radius: 7px;
}

</style>

</head>

<body>

<div class="box">

<h2>تم استلام طلبك بنجاح!</h2>

<p>
شكراً لتسوقك من متجر AL TAWENY.
</p>

<p>
تم تسجيل طلبك بنجاح، وسنتواصل معك لتأكيد التفاصيل.
</p>

<div class="order">

رقم طلبك:

<br><br>

<?= htmlspecialchars($order_code) ?>

</div>

<a href="index.php">
العودة إلى المتجر
</a>

</div>

</body>
</html>