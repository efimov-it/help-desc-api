<?php

require_once '../get_token.php';
require_once '../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user == false) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

$request = '';
$items_count = 20;

if (isset($_GET['search'])) {
    $search = trim(htmlspecialchars(stripslashes($_GET['search'])));
    $request = " where full_name like '%$search%' or application_text like '%$search%'";
}

if (isset($_GET['date'])) {
    $date = trim(htmlspecialchars(stripslashes($_GET['date'])));

    if (!empty($date)) {
        if (preg_match('/^[\d]{4,4}-[\d]{2}-[\d][\d]$/', $date)) {
            if ($request != '') {
                $request .= " and";
            }
            else {
                $request = " where";
            }

            $request .= " applications.date between '$date 00:00:00' and '$date 23:59:59'";
        }
        else {
            exit (json_encode( array(
                'status' => 'error',
                'message' => 'Invalid value of date'
            )));
        }
    }
    else {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'Invalid value of date'
        )));
    }
}
else {
    if (isset($_GET['period'])) {
        $period = trim(htmlspecialchars(stripslashes($_GET['period'])));

        if (floatval($period) > 0) {
            if ($request != '') {
                $request .= " and";
            }
            else {
                $request = " where";
            }

            $start_period_date = date('Y-m-d',time() - ( floatval($period) * 60 * 60 * 24));
            $request .= " applications.date >= '$start_period_date 00:00:00'";
        }
        else {
            exit (json_encode( array(
                'status' => 'error',
                'message' => 'Invalid value of period'
            )));
        }
    }
}

if (isset($_GET['sort']) && isset($_GET['sort_by'])) {
    $sort = trim(htmlspecialchars(stripslashes($_GET['sort'])));
    $sort_by = trim(htmlspecialchars(stripslashes($_GET['sort_by'])));

    if ($sort == 'asc' || $sort == 'desc')
    {
        if (!empty($sort_by) && intval($sort_by) > 0) {
            $request .= " order by " . ($sort_by + 1) . " $sort";
        }
        else {
            exit (json_encode( array(
                'status' => 'error',
                'message' => 'Invalid value of sort_by'
            )));
        }
    }
    else {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'Invalid value of sort'
        )));
    }
}
else {
    if (isset($sort) || isset($sort_by)) {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'For sort query answer you need send "sort" and "sort_by" parametrs'
        )));
    }
}

if (isset($_GET['page'])) {
    $page = trim(htmlspecialchars(stripslashes($_GET['page'])));

    if (intval($page) > 0) {
        $page--;
        $request .= " limit " . ($page * $items_count) . ", $items_count";
    }
    else {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'Invalid value of page'
        )));
    }
}
else {
    $request .= " limit 0, $items_count";
}

$query = 'select applications.*
          FROM applications join processing
          on applications.id_application <> processing.id_application' . 
          $request;

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$result_array = array();
$i = 0;
while ($row = mysqli_fetch_array($result)) {
    $result_array[$i] = $row;
    $i++;
}

exit(json_encode(array(
    'status' => 'success',
    'data' => $result_array
)));