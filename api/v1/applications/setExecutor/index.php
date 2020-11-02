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
if (!isset($_POST['id_user']) || empty($_POST['id_user'])) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'id_user is empty'
    )));
}
$key = trim(stripslashes(htmlspecialchars($_POST['key'])));
$id_executor = trim(stripslashes(htmlspecialchars($_POST['id_user'])));

$query = "select user_type from users where id_user = $id_user";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$user_type = mysqli_fetch_array($result)['user_type'];

if ($user_type === 2) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

$query = "select full_name, user_post, user_type from users where id_user = $id_executor";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$executor_result = mysqli_fetch_array($result);
$executor_type = $executor_result['user_type'];
$executor = array(
    'full_name' => $executor_result['full_name'],
    'user_post' => $executor_result['user_post']
);

if ($user_type >= $executor_type) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Permission denied'
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

if (mysqli_num_rows($result) > 0) {
    $application_data = mysqli_fetch_array($result);
    $id_application = $application_data['id_application'];
    $mail = $application_data['mail'];
    $subscribe = intval($application_data['subscribe']);

    $query = "select id_application
              from processing
              where id_application = $id_application";
    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    if (mysqli_num_rows($result) > 0) {
        $query = "update processing
                  set id_user = $id_executor
                  where id_application = $id_application";
    
        $result = mysqli_query($connection, $query) or
                  exit(json_encode(array(
                      'status' => 'error',
                      'message' => 'Data base error',
                      'mysql_error' => mysqli_error($connection)
                  )));

        send_message($connection, $executor, $id_application, $id_user, $key, $mail, $subscribe);

        exit(json_encode(array(
            'status' => 'success'
        )));
    }
    else {
        $query = "insert into processing
                  (id_user, id_operator, id_application)
                  values ($id_executor, $id_user, $id_application)";
    
        $result = mysqli_query($connection, $query) or
                  exit(json_encode(array(
                      'status' => 'error',
                      'message' => 'Data base error',
                      'mysql_error' => mysqli_error($connection)
                  )));

        send_message($connection, $executor, $id_application, $id_user, $key, $mail, $subscribe);
    
        exit(json_encode(array(
            'status' => 'success'
        )));
    }
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Application not exist'
    )));
}

function send_message($connection, $executor, $id_application, $id_user, $key, $mail, $subscribe) {
    $message = "Исполнителем заявки назначен(а) " . $executor['full_name'] . " (" . $executor['user_post'] . ").";
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

        $mail_html = substr_replace($mail_html, $executor['full_name'], strpos($mail_html, "[owner]"), 7);
        $mail_html = substr_replace($mail_html, $key, strpos($mail_html, "[key]"), 5);
        $mail_html = substr_replace($mail_html, $message, strpos($mail_html, "[message]"), 9);
        mail($mail,
            "Заявка #$key",
            $mail_html,
            "From: Тех. поддержка МГТУ \"Станкин\" <bot@help.stankin.ru>\r\nContent-Type: text/html; charset=UTF-8");
    }
}