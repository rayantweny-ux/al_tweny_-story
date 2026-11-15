<?php
session_start();
require_once "database.php";

/** @var mysqli $conn */

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// حذف منتج من السلة
if (isset($_GET["remove"])) {
    $removeId = (int)$_GET["remove"];
    unset($_SESSION["cart"][$removeId]);

    header("Location: cart.php");
    exit;
}

// تحديث الكميات
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["quantities"])) {

    foreach ($_POST["quantities"] as $id => $quantity) {
        $id = (int)$id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            unset($_SESSION["cart"][$id]);
        } else {
            $_SESSION["cart"][$id] = $quantity;
        }
    }

    header("Location: cart.php");
    exit;
}

$cartProducts = [];
$total = 0;

if (!empty($_SESSION["cart"])) {

    $ids = array_map("intval", array_keys($_SESSION["cart"]));
    $idList = implode(",", $ids);

    $result = mysqli_query(
        $conn,
        "SELECT * FROM products WHERE id IN ($idList)"
    );

    while ($product = mysqli_fetch_assoc($result)) {

        $id = (int)$product["id"];
        $quantity = (int)$_SESSION["cart"][$id];

        // عدم تجاوز المخزون المتوفر
        $quantity = min($quantity, (int)$product["stock"]);

        if ($quantity <= 0) {
            unset($_SESSION["cart"][$id]);
            continue;
        }

        $_SESSION["cart"][$id] = $quantity;

        $product["quantity"] = $quantity;
        $product["subtotal"] = $quantity * (float)$product["price"];

        $total += $product["subtotal"];
        $cartProducts[] = $product;
    }
}

include "header.php";
?>

<style>
.cart-page {
    max-width: 1050px;
    margin: 55px auto;
    padding: 25px;
    direction: rtl;
}

.cart-title {
    color: #692832;
    font-size: 32px;
    margin-bottom: 30px;
}

.cart-item {
    display: grid;
    grid-template-columns: 120px 1fr 120px 110px 90px;
    align-items: center;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid #DED2C3;
}

.cart-item img {
    width: 110px;
    height: 120px;
    object-fit: contain;
    background: #F5F1EB;
}

.cart-item h3 {
    color: #692832;
}

.cart-item input {
    width: 65px;
    padding: 10px;
    border: 1px solid #DED2C3;
}

.cart-price {
    color: #873541;
    font-weight: bold;
}

.cart-actions {
    margin-top: 25px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.cart-button {
    padding: 13px 25px;
    background: #873541;
    color: white;
    border: none;
    text-decoration: none;
    cursor: pointer;
    font-family: inherit;
}

.remove-link {
    color: #873541;
    text-decoration: none;
}

.cart-total {
    text-align: left;
    font-size: 24px;
    color: #692832;
    margin-top: 30px;
    font-weight: bold;
}

.empty-cart {
    padding: 50px;
    background: #F5F1EB;
    text-align: center;
    color: #692832;
}

@media (max-width: 700px) {
    .cart-item {
        grid-template-columns: 85px 1fr;
        gap: 12px;
    }

    .cart-item img {
        width: 80px;
        height: 100px;
    }

    .cart-total {
        text-align: right;
    }
}
</style>

<main class="cart-page">

    <h1 class="cart-title">سلة المشتريات</h1>

    <?php if (empty($cartProducts)): ?>

        <div class="empty-cart">
            <h2>سلة المشتريات فارغة</h2>
            <p>اكتشفي مجموعتنا واختاري المنتجات التي تحبينها.</p>
            <a href="shop.php" class="cart-button">تسوق الآن</a>
        </div>

    <?php else: ?>

        <form method="POST">

            <?php foreach ($cartProducts as $product): ?>

                <div class="cart-item">

                    <img
                        src="images/<?php echo htmlspecialchars($product["image"]); ?>"
                        alt="صورة المنتج"
                    >

                    <div>
                        <h3>
                            <?php echo htmlspecialchars($product["name"]); ?>
                        </h3>

                        <div class="cart-price">
                            <?php echo number_format($product["price"], 2); ?>
                            د.ل
                        </div>
                    </div>

                    <div>
                        <label>الكمية</label><br>

                        <input
                            type="number"
                            name="quantities[<?php echo $product["id"]; ?>]"
                            value="<?php echo $product["quantity"]; ?>"
                            min="0"
                            max="<?php echo (int)$product["stock"]; ?>"
                        >
                    </div>

                    <div class="cart-price">
                        <?php echo number_format($product["subtotal"], 2); ?>
                        د.ل
                    </div>

                    <a
                        class="remove-link"
                        href="cart.php?remove=<?php echo $product["id"]; ?>"
                        onclick="return confirm('هل تريد حذف هذا المنتج؟')"
                    >
                        حذف
                    </a>

                </div>

            <?php endforeach; ?>

            <div class="cart-actions">

                <button type="submit" class="cart-button">
                    تحديث الكميات
                </button>

                <a href="shop.php" class="cart-button">
                    متابعة التسوق
                </a>

            </div>

        </form>

        <div class="cart-total">
            الإجمالي:
            <?php echo number_format($total, 2); ?>
            د.ل
        </div>
        <div class="checkout-section">

    <a href="checkout.php" class="checkout-btn">
        إتمام الطلب
    </a>

</div>

    <?php endif; ?>

</main>

</body>
</html>