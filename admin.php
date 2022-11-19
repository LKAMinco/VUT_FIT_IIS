<?php
    session_start();

    //phpinfo();

    try {
        $db = new PDO("mysql:host=remotemysql.com;dbname=eUGDvDb3sy;port=3306", 'eUGDvDb3sy', '7tTC6lIx7i');
    } catch (PDOException $e) {
        echo "Connection error: ".$e->getMessage();
        die();
    }

    if(isset($_POST['remove'])){
        $stmt = $db->query("DELETE FROM user where email = '" . $_POST['remove'] . "'");
        //echo $_POST['remove'];
        $_POST['admin_filter'] = $_POST['filter_status'];
        //var_dump($_POST);
    }
    if (isset($_POST['admin_filter'])){
        if($_POST['admin_filter'] == "All Users") {
            $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user");
        }
        else{
            $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user where access_type = '" . $_POST['admin_filter'] . "'");
        }

        $html = file_get_contents('admin.html');
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $table = $doc->getElementById('admin_search_results');
        //$appended = $doc->createElement('tr', 'This is a test element.');
        //$table->appendChild($appended);
        $filterForm = $doc->getElementById($_POST['admin_filter']);
        $filterForm->setAttribute('selected', 'True');

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
            $table->appendChild($tableRow);
            /*
            echo $row['first_name'] . "  " . $row['last_name'] . "  " . $row['email'] . "  " . $row['access_type'];
            echo "<form id=\"form_remove\" action=\"admin.php\" method=\"post\">";
            <input type="hidden" name="field" value="fieldname"/>
            echo "<button id=\"remove_btn\" name=\"remove\" value=\"" . $row['email'] . "\" type=\"submit\">Remove</button>";
            echo "</form>";*/
        }
        echo $doc->saveHTML();
        var_dump($_POST);
    }
    else{
        var_dump($_POST);
        //header("Location: admin.html");
    }
?>