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
                if ($row['access_type'] == 'ADMIN') {
                    header('Location: admin.html');
                } elseif ($row['access_type'] == 'MANAGER') {
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

    function addOption($doc, $parent, $value, $string){
        $option = $doc->createElement('option');
        $option->setAttribute('id', $string);
        $option->nodeValue = $string;
        if($value == $string)
            $option->setAttribute('selected', 'True');
        $parent->appendChild($option);
    }

    function openAppointmentDetailsMgr($db, $file){
        $html = file_get_contents($file);
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $form = $doc->getElementById('get_back');
        $button = $doc->getElementById('get_back_btn');

        if(isset($_POST['open_appointment_from_ticket_mgr'])){
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
        }
        else{
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

            foreach ($assignees as $assignee){
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

            if ($row['estimation_date'] == NULL){
                $tableCol = $doc->createElement('td', 'NONE');
            }
            else{
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

        //$date = date('Y-m-d H:i:s', time());

        $button = $doc->getElementById('add_appointment_comment_btn');
        $button->setAttribute('value', $_POST['open_appointment_mgr']);

        echo $doc->saveHTML();

        return NULL;
    }

    function openTicketDetailsMgr($db, $file){
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

        if(isset($_POST['open_ticket_from_appointment_mgr'])){

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
        }
        else{
            $button->setAttribute('name', 'search_tickets_mgr');

            $div = $doc->getElementById('ticket_create_appointment_div');
            $stmt = $db->query("SELECT IFNULL((SELECT id_appointment FROM appointment where parent_ticket = '" . $_POST['open_ticket_mgr'] . "'), 'not_found')");
            foreach ($stmt as $row){

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

                if ($row[0] == 'not_found'){

                    $button = $doc->createElement('button', 'Create Appointment');
                    $button->setAttribute('id', 'create_appointment_btn');
                    $button->setAttribute('name', 'create_appointment');
                    $button->setAttribute('value', $row[0]);
                    $button->setAttribute('type', 'submit');

                    $form->appendChild($button);
                }
                else{

                    $button = $doc->createElement('button', 'Open Appointment');
                    $button->setAttribute('id', 'open_appointment_from_ticket_btn');
                    $button->setAttribute('name', 'open_appointment_from_ticket_mgr');
                    $button->setAttribute('value', $row[0]);
                    $button->setAttribute('type', 'submit');

                    $form->appendChild($button);
                }
            }
        }

        $stmt = $db->query("SELECT id_ticket, title, category, descript, cond, author, date_add FROM ticket where id_ticket = '" . $_POST['open_ticket_mgr'] . "'");

        foreach ($stmt as $row) {
            $text = $doc->getElementById('ticket_title_a');
            $text->nodeValue = $row['title'];

            $text = $doc->getElementById('ticket_desc_a');
            $text->nodeValue = $row['descript'];

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

    function listAppointmentsMgr($db, $file){
        $html = file_get_contents($file);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $combox = $doc->getElementById('appointments_assignee_filter');

        $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

        foreach ($assignees as $row){
            $option = $doc->createElement('option');
            $option->setAttribute('id', $row['email']);
            $option->nodeValue = $row['email'];
            $combox->appendChild($option);
        }

        $query_cond = "";

        if($_POST['appointments_assignee_filter'] != "All Assignees"){
            $query_cond = " where assignee = '" . $_POST['appointments_assignee_filter'] . "'";
        }

        if($_POST['appointments_cond_filter'] != "All Conditions"){
            if($query_cond == "") {
                $query_cond = " where cond = '" . $_POST['appointments_cond_filter'] . "'";
            }
            else{
                $query_cond = $query_cond . " and cond = '" . $_POST['appointments_cond_filter'] . "'";
            }
        }

        if($query_cond == "") {
            $stmt = $db->query("SELECT id_appointment, title, assignee, estimation_date, cond FROM appointment");
        }
        else{
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

        foreach ($stmt as $row){
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

            foreach ($assignees as $assignee){
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


            if ($row['estimation_date'] == NULL){
                $tableCol = $doc->createElement('td', 'NONE');
            }
            else{
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

    function listTicketsMgr($db, $file){
        $query_cond = "";

        if($_POST['ticket_type_filter'] != "All Types"){
            $query_cond = " where category = '" . $_POST['ticket_type_filter'] . "'";
        }

        if($_POST['ticket_cond_filter'] != "All Conditions"){
            if($query_cond == "") {
                $query_cond = " where cond = '" . $_POST['ticket_cond_filter'] . "'";
            }
            else{
                $query_cond = $query_cond . " and cond = '" . $_POST['ticket_cond_filter'] . "'";
            }
        }

        if($query_cond == "") {
            $stmt = $db->query("SELECT id_ticket, title, category, date_add, cond FROM ticket");
        }
        else{
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

        foreach ($stmt as $row){
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

    function listUsers($db, $file){
        if($_POST['admin_filter'] == "All Users") {
            $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user");
        }
        else{
            $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user where access_type = '" . $_POST['admin_filter'] . "'");
        }

        $html = file_get_contents($file);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $table = $doc->getElementById('users_search_results');
        //$appended = $doc->createElement('tr', 'This is a test element.');
        //$table->appendChild($appended);
        if(isset($_POST['admin_search'])) {
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

        foreach ($stmt as $row){
            $tableRow = $doc->createElement('tr');

            $tableCol = $doc->createElement('td', $row['first_name']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['last_name']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['email']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['access_type']);
            $tableRow->appendChild($tableCol);

            if($row['access_type'] != 'ADMIN') {
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
        $stmt = $db->query("SELECT title, category, descript, author, date_add FROM ticket");
        $html = file_get_contents($file);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $table = $doc->getElementById('appointment_info');
        /*
           $filterForm = $doc->getElementById($_POST['ticket']);
           $filterForm->setAttribute('selected', 'True');
       */
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

        }

        /*
        $element = $doc->getElementById('back_btn');
        $element->setAttribute('onclick', "location.href='technic.html'");*/
        echo $doc->saveHTML();
        return NULL;
    }

    function listAppTech($db, $file)
    {
        if ($_POST['tapp_filter1'] == "All appoinments") {
            $stmt = $db->query("SELECT descript, time_spent, estimation_date, cond FROM appointment");
        } else {
            $stmt = $db->query("SELECT COUNT(id_appointment), descript, time_spent, estimation_date, cond FROM appointment GROUP BY estimation_date, time_spent, descript, cond");
        }
        $html = file_get_contents($file);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $table = $doc->getElementById('appointment_search_results');

        /*
        if(isset($_POST['tapp_filter1'])) {
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
        listOneAppoitment($db, 'technic.html');
    }else if (isset($_POST['submit_problem'])){
        //TODO save problem into db
        //TODO add image into db
    }
    else if(isset($_POST['remove'])){
        $stmt = $db->query("DELETE FROM user where email = '" . $_POST['remove'] . "'");
        $_POST['admin_filter'] = $_POST['filter_status'];
        listUsers($db, 'admin.html');
    }
    else if (isset($_POST['admin_search'])){
        listUsers($db, 'admin.html');
    }
    else if (isset($_POST['add_manager'])){
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
    }
    else if(isset($_POST['add_tech'])){
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
        $element->setAttribute('onclick', "location.href='cityman.html'");

        echo $doc->saveHTML();
    }
    else if(isset($_POST['search_tickets_mgr'])){
        if(!isset($_POST['ticket_type_filter'])) {
            $_POST['ticket_type_filter'] = "All Types";
        }
        if(!isset($_POST['ticket_cond_filter'])) {
            $_POST['ticket_cond_filter'] = "All Conditions";
        }
        listTicketsMgr($db, 'list_tickets.html');
    }
    else if(isset($_POST['load_cityman'])) {
        $_POST['admin_filter'] = 'TECHNICIAN';
        listUsers($db, 'cityman.html');
    }
    else if(isset($_POST['set_cond'])){
        $stmt = $db->query("UPDATE ticket SET cond = '" . $_POST['new_cond'] . "' where id_ticket = " . $_POST['set_cond']);
        listTicketsMgr($db, 'list_tickets.html');
    }
    else if(isset($_POST['search_appointments_mgr'])){
        if(!isset($_POST['appointments_assignee_filter'])) {
            $_POST['appointments_assignee_filter'] = "All Assignees";
        }
        if(!isset($_POST['appointments_cond_filter'])) {
            $_POST['appointments_cond_filter'] = "All Conditions";
        }
        listAppointmentsMgr($db, 'list_serviceapp.html');
    }
    else if(isset($_POST['set_assignee'])){
        $stmt = $db->query("UPDATE appointment SET assignee = '" . $_POST['new_assignee'] . "' where id_appointment = " . $_POST['set_assignee']);
        listAppointmentsMgr($db, 'list_serviceapp.html');
    }
    else if(isset($_POST['open_ticket_mgr'])){
        openTicketDetailsMgr($db, 'ticket_detail.html');
    }
    else if(isset($_POST['set_cond_detail'])){
        $_POST['open_ticket_mgr'] = $_POST['set_cond_detail'];
        $stmt = $db->query("UPDATE ticket SET cond = '" . $_POST['new_cond'] . "' where id_ticket = " . $_POST['set_cond_detail']);
        openTicketDetailsMgr($db, 'ticket_detail.html');
    }
    else if(isset($_POST['add_ticket_comment'])){
        $string = "INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES ('". $_POST['ticket_comment_content'] . "', '" . $_POST['ticket_comment_author'] . "', " . $_POST['add_ticket_comment'] . ", NULL, '" . $_POST['ticket_comment_date'] . "')";
        $stmt = $db->query($string);
        $_POST['open_ticket_mgr'] = $_POST['add_ticket_comment'];
        openTicketDetailsMgr($db, 'ticket_detail.html');
    }
    else if(isset($_POST['create_appointment'])){
        var_dump($_POST);
    }
    else if(isset($_POST['open_appointment_mgr'])){
        openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
    }
    else if(isset($_POST['open_appointment_from_ticket_mgr'])){
        $_POST['open_appointment_mgr'] = $_POST['open_appointment_from_ticket_mgr'];
        openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
    }
    else if(isset($_POST['open_ticket_from_appointment_mgr'])){
        $_POST['open_ticket_mgr'] = $_POST['open_ticket_from_appointment_mgr'];
        openTicketDetailsMgr($db, 'ticket_detail.html');
    }
    else if(isset($_POST['add_appointment_comment'])){
        $string = "INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES ('". $_POST['appointment_comment_content'] . "', '" . $_POST['appointment_comment_author'] . "', NULL, '" . $_POST['add_appointment_comment'] . "', '" . $_POST['appointment_comment_date'] . "')";
        $stmt = $db->query($string);
        $_POST['open_appointment_mgr'] = $_POST['add_appointment_comment'];
        openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
    }
    else{
        var_dump($_POST);
    }
?>