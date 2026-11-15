<?php

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "my_store";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die("خطأ الاتصال: " . mysqli_connect_error());
}
 
mysqli_set_charset($conn, "utf8mb4");

  "تم الاتصال بقاعدة البيانات بنجاح";

?>