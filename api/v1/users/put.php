<?php

require_once '../get_token.php';
require_once '../check_auth.php';
require_once '../get_user_type.php';
require_once '../validation.php';

$id_user = check_auth($token, $connection);


if ($id_user != false) {
    $put_request_params = explode('&', urldecode(file_get_contents('php://input')));
    $put_params = array();

    foreach($put_request_params as $value) {
        $params = explode('=', $value);
        $put_params[$params[0]] = $params[1];
    }

    $user_type = get_user_type($id_user, $connection);

    $where;
    $set = '';

    if (isset($put_params['id_user']) && !empty($put_params['id_user'])) {
        $put_id_user = $put_params['id_user'];
    } else {
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'id_user is empty'
        )));
    }

    if ($user_type == 0) {
        $where = "id_user = $put_id_user";

        if (isset($put_params['user_type'])) {
            validation('user_type', $put_params['user_type'], $connection);
            $set .= "user_type = ".trim(htmlspecialchars(stripslashes($put_params['user_type']))).",";
        }
        
        if (isset($put_params['user_post']) && !empty($put_params['user_post'])) {
            validation('user_post', $put_params['user_post'], $connection);
            $set .= "user_post = '".trim(htmlspecialchars(stripslashes($put_params['user_post'])))."'";
        }
    } else {
        if ($put_id_user == $id_user) {
            $where = "id_user = $put_id_user";
        } else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'Permission denied'
            )));
        }
    }

    if (isset($put_params['full_name']) && !empty($put_params['full_name'])) {
        validation('full_name', $put_params['full_name'], $connection);
        $set .= "full_name = '".trim(htmlspecialchars(stripslashes($put_params['full_name'])))."',";
    }

    if (isset($put_params['mail']) && !empty($put_params['mail'])) {
        validation('mail', $put_params['mail'], $connection);
        $set .= "mail = '".trim(htmlspecialchars(stripslashes($put_params['mail'])))."',";
    }

    if (isset($put_params['old_password']) && !empty($put_params['old_password'])) {
        if (isset($put_params['password']) && !empty($put_params['password'])) {
            if (isset($put_params['repeat_password']) && !empty($put_params['repeat_password'])) {
                if ($put_params['password'] == $put_params['repeat_password']) {
                    validation('password', $put_params['password'], $connection);
                    $query = "select id_user
                              from users
                              where id_user = $put_id_user and password like \"".md5($put_params['old_password'])."\"";

                    $result = mysqli_query($connection, $query) or
                              exit(json_encode(array(
                                  'status' => 'error',
                                  'message' => 'Data base error',
                                  'mysql_error' => mysqli_error($connection)
                              )));

                    if (mysqli_num_rows($result) == 1) {
                        $set .= "password = '".md5(trim(htmlspecialchars(stripslashes($put_params['password']))))."',";
                    }
                    else {
                        exit(json_encode(array(
                                'status' => 'error',
                                'message' => 'old_password is incorrect'
                        )));
                    }
                } 
                else {
                    exit(json_encode(array(
                        'status' => 'error',
                        'message' => 'Passwords are not equal'
                    )));
                }
            }
            else {
                exit(json_encode(array(
                    'status' => 'error',
                    'message' => 'repeat_password is empty'
                )));
            }
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'password is empty'
            )));
        }
    }

    if ($set == '') {
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'Empty query'
        )));
    }

    $query = 'update users
              set ' . (substr($set, 0, strlen($set) - 1)) .
             'where ' . $where;

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error'
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