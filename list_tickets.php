<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'MANAGER' || $_SESSION['access_type'] != 'USER'){
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
    <title>List Tickets Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="body">
    <h1>Tickets</h1>
    <form id="get_back" action="main.php" method="post">
        <button id="get_back_btn" type="submit" name="load_cityman" value=".">Back</button>
    </form>
    <div id="your_tickets_div">

    </div>
    <form id="form_search_tickets" action="main.php" method="post">
        <div id="search_tickets_field">
            <select id="ticket_type_filter" name="ticket_type_filter" required>
                <option id="All Types" name="All">All Types</option>
                <option id="Dirty Streets" name="DIRTY_STREETS">Dirty Streets</option>
                <option id="Roads" name="ROADS">Roads</option>
                <option id="Playgrounds" name="PLAYGROUNDS">Playgrounds</option>
                <option id="Benches" name="BENCHES">Benches</option>
                <option id="Abandoned Vehicles" name="ABANDONED_VEHICLES">Abandoned Vehicles</option>
                <option id="Illegal Dumps" name="ILLEGAL_DUMPS">Illegal Dumps</option>
                <option id="Vegetation" name="VEGETATION">Vegetation</option>
                <option id="Vandalism" name="VANDALISM">Vandalism</option>
                <option id="Others" name="OTHERS">Others</option>
            </select>
            <select id="ticket_cond_filter" name="ticket_cond_filter" required>
                <option id="All Conditions" name="All">All Conditions</option>
                <option id="Under Review" name="UNDER_REVIEW">Under Review</option>
                <option id="In Progress" name="IN_PROGRESS">In Progress</option>
                <option id="Done" name="DONE">Done</option>
                <option id="Suspended" name="SUSPENDED">Suspended</option>
                <option id="Rejected" name="REJECTED">Rejected</option>
            </select>
            <button id="search_tickets_btn" type="submit" name="search_tickets_mgr" value=".">Search Tickets</button>
        </div>
    </form>
    <table id="tickets_search_results">

    </table>
</div>
</body>
</html>