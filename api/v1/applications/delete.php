<?php
require_once '../get_token.php';
require_once '../check_auth.php';

$id_user = check_auth($token, $connection);


if ($id_user != false) {

    if (isset($_POST['id'])) {
        $id = trim(htmlspecialchars(stripslashes($_POST['id'])));
    }
    else {
        exit(json_encode(array(
            'status': 'error',
            'message': 'id is empty'
        )));
    }
    
    $query = "select user_type
              from users
              where id_user = $id_user";

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    if (mysqli_num_rows($result) == 1) {
        $user_type = mysqli_fetch_array($result)[0];

        if ($user_type != 1) {
            $query = 'delete from applications
                      where id_application = $id';

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
            exit (json_encode(array(
                'status' => 'error',
                'message' => 'Permission denied'
            )));
        }
    }
    else {
        exit (json_encode(array(
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