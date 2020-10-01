<?php
header('Access-Control-Allow-Headers: *');

require_once '../../db_connect.php';

if (isset($_GET['mail']) && !empty($_GET['mail'])) {
    $mail = trim(htmlspecialchars(stripslashes($_GET['mail'])));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'mail is empty'
    )));
}

$query = "select application_code as code, applications.date as date, application_text as text
          from (applications left join processing on applications.id_application = processing.id_application) left join completed on processing.id_processing = completed.id_processing
          where completed.id_processing is null and mail like \"$mail\"";

$result = mysqli_query($connection, $query) or
          exit(json_encode(array(
              'status' => 'error',
              'message' => 'Data base error',
              'mysql_error' => mysqli_error($connection)
          )));

if(mysqli_num_rows($result) > 0) {
    $applications_count = 0;
    $html_applications_list = '';

    while($row = mysqli_fetch_array($result)) {
        $html_applications_list .= "<table class=\"table\">
                                        <tr>
                                            <td rowspan=\"3\">" . ($applications_count + 1) . ".</td>
                                            <td>Дата подачи</td>
                                            <td>".date('d.m.Y', strtotime($row['date']))."</td>
                                        </tr>
                                        <tr>
                                            <td>Текст заявки</td>
                                            <td>".$row['text']."</td>
                                        </tr>
                                        <tr>
                                            <td>Идентификатор заявки</td>
                                            <td>".$row['code']."</td>
                                        </tr>
                                    </table><br /><br /><br />";
        $applications_count++;
    }

    $mail_html = file_get_contents('../send_code_application.html');

    $mail_html = substr_replace($mail_html, "<strong>" . $applications_count . "</strong> - количество активных заявок на " . date('d.m.Y / H:i:s', time()), strpos($mail_html, "[count]"), 7);
    $mail_html = substr_replace($mail_html, $html_applications_list, strpos($mail_html, "[data]"), 6);
    mail($_GET['mail'],
         'Восстановление доступа к заявкам на сайте тех. поддержки МГТУ "Станкин"',
         $mail_html,
         "From: Тех. поддержка МГТУ \"Станкин\" <bot@help.stankin.ru>\r\nContent-Type: text/html; charset=UTF-8");

    exit(json_encode(array(
        'status' => 'success'
    )));
}
else {
    exit(json_encode(array(
        'status' => 'error',
        'message' => 'No one application was sent with this email address'
    )));
}
