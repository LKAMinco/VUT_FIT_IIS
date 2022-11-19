<?php
session_start();

try {
    $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    die();
}

if (isset($_POST['login'])) {
    $html = file_get_contents('login.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $stmt = $db->query("SELECT pwd, access_type FROM user WHERE email = '" . $_POST['uemail_login'] . "'");
    //TODO get users from db, check if user exists, if yes, check if password is correct
    $descBox = $doc->getElementById('info_msg');
    $fragment = $doc->createDocumentFragment();
    //$fragment->appendXML('This is text');
    $descBox->nodeValue = 'Incorrect username or password';
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if ($row['pwd'] == $_POST['pwd_login']) {
            if ($row['access_type'] == 'ADMIN') {
                header('Location: admin.php');
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

if (isset($_POST['register_submit'])) {
    $stmt = $db->query("SELECT pwd, access_type FROM user WHERE email = '" . $_POST['uemail_register'] . "'");
    $html = file_get_contents('register.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $descBox = $doc->getElementById('info_msg');
    $fragment = $doc->createDocumentFragment();
    if ($_POST['upwd_register'] != $_POST['upwdconf_register']) {
        $descBox->nodeValue = 'Password does not match'; //TODO vratit udaje do formularu po reloade
        $descBox->appendChild($fragment);
        echo $doc->saveHTML();
    } else {
        if ($stmt->rowCount() == 1) {
            //TODO vratit udaje do formularu po reloade
            $descBox->nodeValue = 'User already exists, please choose another email'; //TODO pridat odkaaz na prihlasenie sa
            $descBox->appendChild($fragment);
            echo $doc->saveHTML();
        } else {
            $values = [
                'email' => $_POST['uemail_register'],
                'pwd' => $_POST['upwd_register'],
                'first_name' => $_POST['ufirstname_register'],
                'last_name' => $_POST['ulastname_register'],
                'date_of_birth' => $_POST['udate_register'],
                'residence' => $_POST['uaddress_register'],
                'access_type' => $_POST['utype_register'],
            ];
            //TODO prerobit stranku uspesnej registracie podla typu registrovaneho uzivatela, aby sa to vedelo spravne vratit cez tlacidlo back
            $stmt = $db->prepare("INSERT INTO user (email, pwd, first_name, last_name, date_of_birth, residence, access_type) VALUES (:email, :pwd, :first_name, :last_name, :date_of_birth, :residence, :access_type)");
            $stmt->execute($values);
            $descBox->nodeValue = 'Nice, you are registered';
            $descBox->appendChild($fragment);
            echo $doc->saveHTML();
        }
    }
}