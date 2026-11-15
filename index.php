<?php
require_once "database.php";
include "header.php";

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 8");
?>

<section class="lux-hero">

    <div class="hero-content">

        <span class="hero-label">THE LITTLE LUXURY COLLECTION</span>

        <h1>
            أناقة صغيرة<br>
            <span>بتفاصيل كبيرة</span>
        </h1>

        <p>
            اكتشفي تشكيلة مختارة من أجمل ملابس الأطفال،
            حيث تجتمع الجودة والأناقة في كل قطعة.
        </p>

        <a href="shop.php" class="hero-button">
            اكتشفي المجموعة
        </a>

        <div class="hero-note">
            AL TAWENY — CHILDREN'S BOUTIQUE
        </div>

    </div>

    <div class="hero-visual">
        <img src="images/hero-kids.jpg" alt="مجموعة أزياء أطفال">
    </div>

</section>

<section class="category-section">
    <div class="section-title">
        <span>EXPLORE OUR WORLD</span>
        <h2>تسوقي حسب المجموعة</h2>
        <p>اختاري ما يناسب صغيرك من تشكيلاتنا المميزة</p>
    </div>

   <div class="category-grid">

    <a href="shop.php?category=boys" class="category-card">
        <div class="category-number">01</div>
        <h3>ملابس الأولاد</h3>
        <span>اكتشفي المجموعة</span>
    </a>

    <a href="shop.php?category=girls" class="category-card">
        <div class="category-number">02</div>
        <h3>ملابس البنات</h3>
        <span>اكتشفي المجموعة</span>
    </a>

    <a href="shop.php?category=babies" class="category-card">
        <div class="category-number">03</div>
        <h3>ملابس المواليد</h3>
        <span>اكتشفي المجموعة</span>
    </a>

</div>

<section class="featured-section">
    <div class="section-title">
        <span>OUR SELECTION</span>
        <h2>أحدث المنتجات</h2>
        <p>اختيارات مميزة من متجر الطويني</p>
    </div>

    <div class="products-grid">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>

                <div class="product-card">

                    <?php if (!empty($product['image'])): ?>
                        <img
                            src="images/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        >
                    <?php else: ?>
                        <div class="product-placeholder">
                            AL TAWENY
                        </div>
                    <?php endif; ?>

                    <div class="product-info">
                        <h3>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>

                        <p>
                            <?php echo number_format((float)$product['price'], 2); ?>
                            د.ل
                        </p>

                        <a href="shop.php" class="product-link">
                            عرض المنتج
                        </a>
                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty-products">
                سيتم عرض المنتجات هنا قريباً.
            </p>

        <?php endif; ?>

    </div>

    <div class="all-products">
        <a href="shop.php" class="btn">عرض جميع المنتجات</a>
    </div>
</section>

</main>

<footer class="site-footer">
    <h2>AL TAWENY</h2>
    <p>أناقة صغيرة تستحق الأفضل</p>
    <p>جميع الحقوق محفوظة لمتجر الطويني</p>
</footer>

</body>
</html>