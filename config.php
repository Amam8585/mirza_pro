<?php
$dbname = ''; // اینجا نام پایگاه‌داده (Database Name) خود را وارد کنید
$usernamedb = ''; // اینجا نام کاربری اتصال به پایگاه‌داده را وارد کنید
$passworddb = ''; // اینجا رمز عبور کاربر پایگاه‌داده را قرار دهید
$connect = mysqli_connect("localhost", $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) { die("error" . $connect->connect_error); }
mysqli_set_charset($connect, "utf8mb4");
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];
$dsn = "mysql:host=localhost;dbname=$dbname;charset=utf8mb4";
try { $pdo = new PDO($dsn, $usernamedb, $passworddb, $options); } catch (\PDOException $e) { error_log("Database connection failed: " . $e->getMessage()); }
$APIKEY = ''; // اینجا توکن ربات تلگرام خود را وارد کنید
$adminnumber = ''; // اینجا شناسه عددی ادمین اصلی (Telegram User ID) را بنویسید
$domainhosts = ''; // اینجا دامنه یا آدرس اصلی پنل خود را بدون https:// وارد کنید
$usernamebot = ''; // اینجا نام کاربری ربات تلگرام را قرار دهید (بدون @)

$domainhosts = rtrim(preg_replace('#^https?://#', '', $domainhosts), '/');

$new_marzban = true;
?>
