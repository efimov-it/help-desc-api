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

$query = "select id_application
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

$id_application = mysqli_fetch_array($result)['id_application'];

$query = "insert into processing
          (id_user, id_application, id_operator)
          values ($id_user, $id_application, $id_user)";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

exit(json_encode(array(
    'status' => 'success'
)));