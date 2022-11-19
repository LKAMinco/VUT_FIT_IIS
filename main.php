<?php
session_start();
/*if isset($_POST['login']) {
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];
    header('Location: main.php');
    exit;
}*/
phpinfo();
if (isset($_POST['login'])) {
    if ($_POST['uname'] != "") {
        if ($_POST['uname'] == "Mylan"){
            echo "<h2>". $_POST['uname']." nema malý penis</h2>";
        } else {
            echo "<h2>". $_POST['uname']." má malý penis</h2>";
        }

    }else{
        echo "<h2>nikto nema maly penis</h2>";
    }
    ?>
    <form action="main.php" method="post">
        <button id="clear" type="submit" name="bubak">Back</button>
    </form>
<?php } else echo "BBBBBBBBB\n";
if (isset($_POST['bubak'])) {
    header('Location: index.html');
}

try {
    $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
} catch (PDOException $e) {
    echo "Connection error: ".$e->getMessage();
    die();
}
