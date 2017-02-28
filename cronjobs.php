<?php
// case 3 : symtracker notification
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require('helperfunctions1.php');


$json = file_get_contents('php://input');
$obj = json_decode($json, true);
date_default_timezone_set("Asia/Kolkata");

define( 'API_ACCESS_KEY', 'AIzaSyDMpCteoqv4_yZueLxW9cy4zKLw_BAA6II' );

try
	{
		$now = strtotime("now");
    $result = $db->prepare("SELECT * FROM CronJob WHERE ExeT <= :NOW");
		$result->bindParam(":NOW", $now, PDO::PARAM_INT);
    $result->execute();
    while($row = $result->fetch()){
			pushnotification($row['UserID'], 'RxHealth', $row['Statement'], json_decode($row['DataJSON']), $db);
			$result0 = $db->prepare("INSERT INTO CronJobArchive (CJID, Statement, DataJSON, ExeT, UserID)
      SELECT CJID, Statement, DataJSON, ExeT, UserID FROM CronJob WHERE CJID = :CJID");
      $result0->bindParam(":CJID", $row['CJID'],PDO::PARAM_INT);
      $result0->execute();
			$result2 = $db->prepare("DELETE FROM CronJob WHERE CJID = :CJID");
			$result2->bindParam(":CJID", $row['CJID'],PDO::PARAM_INT);
			$result2->execute();
		}
			$result1 = $db->prepare("SELECT ArID,AGT FROM Articles");
	    $result1->execute();
	    while($row = $result1->fetch()){
              if($row['AGT']){
              $time = strtotime($row['AGT']);
              if($now >= $time){
	      $shrid[] = sharearticle(1,null, $row['ArID'],1,1,1, $db);
	      $result3 = $db->prepare("UPDATE Articles SET AGT = NULL WHERE ArID = :ArID");
	      $result3->bindParam(":ArID", $row['ArID'], PDO::PARAM_INT);
	      $result3->execute();
              }
              }
			}
			$response['ShrIDs'] = $shrid;
		$response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "CronJob Executed";
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
