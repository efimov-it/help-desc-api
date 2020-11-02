<?php

require_once '../get_token.php';
require_once '../check_auth.php';
require_once '../get_user_type.php';

$id_user = check_auth($token, $connection);

if ($id_user === false) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

$user_type = get_user_type($id_user, $connection);

if ($user_type !== 0) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Permission denied'
    )));
}

$delete_request_params = explode('&', urldecode(file_get_contents('php://input')));
$delete_params = array();

foreach($delete_request_params as $value) {
    $params = explode('=', $value);
    $delete_params[$params[0]] = $params[1];
}

if (isset($delete_params['id_user']) && !empty($delete_params['id_user'])) {
    $id_user = trim(htmlspecialchars(stripslashes($delete_params['id_user'])));
} else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'id_user is empty'
    )));
}

$query = "delete
          from users
          where id_user = $id_user";
          
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error'
          )));

exit(json_encode(array(
  'status' => 'success'
)));