<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include_once('db_config.php');
date_default_timezone_set('Asia/Kolkata');


$json = file_get_contents('php://input');
$obj = json_decode($json, true);


$degree = array();
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
			$result3 = $db->prepare(" SELECT DegreeID, Degree from degree ");
			$result3->execute();
			while ($row3 = $result3->fetch())
			{
				$degree[] = array('DegreeID' => (string)$row3['DegreeID'], 'name' => (string)$row3['Degree'] );
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Doctor Menu Data";
			$response['Degree'] = $degree;

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
