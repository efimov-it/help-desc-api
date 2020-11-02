<?php
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    require_once 'put.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    require_once 'get.php';
}