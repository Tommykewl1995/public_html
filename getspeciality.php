<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];

require('db_config.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
	$response['ResponseCode'] = "400";
	$response['ResponseMessage'] = "Invalid api_key"; //user friendly message
	$status['Status'] = $response;
	header('Content-type: application/json');
	echo json_encode($status);
	die();
}

try{
  $query = $db->prepare("SELECT * FROM speciality");
  $query->execute();
  while ($row = $query->fetch()) {
    $data[] = array('SpecID' => $row['SpecID'], 'Speciality' => $row['Speciality']);
  }
  $response['Data'] = $data;
	$response['ResponseCode'] = "200";
	$response['ResponseMessage'] = "Speciality Data";
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
