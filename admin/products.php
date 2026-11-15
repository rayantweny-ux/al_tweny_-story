<?php
require_once "../database.php";

/** @var mysqli $conn */

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $id = (int) $_POST["delete_id"];

    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "تم حذف المنتج بنجاح";
    } else {
        $message = "حدث خطأ أثناء حذف المنتج";
    }

    mysqli_stmt_close($stmt);
}

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات | AL TAWENY</title>

    <link rel="stylesheet" href="../style.css">

    <style>
        body {
            background: #F0EBE3;
            padding: 30px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            background: #F8F4ED;
            padding: 30px;
            border: 1px solid #E6DED3;
        }

        .admin-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .admin-heading h1 {
            color: #692832;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #E6DED3;
            text-align: center;
        }

        .admin-table th {
            background: #873541;
            color: white;
        }

        .admin-table img {
            width: 65px;
            height: 65px;
            object-fit: cover;
        }

        .action-btn {
            display: inline-block;
            padding: 8px 14px;
            margin: 3px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            color: white;
            background: #873541;
        }

        .delete-btn {
            background: #292323;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            color: #692832;
            background: #E9E0D5;
        }

        @media(max-width: 700px) {
            .admin-container {
                overflow-x: auto;
                padding: 15px;
            }

            .admin-table {
                min-width: 650px;
            }
        }
    </style>
</head>

<body>

<div class="admin-container">

    <div class="admin-heading">
        <h1>إدارة المنتجات</h1>

        <a href="add_product.php" class="action-btn">
            إضافة منتج جديد
        </a>
    </div>

    <?php if ($message != ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>الصورة</th>
                <th>اسم المنتج</th>
                <th>السعر</th>
                <th>الكمية</th>
                <th>العمليات</th>
            </tr>
        </thead>

        <tbody>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>

                <tr>
                    <td>
                        <?php if (!empty($product["image"])): ?>
                            <img src="../images/<?php echo htmlspecialchars($product["image"]); ?>">
                        <?php else: ?>
                            لا توجد صورة
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($product["name"]); ?>
                    </td>

                    <td>
                        <?php echo number_format((float)$product["price"], 2); ?>
                        د.ل
                    </td>

                    <td>
                        <?php echo (int)$product["stock"]; ?>
                    </td>

                    <td>
                        <a class="action-btn"
                           href="edit_product.php?id=<?php echo (int)$product["id"]; ?>">
                            تعديل
                        </a>

                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟');">

                            <input type="hidden" name="delete_id"
                                   value="<?php echo (int)$product["id"]; ?>">

                            <button type="submit" class="action-btn delete-btn">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="5">لا توجد منتجات مسجلة حالياً</td>
            </tr>

        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>