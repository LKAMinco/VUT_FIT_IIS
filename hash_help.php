<?php
session_start();
date_default_timezone_set('Europe/Prague');

try {
    $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
} catch (PDOException $e) {
    echo "Connection error: ".$e->getMessage();
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
hashHelp($db);
?>