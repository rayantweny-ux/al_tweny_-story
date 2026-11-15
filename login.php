<?php
session_start();
require_once "database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $password = $_POST["password"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, password, role FROM users WHERE name = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);

    $id = 0;
    $username = "";
    $hashed_password = "";
    $role = "";

    mysqli_stmt_bind_result(
        $stmt,
        $id,
        $username,
        $hashed_password,
        $role
    );

    if (mysqli_stmt_fetch($stmt)) {

        if (password_verify($password, $hashed_password)) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            if ($role == "admin") {
                header("Location: admin/dashboard.php");
                exit;
            } else {
                header("Location: account.php");
                exit;
            }

        } else {
            $message = "كلمة المرور غير صحيحة";
        }

    } else {
        $message = "اسم المستخدم غير موجود";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - AL TAWENY</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div style="max-width:400px; margin:70px auto; padding:30px;
background:#F5F1EB; border-radius:12px; text-align:center;">

    <h2>تسجيل الدخول</h2>
    <p>مرحباً بك في AL TAWENY</p>

    <?php if ($message != ""): ?>
        <p style="color:#a32636;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="name"
        placeholder="اسم المستخدم" required
        style="width:90%; padding:12px; margin:10px;">

        <input type="password" name="password"
        placeholder="كلمة المرور" required
        style="width:90%; padding:12px; margin:10px;">

        <button type="submit"
        style="padding:12px 35px; background:#642D3D;
        color:white; border:0; cursor:pointer;">
            دخول
        </button>

    </form>

</div>

</body>
</html>