
<?php
require_once "../database.php";

/** @var mysqli $conn */

$message = "";

// تحديث حالة الطلب

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = (int)($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? "";

    $allowed = [
        "جديد",
        "قيد التجهيز",
        "تم الشحن",
        "تم التسليم",
        "ملغي"
    ];

    if ($id <= 0 || !in_array($status, $allowed, true)) {
        die("بيانات الطلب غير صحيحة.");
    }

    try {

        mysqli_begin_transaction($conn);

        // جلب الطلب وقفل سجله أثناء التحديث
        $stmt = mysqli_prepare(
            $conn,
            "SELECT status, stock_restored
             FROM orders
             WHERE id = ?
             FOR UPDATE"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$order) {
            throw new Exception("الطلب غير موجود.");
        }

        // منع إعادة تفعيل طلب ملغي
        if ($order["status"] == "ملغي" && $status != "ملغي") {
            throw new Exception(
                "لا يمكن إعادة تفعيل طلب ملغي."
            );
        }

        // إرجاع الكميات للمخزون عند الإلغاء
        if (
            $status == "ملغي" &&
            (int)$order["stock_restored"] == 0
        ) {

            $stmt = mysqli_prepare(
                $conn,
                "SELECT product_id, quantity
                 FROM order_items
                 WHERE order_id = ?"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            $items = mysqli_stmt_get_result($stmt);

            while ($item = mysqli_fetch_assoc($items)) {

                $product_id = (int)$item["product_id"];
                $quantity = (int)$item["quantity"];

                $update = mysqli_prepare(
                    $conn,
                    "UPDATE products
                     SET stock = stock + ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "ii",
                    $quantity,
                    $product_id
                );

                mysqli_stmt_execute($update);

                if (mysqli_stmt_affected_rows($update) != 1) {
                    mysqli_stmt_close($update);
                    throw new Exception(
                        "تعذر تحديث مخزون أحد المنتجات."
                    );
                }

                mysqli_stmt_close($update);
            }

            mysqli_stmt_close($stmt);
        }

        // تحديث حالة الطلب وتسجيل إرجاع المخزون
        $restored = (
            $status == "ملغي" ||
            (int)$order["stock_restored"] == 1
        ) ? 1 : 0;

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE orders
             SET status = ?, stock_restored = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sii",
            $status,
            $restored,
            $id
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        header("Location: orders.php");
        exit;

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        die(
            "حدث خطأ: " .
            htmlspecialchars($e->getMessage())
        );
    }
}


// جلب الطلبات
$result = mysqli_query(
    $conn,
    "SELECT * FROM orders ORDER BY id DESC"
);

if (!$result) {
    die("حدث خطأ في جلب الطلبات.");
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>إدارة الطلبات | AL TAWENY</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #F8F5EF;
    color: #292323;
    margin: 0;
    padding: 30px;
}

.container {
    max-width: 1200px;
    margin: auto;
}

h2 {
    color: #692832;
    margin-bottom: 25px;
}

.table-box {
    background: white;
    padding: 20px;
    border: 1px solid #EAE3D9;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
}

th, td {
    padding: 14px;
    text-align: center;
    border-bottom: 1px solid #EAE3D9;
}

th {
    background: #F8F5EF;
    color: #692832;
}

select {
    padding: 9px;
    border: 1px solid #EAE3D9;
    background: #F8F5EF;
    border-radius: 3px;
}

button {
    padding: 9px 15px;
    background: #873541;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 3px;
}

button:hover {
    background: #692832;
}

.empty {
    text-align: center;
    padding: 30px;
    color: #8B817A;
}
</style>

</head>

<body>

<div class="container">

<h2>إدارة طلبات الزبائن</h2>

<div class="table-box">

<table>

<thead>
<tr>
    <th>رقم الطلب</th>
    <th>الزبون</th>
    <th>الهاتف</th>
    <th>المدينة</th>
    <th>الإجمالي</th>
    <th>الحالة</th>
    <th>تاريخ الطلب</th>
    <th>تحديث</th>
</tr>
</thead>

<tbody>

<?php if (mysqli_num_rows($result) > 0): ?>

<?php while ($order = mysqli_fetch_assoc($result)): ?>

<tr>

<td>
<?= htmlspecialchars($order["order_code"]) ?>
</td>

<td>
<?= htmlspecialchars($order["customer_name"]) ?>
</td>

<td>
<?= htmlspecialchars($order["phone"]) ?>
</td>

<td>
<?= htmlspecialchars($order["city"]) ?>
</td>

<td>
<?= number_format((float)$order["total_price"], 2) ?> د.ل
</td>

<td>
<?= htmlspecialchars($order["status"]) ?>
</td>

<td>
<?= htmlspecialchars($order["created_at"]) ?>
</td>

<td>

<form method="POST">

<input type="hidden" name="id"
value="<?= (int)$order["id"] ?>">

<select name="status">

<?php
$statuses = [
    "جديد",
    "قيد التجهيز",
    "تم الشحن",
    "تم التسليم",
    "ملغي"
];

foreach ($statuses as $s):
?>

<option value="<?= $s ?>"
<?= $order["status"] == $s ? "selected" : "" ?>>
<?= $s ?>
</option>

<?php endforeach; ?>

</select>

<button type="submit">حفظ</button>

</form>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="8" class="empty">
لا توجد طلبات حالياً.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>
</div>

</body>
</html>
