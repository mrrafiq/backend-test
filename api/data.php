<?php
    require_once "../config/connection.php";
    header("Content-Type:application/json");
    

    if(function_exists($_GET['function'])) {
        $_GET['function']();
    }
    

    function getData()
    {
        global $connect;
        $header = apache_request_headers();
        $access_token = $header['access_token'];

        $auth = authenticate($access_token);
        if($auth){
            $query = 'SELECT * FROM customers';
            $execute = $connect->query($query);
            $data =[];
            while($row=mysqli_fetch_object($execute)){
                $data[] = $row;
            }
            $response = [
                'code' => 200,
                'message' => 'Request is successful',
                'data' => $data
            ];
        }else{
            $response = [
                'code' => 401,
                'message' => 'Unauthorized. Please login first with valid token!',
            ];
        }

        echo json_encode($response);
    }

    function insertData()
    {
        global $connect;
        $auth = false;
        $access_token = null;
        $header = apache_request_headers();
        if (array_key_exists("access_token", $header)) {
            $access_token = $header['access_token'];
        }

        $auth = authenticate($access_token);
        if($auth == true){
            $validation = [
                'name' => "",
                'born_date' => "",
                "address" => "",
            ];
    
            $check = count(array_intersect_key($_POST, $validation));
            if ($check == count($validation)) {
                $query = "INSERT INTO customers SET name='$_POST[name]', born_date='$_POST[born_date]', address='$_POST[address]'";
                $data = mysqli_query($connect, $query);
                if($data){
                    $response = [
                        'code' => 201,
                        'message' => 'New data record created successfully'
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
                    'message' => "Bad Request. Make sure name, born_date and address are not blank!"
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

    function updateData()
    {
        global $connect;
        $header = apache_request_headers();
        $auth = false;
        $access_token = null;
        if (array_key_exists("access_token", $header)) {
            $access_token = $header['access_token'];
        }

        $auth = authenticate($access_token);
        if($auth == true){
            $id = null;
            if (!empty($_GET["id"])) {
                $id = $_GET["id"];      
            }
            if ($id == null) {
                $response = [
                    'code' => 406,
                    'message' => "Not acceptable request headers. Need id to update the data!"
                ];
            }else{
                $validation = [
                    'name' => "",
                    'born_date' => "",
                    "address" => "",
                ];
        
                $check = count(array_intersect_key($_POST, $validation));
                if ($check == count($validation)) {
                    $temp_data = null;
                    $data = "SELECT * FROM customers WHERE id=".$id;
                    $get_data = mysqli_query($connect, $data);
                    while($row=mysqli_fetch_object($get_data)){
                        $temp_data = $row;
                    }
                    if ($temp_data != null) {
                        $query = "UPDATE customers SET name='$_POST[name]', born_date='$_POST[born_date]', address='$_POST[address]' WHERE id=$id";
                        $data = mysqli_query($connect, $query);
                        if($data){
                            $response = [
                                'code' => 200,
                                'message' => 'Data record has updated successfully'
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
                            'message' => 'Bad Request. Data not found!'
                        ];
                    }
                    
                }else{
                    $response = [
                        'code' => 400,
                        'message' => "Bad Request. Make sure name, born_date and address are not blank!"
                    ];
                }
            }
        }else{
            $response = [
                'code' => 401,
                'message' => 'Unauthorized. Please login first with valid token!',
            ];
        }
        
        echo json_encode($response);
    }

    function deleteData()
    {
        global $connect;
        $header = apache_request_headers();
        $auth = false;
        $access_token = null;
        if (array_key_exists("access_token", $header)) {
            $access_token = $header['access_token'];
        }

        $auth = authenticate($access_token);
        if($auth == true){
            $id = $_GET['id'];
            if ($id == null) {
                $response = [
                    'code' => 406,
                    'message' => 'Not acceptable request headers. Need id to update the data!'
                ];
            }else{
                $temp_data = null;
                $data = "SELECT * FROM customers WHERE id=".$id;
                $get_data = mysqli_query($connect, $data);
                while($row=mysqli_fetch_object($get_data)){
                    $temp_data = $row;
                }
                if ($temp_data != null) {
                    $query = "DELETE FROM customers WHERE id=".$id;
                    $execute = mysqli_query($connect, $query);
                    if($execute){
                        $response = [
                            'code' => 200,
                            'message' => 'Data record has deleted successfully'
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
                        'message' => 'Bad Request. Data not found!'
                    ];
                }
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