<?php
    session_start();

    function listUsers($db){
        if($_POST['admin_filter'] == "All Users") {
            $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user");
        }
        else{
            $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user where access_type = '" . $_POST['admin_filter'] . "'");
        }

        $html = file_get_contents('admin.html');
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $table = $doc->getElementById('users_search_results');
        //$appended = $doc->createElement('tr', 'This is a test element.');
        //$table->appendChild($appended);
        $filterForm = $doc->getElementById($_POST['admin_filter']);
        $filterForm->setAttribute('selected', 'True');

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
        listUsers($db);
    }
    else if (isset($_POST['admin_filter'])){
        listUsers($db);
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
    else{
        var_dump($_POST);
        if(!isset($_POST['admin_filter'])) {
            $_POST['admin_filter'] = 'All Users';
        }
        listUsers($db);
    }
?>