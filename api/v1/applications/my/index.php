<?php
header('Access-Control-Allow-Headers: *');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    require_once 'get.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    require_once 'delete.php';
}