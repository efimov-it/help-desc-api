<?php
header('Access-Control-Allow-Headers: *');
require_once '../../../get_token.php';
require_once '../../../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user == false) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

$query = "select user_type from users where id_user = $id_user";
$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$user_type = mysqli_fetch_array($result)['user_type'];

if ($user_type === 2) {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

$where;

if($user_type == 0) {
    $where = "user_type in(1,2)";
}
else {
    $where = "user_type = 2";
}

$query = "select *
          from users
          where $where
          order by full_name asc";

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$executors = array();
while($row = mysqli_fetch_array($result)) {
    $executors[count($executors)] = array(
        'id_user' => $row['id_user'],
        'full_name' => $row['full_name'],
        'user_post' => $row['user_post']
    );
}

exit(json_encode(array(
    'status' => 'success',
    'data' => $executors
)));