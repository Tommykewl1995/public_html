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


$rmedicine = array();
$fmedicine = array();
try
		{
			
			$result = $db->prepare(" SELECT DISTINCT m.MID, m.Medicine from medicine m inner join doctormedicine dm inner join appointment3 a
				where m.MID=dm.MID and a.AID=dm.AID and a.DID=:UserID LIMIT 5 ");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result->execute();
			while ($row = $result->fetch())
			{
				$rmedicine[] = array('MID' => (string)$row['MID'], 'Medicine' => (string)$row['Medicine']);
			}

			$result2 = $db->prepare(" SELECT m.MID, m.Medicine,count(dm.MID) from medicine m inner join doctormedicine dm inner join appointment3 a
				where m.MID=dm.MID and a.AID=dm.AID and a.DID=:UserID group by dm.MID LIMIT 5 ");
			$result2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result2->execute();
			while ($row2 = $result2->fetch())
			{
				$fmedicine[] = array('MID' => (string)$row2['MID'], 'Medicine' => (string)$row2['Medicine']);
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Medicine Data";
			$response['RecentMedicine'] = $rmedicine;
			$response['FrequentMedicine'] = $fmedicine;

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
