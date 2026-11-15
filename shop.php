<?php
require_once "database.php";
include "header.php";

// استقبال بيانات البحث
$category = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$productId = $_GET['product_id'] ?? '';

// التصنيفات المسموح بها
$allowedCategories = ['boys', 'girls', 'babies', 'all'];

$where = [];
$params = [];
$types = "";

// البحث باسم المنتج
if ($search !== '') {
    $where[] = "name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// البحث بالتصنيف
if (in_array($category, $allowedCategories, true)) {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

// الحد الأدنى للسعر
if ($minPrice !== '' && is_numeric($minPrice) && $minPrice >= 0) {
    $where[] = "price >= ?";
    $params[] = (float)$minPrice;
    $types .= "d";
}

// الحد الأعلى للسعر
if ($maxPrice !== '' && is_numeric($maxPrice) && $maxPrice >= 0) {
    $where[] = "price <= ?";
    $params[] = (float)$maxPrice;
    $types .= "d";
}

// البحث برقم المنتج
if ($productId !== '' && ctype_digit((string)$productId)) {
    $where[] = "id = ?";
    $params[] = (int)$productId;
    $types .= "i";
}

// تجهيز الاستعلام
$sql = "SELECT * FROM products";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<section class="store-page">

<div class="search-container">

    <form method="GET" action="shop.php" class="advanced-search">

        <div class="search-title">
            <h2>ابحثي عن القطعة المثالية</h2>
            <p>اكتشفي مجموعتنا بسهولة</p>
        </div>

        <div class="search-fields">

            <div class="search-group">
                <label>اسم المنتج</label>
                <input
                    type="text"
                    name="search"
                    placeholder="مثلاً: فستان أطفال"
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>

            <div class="search-group">
                <label>التصنيف</label>
                <select name="category">
                    <option value="">جميع الأقسام</option>
                    <option value="boys" <?php if($category == 'boys') echo 'selected'; ?>>ملابس الأولاد</option>
                    <option value="girls" <?php if($category == 'girls') echo 'selected'; ?>>ملابس البنات</option>
                    <option value="babies" <?php if($category == 'babies') echo 'selected'; ?>>ملابس المواليد</option>
                    <option value="all" <?php if($category == 'all') echo 'selected'; ?>>جميع الملابس</option>
                </select>
            </div>

            <div class="search-group">
                <label>السعر من</label>
                <input
                    type="number"
                    name="min_price"
                    min="0"
                    step="0.01"
                    placeholder="0"
                    value="<?php echo htmlspecialchars($minPrice); ?>"
                >
            </div>

            <div class="search-group">
                <label>السعر إلى</label>
                <input
                    type="number"
                    name="max_price"
                    min="0"
                    step="0.01"
                    placeholder="500"
                    value="<?php echo htmlspecialchars($maxPrice); ?>"
                >
            </div>

            <div class="search-group">
                <label>رقم المنتج</label>
                <input
                    type="number"
                    name="product_id"
                    min="1"
                    placeholder="رقم المنتج"
                    value="<?php echo htmlspecialchars($productId); ?>"
                >
            </div>

        </div>

        <div class="search-buttons">
            <button type="submit" class="search-btn">بحث عن المنتجات</button>
            <a href="shop.php" class="reset-search">إعادة تعيين</a>
        </div>

    </form>

</div>

    <div class="store-heading">
        <span>AL TAWENY COLLECTION</span>
        <h1>اكتشفوا مجموعتنا</h1>
        <p>أناقة مختارة بعناية لأطفالكم الصغار</p>
    </div>

    <div class="products-grid">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>

                <div class="product-card">

                    <img
                        src="images/<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                    >

                    <h3>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>

                    <p>
                        <?php echo number_format((float)$product['price'], 2); ?>
                        د.ل
                    </p>

                    <a href="product_details.php?id=<?php echo (int)$product['id']; ?>"
                       class="btn">
                        عرض التفاصيل
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="empty-products">
                لا توجد منتجات حالياً.
            </p>

        <?php endif; ?>

    </div>

</section>

</main>
</body>
</html>