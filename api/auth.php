<?php
    require_once "../config/connection.php";
    header("Content-Type:application/json");

    if(function_exists($_GET['function'])) {
        $_GET['function']();
    }

    function register()
    {
        global $connect;
        $validation = [
            'username' => "",
            'password' => "",
        ];

        $check = count(array_intersect_key($_POST, $validation));
        if ($check == count($validation)) {
            $uname = sha1($_POST["username"]);
            $pass = sha1($_POST["password"]);
            $encrypted = $uname.''.$pass;

            $query = "INSERT INTO users SET username='$_POST[username]', password = '$encrypted', access_token = null";
            $data = mysqli_query($connect, $query);
            if($data){
                $response = [
                    'code' => 201,
                    'message' => 'New user created successfully'
                ];
            }else{
                $response = [
                    'code' => 400,
                    'message' => 'Bad Request'
                ];
            }
        }else{
            $response = [
                'code' => 400,
                'message' => "Bad Request. Make sure username and password are not blank!"
            ];
        }
        echo json_encode($response);
    }

    function login()
    {
        global $connect;

        $validation = [
            'username' => "",
            'password' => "",
        ];

        $check = count(array_intersect_key($_POST, $validation));
        if ($check == count($validation)) {
            $uname = sha1($_POST["username"]);
            $pass = sha1($_POST["password"]);
            $encrypted = $uname.''.$pass;

            $query = "SELECT * FROM users WHERE username='$_POST[username]' AND password = '$encrypted'";
            $execute = mysqli_query($connect, $query);
            $data = null;
            while($row=mysqli_fetch_object($execute)){
                $data = $row;
            }
            if($data != null){
                date_default_timezone_set("Asia/Jakarta");
                $date = sha1(ltrim(date("Y/m/d H:i:s")));
                $token = $uname.''.$date;
                $update = "UPDATE users SET access_token='$token' WHERE id='$data->id'";
                $exc_token = mysqli_query($connect, $update);
                if ($exc_token) {
                    $response = [
                        'code' => 201,
                        'message' => 'Login Successful',
                        'token' => $token,
                    ];
                }else{
                    $response = [
                        'code' => 500,
                        'message' => 'Failed to create an access token!'
                    ];
                }
            }else{
                $response = [
                    'code' => 400,
                    'message' => 'Username or password is incorrect!'
                ];
            }
        }else{
            $response = [
                'code' => 400,
                'message' => "Bad Request. Make sure username and password are not blank!"
            ];
        }
        echo json_encode($response);
    }

    function logout()
    {
        global $connect;
        $header = apache_request_headers();
        $auth = false;
        $access_token = null;
        if (array_key_exists("access_token", $header)) {
            $access_token = $header['access_token'];
        }

        $auth = authenticate($access_token);
        if ($auth == true) {
            $query = "SELECT * FROM users WHERE access_token='$access_token'";
            $execute = mysqli_query($connect, $query);
            $data = null;
            while($row=mysqli_fetch_object($execute)){
                $data = $row;
            }
            if ($data != null) {
                $logout = "UPDATE users SET access_token=null WHERE id='$data->id'";
                $exc_logout = mysqli_query($connect, $logout);
                if ($exc_logout) {
                    $response = [
                        'code' => 201,
                        'message' => 'Logout Successful',
                    ];
                }else{
                    $response = [
                        'code' => 500,
                        'message' => 'Failed to logout'
                    ];
                }
            }else{
                $response = [
                    'code' => 400,
                    'message' => 'Your current token is invalid. please try to login!'
                ];
            }
        }else{
            $response = [
                'code' => 401,
                'message' => 'Unauthorized. Please login first with valid token!',
            ];
        }
        echo json_encode($response);
    }

    function authenticate($key){
        global $connect;
        $query = "SELECT * FROM users WHERE access_token='$key'";
        $execute = mysqli_query($connect, $query);
        $data = null;
        while($row=mysqli_fetch_object($execute)){
            $data = $row;
        }
        if($data != null){
            return true;
        }else{
            return false;
        }
    }
?>