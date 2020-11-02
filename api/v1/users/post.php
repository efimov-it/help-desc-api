<?php

require_once '../get_token.php';
require_once '../check_auth.php';
require_once '../get_user_type.php';
require_once '../validation.php';

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
    
if (isset($_POST['full_name']) && !empty($_POST['full_name'])) {
    $full_name = trim(htmlspecialchars(stripslashes($_POST['full_name'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'full_name is empty'
    )));
}

if (isset($_POST['login']) && !empty($_POST['login'])) {
    $login = trim(htmlspecialchars(stripslashes($_POST['login'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'login is empty'
    )));
}

if (isset($_POST['mail']) && !empty($_POST['mail'])) {
    $mail = trim(htmlspecialchars(stripslashes($_POST['mail'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'mail is empty'
    )));
}

if (isset($_POST['password']) && !empty($_POST['password'])) {
    $password = trim(htmlspecialchars(stripslashes($_POST['password'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'password is empty'
    )));
}

if (isset($_POST['repeat_password']) && !empty($_POST['repeat_password'])) {
    $repeat_password = trim(htmlspecialchars(stripslashes($_POST['repeat_password'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'repeat_password is empty'
    )));
}

if (isset($_POST['user_type'])) {
    if (!empty($_POST['user_type'])) {
        $user_type = trim(htmlspecialchars(stripslashes($_POST['user_type'])));
    }
    else {
        $user_type = 2;
    }
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'user_type is empty'
    )));
}

if (isset($_POST['user_post']) && !empty($_POST['user_post'])) {
    $user_post = trim(htmlspecialchars(stripslashes($_POST['user_post'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'user_post is empty'
    )));
}

validation('full_name', $full_name, $connection);
validation('login', $login, $connection);
validation('mail', $mail, $connection);
validation('password', $password, $connection);

if ($password !== $repeat_password) {
    exit ( json_encode ( array (
        'status' => 'error',
        'message' => 'Passwords are not equal'
    )));
}

validation('user_type', $user_type, $connection);
validation('user_post', $user_post, $connection);

$query = "insert into users
            (full_name, login, mail, password, user_type, user_post)
            values ('$full_name', '$login', '$mail', '" . md5($password) . "', $user_type, '$user_post')";

$result = mysqli_query($connection, $query) or
            exit(json_encode(array(
            'status' => 'error',
            'message' => 'Data base error',
            'mysql_error' => mysqli_error($connection)
            )));

exit(json_encode(array(
    'status' => 'success',
    'data' => array(
        'id_user' => mysqli_insert_id($connection)
    )
)));
