<?php

function validation ($key, $value, $connection) {
    if ($key == 'full_name') {
        if (!preg_match('/^([А-Я][а-я]*) ([А-Я][а-я]*|[А-Я]\.) ([А-Я][а-я]*(на|ич)|[А-Я]\.)$/u', $value)) {
            exit(json_encode(array(
                'status' => 'error',
                'message' => 'Invalid value of full_name'
            )));
        }
    }

    if ($key == 'login') {
        $query = "select login
                  from users
                  where login like \"$value\"";

        $result = mysqli_query($connection, $query) or
                  exit ( json_encode ( array (
                      'status' => 'error',
                      'message' => 'Data base error',
                      'mysql_error' => mysqli_error($connection)
                  )));

        if (mysqli_num_rows($result) > 0) {
            exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'User with login "' . $value . '" already exist'
            )));
        }

        if (strlen($value) < 5) {
            exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'login must consist of 5 or more symbols'
            )));
        }
    }

    if ($key == 'mail') {
        if (!preg_match('/^[\w-_]{1,}@[\w-_]{1,}\.[\w]{1,}$/u', $value)) {
            exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'Invalid value of mail'
            )));
        }
    }

    if ($key == 'password') {
        if (!preg_match('/^[A-Za-zА-Я-а-яЁё0-9!@#$%^&*()_+~]{8,}$/u', $value)) {
            exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'Invalid value of password'
            )));
        }
    }

    if ($key == 'user_type') {
        if (!preg_match('/^[012]{1}$/', $value)) {
            exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'Invalid value of user_type. It can be equal 0, 1 or 2'
            )));
        }
    }

    if ($key == 'user_post') {
        if (strlen($value) < 3) {
            exit ( json_encode ( array (
                'status' => 'error',
                'message' => 'Invalid value of user_post'
            )));
        }
    }

    return true;
}