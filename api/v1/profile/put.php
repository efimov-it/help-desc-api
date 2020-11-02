<?php

require_once '../get_token.php';
require_once '../check_auth.php';
require_once '../validation.php';

$id_user = check_auth($token, $connection);

if ($id_user != false) {
    $put_request_params = explode('&', urldecode(file_get_contents('php://input')));
    $put_params = array();

    foreach($put_request_params as $value) {
        $params = explode('=', $value);
        $put_params[$params[0]] = trim(htmlspecialchars(stripcslashes($params[1])));
    }

    $set_string = "";
    $empty_password = false;

    foreach($put_params as $column => $value) {
        if(isset($put_params[$column]) && !empty($put_params[$column])) {

            if ($column === 'password') {
                if ($put_params['password'] !== $put_params['repeat_password']) {
                    exit(json_encode(array(
                        'status' => 'error',
                        'message' => 'Passwords are not equal'
                    )));
                }
            }

            if ($column === 'repeat_password' && $empty_password) continue;

            $key = $column === 'post' ? 'user_post' : $column;
            validation($key, $value, $connection);
            if($column === 'password') {
                $value = md5($value);
            }
            if($column !== 'repeat_password') {
                $set_string .= ', '.$key.'=\''.$value.'\'';
            }
        }
        else {
            if ($column === 'password') {
                $empty_password = true;
            }
            else {
                exit(json_encode(array(
                    'status' => 'error',
                    'message' => $column.' is empty'
                )));
            }
        }
    }

    $query = "update users
              set " . substr($set_string, 2) . "
              where id_user = $id_user";

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection) . '\r\n' . $query
              )));
    
    exit(json_encode(array(
        'status' => 'success'
    )));

} else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}