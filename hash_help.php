<?php
session_start();
date_default_timezone_set('Europe/Prague');

try {
    $db = new PDO("mysql:host=localhost;dbname=xsmyka01;port=/var/run/mysql/mysql.sock", 'xsmyka01', 'kor5ergu');
    hashHelp($db);
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    die();
}

function hashHelp($db)
{
    $stmt = $db->query("SELECT pwd, email FROM user");
    foreach ($stmt as $row) {
        $hash_pwd = password_hash($row['pwd'], PASSWORD_DEFAULT);
        $db->query("UPDATE user SET pwd = '" . $hash_pwd . "'WHERE email = '" . $row['email']. "'");
    }
}
?>