<?php
    session_start();

    function listTickets($db, $file){
        $query_cond = "";

        if($_POST['ticket_type_filter'] != "All Ticket Types"){
            $query_cond = " where category = '" . $_POST['ticket_type_filter'] . "'";
        }

        if($_POST['ticket_cond_filter'] != "All Ticket Conditions"){
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

            $tableCol = $doc->createElement('td', $row['title']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['category']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['date_add']);
            $tableRow->appendChild($tableCol);
            $tableCol = $doc->createElement('td', $row['cond']);
            $tableRow->appendChild($tableCol);

            /* FORM FOR CHANGING CONDITION IN TICKET*/

            $tableCol = $doc->createElement('td');
            $form = $doc->createElement('form');

            $form->setAttribute('id', 'form_ticket_cond');
            $form->setAttribute('action', 'admin.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'change_cond');

            $button = $doc->createElement('button', 'Set Condition');
            $button->setAttribute('id', 'set_cond_btn');
            $button->setAttribute('name', 'set_cond');
            $button->setAttribute('value', $row['id_ticket']);
            $button->setAttribute('type', 'submit');

            $tableCol->appendChild($form);
            $tableRow->appendChild($tableCol);

            /* FORM FOR OPENING/CREATING SERVICE APPOINTMENTS*/

            $tableCol = $doc->createElement('td');
            $form = $doc->createElement('form');
            $form->setAttribute('id', 'form_create_service');
            $form->setAttribute('action', 'admin.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'create_service');
            $form->setAttribute('value', $_POST['admin_filter']);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'filter_type');
            $input->setAttribute('value', $_POST['ticket_type_filter']);
            $form->appendChild($input);

            $input = $doc->createElement('input');
            $input->setAttribute('type', 'hidden');
            $input->setAttribute('name', 'filter_cond');
            $input->setAttribute('value', $_POST['ticket_cond_filter']);
            $form->appendChild($input);

            $div = $doc->createElement('div');
            $div->setAttribute('id', 'ticket_list_btn_div');
            $form->appendChild($div);

            $button = $doc->createElement('button', 'Service');
            $button->setAttribute('id', 'create_service_btn');
            $button->setAttribute('name', 'create_service');
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
                $form->setAttribute('action', 'admin.php');
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

    try {
        $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
    } catch (PDOException $e) {
        echo "Connection error: ".$e->getMessage();
        die();
    }



    if(isset($_POST['remove'])){
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
        $element->setAttribute('value', 'CITYMAN');

        $element = $doc->getElementById('back_btn');
        $element->setAttribute('onclick', "location.href='admin.php'");

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
            $_POST['ticket_type_filter'] = "All Ticket Types";
        }
        if(!isset($_POST['ticket_cond_filter'])) {
            $_POST['ticket_cond_filter'] = "All Ticket Conditions";
        }
        listTickets($db, 'list_tickets.html');
    }
    else if(isset($_POST['load_cityman'])) {
        $_POST['admin_filter'] = 'TECHNICIAN';
        listUsers($db, 'cityman.html');
    }
    else{
        var_dump($_POST);
    }
?>