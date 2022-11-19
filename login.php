<?php
session_start();

try {
    $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    die();
}

$html = file_get_contents('login.html');
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);


if (isset($_POST['login'])) {
    $stmt = $db->query("SELECT pwd, access_type FROM user WHERE email = '" . $_POST['uemail'] . "'");
    //TODO get users from db, check if user exists, if yes, check if password is correct
    $descBox = $doc->getElementById('info_msg');
    $fragment = $doc->createDocumentFragment();
    //$fragment->appendXML('This is text');
    $descBox->nodeValue = 'Incorrect username or password';
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if ($row['pwd'] == $_POST['pwd']) {
            if ($row['access_type'] == 'ADMIN') {
                header('Location: admin.html');
            } elseif ($row['access_type'] == 'CITYMAN') {
                header("Location: cityman.html");
            } elseif ($row['access_type'] == 'TECHNICIAN') {
                header('Location: technic.html');
            } elseif ($row['access_type'] == 'USER') {
                header('Location: user.html');
            } else {
                echo "This should never happened";
            }
        } else {
            $descBox->appendChild($fragment);
            echo $doc->saveHTML();
        }
    } else {
        $descBox->appendChild($fragment);
        echo $doc->saveHTML();
    }
}