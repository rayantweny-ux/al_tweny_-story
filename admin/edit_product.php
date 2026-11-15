<?php
require_once "../database.php";

/** @var mysqli $conn */

$message = "";
$product = null;

// الحصول على رقم المنتج
$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    die("رقم المنتج غير صحيح");
}

// جلب بيانات المنتج
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    die("المنتج غير موجود");
}

// تنفيذ التعديل
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $price = (float)($_POST["price"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $stock = (int)($_POST["stock"] ?? 0);
    $category = $_POST["category"] ?? "";

    if ($name === "" || $price <= 0 || $stock < 0 || !in_array($category, ["boys", "girls", "babies", "all"], true)) {
        $message = "يرجى إدخال بيانات صحيحة";
    } else {

        $imageName = $product["image"];

        // إذا اختار المستخدم صورة جديدة
        if (isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
                $message = "حدث خطأ أثناء رفع الصورة";

            } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
                $message = "حجم الصورة يجب ألا يتجاوز 5 ميجابايت";

            } else {
                $imageInfo = getimagesize($_FILES["image"]["tmp_name"]);

                $allowedTypes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                if (!$imageInfo ||
                    !isset($allowedTypes[$imageInfo["mime"]])) {
                    $message = "نوع الصورة غير مسموح به";
                } else {
                    $extension = $allowedTypes[$imageInfo["mime"]];
                    $newImage = uniqid("product_", true) . "." . $extension;

                    if (move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        "../images/" . $newImage
                    )) {
                        $imageName = $newImage;
                    } else {
                        $message = "تعذر رفع الصورة الجديدة";
                    }
                }
            }
        }

        if ($message === "") {

            $update = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET name = ?, price = ?, description = ?, image = ?, stock = ?, category =?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sdssisi",
                $name,
                $price,
                $description,
                $imageName,
                $stock,
                $category,
                $id
            );

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);

                header("Location: products.php?updated=1");
                exit;
            } else {
                $message = "حدث خطأ أثناء تعديل المنتج";
                mysqli_stmt_close($update);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج | AL TAWENY</title>
    <style>
        body {
            background: #EDE7DE;
            font-family: Arial, sans-serif;
            padding: 30px;
        }

        .container {
            max-width: 650px;
            margin: 35px auto;
            padding: 35px;
            background: #F5F1EB;
            border: 1px solid #DED2C3;
        }

        h1 { color: #692832; }

        label {
            display: block;
            margin: 16px 0 8px;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #DED2C3;
            box-sizing: border-box;
            font-family: inherit;
        }

        textarea {
            min-height: 100px;
        }

        img {
            width: 120px;
            display: block;
            margin: 12px 0;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            background: #873541;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .message {
            background: #E9E0D5;
            padding: 12px;
            color: #692832;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #873541;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>تعديل المنتج</h1>

    <?php if ($message !== ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>اسم المنتج</label>
        <input type="text" name="name"
            value="<?php echo htmlspecialchars($product["name"]); ?>"
            required>


            <label>تصنيف المنتج</label>
<select name="category" required>
    <option value="boys" <?php echo ($product["category"] == "boys") ? "selected" : ""; ?>> الأولاد</option>

    <option value="girls" <?php echo ($product["category"] == "girls") ? "selected" : ""; ?>> البنات</option>

    <option value="babies" <?php echo ($product["category"] == "babies") ? "selected" : ""; ?>> المواليد</option>

    <option value="all" <?php echo ($product["category"] == "all") ? "selected" : ""; ?>> الأطفال</option>
</select>

        <label>السعر بالدينار الليبي</label>
        <input type="number" name="price" step="0.01" min="0.01"
            value="<?php echo htmlspecialchars($product["price"]); ?>"
            required>

        <label>وصف المنتج</label>
        <textarea name="description"><?php
            echo htmlspecialchars($product["description"]);
        ?></textarea>

        <label>الصورة الحالية</label>

        <?php if (!empty($product["image"])): ?>
            <img src="../images/<?php echo htmlspecialchars($product["image"]); ?>"
                 alt="صورة المنتج">
        <?php else: ?>
            <p>لا توجد صورة حالية</p>
        <?php endif; ?>

        <label>اختيار صورة جديدة (اختياري)</label>
        <input type="file" name="image"
            accept="image/jpeg,image/png,image/webp">

        <label>الكمية المتوفرة</label>
        <input type="number" name="stock" min="0"
            value="<?php echo (int)$product["stock"]; ?>"
            required>

        <button type="submit">حفظ التعديلات</button>

    </form>

    <a href="products.php">العودة إلى إدارة المنتجات</a>

</div>

</body>
</html>