<?php
$token;
foreach( apache_request_headers() as $key => $value) {
    if ($key === 'token') {
        $token = $value;
    }
}

if (!$token && !$ignore_unauth) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}