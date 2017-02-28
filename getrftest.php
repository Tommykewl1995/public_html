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


$rtest = array();
$ftest = array();
try
		{
			
			$result = $db->prepare(" SELECT DISTINCT t.TID, t.Test, t.TAB from appointment3 a inner join doctortest dt inner join test t
				where t.TID=dt.TID and a.AID=dt.AID and a.DID=:UserID order by dt.TestDate LIMIT 5 ");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result->execute();
			while ($row = $result->fetch())
			{
				$rtest[] = array('TID' => (string)$row['TID'], 'Test' => (string)$row['Test'], 'TAB' => (string)$row['TAB'] );
			}

			$result2 = $db->prepare(" SELECT t.TID, t.Test, t.TAB, count(dt.TID) from appointment3 a inner join doctortest dt inner join test t
				where t.TID=dt.TID and a.AID=dt.AID and a.DID=:UserID group by dt.TID LIMIT 5 ");
			$result2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result2->execute();
			while ($row2 = $result2->fetch())
			{
				$ftest[] = array('TID' => (string)$row2['TID'], 'Test' => (string)$row2['Test'], 'TAB' => (string)$row2['TAB'] );
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Test Data";
			$response['RecentTest'] = $rtest;
			$response['FrequentTest'] = $ftest;

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
