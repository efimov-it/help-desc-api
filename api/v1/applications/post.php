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

if ( strlen ( $phone_number ) != 18) {
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

$query ="insert into applications
         (full_name, phone_type, phone_number, mail, office, unit, dept, application_text)
         values ('$full_name', $phone_type, '$phone_number', '$mail', '$office', '$unit', '$dept', '$application_text')";

$result = mysqli_query($connection, $query) or exit (json_encode ( array (
                                                        'status' => 'error',
                                                        'message' => 'data base error',
                                                        'mysql_error' => mysqli_error($connection)
)));

if($result) {
    echo ( json_encode ( array (
                'status' => 'success'
    )));
}
mysqli_close($connection);