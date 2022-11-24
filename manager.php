<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'MANAGER' ){
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
    <title>City Manager Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="body">
        <h1>City Manager</h1>
        <form id="form_addtech" action="main.php" method="post">
            <button id="addtech_btn" name="add_tech" type="submit">Add New Technician</button>
            <div id="mgr_searches">
                <button id="search_ticket_mgr" name="search_tickets_mgr" value="." type="submit">Search Tickets</button>
                <button id="search_appointment_mgr" name="search_appointments_mgr" type="submit">Search Service Appointments</button>
            </div>
            <button id="logout_btn" type="submit" name="logout">Logout</button>
        </form>

        <table id="users_search_results">

        </table>
    </div>
</body>
</html>