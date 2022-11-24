<?php
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
    <title>Report problem</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<header>
    <div class="body">
        <h1>Report problem</h1>
        <h3 id="info_msg">Please fill problem information.</h3>
        <form id="form_report" action="main.php" method="post" enctype="multipart/form-data">
            <input id="desc_id" type="text" name="desc" placeholder="Description" required maxlength="250"><br>
            <input id="address_id" type="text" name="address" placeholder="Address" required maxlength="250"><br>
            <!-- DIRTY STREETS | ROADS | PLAYGROUNDS | BENCHES | ABANDONED VEHICLES | ILLEGAL DUMPS | PARKS | VANDALISM | OTHERS -->
            <select id="type_id" name="type" required>
                <option disabled selected value> -- select an option --</option>
                <option name="playground">Playground</option>
                <option name="bench">Bench</option>
                <option name="vegetation">Vegetation</option>
                <option name="vandalism">Vandalism</option>
                <option name="dirty_streets">Dirty street</option>
                <option name="roads">Road</option>
                <option name="vehicle">Abandoned vehicle</option>
                <option name="dump">Illegal dump</option>
                <option name="other">Other</option>
            </select>
            <textarea id="problem_id" type="text" name="problem" placeholder="Problem description ..."
                      required maxlength="1000"></textarea><br>
            <div id="btns">
                <label for="file_id">Please select image ...</label>
                <input id="file_id" type="file" name="uploadfile" accept="image/png, image/gif, image/jpeg" required>
                <script type="text/javascript">
                    <!--change label name to filename -->
                    document.getElementById('file_id').onchange = function () {
                        document.getElementById('file_id').previousElementSibling.innerHTML = this.files[0].name;
                    };
                </script>
                <button id="submit_btn" type="submit" name="submit_problem">Submit</button>
                <button id="clear_btn" type="reset">Clear</button>
                <!--TODO remove connection to main page-->
                <button id="back_btn" type="button" onclick="location.href='user.php'">Back</button>
            </div>
        </form>
    </div>
</header>
</body>
</html>