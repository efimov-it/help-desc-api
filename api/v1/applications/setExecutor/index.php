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

$query = "select user_type from users where id_user = $id_executor";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$executor_type = mysqli_fetch_array($result)['user_type'];

if ($user_type >= $executor_type) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Permission denied'
    )));
}

$query = "select id_application from applications where application_code = $key";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    $id_application = mysqli_fetch_array($result)['id_application'];

    $query = "insert into processing
              (id_user, id_operator, id_application)
              values ($id_executor, $id_user, $id_application)";

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

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