<?php
function get_user_type($id_user, $connection) {
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
        return mysqli_fetch_array($result)[0];
    }
    else {
        exit (json_encode(array(
            'status' => 'error',
            'message' => 'User not exist'
        )));
    }
}