<?php
session_start();

try {
    $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    die();
}

if (isset($_POST['login'])) {
    $html = file_get_contents('login.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $stmt = $db->query("SELECT pwd, access_type FROM user WHERE email = '" . $_POST['uemail_login'] . "'");
    //TODO get users from db, check if user exists, if yes, check if password is correct
    $descBox = $doc->getElementById('info_msg');
    $fragment = $doc->createDocumentFragment();
    //$fragment->appendXML('This is text');
    $descBox->nodeValue = 'Incorrect username or password';
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if ($row['pwd'] == $_POST['pwd_login']) {
            if ($row['access_type'] == 'ADMIN') {
                header('Location: admin.html');
            } elseif ($row['access_type'] == 'CITYMAN') {
                header("Location: cityman.html");
            } elseif ($row['access_type'] == 'TECHNICIAN') {
                header('Location: technic.html');
            } elseif ($row['access_type'] == 'USER') {
                header('Location: user.html');
            } else {
                echo "This should never happened";
            }
        } else {
            $descBox->appendChild($fragment);
            echo $doc->saveHTML();
        }
    } else {
        $descBox->appendChild($fragment);
        echo $doc->saveHTML();
    }
}

function listAppTech($db, $file)
{
    if ($_POST['tapp_filter1'] == "All appoinments") {
        $stmt = $db->query("SELECT descript, time_spent, estimation_date, cond FROM appointment");
    } else {
        $stmt = $db->query("SELECT descript, time_spent, estimation_date, cond FROM appointment");
    }
    # where access_type = '" . $_POST['admin_filter'] . "'"
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $table = $doc->getElementById('appointment_search_results');


    /*if(isset($_POST['tapp_filter1'])) {
        $filterForm = $doc->getElementById($_POST['tapp_filter1']);
        $filterForm->setAttribute('selected', 'True');
    }*/

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Time spent');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Estimation Date');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Condition');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');
        $tableCol = $doc->createElement('td', $row['descript']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['time_spent']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['estimation_date']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['cond']);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');
        $form->setAttribute('id', 'form_set');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'filter_status');
        $form->setAttribute('value', $_POST['tapp_filter1']);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'filter_status');
        $input->setAttribute('value', $_POST['tapp_filter1']);
        $form->appendChild($input);

        /*
        $combox = $doc->createElement('condition_select');
        $combox->setAttribute('name', 'new_cond');
        addOption($doc, $combox, $row['cond'], 'IN PROGRESS');
        addOption($doc, $combox, $row['cond'], 'DONE');
        addOption($doc, $combox, $row['cond'], 'SUSPENDED');*/

        #TODO SET
        $button = $doc->createElement('button', 'Set');
        $button->setAttribute('id', 'set_tapp_btn');
        $button->setAttribute('name', 'Set_tapp');
        $button->setAttribute('value', $row['id_appointment']);
        $button->setAttribute('type', 'submit');
        $form->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        #TODO SHOW MORE
        $button = $doc->createElement('button', 'Show more');
        $button->setAttribute('id', 'show_btn');
        $button->setAttribute('name', 'Show_tapp');
        $button->setAttribute('value', $row['id_appointment']);
        $button->setAttribute('type', 'submit');
        $form->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }
    echo $doc->saveHTML();
    return NULL;
}

function returnData($doc, $values, $msg, $can_login)
{
    $doc->getElementById('info_msg')->nodeValue = $msg;
    if ($can_login) {
        $a = $doc->createElement('a', ' Do u want login ?');
        $a->setAttribute('id', 'register_login');
        $a->setAttribute('href', 'login.html');
        $doc->getElementById('info_msg')->appendChild($a);
    }
    $doc->getElementById('name_id')->setAttribute('value', $values['first_name']);
    $doc->getElementById('surename_id')->setAttribute('value', $values['last_name']);
    $doc->getElementById('date_id')->setAttribute('value', $values['date_of_birth']);
    $doc->getElementById('uaddress_id')->setAttribute('value', $values['residence']);
    $doc->getElementById('uemail_id')->setAttribute('value', $values['email']);
    echo $doc->saveHTML();
    return NULL;
}

if (isset($_POST['register_submit'])) {
    $stmt = $db->query("SELECT pwd, access_type FROM user WHERE email = '" . $_POST['uemail_register'] . "'");
    $html = file_get_contents('register.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $values = [
        'email' => $_POST['uemail_register'],
        'pwd' => $_POST['upwd_register'],
        'first_name' => $_POST['ufirstname_register'],
        'last_name' => $_POST['ulastname_register'],
        'date_of_birth' => $_POST['udate_register'],
        'residence' => $_POST['uaddress_register'],
        'access_type' => $_POST['utype_register'],
    ];
    if ($_POST['upwd_register'] != $_POST['upwdconf_register']) {
        $msg = 'Password does not match';
        returnData($doc, $values, $msg, false);
    } else {
        if ($stmt->rowCount() == 1) {
            //TODO vratit udaje do formularu po reloade
            $msg = 'User already exists, please choose another email';
            returnData($doc, $values, $msg, true);
        } else {
            //TODO prerobit stranku uspesnej registracie podla typu registrovaneho uzivatela, aby sa to vedelo spravne vratit cez tlacidlo back
            $stmt = $db->prepare("INSERT INTO user (email, pwd, first_name, last_name, date_of_birth, residence, access_type) VALUES (:email, :pwd, :first_name, :last_name, :date_of_birth, :residence, :access_type)");
            $stmt->execute($values);
            $descBox = $doc->getElementById('info_msg')->nodeValue = 'Nice, you are registered';
            echo $doc->saveHTML();
        }
    }

} else if (isset($_POST['search_tapp'])) {
    listAppTech($db, 'technic.html');
} else if (isset($_POST['Set_tapp'])) {
    #TODO SET
    $stmt = $db->query("UPDATE appointment SET assignee = '" . $_POST['new_assignee'] . "' where id_appointment = " . $_POST['Set_tapp']);
    $_POST['tapp_filter1'] = $_POST['filter_status'];
    listAppTech($db, 'technic.html');
} else if (isset($_POST['Show_tapp'])) {
    #TODO SHOW
    $stmt = $db->query("UPDATE appointment SET assignee = '" . $_POST['new_assignee'] . "' where id_appointment = " . $_POST['Set_tapp']);
    $_POST['tapp_filter1'] = $_POST['filter_status'];
    listAppTech($db, 'technic.html');
}else if (isset($_POST['submit_problem'])){
    //TODO save problem into db
    //TODO add image into db
}
?>