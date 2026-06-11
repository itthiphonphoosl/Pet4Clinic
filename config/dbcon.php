<?php
$dsn = "mysql:host=localhost;dbname=zpwwlszw_pet4care;charset=utf8";
$username = "zpwwlszw_pet4care";
$password = "SDTqTtSjJ3bJmNEn3mmR";

try {
    $dbcon = new PDO($dsn, $username, $password);
    $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>