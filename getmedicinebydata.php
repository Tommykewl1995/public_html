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


$medicine = array();
try
		{
			
			$med = $obj['data']."%";
			$result = $db->prepare(" SELECT MID, Medicine from medicine where Medicine like :Medicine ");
			$result->bindParam(':Medicine', $med, PDO::PARAM_STR);
			$result->execute();
			while ($row = $result->fetch())
			{
				$medicine[] = array('MID' => (string)$row['MID'], 'name' => (string)$row['Medicine'] );
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Medicine Data";
			$response['Medicine'] = $medicine;

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
