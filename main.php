<?php
session_start();
date_default_timezone_set('Europe/Prague');


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
            $_SESSION['username'] = $_POST['uemail_login'];
            if ($row['access_type'] == 'ADMIN') {
                setcookie('access_type', 'ADMIN', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'ADMIN';
                header('Location: admin.html');
            } elseif ($row['access_type'] == 'MANAGER') {
                setcookie('access_type', 'MANAGER', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'MANAGER';
                header("Location: manager.html");
            } elseif ($row['access_type'] == 'TECHNICIAN') {
                setcookie('access_type', 'TECHNICIAN', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'TECHNICIAN';
                header('Location: technic.html');
            } elseif ($row['access_type'] == 'USER') {
                setcookie('access_type', 'USER', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'USER';
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

if (isset($_POST['logout'])) {
    if (isset($_COOKIE['access_type'])) {
        setcookie('access_type', '', time() - 3600);
    }
    if (isset($_COOKIE['username'])) {
        setcookie('username', '', time() - 3600);
    }
    session_destroy();
    header('Location: index.html');
}

function addOption($doc, $parent, $value, $string)
{
    $option = $doc->createElement('option');
    $option->setAttribute('id', $string);
    $option->nodeValue = $string;
    if ($value == $string)
        $option->setAttribute('selected', 'True');
    $parent->appendChild($option);
}

function openAppointmentDetailsMgr($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $form = $doc->getElementById('get_back');
    $button = $doc->getElementById('get_back_btn');

    if (isset($_POST['open_appointment_from_ticket_mgr'])) {
        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_type_filter');
        $input->setAttribute('value', $_POST['ticket_type_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_cond_filter');
        $input->setAttribute('value', $_POST['ticket_cond_filter']);
        $form->appendChild($input);

        $button->setAttribute('name', 'open_ticket_mgr');
        $button->setAttribute('value', $_POST['open_appointment_from_ticket_mgr']);
    } else {
        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $button->setAttribute('name', 'search_appointments_mgr');

        $form = $doc->getElementById('show_appointment_from_ticket');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $button = $doc->createElement('button');
        $button->setAttribute('id', 'ticket_from_appointment_btn');
        $button->setAttribute('type', 'submit');
        $button->setAttribute('name', 'open_ticket_from_appointment_mgr');
        $button->setAttribute('value', $_POST['open_appointment_mgr']);
        $button->nodeValue = 'Show Ticket';
        $form->appendChild($button);

    }

    $stmt = $db->query("SELECT id_appointment, title, author, assignee, descript, estimation_date, cond, time_spent, parent_ticket FROM appointment where id_appointment = '" . $_POST['open_appointment_mgr'] . "'");

    foreach ($stmt as $row) {
        $text = $doc->getElementById('appointment_title_a');
        $text->nodeValue = $row['title'];

        $text = $doc->getElementById('appointment_desc_a');
        $text->nodeValue = $row['descript'];

        $table = $doc->getElementById('appointment_info_table');

        $tableRow = $doc->createElement('tr');

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'form_appointment_detail_assignee');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'change_assignee_detail');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'appointment_detail_list_btn_div');
        $form->appendChild($div);

        $combox = $doc->createElement('select');
        $combox->setAttribute('name', 'new_cond');

        $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

        foreach ($assignees as $assignee) {
            addOption($doc, $combox, $row['assignee'], $assignee['email']);
        }

        $div->appendChild($combox);

        $button = $doc->createElement('button', 'Set');
        $button->setAttribute('id', 'set_assignee_detail_btn');
        $button->setAttribute('name', 'set_assignee_detail');
        $button->setAttribute('value', $row['id_appointment']);
        $button->setAttribute('type', 'submit');

        $div->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['cond']);
        $tableRow->appendChild($tableCol);

        if ($row['estimation_date'] == NULL) {
            $tableCol = $doc->createElement('td', 'NONE');
        } else {
            $tableCol = $doc->createElement('td', $row['estimation_date']);
        }
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['time_spent']);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['author']);
        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }

    $stmt = $db->query("SELECT id_comment, content, author, parent_appointment, date_add FROM comment where parent_appointment = '" . $_POST['open_appointment_mgr'] . "'");
    $div = $doc->getElementById('appointment_comments_div');

    foreach ($stmt as $row) {
        $divInternal = $doc->createElement('div');
        $divInternal->setAttribute('class', 'comment_print_div');
        $div->appendChild($divInternal);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['author'];
        $divInternal->appendChild($text);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['date_add'];
        $divInternal->appendChild($text);

        #FIXME:
        $form2 = $doc->createElement('form');
        $form2->setAttribute('action', 'main.php');
        $form2->setAttribute('method', 'post');
        $form2->setAttribute('class','comment_print_div');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'id_comment');
        $input->setAttribute('value', $row['id_comment']);
        $form2->appendChild($input);

        if (isset($_POST['open_appointment_from_ticket_mgr'])) {
            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'ticket_type_filter');
            $input->setAttribute('value', $_POST['ticket_type_filter']);
            $form2->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'ticket_cond_filter');
            $input->setAttribute('value', $_POST['ticket_cond_filter']);
            $form2->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'open_appointment_from_ticket_mgr');
            $input->setAttribute('value', $_POST['open_appointment_from_ticket_mgr']);
            $form->appendChild($input);
            /*
            $button->setAttribute('name', 'open_ticket_mgr');
            $button->setAttribute('value', $_POST['open_appointment_from_ticket_mgr']);*/
        } else {
            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'appointments_assignee_filter');
            $input->setAttribute('value', $_POST['appointments_assignee_filter']);
            $form2->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'appointments_cond_filter');
            $input->setAttribute('value', $_POST['appointments_cond_filter']);
            $form2->appendChild($input);
        }

        $button = $doc->createElement('button', 'Remove');
        $button->setAttribute('id', 'remove_comment_app_btn');
        $button->setAttribute('name', 'remove_comment_app');
        $button->setAttribute('value', $_POST['open_appointment_mgr']);
        $button->setAttribute('type', 'submit');
        $form2->appendChild($button);
        $divInternal->appendChild($form2);
        $div->appendChild($divInternal);

        $divInternal = $doc->createElement('div');
        $divInternal->setAttribute('class', 'ticket_details_class');
        $div->appendChild($divInternal);

        $text = $doc->createElement('a');
        $text->nodeValue = $row['content'];
        $divInternal->appendChild($text);
    }

    $form = $doc->getElementById('add_comment_form');

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'appointment_comment_author');
    $input->setAttribute('value', 'manager'); //TODO add user from session superglobal
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'appointment_comment_date');
    $input->setAttribute('value', date('Y-m-d H:i:s', time()));
    $form->appendChild($input);

    if (isset($_POST['open_appointment_from_ticket_mgr'])) {
        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_type_filter');
        $input->setAttribute('value', $_POST['ticket_type_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_cond_filter');
        $input->setAttribute('value', $_POST['ticket_cond_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'open_appointment_from_ticket_mgr');
        $input->setAttribute('value', $_POST['open_appointment_from_ticket_mgr']);
        $form->appendChild($input);

        $button = $doc->getElementById('add_appointment_comment_btn');
        $button->setAttribute('value', $_POST['open_appointment_from_ticket_mgr']);
    }
    else {
        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $button = $doc->getElementById('add_appointment_comment_btn');
        $button->setAttribute('value', $_POST['open_appointment_mgr']);
    }


    echo $doc->saveHTML();

    return NULL;
}

function openTicketDetailsMgr($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $form = $doc->getElementById('get_back');
    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'ticket_type_filter');
    $input->setAttribute('value', $_POST['ticket_type_filter']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'ticket_cond_filter');
    $input->setAttribute('value', $_POST['ticket_cond_filter']);
    $form->appendChild($input);

    $button = $doc->getElementById('get_back_btn');

    if (isset($_POST['open_ticket_from_appointment_mgr'])) {

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $button->setAttribute('name', 'open_appointment_mgr');
        $button->setAttribute('value', $_POST['open_ticket_from_appointment_mgr']);
    } else {
        $button->setAttribute('name', 'search_tickets_mgr');

        $div = $doc->getElementById('ticket_create_appointment_div');
        $stmt = $db->query("SELECT IFNULL((SELECT id_appointment FROM appointment where parent_ticket = '" . $_POST['open_ticket_mgr'] . "'), 'not_found')");
        foreach ($stmt as $row) {

            $form = $doc->createElement('form');
            $div->appendChild($form);

            $form->setAttribute('id', 'form_create_appointment');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'create_appointment');

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'ticket_type_filter');
            $input->setAttribute('value', $_POST['ticket_type_filter']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'ticket_cond_filter');
            $input->setAttribute('value', $_POST['ticket_cond_filter']);
            $form->appendChild($input);

            if ($row[0] == 'not_found') {

                $button = $doc->createElement('button', 'Create Appointment');
                $button->setAttribute('id', 'create_appointment_btn');
                $button->setAttribute('name', 'create_appointment');
                $button->setAttribute('value', $row[0]);
                $button->setAttribute('type', 'submit');

                $form->appendChild($button);
            } else {

                $button = $doc->createElement('button', 'Open Appointment');
                $button->setAttribute('id', 'open_appointment_from_ticket_btn');
                $button->setAttribute('name', 'open_appointment_from_ticket_mgr');
                $button->setAttribute('value', $row[0]);
                $button->setAttribute('type', 'submit');

                $form->appendChild($button);
            }
        }
    }

    $stmt = $db->query("SELECT id_ticket,author title, category, descript, cond, author, date_add, image FROM ticket where id_ticket = '" . $_POST['open_ticket_mgr'] . "'");

    foreach ($stmt as $row) {
        $text = $doc->getElementById('ticket_title_a');
        $text->nodeValue = $row['title'];

        $text = $doc->getElementById('ticket_desc_a');
        $text->nodeValue = $row['descript'];

        $img = $doc->createElement('img');//TODO uprav si to julo
        $img->setAttribute('src', './images/' . $row['image']);
        $doc->getElementById('ticket_id')->appendChild($img);

        $table = $doc->getElementById('ticket_info_table');

        $tableRow = $doc->createElement('tr');

        $tableCol = $doc->createElement('td', $row['category']);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'form_ticket_detail_cond');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'change_cond_detail');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_type_filter');
        $input->setAttribute('value', $_POST['ticket_type_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_cond_filter');
        $input->setAttribute('value', $_POST['ticket_cond_filter']);
        $form->appendChild($input);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'ticket_detail_list_btn_div');
        $form->appendChild($div);

        $combox = $doc->createElement('select');
        $combox->setAttribute('name', 'new_cond');


        addOption($doc, $combox, $row['cond'], 'UNDER REVIEW');
        addOption($doc, $combox, $row['cond'], 'IN PROGRESS');
        addOption($doc, $combox, $row['cond'], 'DONE');
        addOption($doc, $combox, $row['cond'], 'SUSPENDED');
        addOption($doc, $combox, $row['cond'], 'REJECTED');

        $div->appendChild($combox);

        $button = $doc->createElement('button', 'Set');
        $button->setAttribute('id', 'set_cond_detail_btn');
        $button->setAttribute('name', 'set_cond_detail');
        $button->setAttribute('value', $row['id_ticket']);
        $button->setAttribute('type', 'submit');

        $div->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['author']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);


        $table->appendChild($tableRow);

    }

    $stmt = $db->query("SELECT id_comment, content, author, parent_ticket, date_add FROM comment where parent_ticket = '" . $_POST['open_ticket_mgr'] . "'");
    $div = $doc->getElementById('ticket_comments_div');

    foreach ($stmt as $row) {
        $divInternal = $doc->createElement('div');
        $divInternal->setAttribute('class', 'comment_print_div');
        $div->appendChild($divInternal);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['author'];
        $divInternal->appendChild($text);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['date_add'];
        $divInternal->appendChild($text);

        $divInternal2 = $doc->createElement('div');
        $divInternal2->setAttribute('class', 'ticket_details_class');
        $div->appendChild($divInternal2);

        $text = $doc->createElement('a');
        $text->nodeValue = $row['content'];
        $divInternal2->appendChild($text);


        $form = $doc->createElement('form');
        $form->setAttribute('id', 'form_ticket_detail_remove_comment');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'ticket_detail_remove_comment');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_type_filter');
        $input->setAttribute('value', $_POST['ticket_type_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_cond_filter');
        $input->setAttribute('value', $_POST['ticket_cond_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'id_comment');
        $input->setAttribute('value', $row['id_comment']);
        $form->appendChild($input);

        $button = $doc->createElement('button', 'Remove');
        $button->setAttribute('id', 'remove_comment_ticket_btn');
        $button->setAttribute('name', 'remove_comment_ticket');
        $button->setAttribute('value', $_POST['open_ticket_mgr']);
        $button->setAttribute('type', 'submit');
        $form->appendChild($button);

        $divInternal->appendChild($form);
    }

    $form = $doc->getElementById('add_comment_form');

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'ticket_comment_author');
    $input->setAttribute('value', 'manager'); //TODO add user from session superglobal
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'ticket_comment_date');
    $input->setAttribute('value', date('Y-m-d H:i:s', time()));
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'ticket_type_filter');
    $input->setAttribute('value', $_POST['ticket_type_filter']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'ticket_cond_filter');
    $input->setAttribute('value', $_POST['ticket_cond_filter']);
    $form->appendChild($input);

    //$date = date('Y-m-d H:i:s', time());

    $button = $doc->getElementById('add_ticket_comment_btn');
    $button->setAttribute('value', $_POST['open_ticket_mgr']);

    echo $doc->saveHTML();

    return NULL;
}

function listAppointmentsMgr($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $combox = $doc->getElementById('appointments_assignee_filter');

    $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

    foreach ($assignees as $row) {
        $option = $doc->createElement('option');
        $option->setAttribute('id', $row['email']);
        $option->nodeValue = $row['email'];
        $combox->appendChild($option);
    }

    $query_cond = "";

    if ($_POST['appointments_assignee_filter'] != "All Assignees") {
        $query_cond = " where assignee = '" . $_POST['appointments_assignee_filter'] . "'";
    }

    if ($_POST['appointments_cond_filter'] != "All Conditions") {
        if ($query_cond == "") {
            $query_cond = " where cond = '" . $_POST['appointments_cond_filter'] . "'";
        } else {
            $query_cond = $query_cond . " and cond = '" . $_POST['appointments_cond_filter'] . "'";
        }
    }

    if ($query_cond == "") {
        $stmt = $db->query("SELECT id_appointment, title, assignee, estimation_date, cond FROM appointment");
    } else {
        $stmt = $db->query("SELECT id_appointment, title, assignee, estimation_date, cond FROM appointment" . $query_cond);
    }

    $table = $doc->getElementById('appointments_search_results');

    $filterForm = $doc->getElementById($_POST['appointments_assignee_filter']);
    $filterForm->setAttribute('selected', 'True');

    $filterForm = $doc->getElementById($_POST['appointments_cond_filter']);
    $filterForm->setAttribute('selected', 'True');

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Assignee');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Estimation Date');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Condition');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'open_appointment_details_mgr');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'open_appointment_mgr');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $button = $doc->createElement('button', $row['title']);
        $button->setAttribute('id', 'open_appointment_mgr_btn');
        $button->setAttribute('name', 'open_appointment_mgr');
        $button->setAttribute('value', $row['id_appointment']);
        $button->setAttribute('type', 'submit');
        $form->appendChild($button);

        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        /* FORM FOR CHANGING CONDITION IN TICKET*/

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'form_appointments_assignee');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'change_cond');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_assignee_filter');
        $input->setAttribute('value', $_POST['appointments_assignee_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'appointments_cond_filter');
        $input->setAttribute('value', $_POST['appointments_cond_filter']);
        $form->appendChild($input);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'appointment_list_btn_div');
        $form->appendChild($div);

        $combox = $doc->createElement('select');
        $combox->setAttribute('name', 'new_assignee');

        //Needs to be done in every cycle, otherwise it ends up empty
        $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

        foreach ($assignees as $assignee) {
            addOption($doc, $combox, $row['assignee'], $assignee['email']);
        }

        $div->appendChild($combox);

        $button = $doc->createElement('button', 'Set');
        $button->setAttribute('id', 'set_assignee_btn');
        $button->setAttribute('name', 'set_assignee');
        $button->setAttribute('value', $row['id_appointment']);
        $button->setAttribute('type', 'submit');

        $div->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);


        if ($row['estimation_date'] == NULL) {
            $tableCol = $doc->createElement('td', 'NONE');
        } else {
            $tableCol = $doc->createElement('td', $row['estimation_date']);
        }
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['cond']);
        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }

    echo $doc->saveHTML();

    return NULL;
}

function listTicketsMgr($db, $file)
{
    $query_cond = "";

    if ($_POST['ticket_type_filter'] != "All Types") {
        $query_cond = " where category = '" . $_POST['ticket_type_filter'] . "'";
    }

    if ($_POST['ticket_cond_filter'] != "All Conditions") {
        if ($query_cond == "") {
            $query_cond = " where cond = '" . $_POST['ticket_cond_filter'] . "'";
        } else {
            $query_cond = $query_cond . " and cond = '" . $_POST['ticket_cond_filter'] . "'";
        }
    }

    if ($query_cond == "") {
        $stmt = $db->query("SELECT id_ticket, title, category, date_add, cond FROM ticket");
    } else {
        $stmt = $db->query("SELECT id_ticket, title, category, date_add, cond FROM ticket" . $query_cond);
    }

    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $table = $doc->getElementById('tickets_search_results');

    $filterForm = $doc->getElementById($_POST['ticket_type_filter']);
    $filterForm->setAttribute('selected', 'True');

    $filterForm = $doc->getElementById($_POST['ticket_cond_filter']);
    $filterForm->setAttribute('selected', 'True');

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Category');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Date');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Condition');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');

        //$tableCol = $doc->createElement('td', $row['title']);
        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'open_ticket_details_mgr');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'open_ticket_mgr');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_type_filter');
        $input->setAttribute('value', $_POST['ticket_type_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_cond_filter');
        $input->setAttribute('value', $_POST['ticket_cond_filter']);
        $form->appendChild($input);

        $button = $doc->createElement('button', $row['title']);
        $button->setAttribute('id', 'open_ticket_mgr_btn');
        $button->setAttribute('name', 'open_ticket_mgr');
        $button->setAttribute('value', $row['id_ticket']);
        $button->setAttribute('type', 'submit');

        $form->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['category']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);

        /* FORM FOR CHANGING CONDITION IN TICKET*/

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'form_ticket_cond');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'change_cond');

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_type_filter');
        $input->setAttribute('value', $_POST['ticket_type_filter']);
        $form->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'ticket_cond_filter');
        $input->setAttribute('value', $_POST['ticket_cond_filter']);
        $form->appendChild($input);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'ticket_list_btn_div');
        $form->appendChild($div);

        $combox = $doc->createElement('select');
        $combox->setAttribute('name', 'new_cond');


        addOption($doc, $combox, $row['cond'], 'UNDER REVIEW');
        addOption($doc, $combox, $row['cond'], 'IN PROGRESS');
        addOption($doc, $combox, $row['cond'], 'DONE');
        addOption($doc, $combox, $row['cond'], 'SUSPENDED');
        addOption($doc, $combox, $row['cond'], 'REJECTED');

        $div->appendChild($combox);

        $button = $doc->createElement('button', 'Set');
        $button->setAttribute('id', 'set_cond_btn');
        $button->setAttribute('name', 'set_cond');
        $button->setAttribute('value', $row['id_ticket']);
        $button->setAttribute('type', 'submit');

        $div->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }

    echo $doc->saveHTML();

    return NULL;
}

function listUsers($db, $file)
{
    if ($_POST['admin_filter'] == "All Users") {
        $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user");
    } else {
        $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user where access_type = '" . $_POST['admin_filter'] . "'");
    }

    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $table = $doc->getElementById('users_search_results');
    //$appended = $doc->createElement('tr', 'This is a test element.');
    //$table->appendChild($appended);
    if (isset($_POST['admin_search'])) {
        $filterForm = $doc->getElementById($_POST['admin_filter']);
        $filterForm->setAttribute('selected', 'True');
    }

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'First Name');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Last Name');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Email');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'User Type');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');

        $tableCol = $doc->createElement('td', $row['first_name']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['last_name']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['email']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['access_type']);
        $tableRow->appendChild($tableCol);

        if ($row['access_type'] != 'ADMIN') {
            $tableCol = $doc->createElement('td');
            $form = $doc->createElement('form');
            $form->setAttribute('id', 'form_remove');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'filter_status');
            $form->setAttribute('value', $_POST['admin_filter']);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'filter_status');
            $input->setAttribute('value', $_POST['admin_filter']);
            $form->appendChild($input);

            $button = $doc->createElement('button', 'Remove');
            $button->setAttribute('id', 'remove_btn');
            $button->setAttribute('name', 'remove');
            $button->setAttribute('value', $row['email']);
            $button->setAttribute('type', 'submit');
            $form->appendChild($button);
            $tableCol->appendChild($form);
            $tableRow->appendChild($tableCol);
        }
        $table->appendChild($tableRow);
    }
    echo $doc->saveHTML();
    return NULL;
}

function listOneAppoitment($db, $file)
{
    $stmt = $db->query("SELECT id_ticket, title, category, descript, author, date_add FROM ticket where id_ticket = " . $_POST['Show_tapp']);
    #$stmt = $db->query("SELECT title, category, descript, author, date_add FROM ticket");
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $table = $doc->getElementById('appointment_info');

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Category');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Description');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Author');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Added on');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');
        $tableCol = $doc->createElement('td', $row['title']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['category']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['descript']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['author']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);

        $temp = $db->query("SELECT content, author, date_add, parent_appointment FROM comment  WHERE parent_ticket = " . $row['id_ticket']);
        #$stmt = $db->query("SELECT title, category, descript, author, date_add FROM ticket");
        $table = $doc->getElementById('comment_section');

        $tableRow = $doc->createElement('tr');
        $tableCol = $doc->createElement('th', 'Comment');
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('th', 'Author');
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('th', 'Added on');
        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);

        foreach ($temp as $row) {
            $tableRow = $doc->createElement('tr');
            $tableCol = $doc->createElement('td', $row['content']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['author']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['date_add']);
            $tableRow->appendChild($tableCol);
            $table->appendChild($tableRow);
        }
        $tableRow = $doc->createElement('tr');
        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');
        $form->setAttribute('id', 'form_set');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');

        $button = $doc->createElement('button', 'Add_comment');
        $button->setAttribute('id', 'Add_comment_btn');
        $button->setAttribute('name', 'Add_comment');
        $button->setAttribute('value', $row['Add_comment']);
        $button->setAttribute('type', 'submit');
        $form->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $button = $doc->createElement('button', 'Back');
        $button->setAttribute('id', 'Back_btn');
        $button->setAttribute('name', 'Back');
        $button->setAttribute('value', $row['Back']);
        $button->setAttribute('type', 'submit');
        $form->appendChild($button);
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);
    }
    echo $doc->saveHTML();
    return NULL;
}

function listAppTech($db, $file)
{

    $temp = "SELECT id_appointment, descript, time_spent, estimation_date, cond, assignee FROM appointment WHERE assignee ='" . $_SESSION['username'] . "'";
    if ($_POST['tapp_filter1'] == "All my appoinments") {
        $temp1 = "";
    } else if ($_POST['tapp_filter1'] == "Latest to oldest") {
        $temp1 = "ORDER BY estimation_date";
        #$stmt = $db->query("SELECT COUNT(id_appointment), descript, time_spent, estimation_date, cond FROM appointment GROUP BY estimation_date, time_spent, descript, cond");
    } else if ($_POST['tapp_filter1'] == "Oldest to latest") {
        $temp1 = "ORDER BY estimation_date DESC";
    }

    if ($_POST['tapp_filter2'] == "All conditions") {
        $temp2 = "";
    } else if ($_POST['tapp_filter2'] == "In progress") {
        $temp2 = "AND cond = 'IN PROGRESS'";
    } else if ($_POST['tapp_filter2'] == "Done") {
        $temp2 = "AND cond = 'DONE'";
    } else if ($_POST['tapp_filter2'] == "Suspended") {
        $temp2 = "AND cond = 'SUSPENDED'";
    }

    $stmt = $db->query($temp . $temp2 . $temp1);
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $filterForm = $doc->getElementById($_POST['tapp_filter1']);
    $filterForm->setAttribute('selected', 'True');
    $filterForm = $doc->getElementById($_POST['tapp_filter2']);
    $filterForm->setAttribute('selected', 'True');


    $table = $doc->getElementById('appointment_search_results');

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Time spent');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Estimation Date');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Condition');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');
        $form = $doc->createElement('form');
        $form->setAttribute('id', $row['id_appointment']);
        $form->setAttribute('class', 'form_app');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $tableCol = $doc->createElement('td');
        $tableDesc = $doc->createElement('p', $row['descript']);
        $tableCol->appendChild($tableDesc);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td');
        $tableCol->setAttribute('colspan', '5');
        $tableCol->appendChild($form);

        $tableInside = $doc->createElement('table');
        $tableInsideRow = $doc->createElement('tr');
        $tableInside->appendChild($tableInsideRow);
        $form->appendChild($tableInside);
        $div = $doc->createElement('div');
        $div->setAttribute('id', 'table_inside_div');
        $tableInsideRow->appendChild($div);


        $input = $doc->createElement('input');
        $input->setAttribute('id', 'time_spent');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'time_spent');
        $input->setAttribute('value', $row['time_spent']);
        $div->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('id', 'est_date');
        $input->setAttribute('type', 'date');
        $input->setAttribute('name', 'est_date');
        $input->setAttribute('value', $row['estimation_date']);
        $div->appendChild($input);


        $stmt_tmp = $db->query("SELECT id_appointment, parent_ticket, cond FROM appointment where id_appointment = " . $row['id_appointment']);
        foreach ($stmt_tmp as $tmp) {
            $combox = $doc->createElement('select');
            $combox->setAttribute('name', 'new_cond');
            $combox->setAttribute('id', 'cond_app');
            addOption($doc, $combox, $tmp['cond'], 'IN PROGRESS');
            addOption($doc, $combox, $tmp['cond'], 'DONE');
            addOption($doc, $combox, $tmp['cond'], 'SUSPENDED');
            $div->appendChild($combox);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'parent_ticket');
            $input->setAttribute('value', $tmp['parent_ticket']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'tapp_filter1');
            $input->setAttribute('value', $_POST['tapp_filter1']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'tapp_filter2');
            $input->setAttribute('value', $_POST['tapp_filter2']);
            $form->appendChild($input);

            $button = $doc->createElement('button', 'Set');
            $button->setAttribute('id', 'set_tapp_btn');
            $button->setAttribute('name', 'set_tapp');
            $button->setAttribute('value', $tmp['id_appointment']);
            $button->setAttribute('type', 'submit');
            $div->appendChild($button);

            $button = $doc->createElement('button', 'Show more');
            $button->setAttribute('id', 'show_btn');
            $button->setAttribute('name', 'show_tapp');
            $button->setAttribute('value', $tmp['id_appointment']);
            $button->setAttribute('type', 'submit');
            $div->appendChild($button);

        }
        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);
    }
    echo $doc->saveHTML();
    return NULL;
}

function makeElement($doc,$element_text, $id, $name, $type, $value, $element_type, $element){
    //TODO pozriet či element_type je nepovinny argument
    //TODO pridat defaultne hodnoty
    if (!isset($element)){
        $ele = $doc->createElement($element_type, $element_text);
    } else {
        $ele = $element;
    }
    if (isset($id)){
        $ele->setAttribute('id', $id);
    }
    if (isset($name)){
        $ele->setAttribute('name', $name);
    }
    if (isset($type)){
        $ele->setAttribute('type', $type);
    }
    if (isset($type)){
        $ele->setAttribute('type', $type);
    }
    if (isset($value)){
        $ele->setAttribute('value', $value);
    }
}

function listAppDetails($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $doc->getElementById('th_ass')->nodeValue = 'Author';
    $form = $doc->getElementById('get_back_btn');
    $form->setAttribute('name', 'search_tapp');

    $form = $doc->getElementById('get_back');

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter1');
    $input->setAttribute('value', $_POST['tapp_filter1']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter2');
    $input->setAttribute('value', $_POST['tapp_filter2']);
    $form->appendChild($input);

    $form = $doc->getElementById('show_appointment_from_ticket');

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter1');
    $input->setAttribute('value', $_POST['tapp_filter1']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter2');
    $input->setAttribute('value', $_POST['tapp_filter2']);
    $form->appendChild($input);

    $button = $doc->createElement('button');
    $button->setAttribute('id', 'ticket_from_tapp_btn');
    $button->setAttribute('type', 'submit');
    $button->setAttribute('name', 'open_ticket_tapp');
    $button->setAttribute('value', $_POST['show_tapp']);
    $button->nodeValue = 'Show Ticket';
    $form->appendChild($button);

    $stmt = $db->query("SELECT id_appointment, title, author, assignee, descript, estimation_date, cond, time_spent, parent_ticket FROM appointment where id_appointment = " . $_POST['show_tapp']);

    foreach ($stmt as $row) {
        $text = $doc->getElementById('appointment_title_a');
        $text->nodeValue = $row['title'];

        //------------------------------------------------------------
        $table = $doc->getElementById('appointment_info_table');
        $table->setAttribute('id', 'appointment_info_table_tech');
        $doc->getElementById('auth')->setAttribute('id', 'auth_nonvis');


        $tableRow = $doc->createElement('tr');
        $form = $doc->createElement('form');
        $form->setAttribute('id', $row['id_appointment']);
        $form->setAttribute('class', 'form_app');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $tableCol = $doc->createElement('td');
        $tableDesc = $doc->createElement('p', $row['author']);
        $tableCol->appendChild($tableDesc);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td');
        $tableCol->setAttribute('colspan', '5');
        $tableCol->appendChild($form);

        $tableInside = $doc->createElement('table');
        $tableInsideRow = $doc->createElement('tr');
        $tableInside->appendChild($tableInsideRow);
        $form->appendChild($tableInside);
        $div = $doc->createElement('div');
        $div->setAttribute('id', 'table_inside_div');
        $tableInsideRow->appendChild($div);

        $stmt_tmp = $db->query("SELECT id_appointment, parent_ticket, cond FROM appointment where id_appointment = " . $row['id_appointment']);
        foreach ($stmt_tmp as $tmp) {
            $combox = $doc->createElement('select');
            $combox->setAttribute('name', 'new_cond');
            $combox->setAttribute('id', 'cond_app_tech');
            addOption($doc, $combox, $tmp['cond'], 'IN PROGRESS');
            addOption($doc, $combox, $tmp['cond'], 'DONE');
            addOption($doc, $combox, $tmp['cond'], 'SUSPENDED');
            $div->appendChild($combox);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'parent_ticket');
            $input->setAttribute('value', $tmp['parent_ticket']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'tapp_filter1');
            $input->setAttribute('value', $_POST['tapp_filter1']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'tapp_filter2');
            $input->setAttribute('value', $_POST['tapp_filter2']);
            $form->appendChild($input);
        }

        $input = $doc->createElement('input');
        $input->setAttribute('id', 'est_date_tech');
        $input->setAttribute('type', 'date');
        $input->setAttribute('name', 'est_date');
        $input->setAttribute('value', $row['estimation_date']);
        $div->appendChild($input);

        $input = $doc->createElement('input');
        $input->setAttribute('id', 'time_spent_tech');
        $input->setAttribute('type', 'text');
        $input->setAttribute('name', 'time_spent');
        $input->setAttribute('value', $row['time_spent']);
        $div->appendChild($input);

        $button = $doc->createElement('button', 'Set');
        $button->setAttribute('id', 'set_tapp_btn_tech');
        $button->setAttribute('name', 'set_tapp_detail');
        $button->setAttribute('value', $tmp['id_appointment']);
        $button->setAttribute('type', 'submit');
        $div->appendChild($button);


        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);
        //------------------------------------------------------------

        $text = $doc->getElementById('appointment_desc_a');
        $text->nodeValue = $row['descript'];
    }


    $stmt = $db->query("SELECT id_comment, content, author, parent_appointment, date_add FROM comment where parent_appointment = '" . $_POST['show_tapp'] . "'");
    $div = $doc->getElementById('appointment_comments_div');

    foreach ($stmt as $row) {
        $divInternal = $doc->createElement('div');
        $divInternal->setAttribute('class', 'comment_print_div');
        $div->appendChild($divInternal);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['author'];
        $divInternal->appendChild($text);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['date_add'];
        $divInternal->appendChild($text);

        if ($row['author'] == $_SESSION['username']) {

            $form = $doc->createElement('form');
            $form->setAttribute('id', 'form_tapp_remove_comment');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'tapp_remove_comment');

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'tapp_filter1');
            $input->setAttribute('value', $_POST['tapp_filter1']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'tapp_filter2');
            $input->setAttribute('value', $_POST['tapp_filter2']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'id_comment');
            $input->setAttribute('value', $row['id_comment']);
            $form->appendChild($input);

            $button = $doc->createElement('button', 'Remove');
            $button->setAttribute('id', 'remove_comment_tapp_btn');
            $button->setAttribute('name', 'remove_tapp_comment');
            $button->setAttribute('value', $_POST['show_tapp']);
            $button->setAttribute('type', 'submit');
            $form->appendChild($button);

            $divInternal->appendChild($form);

        }

        $divInternal = $doc->createElement('div');
        $divInternal->setAttribute('class', 'ticket_details_class');
        $div->appendChild($divInternal);

        $text = $doc->createElement('a');
        $text->nodeValue = $row['content'];
        $divInternal->appendChild($text);
    }

    $form = $doc->getElementById('add_comment_form');

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'appointment_comment_author');
    $input->setAttribute('value', $_SESSION['username']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'appointment_comment_date');
    $input->setAttribute('value', date('Y-m-d H:i:s', time()));
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter1');
    $input->setAttribute('value', $_POST['tapp_filter1']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter2');
    $input->setAttribute('value', $_POST['tapp_filter2']);
    $form->appendChild($input);

    $button = $doc->getElementById('add_appointment_comment_btn');
    $button->setAttribute('name', 'add_tap_comment');
    $button->setAttribute('value', $_POST['show_tapp']);

    echo $doc->saveHTML();

    return NULL;
}

function listAppTicket($db, $file){

    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $button = $doc->getElementById('get_back_btn');
    $button->setAttribute('name', 'show_tapp');
    $button->setAttribute('value', $_POST['open_ticket_tapp']);

    $form = $doc->getElementById('get_back');

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter1');
    $input->setAttribute('value', $_POST['tapp_filter1']);
    $form->appendChild($input);

    $input = $doc->createElement('input');
    $input->setAttribute('type', 'hidden');
    $input->setAttribute('name', 'tapp_filter2');
    $input->setAttribute('value', $_POST['tapp_filter2']);
    $form->appendChild($input);

    $temp = $db->query("SELECT id_appointment, parent_ticket FROM appointment where id_appointment = " . $_POST['open_ticket_tapp']);
    foreach ($temp as $line){
        $ticket_id = $line['parent_ticket'];
    }

    $stmt = $db->query("SELECT id_ticket,author title, category, descript, cond, author, date_add, image FROM ticket where id_ticket = '" . $ticket_id . "'");

    foreach ($stmt as $row){
        $text = $doc->getElementById('ticket_title_a');
        $text->nodeValue = $row['title'];

        $text = $doc->getElementById('ticket_desc_a');
        $text->nodeValue = $row['descript'];

        $img = $doc->createElement('img');//TODO uprav si to julo
        $img->setAttribute('src', './images/' . $row['image']);
        $doc->getElementById('ticket_id')->appendChild($img);

        $table = $doc->getElementById('ticket_info_table');

        $tableRow = $doc->createElement('tr');

        $tableCol = $doc->createElement('td', $row['category']);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['cond']);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['author']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }

    $stmt = $db->query("SELECT id_comment, content, author, parent_ticket, date_add FROM comment where parent_ticket = '" . $ticket_id . "'");
    $div = $doc->getElementById('ticket_comments_div');

    foreach ($stmt as $row) {
        $divInternal = $doc->createElement('div');
        $divInternal->setAttribute('class', 'comment_print_div');
        $div->appendChild($divInternal);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['author'];
        $divInternal->appendChild($text);

        $text = $doc->createElement('h2');
        $text->nodeValue = $row['date_add'];
        $divInternal->appendChild($text);

        $divInternal2 = $doc->createElement('div');
        $divInternal2->setAttribute('class', 'ticket_details_class');
        $div->appendChild($divInternal2);

        $text = $doc->createElement('a');
        $text->nodeValue = $row['content'];
        $divInternal2->appendChild($text);
    }

    $form = $doc->getElementById('add_comment_form');
    $form->setAttribute('id','auth_nonvis');

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

function reportProblem($db)
{
    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $ext = pathinfo($_FILES["uploadfile"]["name"], PATHINFO_EXTENSION);
    $folder = "./images/" . $_SESSION['username'] . date("Ymd_His") . "." . $ext;
    move_uploaded_file($tempname, $folder);
    $values = [
        'title' => $_POST['desc'],
        'address' => $_POST['address'],
        'category' => $_POST['type'],
        'descript' => $_POST['problem'],
        'cond' => 'UNDER REVIEW',
        'author' => $_SESSION['username'],
        'date_add' => date("Y-m-d H:i:s"),
        'image' => $_SESSION['username'] . date("Ymd_His") . "." . $ext,
    ];
    $html = file_get_contents('report.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $stmt = $db->prepare("INSERT INTO ticket (title, address, category, descript, cond, author, date_add, image) VALUES (:title, :address, :category, :descript, :cond, :author, :date_add, :image)");
    $stmt->execute($values);
    $msg = "Problem was reported";
    $descBox = $doc->getElementById('info_msg')->nodeValue = $msg;
    echo $doc->saveHTML();
    return NULL;
}
var_dump($_POST);
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
    $values = [

    ];

    #$stmt = $db->query("UPDATE appointment SET assignee = '" . $_POST['new_assignee'] . "' where id_appointment = " . $_POST['Set_tapp']);
    #$_POST['tapp_filter1'] = $_POST['filter_status'];
    #listAppTech($db, 'technic.html');
} else if (isset($_POST['Show_tapp'])) {
    listOneAppoitment($db, 'technic.html');
} else if (isset($_POST['AAA'])) {
    listAppTech($db, 'technic.html');
} else if (isset($_POST['add_comment'])) {
    $string = "INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES ('" . $_POST['Add_comment'] . "', '" . $_SESSION['username'] . "', " . $_POST['add_comment'] . ", NULL, '" . $_POST['comment_date'] . "')";
    $stmt = $db->query($string);
    listOneAppoitment($db, 'technic.html');
} else if (isset($_POST['submit_problem'])) {
    reportProblem($db);
    //TODO save problem into db
    //TODO add image into db
} else if (isset($_POST['remove'])) {
    $stmt = $db->query("DELETE FROM user where email = '" . $_POST['remove'] . "'");
    $_POST['admin_filter'] = $_POST['filter_status'];
    listUsers($db, 'admin.html');
} else if (isset($_POST['admin_search'])) {
    listUsers($db, 'admin.html');
} else if (isset($_POST['add_manager'])) {
    $html = file_get_contents('register.html');
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $element = $doc->getElementById('register_header');
    $element->nodeValue = 'Register City Manager';

    $element = $doc->getElementById('info_msg');
    $element->nodeValue = 'Please fill employee\'s information.';

    $element = $doc->getElementById('uemail_id');
    $element->setAttribute('type', 'text');

    $element = $doc->getElementById('type_id');
    $element->setAttribute('value', 'MANAGER');

    $element = $doc->getElementById('back_btn');
    $element->setAttribute('onclick', "location.href='admin.html'");

    echo $doc->saveHTML();
} else if (isset($_POST['add_tech'])) {
    $html = file_get_contents('register.html');
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $element = $doc->getElementById('register_header');
    $element->nodeValue = 'Register Service Technician';

    $element = $doc->getElementById('info_msg');
    $element->nodeValue = 'Please fill employee\'s information.';

    $element = $doc->getElementById('uemail_id');
    $element->setAttribute('type', 'text');

    $element = $doc->getElementById('type_id');
    $element->setAttribute('value', 'TECHNICIAN');

    $element = $doc->getElementById('tech_form');
    $input = $doc->createElement('input');
    $input->setAttribute('type', 'text');
    $input->setAttribute('name', 'tech_specialization');
    $input->setAttribute('placeholder', 'Specialization');
    $input->setAttribute('required', 'True');
    $element->appendChild($input);

    $element = $doc->getElementById('back_btn');
    $element->setAttribute('onclick', "location.href='manager.html'");

    echo $doc->saveHTML();
} else if (isset($_POST['search_tickets_mgr'])) {
    if (!isset($_POST['ticket_type_filter'])) {
        $_POST['ticket_type_filter'] = "All Types";
    }
    if (!isset($_POST['ticket_cond_filter'])) {
        $_POST['ticket_cond_filter'] = "All Conditions";
    }
    listTicketsMgr($db, 'list_tickets.html');
} else if (isset($_POST['load_cityman'])) {
    $_POST['admin_filter'] = 'TECHNICIAN';
    listUsers($db, 'manager.html');
} else if (isset($_POST['set_cond'])) {
    $stmt = $db->query("UPDATE ticket SET cond = '" . $_POST['new_cond'] . "' where id_ticket = " . $_POST['set_cond']);
    listTicketsMgr($db, 'list_tickets.html');
} else if (isset($_POST['search_appointments_mgr'])) {
    if (!isset($_POST['appointments_assignee_filter'])) {
        $_POST['appointments_assignee_filter'] = "All Assignees";
    }
    if (!isset($_POST['appointments_cond_filter'])) {
        $_POST['appointments_cond_filter'] = "All Conditions";
    }
    listAppointmentsMgr($db, 'list_serviceapp.html');
} else if (isset($_POST['set_assignee'])) {
    $stmt = $db->query("UPDATE appointment SET assignee = '" . $_POST['new_assignee'] . "' where id_appointment = " . $_POST['set_assignee']);
    listAppointmentsMgr($db, 'list_serviceapp.html');
} else if (isset($_POST['open_ticket_mgr'])) {
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['set_cond_detail'])) {
    $_POST['open_ticket_mgr'] = $_POST['set_cond_detail'];
    $stmt = $db->query("UPDATE ticket SET cond = '" . $_POST['new_cond'] . "' where id_ticket = " . $_POST['set_cond_detail']);
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['add_ticket_comment'])) {
    $string = "INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES ('" . $_POST['ticket_comment_content'] . "', '" . $_POST['ticket_comment_author'] . "', " . $_POST['add_ticket_comment'] . ", NULL, '" . $_POST['ticket_comment_date'] . "')";
    $stmt = $db->query($string);
    $_POST['open_ticket_mgr'] = $_POST['add_ticket_comment'];
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['create_appointment'])) {
    var_dump($_POST);
} else if (isset($_POST['open_appointment_mgr'])) {
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['add_appointment_comment'])) {
    $string = "INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES ('" . $_POST['appointment_comment_content'] . "', '" . $_POST['appointment_comment_author'] . "', NULL, '" . $_POST['add_appointment_comment'] . "', '" . $_POST['appointment_comment_date'] . "')";
    $stmt = $db->query($string);
    $_POST['open_appointment_mgr'] = $_POST['add_appointment_comment'];
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_appointment_from_ticket_mgr'])) {
    $_POST['open_appointment_mgr'] = $_POST['open_appointment_from_ticket_mgr'];
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_ticket_from_appointment_mgr'])) {
    $_POST['open_ticket_mgr'] = $_POST['open_ticket_from_appointment_mgr'];
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['remove_comment_ticket'])) {
    $stmt = $db->query("DELETE FROM comment WHERE id_comment = '" . $_POST['id_comment'] . "'");
    $_POST['open_ticket_mgr'] = $_POST['remove_comment_ticket'];
    openTicketDetailsMgr($db, 'ticket_detail.html');
}   else if (isset($_POST['remove_comment_app'])) {
    $stmt = $db->query("DELETE FROM comment WHERE id_comment = '" . $_POST['id_comment'] . "'");
    $_POST['open_appointment_mgr'] = $_POST['remove_comment_app'];
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['set_tapp'])) {
    $stmt = $db->query("UPDATE appointment SET time_spent = '" . $_POST['time_spent'] . "' where id_appointment = " . $_POST['set_tapp']);
    $stmt = $db->query("UPDATE appointment SET estimation_date = '" . $_POST['est_date'] . "' where id_appointment = " . $_POST['set_tapp']);
    $stmt = $db->query("UPDATE appointment SET cond = '" . $_POST['new_cond'] . "' where id_appointment = " . $_POST['set_tapp']);
    listAppTech($db, 'technic.html');
} else if (isset($_POST['show_tapp'])) {
    listAppDetails($db, 'serviceapp_detail.html');
} else if (isset($_POST['add_tap_comment'])) {
    $string = "INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES ('" . $_POST['appointment_comment_content'] . "', '" . $_POST['appointment_comment_author'] . "', NULL, '" . $_POST['add_tap_comment'] . "', '" . $_POST['appointment_comment_date'] . "')";
    $stmt = $db->query($string);
    $_POST['show_tapp'] = $_POST['add_tap_comment'];
    listAppDetails($db, 'serviceapp_detail.html');
} else if (isset($_POST['remove_tapp_comment'])) {
    $stmt = $db->query("DELETE FROM comment WHERE id_comment = " . $_POST['id_comment']);
    $_POST['show_tapp'] = $_POST['remove_tapp_comment'];
    listAppDetails($db, 'serviceapp_detail.html');
} else if(isset($_POST['set_tapp_detail'])){
    $stmt = $db->query("UPDATE appointment SET time_spent = '" . $_POST['time_spent'] . "' where id_appointment = " . $_POST['set_tapp_detail']);
    $stmt = $db->query("UPDATE appointment SET estimation_date = '" . $_POST['est_date'] . "' where id_appointment = " . $_POST['set_tapp_detail']);
    $stmt = $db->query("UPDATE appointment SET cond = '" . $_POST['new_cond'] . "' where id_appointment = " . $_POST['set_tapp_detail']);
    $_POST['show_tapp'] = $_POST['set_tapp_detail'];
    listAppDetails($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_ticket_tapp'])) {
    listAppTicket($db, 'ticket_detail.html');
} else {
    var_dump($_POST);
}
?>