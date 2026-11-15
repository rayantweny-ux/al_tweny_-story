<?php
require_once "database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($name == "" || $email == "" || $password == "") {
        $message = "يرجى ملء جميع الحقول";
    } else {

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE role = 'admin' LIMIT 1"
        );

        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            $message = "يوجد حساب مدير بالفعل";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'admin')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "تم إنشاء حساب المدير بنجاح";
            } else {
                $message = "حدث خطأ: " . mysqli_stmt_error($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إنشاء حساب المدير</title>
</head>
<body>

<h2>إنشاء حساب المدير</h2>

<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST">
    <label>اسم المستخدم:</label><br>
    <input type="text" name="name" required><br><br>

    <label>البريد الإلكتروني:</label><br>
    <input type="email" name="email" required><br><br>

    <label>كلمة المرور:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">إنشاء الحساب</button>
</form>

</body>
</html>