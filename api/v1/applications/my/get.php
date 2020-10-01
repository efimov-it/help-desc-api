<?php

require_once '../../get_token.php';
require_once '../../check_auth.php';

$id_user = check_auth($token, $connection);

if ($id_user == false) {
    exit (json_encode( array(
        'status' => 'error',
        'message' => 'Access denied'
    )));
}

$where_string = '';
$sort_string = '';
$items_count = 10;
$current_page = 1;

if (isset($_GET['search'])) {
    $search = trim(htmlspecialchars(stripslashes($_GET['search'])));
    $where_string = " and (full_name like '%$search%' or application_text like '%$search%')";
}

if (isset($_GET['date'])) {
    $date = trim(htmlspecialchars(stripslashes($_GET['date'])));

    if (!empty($date)) {
        if (preg_match('/^[\d]{4,4}-[\d]{2}-[\d][\d]$/', $date)) {

            $where_string .= " and applications.date between '$date 00:00:00' and '$date 23:59:59'";
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
    /*if (isset($_GET['period'])) {
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
    }*/
}

if (isset($_GET['sort']) && isset($_GET['sort_by'])) {
    $sort = trim(htmlspecialchars(stripslashes($_GET['sort'])));
    $sort_by = trim(htmlspecialchars(stripslashes($_GET['sort_by'])));

    if ($sort == 'asc' || $sort == 'desc')
    {
        if (!empty($sort_by)) {
            $sort_string .= " order by " . $sort_by . " $sort";
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
    $sort_string .= " order by date asc";
}

if (isset($_GET['page'])) {
    $page = trim(htmlspecialchars(stripslashes($_GET['page'])));
    $current_page = $page;

    if (intval($page) > 0) {
        $page--;
        $sort_string .= " limit " . ($page * $items_count) . ", $items_count";
    }
    else {
        exit (json_encode( array(
            'status' => 'error',
            'message' => 'Invalid value of page'
        )));
    }
}
else {
    $sort_string .= " limit 0, $items_count";
}

$query = "select applications.*
          FROM applications left join processing
            on applications.id_application = processing.id_application
            left join completed
            on processing.id_processing = completed.id_processing
          where (processing.id_user = $id_user or processing.id_operator = $id_user) and id_completed is null " . 
          $where_string . $sort_string;

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$result_array = array();
$i = 0;
while ($row = mysqli_fetch_array($result)) {
    $query = "select message as text, date, full_name, user_type
              from application_message join users
                on application_message.id_user = users.id_user
              where id_application = " . $row["id_application"] . " 
              order by date desc";
    $message_result = mysqli_query($connection, $query) or
                    exit(json_encode(array(
                        'status' => 'error',
                        'message' => 'Data base error',
                        'mysql_error' => mysqli_error($connection)
                    )));
    $message = mysqli_num_rows($message_result) > 0 ? mysqli_fetch_array($message_result) : null;

    $result_array[$i] = array(
        "application_code" => $row["application_code"],
        "application_text" => $row["application_text"],
        "date" => $row["date"],
        "dept" => $row["dept"],
        "full_name" => $row["full_name"],
        "mail" => $row["mail"],
        "office" => $row["office"],
        "phone_number" => $row["phone_number"],
        "phone_type" => $row["phone_type"],
        "unit" => $row["unit"],
        "last_message" => $message !== null ? array(
            "text" => $message['text'],
            "date" => $message['date'],
            "full_name" => $message['full_name'],
            "user_type" => intval($message['user_type']),
        ) : null
    );
    $i++;
}

$query = "select count(applications.id_application)
          FROM applications left join processing
            on applications.id_application = processing.id_application
            left join completed
            on processing.id_processing = completed.id_processing
          where (processing.id_user = $id_user or processing.id_operator = $id_user) and id_completed is null " . 
          $where_string;

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

$total_count = mysqli_fetch_array($result)[0];
$loaded_count = $current_page * $items_count;

if ($loaded_count > $total_count) {
    $loaded_count = $total_count;
}

if($total_count - $loaded_count > $items_count) {
    $next_page_count = $items_count;
}
else {
    $next_page_count = $total_count - $loaded_count;
}

exit(json_encode(array(
    'status' => 'success',
    'data' => array(
        'applications' => $result_array,
        'pagination' => array(
            'tottal_count' => intval($total_count),
            'loaded_count' => $loaded_count,
            'next_page_count' => $next_page_count
        )
    )
)));