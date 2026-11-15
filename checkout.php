<?php
session_start();
require_once "database.php";

$message = "";

if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    die("سلة التسوق فارغة. <a href='products.php'>العودة للمنتجات</a>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $customer_name = trim($_POST["customer_name"]);
    $phone = trim($_POST["phone"]);
    $city = trim($_POST["city"]);
    $address = trim($_POST["address"]);
    $notes = trim($_POST["notes"]);

    if (
        $customer_name == "" ||
        $phone == "" ||
        $city == "" ||
        $address == ""
    ) {
        $message = "يرجى تعبئة جميع الحقول المطلوبة.";
    } else {

        try {

            mysqli_begin_transaction($conn);

            $cart = $_SESSION["cart"];
            $total = 0;
            $items = [];

            // التحقق من المنتجات والأسعار والكميات
            foreach ($cart as $product_id => $quantity) {

                $product_id = (int)$product_id;
                $quantity = (int)$quantity;

                if ($product_id <= 0 || $quantity <= 0) {
                    throw new Exception("بيانات المنتج غير صحيحة.");
                }

                $stmt = mysqli_prepare(
                    $conn,
                    "SELECT id, name, price, stock
                     FROM products
                     WHERE id = ?
                     FOR UPDATE"
                );

                mysqli_stmt_bind_param($stmt, "i", $product_id);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);

                mysqli_stmt_close($stmt);

                if (!$product) {
                    throw new Exception("أحد المنتجات لم يعد متاحاً.");
                }

                if ($quantity > (int)$product["stock"]) {
                    throw new Exception(
                        "الكمية المطلوبة من " .
                        $product["name"] .
                        " غير متوفرة."
                    );
                }

                $price = (float)$product["price"];
                $subtotal = $price * $quantity;

                $total += $subtotal;

                $items[] = [
                    "id" => $product_id,
                    "name" => $product["name"],
                    "price" => $price,
                    "quantity" => $quantity,
                    "subtotal" => $subtotal
                ];
            }

            // إنشاء رقم الطلب
            $order_code = "AT" . date("Ymd") .
                          strtoupper(bin2hex(random_bytes(4)));

            // حفظ بيانات الطلب
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO orders
                (order_code, customer_name, phone,
                 city, address, notes, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssd",
                $order_code,
                $customer_name,
                $phone,
                $city,
                $address,
                $notes,
                $total
            );

            mysqli_stmt_execute($stmt);

            $order_id = mysqli_insert_id($conn);

            mysqli_stmt_close($stmt);

            
foreach ($items as $item) {

    // حفظ المنتج داخل الطلب
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO order_items
        (order_id, product_id, product_name,
         price, quantity, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iisdid",
        $order_id,
        $item["id"],
        $item["name"],
        $item["price"],
        $item["quantity"],
        $item["subtotal"]
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // خصم الكمية من المخزون
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $item["quantity"],
        $item["id"],
        $item["quantity"]
    );

    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) != 1) {
        mysqli_stmt_close($stmt);
        throw new Exception("تعذر تحديث مخزون المنتج.");
    }

    mysqli_stmt_close($stmt);
}

            // تأكيد حفظ الطلب
            mysqli_commit($conn);

            // تفريغ السلة
            $_SESSION["cart"] = [];

            $_SESSION["last_order_code"] = $order_code;

            header("Location: order_success.php");
            exit;

        } catch (Throwable $e) {

            mysqli_rollback($conn);

            $message = "لم يتم تسجيل الطلب: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>إتمام الطلب | AL TAWENY</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #F8F5EF;
    color: #292323;
    margin: 0;
    padding: 40px 20px;
}

.container {
    max-width: 550px;
    margin: auto;
    background: #fff;
    padding: 35px;
    border: 1px solid #EAE3D9;
    border-radius: 4px;
}

h2 {
    color: #692832;
    text-align: center;
}

p {
    color: #8B817A;
    line-height: 1.8;
}

label {
    display: block;
    margin-top: 18px;
    color: #292323;
}

input, textarea {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border: 1px solid #EAE3D9;
    border-radius: 3px;
    box-sizing: border-box;
    background: #F8F5EF;
    font-family: Arial;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #C4A36B;
}

button {
    width: 100%;
    padding: 14px;
    margin-top: 25px;
    background: #873541;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}

button:hover {
    background: #692832;
}

.error {
    color: #b00020;
    text-align: center;
}

</style>

</head>

<body>

<div class="container">

<h2>إتمام الطلب</h2>

<p>يرجى إدخال بياناتك لإتمام عملية الشراء.</p>

<?php if ($message != ""): ?>

<p class="error">
<?= htmlspecialchars($message) ?>
</p>

<?php endif; ?>

<form method="POST">

<label>الاسم الكامل</label>
<input type="text" name="customer_name" required>

<label>رقم الهاتف</label>
<input type="tel" name="phone" required>

<label>المدينة</label>
<input type="text" name="city" required>

<label>العنوان بالتفصيل</label>
<textarea name="address" required></textarea>

<label>ملاحظات إضافية</label>
<textarea name="notes"></textarea>

<button type="submit">
تأكيد الطلب
</button>

</form>

</div>

</body>
</html>