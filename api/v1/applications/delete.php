<?php
require_once '../get_token.php';
require_once '../check_auth.php';
require_once '../get_user_type.php';

$id_user = check_auth($token, $connection);


if ($id_user != false) {

    $delete_request_params = explode('&', file_get_contents('php://input'));
    $delete_params = array();

    foreach($delete_request_params as $param) {
        $exploded = explode('=', $param);
        $delete_params[$exploded[0]] = $exploded[1]; 
    }

    if (isset($delete_params['id'])) {
        $id = trim(htmlspecialchars(stripslashes($delete_params['id'])));
    }
    else {
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'id is empty'
        )));
    }
    
    $user_type = get_user_type($id_user, $connection);
    
    if ($user_type != 1) {

        $query = "select *
                  from applications
                  where id_application = $id";

        $result = mysqli_query($connection, $query) or
                  exit (json_encode(array(
                      'status' => 'error',
                      'message' => 'Data base error',
                      'mysql_error' => mysqli_error($connection)
                  )));

        if (mysqli_num_rows($result) == 1) {
            $query = "delete from applications
                      where id_application = $id";
    
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
                'message' => 'Application not exist'
            )));
        }
    }
    else {
        exit (json_encode(array(
            'status' => 'error',
            'message' => 'Permission denied'
        )));
    }
}
else {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}