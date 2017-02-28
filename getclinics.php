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
  $clinics = array();
  $query = $db->prepare("SELECT * FROM clinics WHERE CommuID = :CommuID");
  $query->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
  $query->execute();
  while($que = $query->fetch()){
    $clinics[] = array("ClinicID" => $que['ClinicID'],
    "Name" => $que['ClinicName'],
    "Summary" => $que['Summary'],
    "Address" => (string)$que['Address'],
    "City" => $que['City'],
    "PinCode" => $que['PinCode'],
    "ClinicEmail" => $que['ClinicEmail'],
    "ClinicPhone" => $que['ClinicPhone'],
    "ClinicLogo" => $que['ClinicLogo']);
  }
  $response['Clinics'] = $clinics;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Clinics Data";
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
