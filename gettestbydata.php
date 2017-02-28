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


$test = array();
try
		{
			
			$med = $obj['data']."%";
			$result = $db->prepare(" SELECT TID, Test, TAB from test where Test like :test or TAB like :test2 ");
			$result->bindParam(':test', $med, PDO::PARAM_STR);
			$result->bindParam(':test2', $med, PDO::PARAM_STR);
			$result->execute();
			while ($row = $result->fetch())
			{
				$test[] = array('TID' => (string)$row['TID'], 'TestName' => (string)$row['Test'], 'TAB' => (string)$row['TAB'] );
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Medicine Data";
			$response['Test'] = $test;

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
