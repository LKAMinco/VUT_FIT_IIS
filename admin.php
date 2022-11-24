<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['access_type'] != 'ADMIN' ){
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
    <title>Admin page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="body">
        <h1>Admin</h1>
        <form id="form_addmgr" action="main.php" method="post">
            <button id="addmgr_btn" name="add_manager" type="submit">Add New City Manager</button>
        </form>
        <form id="form_search" action="main.php" method="post">
            <div id="search_field">
                <select id="admin_filter" name="admin_filter" required>
                    <option id="All Users" name="All">All Users</option>
                    <option id="Admin" name="Admins">Admin</option>
                    <option id="Manager" name="Managers">Manager</option>
                    <option id="Technician" name="Technicians">Technician</option>
                    <option id="User" name="Users">User</option>
                </select>
                <button id="search_btn" name="admin_search" value="." type="submit">Search</button>
                <button id="logout_btn" type="submit" name="logout">Logout</button>
            </div>
        </form>
        <table id="users_search_results">

        </table>
    </div>
</body>
</html>