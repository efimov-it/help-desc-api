<?php
$key = trim(htmlspecialchars(stripslashes($_GET['key'])));

$query = "select *
          from applications
          where application_code = $key";

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if (mysqli_num_rows($result) > 0) {
    $application_data = mysqli_fetch_array($result);

    $query = "select user_type from users where id_user = $id_user";
    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));
    $user_type = intval(mysqli_fetch_array($result)['user_type']);

    $query = "select id_processing, id_user, id_operator
              from processing
              where " . $application_data["id_application"] . " = id_application" . ($user_type !== 0 ? " and $id_user in(id_user, id_operator)" : "");

    $result = mysqli_query($connection, $query) or
              exit(json_encode(array(
                  'status' => 'error',
                  'message' => 'Data base error',
                  'mysql_error' => mysqli_error($connection)
              )));

    if (mysqli_num_rows($result) > 0) {
        $processing = mysqli_fetch_array($result);

        if ($processing['id_user'] === $processing['id_operator']) {
            $query = "select full_name from users where id_user = ".$processing['id_user'];
        }
        else {
            $query = "select (select full_name from users where id_user = ".$processing['id_user'].") as executor_full_name,
                             (select full_name from users where id_user = ".$processing['id_operator'].") as operator_full_name";
        }
    
        $result = mysqli_query($connection, $query) or
                  exit(json_encode(array(
                      'status' => 'error',
                      'message' => 'Data base error',
                      'mysql_error' => mysqli_error($connection)
                  )));

        $executors = mysqli_fetch_array($result);
        

        $query = "select * from completed where id_processing = " . $processing['id_processing'];
        $result = mysqli_query($connection, $query) or
                  exit(json_encode(array(
                      'status' => 'error',
                      'message' => 'Data base error',
                      'mysql_error' => mysqli_error($connection)
                  )));

        if (mysqli_num_rows($result) > 0) {
            $completed_result = mysqli_fetch_array($result);
            $completed = array(
                "date" => $completed_result['date'],
                "result_text" => $completed_result['result_text']
            );
        }
    }
    else {
        if ($user_type !== 0) {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'Permission denied'
            )));
        }
    }

    $resultArray = array(
        "full_name" => $application_data["full_name"],
        "date" => $application_data["date"],
        "phone" => $application_data["phone_number"],
        "phone_type" => $application_data["phone_type"],
        "mail" => $application_data["mail"],
        "dept" => $application_data["dept"],
        "unit" => $application_data["unit"],
        "office" => $application_data["office"],
        "application_text" => $application_data["application_text"],
        "completed" => $completed ? $completed : null
    );

    if ($processing['id_user'] === $processing['id_operator']) {
        $resultArray["executor_full_name"] = $executors["full_name"];
        $resultArray["operator_full_name"] = null;
    }
    else {
        $resultArray["executor_full_name"] = $executors["executor_full_name"];
        $resultArray["operator_full_name"] = $executors["operator_full_name"];
    }

    $query = "select message, date, (select full_name
                                     from users as u
                                     where u.id_user = m.id_user) as full_name,
                                    (select user_type
                                     from users as u
                                     where u.id_user = m.id_user) as user_type
              from application_message as m
              where id_application = ".$application_data["id_application"]."
              group by id_message
              order by date desc";


    $result = mysqli_query($connection, $query) or
                exit(json_encode(array(
                    'status' => 'error',
                    'message' => 'Data base error',
                    'mysql_error' => mysqli_error($connection)
                )));

    $i = 0;
    while($message = mysqli_fetch_array($result)) {
        $resultArray["messages"][$i] = array(
            "text" => $message["message"],
            "date" => $message["date"],
            "full_name" => $message["full_name"],
            "user_type" => intval($message["user_type"])
        );
        $i++;
    }

    exit(json_encode(array(
        'status' => 'success',
        'data' => $resultArray
    )));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'Application not exist'
    )));
}