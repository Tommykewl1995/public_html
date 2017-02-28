<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
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
  $time = (int)$obj['Time'];
  $date = date("Y-m-d H:i:s");
  $result = $db->prepare("UPDATE appointment3 SET AppointmentDate = :AppointmentDate,Status = 'Confirm' WHERE AID = :AID");
  $result->bindParam(':AID', $obj['AID'], PDO::PARAM_INT);
  $result->bindParam(':AppointmentDate', $date, PDO::PARAM_INT);
  $result->execute();
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Appointment Confirmed";
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($status);
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
  $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($response);
}
