<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'post.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    require_once 'put.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    require_once 'delete.php';
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    require_once 'get.php';
}