<?php
header('Access-Control-Allow-Headers: *');
require_once '../../get_token.php';
require_once '../../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user === false) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

if (!isset($_POST['key']) || empty($_POST['key'])) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'key is empty'
    )));
}

$key = trim(stripslashes(htmlspecialchars($_POST['key'])));

$query = "select *
          from applications right join processing
            on applications.id_application = processing.id_application
          where application_code = $key";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Application has already been processing'
    )));
}

$query = "select id_application, mail, subscribe
          from applications
          where application_code = $key";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));
if (mysqli_num_rows($result) === 0) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Application not exist'
    )));
}
$application_data = mysqli_fetch_array($result);
$id_application = $application_data['id_application'];
$mail = $application_data['mail'];
$subscribe = intval($application_data['subscribe']);

$query = "insert into processing
          (id_user, id_application, id_operator)
          values ($id_user, $id_application, $id_user)";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$query = "select full_name, user_post from users where id_user = $id_user";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$user_data = mysqli_fetch_array($result);
$full_name = $user_data['full_name'];
$user_post = $user_data['user_post'];

$message = "Работа по Вашей заявке была начата.";
$msg_query = "insert into application_message
              (id_application, id_user, message)
              values ($id_application, $id_user, '$message')";

$msg_result = mysqli_query($connection, $msg_query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

if ($subscribe === 1) {
    $mail_html = file_get_contents('../application_status_update.html');
    
    $mail_html = substr_replace($mail_html, $key, strpos($mail_html, "[key]"), 5);
    $mail_html = substr_replace($mail_html, $message."<br />Исполнитель: $full_name ($user_post).", strpos($mail_html, "[message]"), 9);
    mail($mail,
        "Заявка #$key",
        $mail_html,
        "From: Тех. поддержка МГТУ \"Станкин\" <bot@help.stankin.ru>\r\nContent-Type: text/html; charset=UTF-8");
}

exit(json_encode(array(
    'status' => 'success'
)));