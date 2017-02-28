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


$match = array();
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
			$result = $db->prepare(" SELECT SymptomID, SymptomName from dxdata where ConditionID=:ConditionID");
			$result->bindParam(':ConditionID', $obj['ConditionID'], PDO::PARAM_STR);
			$result->execute();
			while ($row = $result->fetch())
			{
				$match[] = array('SymptomID' => (string)$row['SymptomID'], 'SymptomName' => (string)$row['SymptomName']);
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Match Data";
			$response['Match'] = $match;

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
			echo json_encode($response);
		}
