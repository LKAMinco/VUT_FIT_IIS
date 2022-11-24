<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'MANAGER' || $_SESSION['access_type'] != 'TECHNICIAN' || $_SESSION['access_type'] != 'USER'){
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
    <title>Ticket Detail</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="body">
    <h1>Ticket Detail</h1>
    <div id="ticket_create_appointment_div">

    </div>
    <form id="get_back" action="main.php" method="post">
        <button id="get_back_btn" type="submit" value=".">Back</button>
    </form>
    <h2>Title</h2>
    <div class="ticket_details_class">
        <a id="ticket_title_a">PLACEHOLDER TEXT</a>
    </div>
    <table id="ticket_info_table">
        <tr>
            <th>Type</th>
            <th>Condition</th>
            <th>Author</th>
            <th>Date Add</th>
        </tr>
    </table>
    <h2>Description</h2>
    <div class="ticket_details_class">
        <a id="ticket_desc_a">PLACEHOLDER TEXT</a>
    </div>
    <div id="ticket_id">
    </div>
    <h1>Comments</h1>
    <div id="ticket_comments_div">

    </div>
    <form id="add_comment_form" action="main.php" method="post">
        <input id="ticket_comment_content" type="text" maxlength="1000" name="ticket_comment_content" placeholder="Write Comment Here..." required value=""><br>
        <button id="add_ticket_comment_btn" type="submit" name="add_ticket_comment">Send</button>
    </form>
</div>
</body>
</html>