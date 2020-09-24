<?php
$db_host = 'localhost'; 
$db_name = 'u1098369_help';
$db_user = 'u1098369_help'; 
$db_password = '001122Man';

$connection = mysqli_connect($db_host, $db_user, $db_password, $db_name) 
    or die(json_encode ( array (
                                'status' => 'error',
                                'message' => 'data base error',
                                'mysql_error' => mysqli_error($connection)
    )));