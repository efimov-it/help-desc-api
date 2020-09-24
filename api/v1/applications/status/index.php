<?php
header('Access-Control-Allow-Headers: *');

if (isset($_GET['key']) && !empty($_GET['key']))
    $key = trim(htmlspecialchars(stripslashes($_GET['key'])));
else exit(json_encode(array(
         'status' => 'error',
         'message' => 'key is empty'
     )));

require_once '../../db_connect.php';

$query = "select *
          from applications
          where application_code = \"$key\"";

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    $application_row = mysqli_fetch_array($result);
    $application = array();
    $application['id_application'] = $application_row['id_application'];
    $application['key'] = $application_row['application_code'];
    $application['date'] = $application_row['date'];
    $application['application_text'] = $application_row['application_text'];
    $application['status'] = "created";

    $query = "select *
              from processing
              where id_application = " . $application['id_application'];

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    if(mysqli_num_rows($result) > 0) {
        $processing = mysqli_fetch_array($result);

        if ($processing['id_user'] == $processing['id_operator']) {
            $query = "select *
                      from users
                      where id_user = " . $processing['id_user'];

            $result = mysqli_query($connection, $query) or
                      exit(json_encode(array(
                          'status' => 'error',
                          'message' => 'Data base error',
                          'mysql_error' => mysqli_error($connection)
                      )));
            
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_array($result);
                $application['support_fullname'] = $user['full_name'];
                $application['support_post'] = $user['user_post'];
            }
        }
        else {
            $query = "select *
                      from users
                      where id_user = in(" . $processing['id_user'] . ", " . $processing['id_operator'] . ")";

            $result = mysqli_query($connection, $query) or
                      exit(json_encode(array(
                          'status' => 'error',
                          'message' => 'Data base error',
                          'mysql_error' => mysqli_error($connection)
                      )));
            
            if (mysqli_num_rows($result) > 1) {
                $user = mysqli_fetch_array($result);
                $operator = mysqli_fetch_array($result);
                $application['support_fullname'] = $user['full_name'];
                $application['support_post'] = $user['user_post'];
                $application['operator_fullname'] = $operator['full_name'];
                $application['operator_post'] = $operator['user_post'];
            }
        }

        $application['processing_date'] = $processing['date'];
        $application['status'] = "processing";

        $query = "select *
                  from completed
                  where id_processing = " . $processing['id_processing'];

        $result = mysqli_query($connection, $query) or
                  exit(json_encode(array(
                    'status' => 'error',
                    'message' => 'Data base error',
                    'mysql_error' => mysqli_error($connection)
                  )));

        if (mysqli_num_rows($result) > 0) {
            $completed = mysqli_fetch_array($result);

            $application['completed_date'] = $completed['date'];
            $application['result_text'] = $completed['result_text'];
            $application['status'] = "completed";
        }
    }

    unset($application['id_application']);

    exit(json_encode(array(
        'status' => 'success',
        'data' => $application
    )));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Application not exist'
    )));
}