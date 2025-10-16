<?php
$dbname = 'vstesvip_Pro';
$usernamedb = 'vstesvip_pro2';
$passworddb = 'qQ3jy;YWH&zY6hhR';
$connect = mysqli_connect("localhost", $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) { die("error" . $connect->connect_error); }
mysqli_set_charset($connect, "utf8mb4");
$options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, ];
$dsn = "mysql:host=localhost;dbname=$dbname;charset=utf8mb4";
try { $pdo = new PDO($dsn, $usernamedb, $passworddb, $options); } catch (\PDOException $e) { error_log("Database connection failed: " . $e->getMessage()); }
$APIKEY = '7563932023:AAFuXC8Mk778HuqI5LaDNfa0IHhjrIKfi7o';
$adminnumber = '245136195';
$domainhosts = 'vste.s6.viptelbot.top/probot/mirza';
$usernamebot = 'testioyvbot';

// Normalise the domain value so it can safely be concatenated with "https://" elsewhere.
$domainhosts = rtrim(preg_replace('#^https?://#', '', $domainhosts), '/');

$new_marzban = true;
?>
