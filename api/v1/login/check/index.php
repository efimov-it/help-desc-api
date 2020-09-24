<?php
require_once '../../get_token.php';
require_once '../../check_auth.php';

if($id_user = check_auth($token, $connection)) {
    $query = "select user_type
              from users
              where id_user = $id_user";

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                'status' => 'error',
                'message' => 'Data base error',
                'mysql_error' => mysqli_error($connection)
              )));
    
    if (mysqli_num_rows($result) > 0) {
        $user_type = mysqli_fetch_array($result)[0];
        exit(json_encode(array(
            'status' => 'success',
            'data' => array(
                'user_type' => intval($user_type)
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