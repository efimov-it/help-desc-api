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

$query = "select id_user, full_name, login, mail, user_type, user_post
          from users
          where id_user <> $id_user
          order by full_name asc";
          
$result = mysqli_query($connection, $query) or
        exit(json_encode(array(
            'status' => 'error',
            'message' => 'Data base error',
            'mysql_error' => mysqli_error($connection)
        )));

$result_array = array();
$i = 0;
while($row = mysqli_fetch_array($result)) {
    $result_array[$i] = array(
        'id_user' => $row['id_user'],
        'full_name' => $row['full_name'],
        'login' => $row['login'],
        'mail' => $row['mail'],
        'user_type' => intval($row['user_type']),
        'user_post' => $row['user_post']
    );
    $i++;
}

exit(json_encode(array(
    'status' => 'success',
    'data' => $result_array
)));