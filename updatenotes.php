<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try{
               $result2 = $db->prepare("UPDATE doctornotes SET Notes=:Notes WHERE AID=:AID");
	       $result2->bindParam(':Notes', $obj['Notes'], PDO::PARAM_STR);
	       $result2->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
	       $result2->execute();

    

  $response['ResponseCode'] = "200";
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