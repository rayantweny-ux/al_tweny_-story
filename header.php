<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AL TAWENY | متجر الطويني</title>

    <link rel="stylesheet" href="style.css?v=10">
</head>

<body>

<header class="site-header">
    <div class="header-content">

        <a href="index.php" class="logo">
            AL TAWENY
            <span>CHILDREN'S BOUTIQUE</span>
        </a>

        <nav class="main-nav">
            <a href="index.php">الرئيسية</a>
            <a href="cart.php">سلة</a>
            <a href="shop.php">المتجر</a>
            <a href="shop.php">الأولاد</a>
            <a href="shop.php">البنات</a>
            <a href="shop.php">المواليد</a>
        </nav>

        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="header-action">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="header-action">حسابي</a>
            <?php endif; ?>
        </div>

    </div>
</header>

<main class="container">