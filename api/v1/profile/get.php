<?php
require_once '../get_token.php';
require_once '../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user != false) {
    $query  = "select *
               from users
               where id_user = $id_user";
    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                'status' => 'error',
                'message' => 'Data base error',
                'mysql_error' => mysqli_error($connection)
              )));

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        exit(json_encode(array(
            'status' => 'success',
            'data' => array(
                'full_name' => $row['full_name'],
                'login' => $row['login'],
                'mail' => $row['mail'],
                'user_type' => intval($row['user_type']),
                'user_post' => $row['user_post']
            )
        )));
    }
    else {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'User not exist'
        )));
    }
}
else {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}