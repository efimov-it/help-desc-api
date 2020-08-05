<?php

$db_host = 'localhost'; 
$db_name = 'help_stankin_2020';
$db_user = 'root'; 
$db_password = 'root';

$connection = mysqli_connect($db_host, $db_user, $db_password, $db_name) 
    or die(json_encode ( array (
                                'status' => 'error',
                                'message' => 'data base error',
                                'mysql_error' => mysqli_error($connection)
    )));