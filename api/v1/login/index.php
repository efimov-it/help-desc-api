<?php
if (isset($_POST['login'])) {
    $login = trim(htmlspecialchars(stripslashes($_POST['login'])));
}
else {
    exit (json_encode(array(
        'status' => 'error',
        'message' => 'login is empty'
    )));
}

if (isset($_POST['password'])) {
    $password = md5(trim(htmlspecialchars(stripslashes($_POST['password']))));
}
else {
    exit (json_encode(array(
        'status' => 'error',
        'message' => 'password is empty'
    )));
}

require_once('../db_connect.php');

$query = 'select *
          from users
          where login like "'.$login.'" and password like "'.$password.'"';

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    $id_user = $row['id_user'];
    $user_type = $row['user_type'];

    $query = "select *
              from auth
              where id_user = $id_user";
    
    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'messages' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    if (mysqli_num_rows($result) == 1) {
        $time = time();
        $new_token = md5($time . $id_user);
        $expires_in = $time + 86400;
        $query = "update auth
                    set date = $time,
                        token = \"$new_token\",
                        expires_in = $expires_in
                    where id_user = $id_user";

        $result = mysqli_query($connection, $query);

        if ($result) {
            exit (json_encode(array(
                'status' => 'success',
                'data' => array (
                    'token' => $new_token,
                    'expires_in' => $expires_in,
                    'user_type' => $user_type
                )
            )));
        }
        echo 123;
    }
    else {
        $time = time();
        $token = md5($time . $id_user);
        $expires_in = $time + 86400;
        $query = "insert into auth
                (token, date, id_user, expires_in)
                values ('$token', $time, $id_user, $expires_in)";

        $result = mysqli_query($connection, $query);

        if ($result) {
            exit (json_encode(array(
                'status' => 'success',
                'data' => array (
                    'token' => $token,
                    'expires_in' => $expires_in,
                    'user_type' => $user_type
                )
            )));
        }
        else {
            echo mysqli_error($connection);
        }
    }
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Invalid login or password'
    )));
}