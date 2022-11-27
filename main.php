<?php
/* -- Part One: Session start and database connection -- */

session_start();
// for time() function to display our local time
date_default_timezone_set('Europe/Prague');


try {
    $db = new PDO("mysql:host=localhost;dbname=xcesko00;port=/var/run/mysql/mysql.sock", 'xcesko00', 'i4okonun');
} catch (PDOException $e) {
    echo "Connection error: ".$e->getMessage();
    die();
}

// automatically logs out user after 30 min of inactivity
if (!isset($_COOKIE['username']) && !isset($_POST['login']) && !isset($_POST['register_submit'])) {
    session_destroy();
    header("Location: wrong_access.html");
} else {
    if(isset($_SESSION['username'])) {
        setcookie('username', $_SESSION['username'], time() + 3600);
    }
}

/* -- Part Two: Function for displaying data in HTML page -- */

// Function creates/searches for html element and changes its values based on parameters
function setElement( $doc, $element_type, $element_text, $id, $name, $type, $value, $parent, $element){
    if($element != NULL){
        $ele = $doc->getElementById($element);
    }
    else{
        $ele = $doc->createElement($element_type, $element_text);
    }
    if ($id != NULL){
        $ele->setAttribute('id', $id);
    }
    if ($name != NULL){
        $ele->setAttribute('name', $name);
    }
    if ($type != NULL){
        $ele->setAttribute('type', $type);
    }
    if ($value != NULL){
        $ele->setAttribute('value', $value);
    }
    if ($parent != NULL){
        $parent->appendChild($ele);
    }
}

// Function adds option to combobox and sets it as selected if needed
function addOption($doc, $parent, $value, $string)
{
    $option = $doc->createElement('option');
    $option->setAttribute('id', $string);
    $option->nodeValue = $string;
    if ($value == $string)
        $option->setAttribute('selected', 'True');
    $parent->appendChild($option);
}

// Function lists tickets for admin, gives him ability to remove them from database
function listTicketsAdmin($db, $file){
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Checks access rights
    if($_SESSION['access_type'] == 'ADMIN'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    // Sets button to work for admin pages
    $form = $doc->getElementById('get_back');
    setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);

    $button = $doc->getElementById('get_back_btn');
    $button->setAttribute('name', 'admin_search');

    $button = $doc->getElementById('search_tickets_btn');
    $button->setAttribute('name', 'admin_tickets');

    // Dispay filter settings for admin page
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

    $stmt = $db->prepare("SELECT id_ticket, title, date_add FROM ticket" . $query_cond);
    $stmt->execute();

    // Setting displaying active filters in html
    $filterForm = $doc->getElementById($_POST['ticket_type_filter']);
    $filterForm->setAttribute('selected', 'True');

    $filterForm = $doc->getElementById($_POST['ticket_cond_filter']);
    $filterForm->setAttribute('selected', 'True');

    $form = $doc->getElementById('form_search_tickets');
    // Parameters for keeping active search filter
    setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);

    $table = $doc->getElementById('tickets_search_results');
    $table->setAttribute('id', 'tickets_search_results_admin');

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Date');
    $tableRow->appendChild($tableCol);
    $table->appendChild($tableRow);

    // Fill table with data of all tickets in system
    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');
        $tableCol = $doc->createElement('td', $row['title']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td');
        // Creates form to delete tickets
        $form = $doc->createElement('form');
        $form->setAttribute('id', 'form_ticket_remove');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'remove_ticket');
        $tableCol->appendChild($form);

        // Parameters for keeping active search filter
        setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
        // Button to execute this form
        setElement( $doc, 'button', 'Delete', 'ticket_delete_btn', 'ticket_delete', 'submit', $row['id_ticket'], $form, NULL);

        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }

    echo $doc->saveHTML();
}

// Function enables user to edit their information
function editUser($db, $file){
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // All users have access to this page
    if(isset($_SESSION['access_type'])) {
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    // Parameters for keeping active search filter
    $form = $doc->getElementById('form_edit_back');
    if(isset($_POST['admin_filter']))
        setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);
    if(isset($_POST['tapp_filter1']))
    setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
    if(isset($_POST['tapp_filter2']))
    setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);

    // Parameters for keeping active search filter
    $form = $doc->getElementById('form_edit');
    if(isset($_POST['admin_filter']))
        setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);
    if(isset($_POST['tapp_filter1']))
        setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
    if(isset($_POST['tapp_filter2']))
        setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);

    $button = $doc->getElementById('edit_btn');
    $button->setAttribute('value', $_SESSION['username']);

    // Setting back button for each user type
    if($_SESSION['access_type'] == 'ADMIN'){
        $button = $doc->getElementById('edit_back_btn');
        $button->setAttribute('name', 'admin_search');
    }
    else if ($_SESSION['access_type'] == 'MANAGER'){
        $button = $doc->getElementById('edit_back_btn');
        $button->setAttribute('name', 'load_cityman');
    }
    else if ($_SESSION['access_type'] == 'TECHNICIAN'){
        $button = $doc->getElementById('edit_back_btn');
        $button->setAttribute('name', 'search_tapp');
    }
    else if ($_SESSION['access_type'] == 'USER'){
        $button = $doc->getElementById('edit_back_btn');
        $button->setAttribute('name', 'back_to_user');
    }

    // Error message for invalid new password
    if(isset($_POST['edit_submit'])) {
        if ($_POST['edit_submit'] == 'None') {
            $text = $doc->getElementById('info_msg');
            $text->nodeValue = 'Passwords do not match';
        }
    }
    // Fills input fields with current user data
    $values = [
        'email' => $_SESSION['username'],
    ];
    $user = $db->prepare("SELECT email, first_name, last_name, date_of_birth, residence, specialization FROM user where email = :email");
    $user->execute($values);
    $user = $user->fetch();

    $text = $doc->getElementById('name_id');
    $text->setAttribute('value', $user['first_name']);

    $text = $doc->getElementById('surename_id');
    $text->setAttribute('value', $user['last_name']);

    $text = $doc->getElementById('date_id');
    $text->setAttribute('value', $user['date_of_birth']);

    $text = $doc->getElementById('uaddress_id');
    $text->setAttribute('value', $user['residence']);

    echo $doc->saveHTML();
}

// Function opens basic user page and checks for access rights
function openUser($db, $file){
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Checks access rights
    if($_SESSION['access_type'] == 'USER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    // Sets value to edit user button
    $text = $doc->getElementById('edit_btn');
    $text->setAttribute('value', $_SESSION['username']);

    echo $doc->saveHTML();
    return NULL;
}

// Function completes page to create service appointments
function openCreationForm($db, $file){
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Checks access rights
    if($_SESSION['access_type'] == 'MANAGER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    $form = $doc->getElementById('form_create_back');
    // Parameters for keeping active search filter
    setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);

    $button = $doc->getElementById('create_back_btn');
    $button->setAttribute('value', $_POST['create_appointment']);

    $combox = $doc->getElementById('tech_id');
    $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

    // Displays all technicians for assignee selection
    foreach ($assignees as $row){
        addOption($doc, $combox, $row['email'], $row['email']);
        $currentOption = $doc->getElementById($row['email']);
        $currentOption->removeAttribute('selected');
    }

    $button = $doc->getElementById('create_btn');
    $button->setAttribute('value', $_POST['create_appointment']);

    echo $doc->saveHTML();
    return NULL;
}

// Function displays data for service appointment to manager
function openAppointmentDetailsMgr($db, $file){
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Checks access rights
    if($_SESSION['access_type'] == 'MANAGER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    $form = $doc->getElementById('get_back');

    // Parameters for keeping active search filter
    // When appointment detail accessed from ticket
    if(isset($_POST['open_appointment_from_ticket_mgr'])){
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
        setElement( $doc, NULL, NULL, NULL, 'open_ticket_mgr', NULL, $_POST['open_ticket_mgr'], NULL, 'get_back_btn');
    }
    // When appointment accessed directly
    else{
        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
        setElement( $doc, NULL, NULL, NULL, 'search_appointments_mgr', NULL, NULL, NULL, 'get_back_btn');

        // Enables user redirecting to parent ticket
        $form = $doc->getElementById('show_appointment_from_ticket');
        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
        if(isset($_POST['open_ticket_mgr']))
            setElement( $doc, 'input', '', NULL, 'open_ticket_mgr', 'hidden', $_POST['open_ticket_mgr'], $form, NULL);
        setElement( $doc, 'button', 'Show Ticket', 'ticket_from_appointment_btn', 'open_ticket_from_appointment_mgr', 'submit', $_POST['open_appointment_mgr'], $form, NULL);
    }

    $values = [
        'id' => $_POST['open_appointment_mgr'],
    ];
    $stmt = $db->prepare("SELECT id_appointment, title, author, assignee, descript, estimation_date, cond, time_spent, parent_ticket FROM appointment where id_appointment = :id");
    $stmt->execute($values);

    // Displays appointment's data in table
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

        // Parameters for keeping active search filter
        if(isset($_POST['appointments_assignee_filter']))
            setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        if(isset($_POST['appointments_cond_filter']))
            setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'appointment_detail_list_btn_div');
        $form->appendChild($div);

        $combox = $doc->createElement('select');
        $combox->setAttribute('name', 'new_assignee');

        // Fills technicians to assignee selector
        $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");
        foreach ($assignees as $assignee){
            addOption($doc, $combox, $row['assignee'], $assignee['email']);
        }

        $div->appendChild($combox);

        // Button to change assignee
        setElement( $doc, 'button', 'Set', 'set_assignee_detail_btn', 'set_assignee_detail', 'submit', $row['id_appointment'], $form, NULL);

        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        // Service appointment's data for table
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

    $values = [
        'parent' => $_POST['open_appointment_mgr'],
    ];
    // database query to select all appointment's comments
    $stmt = $db->prepare("SELECT id_comment, content, author, parent_appointment, date_add FROM comment where parent_appointment = :parent");
    $stmt->execute($values);
    $div = $doc->getElementById('appointment_comments_div');

    // Displays all comment to the user
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

        // Gives manager ability to delete all comments
        $form = $doc->createElement('form');
        $form->setAttribute('id', 'form_appointment_detail_remove_comment');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'appointment_detail_remove_comment');

        // Parameters for keeping active search filter
        // When appointment detail accessed from ticket
        if(isset($_POST['open_appointment_from_ticket_mgr'])){
            setElement( $doc, 'input', '', NULL, 'open_appointment_from_ticket_mgr', 'hidden', $_POST['open_appointment_from_ticket_mgr'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
        }
        // When appointment accessed directly
        else{
            setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
        }

        setElement( $doc, 'input', '', NULL, 'id_comment', 'hidden', $row['id_comment'], $form, NULL);

        $input = $doc->getElementById('add_appointment_comment_btn');
        setElement( $doc, 'input', '', NULL, 'open_ticket_mgr', 'hidden', $_POST['open_ticket_mgr'], $form, NULL);
        setElement( $doc, 'button', 'Remove', 'remove_comment_appointment_btn', 'remove_appointment_comment', 'submit', $_POST['open_appointment_mgr'], $form, NULL);

        $divInternal->appendChild($form);
    }

    $form = $doc->getElementById('add_comment_form');

    // Parameters for keeping active search filter
    // When appointment detail accessed from ticket
    if(isset($_POST['open_appointment_from_ticket_mgr'])){
        setElement( $doc, 'input', '', NULL, 'open_appointment_from_ticket_mgr', 'hidden', $_POST['open_appointment_from_ticket_mgr'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
    }
    // When appointment accessed directly
    else{
        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
    }

    // Form to add comments to service appointment
    $input = $doc->getElementById('add_appointment_comment_btn');
    if(isset($_POST['open_ticket_mgr']))
        setElement( $doc, 'input', '', NULL, 'open_ticket_mgr', 'hidden', $_POST['open_ticket_mgr'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'appointment_comment_author', 'hidden', $_SESSION['username'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'appointment_comment_date', 'hidden', date('Y-m-d H:i:s', time()), $form, NULL);

    $button = $doc->getElementById('add_appointment_comment_btn');
    $button->setAttribute('value', $_POST['open_appointment_mgr']);

    echo $doc->saveHTML();

    return NULL;
}

// Function display data for ticket to manager
function openTicketDetailsMgr($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Checks access rights
    if($_SESSION['access_type'] == 'MANAGER' || $_SESSION['access_type'] == 'USER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    $form = $doc->getElementById('get_back');

    // Parameters for keeping active search filter
    if(isset($_POST['ticket_type_filter']))
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
    if(isset($_POST['ticket_cond_filter']))
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
    // When ticket was opened from appointment
    if (isset($_POST['open_ticket_from_appointment_mgr'])) {
        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
        setElement( $doc, NULL, NULL, NULL, 'open_appointment_mgr', NULL, $_POST['open_ticket_from_appointment_mgr'], NULL, 'get_back_btn');
    }
    // When ticket was opened directly
    else {
        setElement( $doc, NULL, NULL, NULL, 'search_tickets_mgr', NULL, NULL, NULL, 'get_back_btn');
        // Searches for existing service appointment
        $div = $doc->getElementById('ticket_create_appointment_div');
        $values = [
            'parent' => $_POST['open_ticket_mgr'],
        ];
        $stmt = $db->prepare("SELECT IFNULL((SELECT id_appointment FROM appointment where parent_ticket = :parent), 'not_found')");
        $stmt->execute($values);
        foreach ($stmt as $row) {

            $form = $doc->createElement('form');
            $div->appendChild($form);

            $form->setAttribute('id', 'form_create_appointment');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'create_appointment');

            setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);

            setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);

            // Creates button based on whether service appointment exists or not
            if (isset($_SESSION['username']) && $_SESSION['access_type'] != 'USER') {
                if ($row[0] == 'not_found') {
                    // If not, create service appointment button is created
                    setElement( $doc, 'button', 'Create Appointment', 'create_appointment_btn', 'create_appointment', 'submit', $_POST['open_ticket_mgr'], $form, NULL);
                } else {
                    // Show service appointment button is created
                    setElement( $doc, 'button', 'Open Appointment', 'open_appointment_from_ticket_btn', 'open_appointment_from_ticket_mgr', 'submit', $row[0], $form, NULL);
                }
            }
        }
    }

    $values = [
        'id' => $_POST['open_ticket_mgr'],
    ];
    $stmt = $db->prepare("SELECT id_ticket,author, title, category, descript, cond, author, date_add, image FROM ticket WHERE id_ticket = :id");
    $stmt->execute($values);

    // Displays ticket's data to manager or user resident
    foreach ($stmt as $row) {
        $text = $doc->getElementById('ticket_title_a');
        $text->nodeValue = $row['title'];

        $text = $doc->getElementById('ticket_desc_a');
        $text->nodeValue = $row['descript'];

        // Displays image, if one is contained in ticket
        if($row['image'] != NULL){
            $img = $doc->createElement('img');
            $img->setAttribute('src', './images/' . $row['image']);
            $img->setAttribute('alt', 'Ticket image');
            $img->setAttribute('width', '700');
            $doc->getElementById('ticket_id')->appendChild($img);
        }

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

        // Parameters for keeping active search filter
        if(isset($_POST['ticket_type_filter']))
            setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        if(isset($_POST['ticket_cond_filter']))
            setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);

        // Checks if logged user is manager or resident
        if (isset($_SESSION['username']) && $_SESSION['access_type'] == 'USER') {
            $tableCol = $doc->createElement('td', $row['cond']);
            $tableRow->appendChild($tableCol);
        } else {
            // If manager, then button to change ticket's condition is created
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
            setElement( $doc, 'button', 'Set', 'set_cond_detail_btn', 'set_cond_detail', 'submit', $row['id_ticket'], $div, NULL);

        }
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['author']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);


        $table->appendChild($tableRow);

    }

    // Searches database for ticket's comments
    $values = [
        'parent' => $_POST['open_ticket_mgr'],
    ];
    $stmt = $db->prepare("SELECT id_comment, content, author, parent_ticket, date_add FROM comment where parent_ticket = :parent");
    $stmt->execute($values);
    $div = $doc->getElementById('ticket_comments_div');

    // Displays all comments
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

        if ($row['author'] ==$_SESSION['username'] || $_SESSION['access_type'] == 'MANAGER') {

            // If user is manager or comment's owner, gives him ability to remove comment
            $form = $doc->createElement('form');
            $form->setAttribute('id', 'form_ticket_detail_remove_comment');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'ticket_detail_remove_comment');

            // Parameters for keeping active search filter
            // If ticket was accessed from appointment
            if(isset($_POST['open_ticket_from_appointment_mgr'])){
                setElement( $doc, 'input', '', NULL, 'open_ticket_from_appointment_mgr', 'hidden', $_POST['open_ticket_from_appointment_mgr'], $form, NULL);
                setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
                setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
            }
            // If ticket was accessed directly
            else{
                setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
                setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
            }
            setElement( $doc, 'input', '', NULL, 'id_comment', 'hidden', $row['id_comment'], $form, NULL);
            setElement( $doc, 'button', 'Remove', 'remove_comment_ticket_btn', 'remove_comment_ticket', 'submit', $_POST['open_ticket_mgr'], $form, NULL);
            $divInternal->appendChild($form);
        }
    }

    $form = $doc->getElementById('add_comment_form');

    // Parameters for keeping active search filter
    // If ticket was accessed from appointment
    if(isset($_POST['open_ticket_from_appointment_mgr'])){
        setElement( $doc, 'input', '', NULL, 'open_ticket_from_appointment_mgr', 'hidden', $_POST['open_ticket_from_appointment_mgr'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);
    }
    // If ticket was accessed directly
    else{
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
    }

    setElement( $doc, 'input', '', NULL, 'ticket_comment_author', 'hidden', $_SESSION['username'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'ticket_comment_date', 'hidden', date('Y-m-d H:i:s', time()), $form, NULL);

    // Button to create new comments
    $button = $doc->getElementById('add_ticket_comment_btn');
    $button->setAttribute('value', $_POST['open_ticket_mgr']);

    echo $doc->saveHTML();

    return NULL;
}

// Function display all appointments to manager
function listAppointmentsMgr($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Access rights check
    if($_SESSION['access_type'] == 'MANAGER' || $_SESSION['access_type'] == 'TECHNICIAN'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    $combox = $doc->getElementById('appointments_assignee_filter');
    $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

    // Sets filter assignees
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

    // Search service appointments with filters
    $stmt = $db->prepare("SELECT id_appointment, title, assignee, estimation_date, cond FROM appointment" . $query_cond);
    $stmt->execute();

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

    // Display all service appointment's data in table
    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');

        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'open_appointment_details_mgr');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'open_appointment_mgr');

        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);

        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);

        setElement( $doc, 'button', $row['title'], 'open_appointment_mgr_btn', 'open_appointment_mgr', 'submit', $row['id_appointment'], $form, NULL);

        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        // Form for changing condition in filter
        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'form_appointments_assignee');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'change_cond');

        // Parameters for keeping active search filter
        setElement( $doc, 'input', '', NULL, 'appointments_assignee_filter', 'hidden', $_POST['appointments_assignee_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'appointments_cond_filter', 'hidden', $_POST['appointments_cond_filter'], $form, NULL);

        $div = $doc->createElement('div');
        $div->setAttribute('id', 'appointment_list_btn_div');
        $form->appendChild($div);

        $combox = $doc->createElement('select');
        $combox->setAttribute('name', 'new_assignee');

        //Needs to be done in every cycle, otherwise it ends up empty
        $assignees = $db->query("SELECT email FROM user where access_type = 'TECHNICIAN'");

        // Fills assignee drop list
        foreach ($assignees as $assignee) {
            addOption($doc, $combox, $row['assignee'], $assignee['email']);
        }

        $div->appendChild($combox);
        // Creates button to enable changing assignee
        setElement( $doc, 'button', 'Set', 'set_assignee_btn', 'set_assignee', 'submit', $row['id_appointment'], $div, NULL);

        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        // Displays rest of the data
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

// Function lists all tickets to manager or user resident
function listTicketsMgr($db, $file)
{

    $query_cond = "";
    // Seaches database by filters
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

    $stmt = $db->prepare("SELECT id_ticket, title, category, date_add, cond FROM ticket" . $query_cond);
    $stmt->execute();

    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Access rights check
    if($_SESSION['access_type'] == 'MANAGER' || $_SESSION['access_type'] == 'USER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    $table = $doc->getElementById('tickets_search_results');

    // Parameters for keeping active search filter
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

    // Displays all ticket's data in table
    foreach ($stmt as $row) {
        $tableRow = $doc->createElement('tr');

        //$tableCol = $doc->createElement('td', $row['title']);
        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'open_ticket_details_mgr');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'open_ticket_mgr');

        // Parameters for keeping active search filter
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);
        setElement( $doc, 'button', $row['title'], 'open_ticket_mgr_btn', 'open_ticket_mgr', 'submit', $row['id_ticket'], $form, NULL);

        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $tableCol = $doc->createElement('td', $row['category']);
        $tableRow->appendChild($tableCol);
        $tableCol = $doc->createElement('td', $row['date_add']);
        $tableRow->appendChild($tableCol);
        // Changes back button values if user is user resident
        if (isset($_SESSION['username']) && $_SESSION['access_type'] == 'USER') {
            $tableCol = $doc->createElement('td', $row['cond']);
            $tableRow->appendChild($tableCol);
            $doc->getElementById('get_back_btn')->setAttribute('name', 'load_user');
        }

        // Form for changing contition in ticket
        $tableCol = $doc->createElement('td');
        $form = $doc->createElement('form');

        $form->setAttribute('id', 'form_ticket_cond');
        $form->setAttribute('action', 'main.php');
        $form->setAttribute('method', 'post');
        $form->setAttribute('name', 'change_cond');

        // Parameters for keeping active search filter
        setElement( $doc, 'input', '', NULL, 'ticket_type_filter', 'hidden', $_POST['ticket_type_filter'], $form, NULL);
        setElement( $doc, 'input', '', NULL, 'ticket_cond_filter', 'hidden', $_POST['ticket_cond_filter'], $form, NULL);

        // If user is manager, gives them ability to change ticket's condition
        if (isset($_SESSION['username']) && $_SESSION['access_type'] != 'USER') {

            $div = $doc->createElement('div');
            $div->setAttribute('id', 'ticket_list_btn_div');
            $form->appendChild($div);

            $combox = $doc->createElement('select');
            $combox->setAttribute('name', 'new_cond');

            //--------------------CONDITIONS--------------------
            addOption($doc, $combox, $row['cond'], 'UNDER REVIEW');
            addOption($doc, $combox, $row['cond'], 'IN PROGRESS');
            addOption($doc, $combox, $row['cond'], 'DONE');
            addOption($doc, $combox, $row['cond'], 'SUSPENDED');
            addOption($doc, $combox, $row['cond'], 'REJECTED');

            $div->appendChild($combox);

            setElement( $doc, 'button', 'Set', 'set_cond_btn', 'set_cond', 'submit', $row['id_ticket'], $div, NULL);

            //--------------------------------------------------
        }
        $tableCol->appendChild($form);
        $tableRow->appendChild($tableCol);

        $table->appendChild($tableRow);
    }

    echo $doc->saveHTML();

    return NULL;
}

// Function lists users in basic admin/manager page
function listUsers($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    // Access rights check
    if($file == 'admin.html'){
        if($_SESSION['access_type'] == 'ADMIN'){
            $meta = $doc->getElementById('redirect');
            $meta->setAttribute('content', '3800;url=main.php');
        }
    } else if($file == 'manager.html'){
        if($_SESSION['access_type'] == 'MANAGER'){
            $meta = $doc->getElementById('redirect');
            $meta->setAttribute('content', '3800;url=main.php');
        }
    }

    // Searches users by filter
    if ($_POST['admin_filter'] == "All Users") {
        $stmt = $db->query("SELECT first_name, last_name, email, access_type FROM user WHERE NOT access_type = 'NONE'");
    } else {
        $values = [
            'access_type' => $_POST['admin_filter'],
        ];
        $stmt = $db->prepare("SELECT first_name, last_name, email, access_type FROM user WHERE access_type = :access_type");
        $stmt->execute($values);
    }

    // Parameters for keeping active search filter
    // Also sets edit user button values for each user type
    if($_SESSION['access_type'] == 'ADMIN'){
        $form = $doc->getElementById('form_addmgr');
        setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);

        $button = $doc->getElementById('edit_btn');
        $button->setAttribute('value', $_SESSION['username']);

        setElement( $doc, 'button', 'Examine Tickets', 'admin_tickets_btn', 'admin_tickets', 'submit', NULL, $form, NULL);
    } else if($_SESSION['access_type'] == 'MANAGER'){
        $button = $doc->getElementById('edit_btn');
        $button->setAttribute('value', $_SESSION['username']);
    }

    $table = $doc->getElementById('users_search_results');
    // Parameters for keeping active search filter
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

    // Displays all user's data
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

        // Gives user rights to delete account, if account type is not admin
        if ($row['access_type'] != 'ADMIN') {
            $tableCol = $doc->createElement('td');
            $form = $doc->createElement('form');
            $form->setAttribute('id', 'form_remove');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'filter_status');
            $form->setAttribute('value', $_POST['admin_filter']);

            setElement( $doc, 'input', '', NULL, 'filter_status', 'hidden', $_POST['admin_filter'], $form, NULL);

            if($_SESSION['access_type'] == 'ADMIN'){
                setElement( $doc, 'input', '', NULL, 'admin_remove', 'hidden', 'admin_remove', $form, NULL);
            }

            setElement( $doc, 'button', 'Remove', 'remove_btn', 'remove', 'submit', $row['email'], $form, NULL);

            $tableCol->appendChild($form);
            $tableRow->appendChild($tableCol);
        }
        $table->appendChild($tableRow);
    }
    echo $doc->saveHTML();
    return NULL;
}

// Function displays all technician's service appointments
function listAppTech($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    // Access rights check
    if($_SESSION['access_type'] == 'TECHNICIAN'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    // Searches service appointments by given filters
    $values = [
        'assignee' => $_SESSION['username']
    ];
    $temp = "SELECT id_appointment, descript, time_spent, estimation_date, cond, assignee FROM appointment WHERE assignee = :assignee";
    if ($_POST['tapp_filter1'] == "All my appoinments") {
        $temp1 = "";
    } else if ($_POST['tapp_filter1'] == "Latest to oldest") {
        $temp1 = " ORDER BY estimation_date";
    } else if ($_POST['tapp_filter1'] == "Oldest to latest") {
        $temp1 = " ORDER BY estimation_date DESC";
    }
    $temp2 = "";
    if ($_POST['tapp_filter2'] == "All conditions") {
        $temp2 = "";
    } else if ($_POST['tapp_filter2'] == "In progress") {
        $temp2 = " AND cond = 'IN PROGRESS'";
    } else if ($_POST['tapp_filter2'] == "Done") {
        $temp2 = " AND cond = 'DONE'";
    } else if ($_POST['tapp_filter2'] == "Suspended") {
        $temp2 = " AND cond = 'SUSPENDED'";
    }

    $stmt = $db->prepare($temp . $temp2 . $temp1);
    $stmt->execute($values);

    // Parameters for keeping active search filter
    $filterForm = $doc->getElementById($_POST['tapp_filter1']);
    $filterForm->setAttribute('selected', 'True');
    $filterForm = $doc->getElementById($_POST['tapp_filter2']);
    $filterForm->setAttribute('selected', 'True');

    $table = $doc->getElementById('appointment_search_results');

    $tableRow = $doc->createElement('tr');
    $tableCol = $doc->createElement('th', 'Title');
    $tableRow->appendChild($tableCol);
    $tableCol = $doc->createElement('th', 'Time spent [h]');
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

    // Displays all service appointment's data in a table
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

        // Creates form to change service appointment's values
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

        setElement( $doc, 'input', '', 'time_spent', 'time_spent', 'number', $row['time_spent'], $div, NULL);
        setElement( $doc, 'input', '', 'est_date', 'est_date', 'date', $row['estimation_date'], $div, NULL);

        $values = [
            'id' => $row['id_appointment'],
        ];
        $stmt_tmp = $db->prepare("SELECT id_appointment, parent_ticket, cond FROM appointment where id_appointment = :id");
        $stmt_tmp->execute($values);

        foreach ($stmt_tmp as $tmp) {
            $combox = $doc->createElement('select');
            $combox->setAttribute('name', 'new_cond');
            $combox->setAttribute('id', 'cond_app');
            addOption($doc, $combox, $tmp['cond'], 'IN PROGRESS');
            addOption($doc, $combox, $tmp['cond'], 'DONE');
            addOption($doc, $combox, $tmp['cond'], 'SUSPENDED');
            $div->appendChild($combox);

            // Parameters for keeping active search filter
            setElement( $doc, 'input', '', NULL, 'parent_ticket', 'hidden', $tmp['parent_ticket'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);
            // Creates buttons to set data changes or open service appointment's details
            setElement( $doc, 'button', 'Set', 'set_tapp_btn', 'set_tapp', 'submit', $tmp['id_appointment'], $div, NULL);
            setElement( $doc, 'button', 'Show more', 'show_btn', 'show_tapp', 'submit', $tmp['id_appointment'], $div, NULL);

        }
        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);
    }
    echo $doc->saveHTML();
    return NULL;
}

// Function displays service appointment's details to technician
function listAppDetails($db, $file)
{
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    // Access rights check
    if($_SESSION['access_type'] == 'TECHNICIAN'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    $doc->getElementById('th_ass')->nodeValue = 'Author';
    $form = $doc->getElementById('get_back_btn');
    $form->setAttribute('name', 'search_tapp');

    // Parameters for keeping active search filter
    $form = $doc->getElementById('get_back');
    setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);

    // Parameters for keeping active search filter
    $form = $doc->getElementById('show_appointment_from_ticket');
    setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);
    setElement( $doc, 'button', 'Show Ticket', 'ticket_from_tapp_btn', 'open_ticket_tapp', 'submit', $_POST['show_tapp'], $form, NULL);

    $values = [
        'id' => $_POST['show_tapp'],
    ];
    $stmt = $db->prepare("SELECT id_appointment, title, author, assignee, descript, estimation_date, cond, time_spent, parent_ticket FROM appointment where id_appointment = :id");
    $stmt->execute($values);

    // Displays all service appointment's data in a table
    foreach ($stmt as $row) {
        $text = $doc->getElementById('appointment_title_a');
        $text->nodeValue = $row['title'];

        $table = $doc->getElementById('appointment_info_table');
        $table->setAttribute('id', 'appointment_info_table_tech');
        $doc->getElementById('auth')->setAttribute('id', 'auth_nonvis');

        // Creates form to change service appointment's data
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

        $values = [
            'id' => $row['id_appointment'],
        ];
        $stmt_tmp = $db->prepare("SELECT id_appointment, parent_ticket, cond FROM appointment where id_appointment = :id");
        $stmt_tmp->execute($values);

        // Drop list for conditions
        foreach ($stmt_tmp as $tmp) {
            $combox = $doc->createElement('select');
            $combox->setAttribute('name', 'new_cond');
            $combox->setAttribute('id', 'cond_app_tech');
            addOption($doc, $combox, $tmp['cond'], 'IN PROGRESS');
            addOption($doc, $combox, $tmp['cond'], 'DONE');
            addOption($doc, $combox, $tmp['cond'], 'SUSPENDED');
            $div->appendChild($combox);

            // Parameters for keeping active search filter
            setElement( $doc, 'input', '', NULL, 'parent_ticket', 'hidden', $tmp['parent_ticket'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);
        }
        // Input fields for values
        setElement( $doc, 'input', '', 'est_date_tech', 'est_date', 'date', $row['estimation_date'], $div, NULL);
        setElement( $doc, 'input', '', 'time_spent_tech', 'time_spent', 'number', $row['time_spent'], $div, NULL);
        setElement( $doc, 'button', 'Set', 'set_tapp_btn_tech', 'set_tapp_detail', 'submit', $tmp['id_appointment'], $div, NULL);

        $tableRow->appendChild($tableCol);
        $table->appendChild($tableRow);

        $text = $doc->getElementById('appointment_desc_a');
        $text->nodeValue = $row['descript'];
    }

    $values = [
        'parent' => $_POST['show_tapp'],
    ];
    $stmt = $db->prepare("SELECT id_comment, content, author, parent_appointment, date_add FROM comment where parent_appointment = :parent");
    $stmt->execute($values);

    $div = $doc->getElementById('appointment_comments_div');

    // Displays all service appointment's comments
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

        // If current user is comment's author, gives them ability to remove the comment
        if ($row['author'] == $_SESSION['username']) {

            $form = $doc->createElement('form');
            $form->setAttribute('id', 'form_tapp_remove_comment');
            $form->setAttribute('action', 'main.php');
            $form->setAttribute('method', 'post');
            $form->setAttribute('name', 'tapp_remove_comment');

            // Parameters for keeping active search filter
            setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
            setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);
            // Sets values to remove comment
            setElement( $doc, 'input', '', NULL, 'id_comment', 'hidden', $row['id_comment'], $form, NULL);
            setElement( $doc, 'button', 'Remove', 'remove_comment_tapp_btn', 'remove_tapp_comment', 'submit', $_POST['show_tapp'], $form, NULL);

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

    setElement( $doc, 'input', '', NULL, 'appointment_comment_author', 'hidden', $_SESSION['username'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'appointment_comment_date', 'hidden', date('Y-m-d H:i:s', time()), $form, NULL);

    // Parameters for keeping active search filter
    setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);

    // Sets button value to create comment
    $button = $doc->getElementById('add_appointment_comment_btn');
    $button->setAttribute('name', 'add_tap_comment');
    $button->setAttribute('value', $_POST['show_tapp']);

    echo $doc->saveHTML();

    return NULL;
}

// Function displays ticket details to technician
function listAppTicket($db, $file){
    $html = file_get_contents($file);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    // Access rights check
    if($_SESSION['access_type'] == 'TECHNICIAN'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

    // Parameters for keeping active search filter
    $button = $doc->getElementById('get_back_btn');
    $button->setAttribute('name', 'show_tapp');
    $button->setAttribute('value', $_POST['open_ticket_tapp']);

    // Parameters for keeping active search filter
    $form = $doc->getElementById('get_back');
    setElement( $doc, 'input', '', NULL, 'tapp_filter1', 'hidden', $_POST['tapp_filter1'], $form, NULL);
    setElement( $doc, 'input', '', NULL, 'tapp_filter2', 'hidden', $_POST['tapp_filter2'], $form, NULL);

    // Searches for selected service appointment in database
    $values = [
        'id' => $_POST['open_ticket_tapp'],
    ];
    $temp = $db->prepare("SELECT id_appointment, parent_ticket FROM appointment where id_appointment = :id");
    $temp->execute($values);

    foreach ($temp as $line){
        $ticket_id = $line['parent_ticket'];
    }

    // Searches for linked ticket in database
    $stmt = $db->query("SELECT id_ticket,author title, category, descript, cond, author, date_add, image FROM ticket where id_ticket = '" . $ticket_id . "'");

    foreach ($stmt as $row){
        $text = $doc->getElementById('ticket_title_a');
        $text->nodeValue = $row['title'];

        $text = $doc->getElementById('ticket_desc_a');
        $text->nodeValue = $row['descript'];

        // Displays image if there is any in ticket
        if($row['image'] != NULL){
            $img = $doc->createElement('img');
            $img->setAttribute('src', './images/' . $row['image']);
            $img->setAttribute('alt', 'Ticket image');
            $img->setAttribute('width', '700');
            $doc->getElementById('ticket_id')->appendChild($img);
        }

        // Displays all data in a table
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

    // Searches for ticket's comments
    $stmt = $db->query("SELECT id_comment, content, author, parent_ticket, date_add FROM comment where parent_ticket = '" . $ticket_id . "'");
    $div = $doc->getElementById('ticket_comments_div');

    // Displays all comments
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

    // Removes ability to add comments
    $form = $doc->getElementById('add_comment_form');
    $form->setAttribute('id','auth_nonvis');

    echo $doc->saveHTML();

    return NULL;
}

// Function returns data back to input field, if registration was incorrect
function returnData($doc, $values, $msg, $can_login)
{
    $doc->getElementById('info_msg')->nodeValue = $msg;
    if ($can_login) {
        $a = $doc->createElement('a', ' Do u want to log in ?');
        $a->setAttribute('id', 'register_login');
        $a->setAttribute('href', 'login.html');
        $doc->getElementById('info_msg')->appendChild($a);
    }
    $doc->getElementById('name_id')->setAttribute('value', $values['first_name']);
    $doc->getElementById('surename_id')->setAttribute('value', $values['last_name']);
    $doc->getElementById('date_id')->setAttribute('value', $values['date_of_birth']);
    $doc->getElementById('uaddress_id')->setAttribute('value', $values['residence']);
    $doc->getElementById('uemail_id')->setAttribute('value', $values['email']);

    return NULL;
}

// Function creates ticket from user's report
function reportProblem($db)
{
    $html = file_get_contents('report.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    // Access rights check
    if($_SESSION['access_type'] == 'USER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }

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
    // Sets path to null, if no image was uploaded
    if ($_FILES['uploadfile']['type'] == ''){
        $values['image'] = NULL;
    }
    else{
        chmod($folder, 0644);
    }

    $stmt = $db->prepare("INSERT INTO ticket (title, address, category, descript, cond, author, date_add, image) VALUES (:title, :address, :category, :descript, :cond, :author, :date_add, :image)");
    $stmt->execute($values);
    $msg = "Problem was reported";
    $descBox = $doc->getElementById('info_msg')->nodeValue = $msg;
    echo $doc->saveHTML();
    return NULL;
}

/* -- Part Three: Responses to button input -- */

// If-else-if statement for $_POST values, which represent buttons in html pages
if (isset($_POST['login'])) {
    $html = file_get_contents('login.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $values = [
        'email' => $_POST['uemail_login'],
    ];
    $stmt = $db->prepare("SELECT pwd, access_type FROM user WHERE email = :email");
    $stmt->execute($values);

    $descBox = $doc->getElementById('info_msg');
    $fragment = $doc->createDocumentFragment();
    //$fragment->appendXML('This is text');
    $descBox->nodeValue = 'Incorrect username or password';
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if(password_verify($_POST['pwd_login'], $row['pwd'])) {
            $_SESSION['username'] = $_POST['uemail_login'];
            if ($row['access_type'] == 'ADMIN') {
                setcookie('access_type', 'ADMIN', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'ADMIN';
                $_POST['admin_filter'] = 'All Users';
                listUsers($db, 'admin.html');
            } elseif ($row['access_type'] == 'MANAGER') {
                setcookie('access_type', 'MANAGER', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'MANAGER';
                $_POST['admin_filter'] = 'TECHNICIAN';
                listUsers($db, 'manager.html');
            } elseif ($row['access_type'] == 'TECHNICIAN') {
                setcookie('access_type', 'TECHNICIAN', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'TECHNICIAN';
                $_POST['tapp_filter1'] = 'All my appoinments';
                $_POST['tapp_filter2'] = 'All condition';
                listAppTech($db, 'technic.html');
            } elseif ($row['access_type'] == 'USER') {
                setcookie('access_type', 'USER', time() + 3600);
                setcookie('username', $_POST['uemail_login'], time() + 3600);
                $_SESSION['access_type'] = 'USER';
                openUser($db, 'user.html');
            } else {
                $descBox->nodeValue = 'Incorrect username or password';
                $descBox->appendChild($fragment);
                echo $doc->saveHTML();
            }
        } else {
            $descBox->appendChild($fragment);
            echo $doc->saveHTML();
        }
    } else {
        //$descBox->appendChild($fragment);
        echo $doc->saveHTML();
    }
} else if (isset($_POST['logout'])) {
    if (isset($_COOKIE['access_type'])) {
        setcookie('access_type', '', time() - 3600);
    }
    if (isset($_COOKIE['username'])) {
        setcookie('username', '', time() - 3600);
    }
    session_destroy();
    header('Location: index.html');
} else if (isset($_POST['register_submit'])) {
    $values = [
        'email' => $_POST['uemail_register'],
    ];
    $stmt = $db->prepare("SELECT pwd, access_type FROM user WHERE email = :email");
    $stmt->execute($values);

    $html = file_get_contents('register.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    $hash_pwd = password_hash($_POST['upwd_register'], PASSWORD_DEFAULT);
    $values = [
        'email' => $_POST['uemail_register'],
        'pwd' => $hash_pwd,
        'first_name' => $_POST['ufirstname_register'],
        'last_name' => $_POST['ulastname_register'],
        'date_of_birth' => $_POST['udate_register'],
        'residence' => $_POST['uaddress_register'],
        'access_type' => $_POST['utype_register'],
    ];
    if($_POST['upwd_register'] != $_POST['upwdconf_register']) {
        $msg = 'Password does not match';
        returnData($doc, $values, $msg, false);
        echo $doc->saveHTML();
    } else {
        if ($stmt->rowCount() == 1) {
            $msg = 'User already exists, please choose another email';
            returnData($doc, $values, $msg, true);
        } else {
            $stmt = $db->prepare("INSERT INTO user (email, pwd, first_name, last_name, date_of_birth, residence, access_type) VALUES (:email, :pwd, :first_name, :last_name, :date_of_birth, :residence, :access_type)");
            $stmt->execute($values);
            $descBox = $doc->getElementById('info_msg')->nodeValue = 'Nice, you are registered';
        }
        if($_POST['utype_register'] == 'MANAGER'){
            $element = $doc->getElementById('reg_back_btn');
            $element->setAttribute('name', "admin_search");

            $descBox = $doc->getElementById('register_header')->nodeValue = 'Register City Manager';
            $descBox = $doc->getElementById('info_msg')->nodeValue = 'Manager has been registered!';


            $form = $doc->getElementById('form_register_back');
            setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);
            echo $doc->saveHTML();
        }
        else if($_POST['utype_register'] == 'TECHNICIAN'){
            $element = $doc->getElementById('reg_back_btn');
            $element->setAttribute('name', "load_cityman");

            $descBox = $doc->getElementById('register_header')->nodeValue = 'Register Service Technician';
            $descBox = $doc->getElementById('info_msg')->nodeValue = 'Technician has been registered!';

            $form = $doc->getElementById('form_register_back');
            if(isset($_POST['admin_filter']))
                setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);
            echo $doc->saveHTML();
        }
        else{
            $html = file_get_contents('login.html');
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($html);

            $text = $doc->getElementById('info_msg');
            $text->nodeValue = 'Congratulation, you are registered';

            echo $doc->saveHTML();
        }
    }

} else if (isset($_POST['search_tapp'])) {
    listAppTech($db, 'technic.html');
} else if (isset($_POST['submit_problem'])) {
    reportProblem($db);
} else if (isset($_POST['remove'])) {
    $stmt = $db->query("SET FOREIGN_KEY_CHECKS=0;");
    $values = [
        'email' => $_POST['remove'],
    ];
    $stmt = $db->prepare("UPDATE ticket SET author = '[DELETED ACCOUNT]' where author = :email");
    $stmt->execute($values);
    $values = [
        'email' => $_POST['remove'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET author = '[DELETED ACCOUNT]' where author = :email");
    $stmt->execute($values);
    $values = [
        'email' => $_POST['remove'],
    ];
    $stmt = $db->prepare("UPDATE comment SET author = '[DELETED ACCOUNT]' where author = :email");
    $stmt->execute($values);
    $values = [
        'email' => $_POST['remove'],
    ];
    $stmt = $db->prepare("DELETE FROM user where email = :email");
    $stmt->execute($values);
    $stmt = $db->query("SET FOREIGN_KEY_CHECKS=1;");

    $_POST['admin_filter'] = $_POST['filter_status'];
    if(isset($_POST['admin_remove'])){
        listUsers($db, 'admin.html');
    }
    else{
        listUsers($db, 'manager.html');
    }
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

    $form = $doc->getElementById('form_register');
    setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);

    $form = $doc->getElementById('form_register_back');
    setElement( $doc, 'input', '', NULL, 'admin_filter', 'hidden', $_POST['admin_filter'], $form, NULL);

    $element = $doc->getElementById('reg_back_btn');
    $element->setAttribute('name', "admin_search");

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

    $element = $doc->getElementById('reg_back_btn');
    $element->setAttribute('name', "load_cityman");

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
    $values = [
        'cond' => $_POST['new_cond'],
        'id' => $_POST['set_cond'],
    ];
    $stmt = $db->prepare("UPDATE ticket SET cond = :cond where id_ticket = :id");
    $stmt->execute($values);
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
    $values = [
        'assignee' => $_POST['new_assignee'],
        'id' => $_POST['set_assignee'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET assignee = :assignee where id_appointment = :id");
    $stmt->execute($values);

    listAppointmentsMgr($db, 'list_serviceapp.html');
} else if (isset($_POST['set_assignee_detail'])) {
    $_POST['open_appointment_mgr'] = $_POST['set_assignee_detail'];
    $values = [
        'assignee' => $_POST['new_assignee'],
        'id' => $_POST['set_assignee_detail'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET assignee = :assignee where id_appointment = :id");
    $stmt->execute($values);
    if (!isset($_POST['appointments_assignee_filter']) || $_POST['appointments_assignee_filter'] == "") {
        $_POST['appointments_assignee_filter'] = "All Assignees";
    }
    if (!isset($_POST['appointments_cond_filter']) || $_POST['appointments_cond_filter'] == "") {
        $_POST['appointments_cond_filter'] = "All Conditions";
    }
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['set_cond_detail'])) {
    $_POST['open_ticket_mgr'] = $_POST['set_cond_detail'];
    $values = [
        'cond' => $_POST['new_cond'],
        'id' => $_POST['set_cond_detail']
    ];
    $stmt = $db->prepare("UPDATE ticket SET cond = :cond where id_ticket = :id");
    $stmt->execute($values);
    if (!isset($_POST['ticket_type_filter']) || $_POST['ticket_type_filter'] == "") {
        $_POST['ticket_type_filter'] = "All Types";
    }
    if (!isset($_POST['ticket_cond_filter']) || $_POST['ticket_cond_filter'] == "") {
        $_POST['ticket_cond_filter'] = "All Conditions";
    }
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['add_ticket_comment'])) {
    $values = [
        'content' => $_POST['ticket_comment_content'],
        'author' => $_POST['ticket_comment_author'],
        'parent_t' => $_POST['add_ticket_comment'],
        'parent_app' => NULL,
        'date' => $_POST['ticket_comment_date'],
    ];
    $stmt = $db->prepare("INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES (:content, :author, :parent_t, :parent_app, :date)");
    $stmt->execute($values);
    $_POST['open_ticket_mgr'] = $_POST['add_ticket_comment'];
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['create_appointment'])) {
    openCreationForm($db, 'create_serviceapp.html');
} else if (isset($_POST['open_appointment_mgr'])) {
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['remove_comment_ticket'])) {
    $values = [
        'id' => $_POST['id_comment'],
    ];
    $stmt = $db->prepare("DELETE FROM comment WHERE id_comment = :id");
    $stmt->execute($values);
    $_POST['open_ticket_mgr'] = $_POST['remove_comment_ticket'];
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['remove_appointment_comment'])) {
    $values = [
        'id' => $_POST['id_comment'],
    ];
    $stmt = $db->prepare("DELETE FROM comment WHERE id_comment = :id");
    $stmt->execute($values);
    $_POST['open_appointment_mgr'] = $_POST['remove_appointment_comment'];
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['add_appointment_comment'])) {
    $values = [
        'content' => $_POST['appointment_comment_content'],
        'author' => $_POST['appointment_comment_author'],
        'parent_t' => NULL,
        'parent_app' => $_POST['add_appointment_comment'],
        'date' => $_POST['appointment_comment_date'],
    ];
    $stmt = $db->prepare("INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES (:content, :author, :parent_t, :parent_app, :date)");
    $stmt->execute($values);
    $_POST['open_appointment_mgr'] = $_POST['add_appointment_comment'];
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_appointment_from_ticket_mgr'])) {
    $values = [
        'id' => $_POST['open_appointment_from_ticket_mgr'],
    ];
    $stmt = $db->prepare("SELECT id_appointment, parent_ticket FROM appointment WHERE id_appointment = :id");
    $stmt->execute($values);
    foreach ($stmt as $row){
        $_POST['open_ticket_mgr'] = $row['parent_ticket'];
    }
    $_POST['open_appointment_mgr'] = $_POST['open_appointment_from_ticket_mgr'];
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_ticket_from_appointment_mgr'])) {
    $values = [
        'id' => $_POST['open_ticket_from_appointment_mgr'],
    ];
    $stmt = $db->prepare("SELECT id_appointment, parent_ticket FROM appointment WHERE id_appointment = :id");
    $stmt->execute($values);
    foreach ($stmt as $row){
        $_POST['open_ticket_mgr'] = $row['parent_ticket'];
    }
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if (isset($_POST['load_user'])) {
    openUser($db, 'user.html');
} else if (isset($_POST['back_to_user'])) {
    openUser($db, 'user.html');
} else if(isset($_POST['get_back_to_ticket'])){
    $_POST['open_ticket_mgr'] = $_POST['get_back_to_ticket'];
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if(isset($_POST['create_service_submit'])){
    $values = [
        'author' => $_SESSION['username'],
        'assignee' => $_POST['tech_id'],
        'title' => $_POST['title_create'],
        'descript' => $_POST['descript_create'],
        'cond' => 'IN PROGRESS',
        'time_spent' => '0',
        'parent_ticket' => $_POST['create_service_submit'],
    ];
    $stmt = $db->prepare("INSERT INTO appointment(author, assignee, title, descript, cond, time_spent, parent_ticket) VALUES (:author, :assignee, :title, :descript, :cond, :time_spent, :parent_ticket)");
    $stmt->execute($values);
    $values = [
        'parent_ticket' => $_POST['create_service_submit'],
    ];
    $stmt = $db->prepare("UPDATE ticket SET cond = 'IN PROGRESS' where id_ticket = :parent_ticket");
    $stmt->execute($values);
    $_POST['appointments_assignee_filter'] = "All Assignees";
    $_POST['appointments_cond_filter'] = "All Conditions";
    $values = [
        'parent_ticket' => $_POST['create_service_submit'],
    ];
    $stmt = $db->prepare("SELECT id_appointment FROM appointment WHERE parent_ticket = :parent_ticket");
    $stmt->execute($values);
    foreach ($stmt as $row){
        $_POST['open_appointment_mgr'] = $row['id_appointment'];
    }
    openAppointmentDetailsMgr($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_ticket_mgr'])) {
    openTicketDetailsMgr($db, 'ticket_detail.html');
} else if(isset($_POST['reg_get_back'])){
    header('Location: index.html');
} else if (isset($_POST['set_tapp'])) {
    $values = [
        'time_spent' => $_POST['time_spent'],
        'id' => $_POST['set_tapp'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET time_spent = :time_spent where id_appointment =  :id");
    $stmt->execute($values);
    $values = [
        'estimation_date' => $_POST['est_date'],
        'id' => $_POST['set_tapp'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET estimation_date = :estimation_date where id_appointment = :id");
    $stmt->execute($values);
    $values = [
        'cond' => $_POST['new_cond'],
        'id' => $_POST['set_tapp'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET cond = :cond where id_appointment = :id");
    $stmt->execute($values);
    listAppTech($db, 'technic.html');
} else if (isset($_POST['show_tapp'])) {
    listAppDetails($db, 'serviceapp_detail.html');
} else if (isset($_POST['add_tap_comment'])) {
    $values = [
        'content' => $_POST['appointment_comment_content'],
        'author' => $_POST['appointment_comment_author'],
        'parent_t' => NULL,
        'parent_app' => $_POST['add_tap_comment'],
        'date' => $_POST['appointment_comment_date'],
    ];
    $stmt = $db->prepare("INSERT INTO comment(content, author, parent_ticket, parent_appointment, date_add) VALUES (:content, :author, :parent_t, :parent_app, :date)");
    $stmt->execute($values);
    $_POST['show_tapp'] = $_POST['add_tap_comment'];
    listAppDetails($db, 'serviceapp_detail.html');
} else if (isset($_POST['remove_tapp_comment'])) {
    $values = [
        'id' => $_POST['id_comment'],
    ];
    $stmt = $db->prepare("DELETE FROM comment WHERE id_comment = :id");
    $stmt->execute($values);
    $_POST['show_tapp'] = $_POST['remove_tapp_comment'];
    listAppDetails($db, 'serviceapp_detail.html');
} else if(isset($_POST['set_tapp_detail'])){
    $values = [
        'time_spent' => $_POST['time_spent'],
        'id' => $_POST['set_tapp_detail'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET time_spent = :time_spent where id_appointment =  :id");
    $stmt->execute($values);
    $values = [
        'estimation_date' => $_POST['est_date'],
        'id' => $_POST['set_tapp_detail'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET estimation_date = :estimation_date where id_appointment = :id");
    $stmt->execute($values);
    $values = [
        'cond' => $_POST['new_cond'],
        'id' => $_POST['set_tapp_detail'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET cond = :cond where id_appointment = :id");
    $stmt->execute($values);
    $_POST['show_tapp'] = $_POST['set_tapp_detail'];
    listAppDetails($db, 'serviceapp_detail.html');
} else if (isset($_POST['open_ticket_tapp'])) {
    listAppTicket($db, 'ticket_detail.html');
} else if (isset($_POST['report_port'])){
    $html = file_get_contents('report.html');
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);

    if($_SESSION['access_type'] == 'USER'){
        $meta = $doc->getElementById('redirect');
        $meta->setAttribute('content', '3800;url=main.php');
    }
    echo $doc->saveHTML();
} else if (isset($_POST['edit_profile'])){
    editUser($db, 'edit_profile.html');
} else if (isset($_POST['edit_submit'])){
    if($_POST['upwd_edit'] != $_POST['upwdconf_edit']){
        $_POST['edit_submit'] = 'None';
        editUser($db, 'edit_profile.html');
    }
    else{
        $values = [
            'first_name' => $_POST['ufirstname_edit'],
            'last_name' => $_POST['ulastname_edit'],
            'date' => $_POST['udate_edit'],
            'residence' => $_POST['uaddress_edit'],
            'email' => $_POST['edit_submit'],
        ];
        $stmt = $db->prepare("UPDATE user SET first_name = :first_name, last_name = :last_name, date_of_birth = :date, residence = :residence WHERE email = :email");
        $stmt->execute($values);
        if($_POST['upwd_edit'] != ''){
            $hash_pwd = password_hash($_POST['upwd_edit'], PASSWORD_DEFAULT);
            $values = [
                'email' => $_POST['edit_submit'],
            ];
            $stmt = $db->prepare("UPDATE user SET pwd = '" . $hash_pwd . "' WHERE email = :email");
            $stmt->execute($values);
        }
        editUser($db, 'edit_profile.html');
    }
} else if (isset($_POST['delete_profile'])){
    $stmt = $db->query("SET FOREIGN_KEY_CHECKS=0;");
    $values = [
        'email' => $_SESSION['username'],
    ];
    $stmt = $db->prepare("UPDATE ticket SET author = '[DELETED ACCOUNT]' where author = :email");
    $stmt->execute($values);
    $values = [
        'email' => $_SESSION['username'],
    ];
    $stmt = $db->prepare("UPDATE appointment SET author = '[DELETED ACCOUNT]' where author = :email");
    $stmt->execute($values);
    $values = [
        'email' => $_SESSION['username'],
    ];
    $stmt = $db->prepare("UPDATE comment SET author = '[DELETED ACCOUNT]' where author = :email");
    $stmt->execute($values);
    $values = [
        'email' => $_SESSION['username'],
    ];
    $stmt = $db->prepare("DELETE FROM user where email = :email");
    $stmt->execute($values);
    $stmt = $db->query("SET FOREIGN_KEY_CHECKS=1;");
    session_destroy();
    header('Location: index.html');
} else if (isset($_POST['admin_tickets'])) {
    if (!isset($_POST['ticket_type_filter'])) {
        $_POST['ticket_type_filter'] = "All Types";
    }
    if (!isset($_POST['ticket_cond_filter'])) {
        $_POST['ticket_cond_filter'] = "All Conditions";
    }
    listTicketsAdmin($db, 'list_tickets.html');
} else if (isset($_POST['ticket_delete'])){
    $values = [
        'id' => $_POST['ticket_delete'],
    ];
    $stmt = $db->prepare("DELETE FROM ticket where id_ticket = :id");
    $stmt->execute($values);
    listTicketsAdmin($db, 'list_tickets.html');
} else{
    var_dump($_POST);
    if (isset($_COOKIE['access_type'])) {
        setcookie('access_type', '', time() - 3600);
    }
    if (isset($_COOKIE['username'])) {
        setcookie('username', '', time() - 3600);
    }
    session_destroy();
    header('Location: index.html');
}
?>