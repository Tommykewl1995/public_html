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
			$result = $db->prepare("INSERT INTO prescriptionimages (UserID, ImageLink) VALUES (:UserID, :ImageLink)");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
			$result->bindParam(':ImageLink', $obj['ImageLink'], PDO::PARAM_STR);
			$result->execute();
			$presid = $db->lastInsertID();
			$result1= $db->prepare("SELECT ImageLink, Details, PresID from prescriptionimages WHERE PresID = :PresID");
			$result1->bindParam(':PresID', $presid, PDO::PARAM_INT);
			$result1->execute();
			$row = $result1->fetch();
			$result2['src'] = $row['ImageLink'];
			$result2['PresID'] = $row['PresID'];
			$result2['sub'] = $row['Details'];
			$response['PresData'] = $result2;
            $response['ResponseCode'] = "200";
		    $response['ResponseMessage'] = " Image Uploaded Successfully";
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
