<?php
    session_start();

    //phpinfo();

    try {
        $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
    } catch (PDOException $e) {
        echo "Connection error: ".$e->getMessage();
        die();
    }



    if ($_POST['filter'] == "All Users"){
        $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user");

        $html = file_get_contents('admin.html');
        echo $html;

        foreach ($stmt as $row){
            echo $row['first_name'] . "  " . $row['last_name'] . "  " . $row['email'] . "  " . $row['access_type'];
            echo "<form id=\"form_remove\" action=\"admin.php\" method=\"post\">";
            echo "<button id=\"remove_btn\" name=\"remove\" value=\"" . $row['email'] . "\" type=\"submit\">Remove</button>";
            echo "</form>";
        }
        var_dump($_POST);
    }
    else if(isset($_POST['remove'])){
        echo $_POST['remove'];
    }
    else{
        var_dump($_POST);
        //header("Location: admin.html");
    }
?>