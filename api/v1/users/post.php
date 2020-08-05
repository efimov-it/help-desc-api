<?php

require_once '../get_token.php';
require_once '../check_auth.php';
require_once '../get_user_type.php';

$id_user = check_auth($token, $connection);

if ($id_user != false) {
    $user_type = get_user_type($id_user, $connection);
    
    if ($user_type == 0) {
        
        if (isset($_POST['full_name'])) {
            $full_name = trim(htmlspecialchars(stripslashes($_POST['full_name'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'full_name is empty'
            )));
        }
        
        if (isset($_POST['login'])) {
            $login = trim(htmlspecialchars(stripslashes($_POST['login'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'login is empty'
            )));
        }
        
        if (isset($_POST['mail'])) {
            $mail = trim(htmlspecialchars(stripslashes($_POST['mail'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'mail is empty'
            )));
        }
        
        if (isset($_POST['password'])) {
            $password = trim(htmlspecialchars(stripslashes($_POST['password'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'password is empty'
            )));
        }
        
        if (isset($_POST['repeat_password'])) {
            $repeat_password = trim(htmlspecialchars(stripslashes($_POST['repeat_password'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'repeat_password is empty'
            )));
        }
        
        if (isset($_POST['user_type'])) {
            $user_type = trim(htmlspecialchars(stripslashes($_POST['user_type'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'user_type is empty'
            )));
        }
        
        if (isset($_POST['user_post'])) {
            $user_post = trim(htmlspecialchars(stripslashes($_POST['user_post'])));
        }
        else {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'user_post is empty'
            )));
        }

        // TODO: code for validation new user data

        $query = "insert into users
                  (full_name, login, mail, password, user_type, user_post)
                  values ('$full_name', '$login', '$mail', '$password', $user_type, '$user_post')";

        $result = mysqli_query($connection, $query) or
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
            'message' => 'Permission denied'
        )));
    }
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}