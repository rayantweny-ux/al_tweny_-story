<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المدير - AL TAWENY</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #EDE7DE;
            color: #34272A;
        }

        header {
            background: #642D3D;
            color: white;
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            padding: 40px;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: #F5F1EB;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px #00000010;
        }

        .card h3 {
            color: #642D3D;
        }

        .card a {
            display: inline-block;
            margin-top: 15px;
            background: #642D3D;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
        }

        .logout {
            color: white;
            text-decoration: none;
            border: 1px solid white;
            padding: 10px 18px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<header>
    <h2>AL TAWENY</h2>
    <a class="logout" href="../logout.php">تسجيل الخروج</a>
</header>

<div class="container">

    <div class="welcome">
        <h2>مرحباً بك في لوحة تحكم المدير</h2>
        <p>أهلاً <?php echo htmlspecialchars($_SESSION["username"]); ?>، يمكنك إدارة متجرك من هنا.</p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>إدارة المنتجات</h3>
            <p>إضافة وتعديل وحذف المنتجات.</p>
            <a href="products.php">إدارة المنتجات</a>
        </div>

        <div class="card">
            <h3>إضافة منتج</h3>
            <p>إضافة منتجات جديدة إلى المتجر.</p>
            <a href="add_product.php">إضافة منتج</a>
        </div>

    </div>
    <div class="card">
    <a href="orders.php" class="nav-link">
    إدارة الطلبات
</a>
    </div>
</div>

</body>
</html>