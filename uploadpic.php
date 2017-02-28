<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
include('helperfunctions1.php');
date_default_timezone_set('Asia/Kolkata');

// Path to move uploaded files
$console = new Console();
$target_path = "/home/u824038781/public_html/RxHealth0.1/img/";

// array for final json respone
$response = array();

$userid = isset($_POST['UserID']) ? $_POST['UserID'] : '';
$count = 0;

if (isset($_FILES[0]['name'])) {
    try {
    // reading other post parameters
    $console->console($_POST);
    $console->console($_FILES);
    $userid = isset($_POST['UserID']) ? $_POST['UserID'] : '';
    $t = time();
    $response['UserID'] = $userid;

    $filename = $_FILES[0]['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $target_path = $target_path.$userid."_".$t.".".$ext;

    $response['file_name'] = $userid .$t. $ext;
        // Throws exception incase file is not being moved
        if (!move_uploaded_file($_FILES[0]['tmp_name'], $target_path))
        {
            // make error flag true
            $response['error'] = true;
            $response['message'] = 'Could not move the file!';
        }
        $path = "http://dxhealth.esy.es/RxHealth0.1/img/".$userid."_".$t.".".$ext;
        $response['console'] = $console->flush();
        $response['ResponseCode'] = "200";
        $response['ResponseMessage'] = "File uploaded successfully!";
        $response['error'] = false;
        $response['Url'] = $path;
        $status['Status'] = $response;
        header('Content-type: application/json');
        echo json_encode($status);
    } catch (Exception $e) {
        // Exception occurred. Make error flag true
        $response['error'] = true;
        $response['message'] = $e->getMessage();
        $status['Status'] = $response;
        header('Content-type: application/json');
        echo json_encode($status);
    }
} else {
    // File parameter is missing
    $response['Files'] = $_FILES;
    $response['post'] = $_POST;
    $response['error'] = true;
    $response['message'] = 'Not received any file!F';
    $status['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($status);
}
