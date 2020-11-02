<?php
header('Access-Control-Allow-Headers: *');
require_once '../../get_token.php';
require_once '../../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user == false) {
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

if (!isset($_POST['message']) || empty($_POST['message'])) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'message is empty'
    )));
}

$key = trim(stripslashes(htmlspecialchars($_POST['key'])));
$message = trim(stripslashes(htmlspecialchars($_POST['message'])));

$query = "select user_type from users where id_user = $id_user";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$user_type = mysqli_fetch_array($result)['user_type'];

$query = "select id_completed
          from applications right join processing 
          on applications.id_application = processing.id_application
          right join completed
          on processing.id_processing = completed.id_processing
          where application_code = $key";

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if(mysqli_num_rows($result) > 0) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'The application has already been completed'
    )));
}

$query = "select processing.*, applications.mail, applications.subscribe
        from applications right join processing
        on applications.id_application = processing.id_application
        where application_code = $key";

$result = mysqli_query($connection, $query) or
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'Data base error',
            'mysql_error' => mysqli_error($connection)
        )));

if(mysqli_num_rows($result) > 0) {
    $processing = mysqli_fetch_array($result);

    if ($user_type == 2) {
        if ($id_user != $processing['id_user']) {
            exit (json_encode( array(
                'status' => 'error',
                'message' => 'Access denied'
            )));
        }
    }
    if ($user_type == 1) {
        if (!($id_user == $processing['id_user'] || $id_user == $processing['id_operator'])) {
            exit (json_encode( array(
                'status' => 'error',
                'message' => 'Access denied'
            )));
        }
    }

    $admin_mess = $user_type == 0 &&
                  $id_user != $processing['id_user'] &&
                  $id_user != $processing['id_operator'] ? '<b>От лица администратора системы:</b><br/>' : '';

    $query = "insert into completed
              (id_processing, result_text)
              values (".$processing['id_processing'].", '$admin_mess $message')";
    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    if (intval($processing['subscribe']) === 1) {
        send_mail($key, $processing['mail'], $admin_mess.$message);
    }

    exit(json_encode(array(
        'status' => 'success'
    )));
}
else {
    if ($user_type === 2) {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'Access denied'
        )));
    }
    else {
        $query = "select id_application, mail from applications where application_code = $key";
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
            $query = "insert into processing
                      (id_user, id_application, id_operator)
                      values ($id_user, $id_application, $id_user)";

            $result = mysqli_query($connection, $query) or
                      exit(json_encode(array(
                          'status' => 'error',
                          'message' => 'Data base error',
                          'mysql_error' => mysqli_error($connection)
                      )));

            $id_processing = mysqli_insert_id($connection);
            
            $message = "<i>Заявка завершена досрочно.</i><br />$message";
            $query = "insert into completed
                      (id_processing, result_text)
                      values ($id_processing, '$message')";

            $result = mysqli_query($connection, $query) or
                      exit(json_encode(array(
                          'status' => 'error',
                          'message' => 'Data base error',
                          'mysql_error' => mysqli_error($connection)
                      )));

            if (intval($processing['subscribe']) === 1) {
                send_mail($key, $mail, $message);
            }

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
    }
}

function send_mail ($key, $mail, $message) {
    $mail_html = file_get_contents('../application_status_update.html');

    $mail_html = substr_replace($mail_html, $key, strpos($mail_html, "[key]"), 5);
    $mail_html = substr_replace($mail_html, $message, strpos($mail_html, "[message]"), 9);
    mail($mail,
        "Заявка #$key",
        $mail_html,
        "From: Тех. поддержка МГТУ \"Станкин\" <bot@help.stankin.ru>\r\nContent-Type: text/html; charset=UTF-8");
}