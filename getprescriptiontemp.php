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
				$result = $db->prepare("SELECT m.Medicine, dtm.Dosage, dtm.Type, dtm.Morning, dtm.Afternoon, dtm.Night, dtm.IsAfter, dtm.OnNeed, dtm.Days
				 from doctortempmedicine dtm inner join medicine m where dtm.AID = :AID and m.MID = dtm.MID order by dtm.DTMID");
				$result->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result->execute();

				while ($row = $result->fetch())
				{
					$prescription[] = array('Medicine' => (string)$row['Medicine'], 'Dosage' => (string)$row['Dosage'], 'Type' => (string)$row['Type'], 'Morning' => (string)$row['Morning'], 'Afternoon' => (string)$row['Afternoon'], 'Night' => (string)$row['Night'],
						'IsAfter' => (string)$row['IsAfter'], 'OnNeed' => (string)$row['OnNeed'], 'Days' => (string)$row['Days']);
				}

				$result2 = $db->prepare("SELECT t.Test, t.TAB, dtt.TestDate from doctortemptest dtt inner join test t where dtt.AID = :AID and t.TID = dtt.TID order by dtt.DTTID");
				$result2->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result2->execute();

				while ($row2 = $result2->fetch())
				{
					$test[] = array('TestName' => (string)$row2['Test'], 'TAB' => (string)$row2['TAB']);
				}

				$result3 = $db->prepare("SELECT Comment from doctortempcomment where AID = :AID");
				$result3->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result3->execute();
				$row3 = $result3->fetch();

				$result7 = $db->prepare("SELECT Notes from doctortempnotes where AID = :AID");
				$result7->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result7->execute();
				$row7 = $result7->fetch();

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Prescription Data";
			$response['Prescription'] = $prescription;
			$response['Test'] = $test;
			if(!is_null($row3['Comment']))
				$response['Comment'] = (string)$row3['Comment'];
			else
				$response['Comment'] = "";

			if(!is_null($row7['Notes']))
				$response['Notes'] = (string)$row7['Notes'];
			else
				$response['Notes'] = "";

				$result4 = $db->prepare("DELETE from doctortempcomment where AID = :AID");
				$result4->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result4->execute();
				$result8 = $db->prepare("DELETE from doctortempnotes where AID = :AID");
				$result8->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result8->execute();
				$result5 = $db->prepare("DELETE from doctortemptest where AID = :AID");
				$result5->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result5->execute();
				$result6 = $db->prepare("DELETE from doctortempmedicine where AID = :AID");
				$result6->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result6->execute();

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
