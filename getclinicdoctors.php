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
  $doctors = array();
  $query = $db->prepare("SELECT u.UserID,u.FName,u.LName,u.Phone,u.Email,u.Pic FROM clinicdoctors c INNER JOIN user u ON u.UserID = c.DID WHERE c.ClinicID = :ClinicID");
  $query->bindParam(":ClinicID", $obj['ClinicID'], PDO::PARAM_INT);
  $query->execute();
  while($que = $query->fetch()){
    if(is_null($que['Pic'])){
      $fpic = "http://52.24.83.227/default.png";
    }else{
      $fpic = $row4['Pic'];
    }
    $doctors[] = array("DID" => $que['UserID'],
    "Name" => "Dr. ".(string)$que['FName']." ".(string)$que['LName'],
    "Phone" => $que['Phone'],
    "Email" => $que['Email'],
    "Pic" => $fpic);
  }
  $response['Doctors'] = $doctors;
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
