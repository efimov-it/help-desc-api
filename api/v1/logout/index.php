<?php
require_once '../get_token.php';
require_once '../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user != false) {
    $query = "delete
              from auth
              where id_user = $id_user";

    $result = mysqli_query($connection, $query) or
                exit (json_encode(array(
                    'status' => 'error',
                    'message' => 'Data base error',
                    'mysql_error' => mysqli_error($connection)
                )));

    exit (json_encode(array(
        'status' => 'success'
    )));
}
else {
    exit(json_encode(array(
        'status' => 'success'
    )));
}