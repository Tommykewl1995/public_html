<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require_once 'Bcrypt.php';
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
  $query = $db->prepare("SELECT ClinicID,AssistPassword FROM clinics WHERE AssistName =:Name");
  $query->bindParam(':Name', $obj['UserName'], PDO::PARAM_INT);
  $query->execute();
  $que = $query->fetch();
  if($query->rowCount()==1){
		if(Bcrypt::checkPassword($obj['Password'], $que['AssistPassword'])){
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Successful Login";
			$response['ClinicID'] = (string)$row['ClinicID'];
		}else{
			$response['ResponseCode'] = "500";
			$response['ResponseMessage'] = "Phone and password mismatch! Please check";
		}
  }else{
  		$response['ResponseCode'] = "500";
  		$response['ResponseMessage'] = "No clinic present with this username";
  }
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
