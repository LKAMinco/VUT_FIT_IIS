<?php
//TODO pridat ako jednu funkciu, do suboru s funkciami, a volat ju len s inym parametrom
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'USER' ){
    $html = file_get_contents('wrong_access.html');
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $btn = $doc->getElementById('back_btn_access');
    if ($_SESSION['access_type'] == 'ADMIN'){
        $btn->setAttribute('onclick', 'location.href=\'admin.php\'');
    } else if($_SESSION['access_type'] == 'MANAGER'){
        $btn->setAttribute('onclick', 'location.href=\'manager.php\'');
    }else if($_SESSION['access_type'] == 'TECHNICIAN'){
        $btn->setAttribute('onclick', 'location.href=\'technic.php\'');
    }
    else if($_SESSION['access_type'] == 'USER'){
        $btn->setAttribute('onclick', 'location.href=\'user.php\'');
    } else {
        $btn->setAttribute('onclick', 'location.href=\'index.html\'');
    }
    echo $doc->saveHTML();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<header>
    <h1>User Page</h1>
    <form id="btns_reg" action="main.php" method="post">
        <button id="report_btn" type="button" name="report" onclick="location.href='report.html'">Report problem
        </button>
        <button id="search_btn" type="submit" name="search_tickets_mgr">Search problem</button>
        <button id="logout_btn" type=submit" name="logout">Logout</button>
    </form>
</header>
</body>
</html>