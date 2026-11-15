<?php
require_once "database.php";

/** @var mysqli $conn */

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    die("رقم المنتج غير صحيح");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM products WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    die("المنتج غير موجود");
}

include "header.php";
?>

<style>
.details-page {
    max-width: 1100px;
    margin: 60px auto;
    padding: 25px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    background: #F5F1EB;
    align-items: center;
}

.details-image img {
    width: 100%;
    max-height: 520px;
    object-fit: contain;
}

.details-info h1 {
    color: #692832;
    font-size: 32px;
    margin-bottom: 20px;
}

.details-price {
    color: #873541;
    font-size: 26px;
    margin: 20px 0;
    font-weight: bold;
}

.details-description {
    line-height: 2;
    color: #555;
}

.details-stock {
    margin: 20px 0;
    color: #555;
}

.details-button {
    display: inline-block;
    padding: 14px 35px;
    background: #873541;
    color: white;
    text-decoration: none;
    margin-top: 15px;
}

@media (max-width: 700px) {
    .details-page {
        grid-template-columns: 1fr;
        gap: 25px;
        margin: 20px auto;
    }
}
</style>

<main class="details-page">

    <div class="details-image">
        <?php if (!empty($product["image"])): ?>
            <img
                src="images/<?php echo htmlspecialchars($product["image"]); ?>"
                alt="<?php echo htmlspecialchars($product["name"]); ?>"
            >
        <?php endif; ?>
    </div>

    <div class="details-info">

        <h1>
            <?php echo htmlspecialchars($product["name"]); ?>
        </h1>

        <div class="details-price">
            <?php echo number_format((float)$product["price"], 2); ?>
            د.ل
        </div>

        <div class="details-description">
            <?php echo nl2br(htmlspecialchars($product["description"])); ?>
        </div>

        <div class="details-stock">
            الكمية المتوفرة:
            <?php echo (int)$product["stock"]; ?>
        </div>

        <form action="add_to_cart.php" method="POST">

    <input type="hidden" name="product_id"
           value="<?php echo (int)$product["id"]; ?>">

    <label>الكمية المطلوبة</label>

    <input type="number" name="quantity"
           value="1" min="1"
           max="<?php echo (int)$product["stock"]; ?>"
           required>

    <button type="submit" class="details-button"
        <?php echo ((int)$product["stock"] <= 0) ? "disabled" : ""; ?>>
        إضافة إلى السلة
    </button>

</form>

        <a href="shop.php" class="details-button">
            العودة إلى المجموعة
        </a>

    </div>

</main>
</body>
</html>