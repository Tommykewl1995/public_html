<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

	try
		{
			if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
				$response['ResponseCode'] = "400";
			    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
				$status['Status'] = $response;
				header('Content-type: application/json');
				echo json_encode($status);
				die();
			}
			$result = $db->prepare("SELECT * FROM main_data WHERE Name = :Name");
			$result->bindParam(":Name", $obj['ConditionName'], PDO::PARAM_INT);
			$result->execute();
			$res = $result->fetch();
			$data = '';
			if(!is_null($res['Introduction']) && strlen(strip_tags($res['Introduction'])) > 0){
				$data.=$res['Introduction'];
			}
			if(!is_null($res['Cause']) && strlen(strip_tags($res['Cause'])) > 0){
				$data.='<div><h3>Causes</h3>'.$res['Cause'].'</p></div>';
			}
			if(!is_null($res['Symptoms']) && strlen(strip_tags($res['Symptoms'])) > 0){
				$data.='<div><h3>Symptoms</h3>'.$res['Symptoms'].'</div>';
			}
			if(!is_null($res['Prognosis']) && strlen(strip_tags($res['Prognosis'])) > 0){
				$data.='<div><h3>Prognosis</h3>'.$res['Prognosis'].'</div>';
			}
			if(!is_null($res['Treatment']) && strlen(strip_tags($res['Treatment'])) > 0){
				$data.='<div><h3>Treatment</h3>'.$res['Treatment'].'</p></div>';
			}
			if(!is_null($res['Tests']) && strlen(strip_tags($res['Tests'])) > 0){
				$data.='<div><h3>Tests</h3>'.$res['Tests'].'</div>';
			}
			if(!is_null($res['Prevention']) && strlen(strip_tags($res['Prevention'])) > 0){
				$data.='<div><h3>Prevention</h3>'.$res['Prevention'].'</div>';
			}
			if(!is_null($res['Alternate']) && strlen(strip_tags($res['Alternate'])) > 0){
				$data.='<div><h3>Alternate</h3>'.$res['Alternate'].'</div>';
			}
			if($data == ''){
				$data = "<div><h3>OOPS..Sorry no data for ".$obj['ConditionName']." Updating SOON !!!</h3></div>";
			}
			$response['data'] = $data;
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Symptoms Submitted";
			$status['Status'] = $response;
			header('Content-type: application/json');
			echo json_encode($status);
		}
	catch(PDOException $ex)
		{
			$response['ResponseCode'] = "500";
		    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
		    $status['Status'] = $response;
		    header('Content-type: application/json');
			echo json_encode($status);
		}