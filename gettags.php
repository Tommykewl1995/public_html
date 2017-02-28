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
		$arr = array();
		if($obj['UserID']){
			$query = $db->prepare("SELECT DISTINCT Tag FROM UserTags WHERE UserID = :UserID");
			$query->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
			$query->execute();
			while($que = $query->fetch()){
				$arr[] = $que['Tag'];
			}
		}
		if(!$obj['my']){
			$result = $db->prepare("SELECT DISTINCT( Preferences) FROM Tags GROUP BY Tag");
                       
	    $result->execute();
	    while($row = $result->fetch()){
	      $data[] = array("name" => $row['Preferences'], "selected" => (in_array($row['Preferences'], $arr))?1:0);
	    }
	    $response['Tags'] = $data;
		}else{
			$response['MyTags'] = $arr;
		}
		$response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "CronJob Added";
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
