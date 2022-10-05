<?php
    require_once "../config/connection.php";
    header("Content-Type:application/json");
    
    $content = file_get_contents($_POST['photo']);
    $file_name = $_POST['photo'];

    $path_parts = pathinfo($file_name);

    $data = base64_encode($content);
    $photo = base64_decode($data);
    $img_name = $path_parts['filename'].'.'.$path_parts['extension'];
    file_put_contents('../img/'.$img_name, $photo);
    

    global $connect;
    $query = "INSERT INTO gallery SET img='$img_name'";
    $execute = mysqli_query($connect, $query);
    if ($execute) {
        $response = [
            'code' => 201,
            'message' => 'Upload image successfully'
        ];
    }else{
        $response = [
            'code' => 400,
            'message' => 'Bad Request. Try again!'
        ];
    }
    echo json_encode($response);
?>