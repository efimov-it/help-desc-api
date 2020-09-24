<?php

require_once '../db_connect.php';

$query = "select count(applications.id_application)
          from (applications left join processing
                on applications.id_application = processing.id_application)
                left join completed on processing.id_processing = completed.id_processing
          where completed.id_processing is null";

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));
srand(date("Ymd"), time());
$response = array(
    'status' => 'success',
    'data' => array(
        'applications_count' => intval(mysqli_fetch_array($result)[0]),
        'processing_time' => rand(450,1000)/100
    )
);

exit(json_encode($response));