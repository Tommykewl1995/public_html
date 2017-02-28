<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require('helperfunctions1.php');
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
  $query = $db->prepare("SELECT CONCAT_WS(FName, LName, ' ') AS FullName, Phone, Email FROM user WHERE UserID = :UserID");
  $query->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
  $query->execute();
  $que = $query->fetch();
  $query1 = $db->prepare("SELECT RegistrationID FROM verify");
  $query1->execute();
  while($que1 = $query1->fetch()){
    if($que1['RegistrationID']){
      $regids[] = $que1['RegistrationID'];
    }
  }
  if($regids){
    $result = pushnotification(null, 'Community Verify Notification', $que['FullName'].'with mobile no '.$que['Phone']." and Email ".$que['Email']." has requested to create a community", array("Data" => array("UserID" => $obj['UserID'], "Name" => $que['FullName'])), $db, $regids);
    $response['PushResponse'] = json_decode($result);
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Community Request Sent";
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
