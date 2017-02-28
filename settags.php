<?php
// case 3 : symtracker notification
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');


$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try
	{
		foreach ($obj['add'] as $value1) {
			$result = $db->prepare("INSERT INTO UserTags (UserID, Tag) VALUES (:UserID, :Tag)");
      $result->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
      $result->bindParam(":Tag", $value1, PDO::PARAM_STR);
      $result->execute();
		}
		foreach ($obj['del'] as $value2) {
			$result = $db->prepare("DELETE FROM UserTags WHERE UserID = :UserID AND Tag = :Tag");
      $result->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
      $result->bindParam(":Tag", $value2, PDO::PARAM_STR);
      $result->execute();
		}
		$response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "Tag Set Successfully";
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
