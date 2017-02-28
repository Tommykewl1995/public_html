<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');
require_once 'Bcrypt.php';
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try{
  if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
    $response['ResponseCode'] = "400";
    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($status);
    die();
  }
  $result = $db->prepare("SELECT Password FROM user WHERE UserID = :UserID");
  $result->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
  $result->execute();
  $row = $result->fetch();
  if(Bcrypt::checkPassword((string)$obj['pass'], (string)$row['Password'])){
    $res = "true";
  }else{
    $res = "false";
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Doctor List Data";
  $response['Result'] = $res;
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($status);
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
  echo json_encode($status);
}
