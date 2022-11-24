<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'MANAGER' || $_SESSION['access_type'] != 'TECHNICIAN'){
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
    <title>Service Appointment Detail</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="body">
    <h1>Service Appointment Detail</h1>
    <form id="show_appointment_from_ticket" action="main.php" method="post">

    </form>
    <form id="get_back" action="main.php" method="post">
        <button id="get_back_btn" type="submit" value=".">Back</button>
    </form>
    <h2>Title</h2>
    <div class="ticket_details_class">
        <a id="appointment_title_a">PLACEHOLDER TEXT</a>
    </div>
    <table id="appointment_info_table">
        <tr>
            <th id="th_ass">Assignee</th>
            <th>Condition</th>
            <th>Estimation Date</th>
            <th>Time Spent</th>
            <th id="auth">Author</th>
        </tr>
    </table>
    <h2>Description</h2>
    <div class="ticket_details_class">
        <a id="appointment_desc_a">PLACEHOLDER TEXT</a>
    </div>

    <h1>Comments</h1>
    <div id="appointment_comments_div">

    </div>
    <form id="add_comment_form" action="main.php" method="post">
        <input id="appointment_comment_content" maxlength="1000" type="text" name="appointment_comment_content" placeholder="Write Comment Here..." required value=""><br>
        <button id="add_appointment_comment_btn" type="submit" name="add_appointment_comment">Send</button>
    </form>
</div>
</body>
</html>