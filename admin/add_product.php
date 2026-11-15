<?php
require_once "../database.php";

/** @var mysqli $conn */

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $price = (float)($_POST["price"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $stock = (int)($_POST["stock"] ?? 0);
    $category = $_POST["category"] ?? "";


    if ($name === "" || $price <= 0 || $stock < 0 || !in_array($category, ["boys", "girls", "babies", "all"], true)) {
        $message = "يرجى إدخال بيانات صحيحة";
    } elseif (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {
        $message = "يرجى اختيار صورة للمنتج";
    } elseif ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
        $message = "حجم الصورة يجب ألا يتجاوز 5 ميجابايت";
    } else {

        $imageInfo = getimagesize($_FILES["image"]["tmp_name"]);

        $allowedTypes = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        if (!$imageInfo || !isset($allowedTypes[$imageInfo["mime"]])) {
            $message = "نوع الصورة غير مسموح به";
        } else {

            $extension = $allowedTypes[$imageInfo["mime"]];
            $imageName = uniqid("product_", true) . "." . $extension;

            $imageFolder = "../images/";

            if (!is_dir($imageFolder)) {
                mkdir($imageFolder, 0755, true);
            }

            $imagePath = $imageFolder . $imageName;

            if (move_uploaded_file(
                $_FILES["image"]["tmp_name"],
                $imagePath
            )) {

                $stmt = mysqli_prepare(
                    $conn,"INSERT INTO products (name, price, description, image, stock, category)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sdssis",
                    $name,
                    $price,
                    $description,
                    $imageName,
                    $stock,
                    $category
                );

                if (mysqli_stmt_execute($stmt)) {
                    $message = "تمت إضافة المنتج بنجاح";
                } else {
                    unlink($imagePath);
                    $message = "حدث خطأ أثناء حفظ المنتج";
                }

                mysqli_stmt_close($stmt);

            } else {
                $message = "تعذر رفع الصورة";
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
    <title>إضافة منتج | AL TAWENY</title>
    <link rel="stylesheet" href="../style.css">

    <style>
        body {
            background: #F0EBE3;
            padding: 30px;
        }

        .add-container {
            max-width: 650px;
            margin: 35px auto;
            padding: 35px;
            background: #F8F4ED;
            border: 1px solid #E6DED3;
        }

        .add-container h1 {
            color: #692832;
            margin-bottom: 25px;
        }

        .add-container label {
            display: block;
            margin: 16px 0 8px;
            color: #292323;
        }

        .add-container input,
        .add-container textarea
        .add_container select {
            width: 100%;
            padding: 12px;
            border: 1px solid #DED2C3;
            background: white;
            font-family: inherit;
        }

        .add-container textarea {
            min-height: 110px;
            resize: vertical;
        }

        .add-container button {
            margin-top: 22px;
            width: 100%;
            padding: 14px;
            border: none;
            background: #873541;
            color: white;
            cursor: pointer;
            font-family: inherit;
            font-size: 16px;
        }

        .message {
            background: #E9E0D5;
            padding: 12px;
            margin-bottom: 18px;
            color: #692832;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #873541;
        }
    </style>
</head>

<body>

<div class="add-container">

    <h1>إضافة منتج جديد</h1>

    <?php if ($message !== ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>اسم المنتج</label>
        <input type="text" name="name" required>

        <label>تصنيف المنتج</label>
        <select name="category" required>
            <option value="">اختاري القسم</option>
            <option value="boys">الاولاد</option>
            <option value="girls"> البنات</option>
            <option value="babies">المواليد </option>
            <option value="all"> الاطفال</option>
        </select>

        <label>السعر بالدينار الليبي</label>
        <input type="number" name="price" step="0.01" min="0.01" required>

        <label>وصف المنتج</label>
        <textarea name="description"></textarea>

        <label>صورة المنتج</label>
        <input type="file" name="image"
               accept="image/jpeg,image/png,image/webp" required>

        <label>الكمية المتوفرة</label>
        <input type="number" name="stock" min="0" required>

        <button type="submit">حفظ المنتج</button>

    </form>

    <a href="products.php" class="back-link">العودة إلى إدارة المنتجات</a>

</div>

</body>
</html>