<?php
require_once "database.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($name == "" || $email == "" || $password == "") {
        $message = "يرجى ملء جميع الحقول";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "البريد الإلكتروني غير صحيح";

    } elseif (strlen($password) < 6) {
        $message = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";

    } elseif ($password !== $confirm_password) {
        $message = "كلمتا المرور غير متطابقتين";

    } else {

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE name = ? OR email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($check, "ss", $name, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $message = "اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'customer')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $message = "تم إنشاء حسابك بنجاح";
            } else {
                $message = "حدث خطأ أثناء إنشاء الحساب";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إنشاء حساب - AL TAWENY</title>

<style>
body {
    background: #EDE7DE;
    font-family: Arial, sans-serif;
    color: #34272A;
}

.register-box {
    width: 90%;
    max-width: 420px;
    margin: 50px auto;
    padding: 30px;
    background: #F5F1EB;
    border-radius: 12px;
    text-align: center;
    box-sizing: border-box;
}

.register-box input {
    width: 100%;
    padding: 13px;
    margin: 9px 0;
    box-sizing: border-box;
    border: 1px solid #D5C9BD;
    border-radius: 6px;
}

.register-box button {
    width: 100%;
    padding: 13px;
    margin-top: 15px;
    background: #642D3D;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.register-box a {
    color: #642D3D;
    text-decoration: none;
}

.message {
    color: #642D3D;
    margin: 15px 0;
}
</style>
</head>

<body>

<div class="register-box">

    <h2>إنشاء حساب جديد</h2>
    <p>مرحباً بك في AL TAWENY</p>

    <div class="message">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <?php if (!$success): ?>

    <form method="POST">

        <input type="text" name="name"
        placeholder="اسم المستخدم" required>

        <input type="email" name="email"
        placeholder="البريد الإلكتروني" required>

        <input type="password" name="password"
        placeholder="كلمة المرور" required>

        <input type="password" name="confirm_password"
        placeholder="تأكيد كلمة المرور" required>

        <button type="submit">إنشاء الحساب</button>

    </form>

    <?php else: ?>

        <a href="login.php">تسجيل الدخول الآن</a>

    <?php endif; ?>

    <p>لديك حساب بالفعل؟ <a href="login.php">دخول</a></p>

</div>

</body>
</html>