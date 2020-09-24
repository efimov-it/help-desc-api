<?php
header('Access-Control-Allow-Headers: token');

$token;
foreach( apache_request_headers() as $key => $value) {
    if ($key === 'token') {
        $token = $value;
    }
}

if (!$token) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}