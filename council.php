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
// $query = $db->prepare("Select IsDoctor from user where UserID=:UserID");
// $query->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
// $query->execute();
// $response = array();
// $row2 = $query->fetch();
// if($obj['IsDoctor']==1)
// {

$council = array();

	try
		{
			$result = $db->prepare(" SELECT CouncilID, Council from council ");
			$result->execute();
			while ($row = $result->fetch())
			{
				$council[] = array('CouncilID' => (string)$row['CouncilID'], 'Council' => (string)$row['Council'] );
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Registration Council";
			$response['Council'] = $council;

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
// }
