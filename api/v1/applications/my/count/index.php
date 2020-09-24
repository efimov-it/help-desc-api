<?php

require_once '../../../get_token.php';
require_once '../../../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user != false) {
    $query = "select count(processing.id_processing)
              from processing left join completed
                   on processing.id_processing = completed.id_processing
              where id_user = ($id_user or id_operator = $id_user) and id_completed is null";

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    exit(json_encode(array(
        'status' => 'success',
        'data' => array(
            'count' => intval(mysqli_fetch_array($result)[0])
        )
    )));
}
else {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}