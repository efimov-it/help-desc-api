<?php

require_once '../../db_connect.php';

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

$query = "select id_application
          from applications
          where application_code = $key";

$result = mysqli_query($connection, $query) or 
          exit(json_encode(array(
            'status' => 'error',
            'message' => 'Data base error',
            'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    $id_application = mysqli_fetch_array($result)['id_application'];

    $query = "insert into application_message
              (id_application, message)
              values ($id_application, '$message')";

    mysqli_query($connection, $query) or
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