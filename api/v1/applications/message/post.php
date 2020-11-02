<?php
$ignore_unauth = true;
require_once '../../get_token.php';
require_once '../../check_auth.php';

if ($token) {
    $id_user = check_auth($token, $connection);
    
    if ($id_user == false) {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'Access denied'
        )));
    }
}

if (!isset($_POST['key']) || empty($_POST['key'])) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'key is empty'
    )));
}

if (!isset($_POST['message']) || empty($_POST['message'])) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'message is empty'
    )));
}

$key = trim(stripslashes(htmlspecialchars($_POST['key'])));
$message = trim(stripslashes(htmlspecialchars($_POST['message'])));

$query = "select id_application, mail
          from applications
          where application_code = $key";

$result = mysqli_query($connection, $query) or 
          exit(json_encode(array(
            'status' => 'error',
            'message' => 'Data base error',
            'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    $application_data = mysqli_fetch_array($result);
    $id_application = $application_data['id_application'];
    $mail = $application_data['mail'];

    $query = "insert into application_message
              (id_application, id_user, message)
              values ($id_application, " . ($id_user ? $id_user : "null") . ", '$message')";

    mysqli_query($connection, $query) or
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Data base error',
        'mysql_error' => mysqli_error($connection)
    )));
    
    $mail_html = file_get_contents('../application_status_update.html');

    $mail_html = substr_replace($mail_html, $key, strpos($mail_html, "[key]"), 5);
    $mail_html = substr_replace($mail_html, $message, strpos($mail_html, "[message]"), 9);
    mail($mail,
        "Заявка #$key",
        $mail_html,
        "From: Тех. поддержка МГТУ \"Станкин\" <bot@help.stankin.ru>\r\nContent-Type: text/html; charset=UTF-8");

    exit(json_encode(array(
        'status' => 'success'
    )));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Application not exist'
    )));
}