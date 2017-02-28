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

$sharedsymptoms = array();
	try
		{
			$result4 = $db->prepare(" SELECT AID, PID, PFID from appointment3 bh where DID=:DID and Status='Active' order by AID desc");
			$result4->bindParam(':DID', $obj['UserID'], PDO::PARAM_INT);
			$result4->execute();
			while ($row4 = $result4->fetch())
			{
				$result5 = $db->prepare(" SELECT FName, LName, Pic from user u where UserID = :UserID");
				$result5->bindParam(':UserID', $row4['PID'], PDO::PARAM_INT);
				$result5->execute();
				$row5 = $result5->fetch();

				$result6 = $db->prepare(" SELECT Symptom from patientfinalsymptom where PFID = :PFID and SymptomChoice='present' ");
				$result6->bindParam(':PFID', $row4['PFID'], PDO::PARAM_INT);
				$result6->execute();
				$symptoms = "";
				while ($row6 = $result6->fetch())
				{
					$symptoms = $symptoms.$row6['Symptom'].", ";
				}
				$symptoms = substr($symptoms, 0, -2);

				if(is_null($row4['Pic'])){
					$fpic = "http://52.24.83.227/default.png";
				}else{
					$fpic = $row4['Pic'];
				}
				$sharedsymptoms[] = array('FName' => (string)$row5['FName'], 'LName' => (string)$row5['LName'], 'Pic' => (string)$fpic, 'AID' => (string)$row4['AID'], 'PID' => (string)$row4['PID'], 'PFID' => (string)$row4['PFID'], 'Symptom' => (string)$symptoms );
			}


			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Shared Symptoms List";
			$response['SharedSymptoms'] = $sharedsymptoms;

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
// }
