<?php

require_once '../db_connect.php';

if (isset($_POST['full_name'])) {
    $full_name = trim(htmlspecialchars(stripslashes($_POST['full_name'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'full_name is empty'
    )));
}

if (isset($_POST['phone_type'])) {
    $phone_type = trim(htmlspecialchars(stripslashes($_POST['phone_type'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'phone_type is empty'
    )));
}

if (isset($_POST['phone_number'])) {
    $phone_number = trim(htmlspecialchars(stripslashes($_POST['phone_number'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'phone_number is empty'
    )));
}

if (isset($_POST['mail'])) {
    $mail = trim(htmlspecialchars(stripslashes($_POST['mail'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'mail is empty'
    )));
}

if (isset($_POST['office'])) {
    $office = trim(htmlspecialchars(stripslashes($_POST['office'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'office is empty'
    )));
}

if (isset($_POST['unit'])) {
    $unit = trim(htmlspecialchars(stripslashes($_POST['unit'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'unit is empty'
    )));
}

if (isset($_POST['dept'])) {
    $dept = trim(htmlspecialchars(stripslashes($_POST['dept'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'dept is empty'
    )));
}

if (isset($_POST['application_text'])) {
    $application_text = trim(htmlspecialchars(stripslashes($_POST['application_text'])));
}
else {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'application_text is empty'
    )));
}

if ($phone_type < 0 || $phone_type > 1) {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'phone_type can be 0 or 1'
    )));
}

if ( strlen ( $phone_number ) > 18) {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'Invalid value of phone_number'
    )));
}

if (!strrpos($mail, '@') || !strrpos($mail, '.')) {
    exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'Invalid value of mail'
    )));
}

$application_code = time();

$query ="insert into applications
         (full_name, phone_type, phone_number, mail, office, unit, dept, application_text, application_code)
         values ('$full_name', $phone_type, '$phone_number', '$mail', '$office', '$unit', '$dept', '$application_text', '$application_code')";

$result = mysqli_query($connection, $query) or exit (json_encode ( array (
                                                        'status' => 'error',
                                                        'message' => 'Data base error',
                                                        'mysql_error' => mysqli_error($connection)
)));

if($result) {

    $mail_html = file_get_contents('./application_created_mail.html');

    $mail_html = substr_replace($mail_html, $full_name, strpos($mail_html, "[full_name]"), 11);
    $mail_html = substr_replace($mail_html, $application_code, strpos($mail_html, "[key]"), 5);
    mail($_GET['mail'],
         'Регистрация заявки на сайте тех. поддержки МГТУ "Станкин"',
         $mail_html,
         "From: Тех. поддержка МГТУ \"Станкин\" <bot@help.stankin.ru>\r\nContent-Type: text/html; charset=UTF-8");


    echo ( json_encode ( array (
                'status' => 'success',
                'data' => array(
                    'code' => $application_code
                )
    )));
}
mysqli_close($connection);