<?php

require_once 'db_connect.php';

function check_auth ($token, $connection) {
    $token = trim(htmlspecialchars(stripslashes($token)));
    $query = "select *
              from auth
              where token like \"$token\"";

    $result = mysqli_query($connection, $query) or
              exit (json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
    )));

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);

        $time = time();
        $id_user = $row['id_user'];

        if ($time < $row['expires_in']) {
            $new_token = md5($time . $id_user);
            $expires_in = $time + 86400;
            $query = "update auth
                      set expires_in = $expires_in
                      where id_user = $id_user";

            $result = mysqli_query($connection, $query) or
                      exit (json_encode(array(
                          'status' => 'error',
                          'message' => 'Data base error',
                          'mysql_error' => mysqli_error($connection)
                      )));
                      
            return $row['id_user'];
        }
        else {
            $query = "delete
                      from auth
                      where id_user = $id_user";
            
            $result = mysqli_query($connection, $query) or
                      exit (json_encode(array(
                          'status' => 'error',
                          'message' => 'Data base error',
                          'mysql_error' => mysqli_error($connection)
                      )));

            exit (json_encode(array(
                'status' => 'error',
                'message' => 'Token time out'
            )));
        }
    }
    return false;
}