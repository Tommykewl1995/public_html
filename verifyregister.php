<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');

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
  $query = $db->prepare("SELECT ID FROM verify WHERE DeviceID = :DeviceID");
  $query->bindParam(":DeviceID", $obj['DeviceID'], PDO::PARAM_STR);
  $query->execute();
  if($que = $query->fetch()){
    $result = $db->prepare("UPDATE verify SET RegistrationID = :RegistrationID WHERE ID = :ID");
    $result->bindParam(":ID", $que['ID'], PDO::PARAM_INT);
    $result->bindParam(":RegistrationID", $obj['RegistrationID'], PDO::PARAM_STR);
    $result->execute();
  }else{
    $result = $db->prepare("INSERT INTO verify (DeviceID,RegistrationID) VALUES (:DeviceID,:RegistrationID)");
    $result->bindParam(":DeviceID", $obj['DeviceID'], PDO::PARAM_STR);
    $result->bindParam(":RegistrationID", $obj['RegistrationID'], PDO::PARAM_STR);
    $result->execute();
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Verify Device Registered";
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
