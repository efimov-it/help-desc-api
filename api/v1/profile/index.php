<?php
header('Access-Control-Allow-Headers: token');

if ($_SERVER['REQUEST_METHOD'] == 'put') {
    require_once 'put.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    require_once 'get.php';
}