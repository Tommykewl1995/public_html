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
  if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
    $response['ResponseCode'] = "400";
    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($status);
    die();
  }
    if($obj['action'] == "0"){
               $result2 = $db->prepare("DELETE FROM bookmark WHERE UserID = :UserID and ShrID = :ArticleID");
               $result2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
               $result2->bindParam(':ArticleID', $obj['ShrID'], PDO::PARAM_INT);
               $response['ResponseMessage'] = "Bookmark Removed Successfully";
               $result2->execute();
     }else{
               $result = $db->prepare("INSERT INTO bookmark (`UserID`, `ShrID`) VALUES (:UserID, :ArticleID)");
               $result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
               $result->bindParam(':ArticleID', $obj['ShrID'], PDO::PARAM_INT);
               $response['ResponseMessage'] = "Bookmark Added Successfully";
               $result->execute();
          }

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
