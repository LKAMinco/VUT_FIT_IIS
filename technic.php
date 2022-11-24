<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'TECHNICIAN' ){
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
    <title>Technic Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="body">
    <h1>Lego Technic®</h1>
    <form id="form_tapp_search" action="main.php" method="post">
        <div id="search_field">
            <select id="tapp_filter1" name="tapp_filter1" required>
                <option id="All my appoinments" name="All my appoinments">All my appoinments</option>
                <option id="Latest to oldest" name="Latest to oldest">Latest to oldest</option>
                <option id="Oldest to latest" name="Oldest to latest">Oldest to latest</option>
            </select>
            <select id="tapp_filter2" name="tapp_filter2" required>
                <option id="All condition" name="All">All condition</option>
                <option id="In progress" name="In progress">In progress</option>
                <option id="Done" name="Done">Done</option>
                <option id="Suspended" name="Suspended">Suspended</option>
            </select>
            <button id="search_tapp_btn" name="search_tapp" value="." type="submit">Search</button>
        </div>
        <button id="logout_btn" type="submit" name="logout">Logout</button>
    </form>
    <table id="appointment_search_results"></table>
    <!--table id="appointment_info"></table>
    <table id="comment_section"></table-->
</div>
</body>
</html>