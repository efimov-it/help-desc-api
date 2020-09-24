<?php
$db_host = 'localhost'; 
$db_name = 'help_desk';
$db_user = 'root'; 
$db_password = 'root';

$connection = mysqli_connect($db_host, $db_user, $db_password, $db_name) 
    or die(json_encode ( array (
                                'status' => 'error',
                                'message' => 'Data base error',
                                'mysql_error' => mysqli_error($connection)
    )));