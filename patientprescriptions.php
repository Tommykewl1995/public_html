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

$previous = array();
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
			$result4 = $db->prepare(" SELECT AID, DID, PFID, PrescriptionDate from appointment3 where PID=:PID and PrescriptionDate IS NOT NULL group by AID order by AID desc ");
			$result4->bindParam(':PID', $obj['UserID'], PDO::PARAM_INT);
			$result4->execute();
			while ($row4 = $result4->fetch())
			{
				$result5 = $db->prepare(" SELECT u.FName, u.LName, u.Pic from user u inner join doctorprofile dp where UserID = :UserID and u.UserID = dp.DID");
				$result5->bindParam(':UserID', $row4['DID'], PDO::PARAM_INT);
				$result5->execute();
				$row5 = $result5->fetch();

				$result6 = $db->prepare(" SELECT Symptom from patientfinalsymptom where PFID = :PFID and SymptomChoice ='present' ");
				$result6->bindParam(':PFID', $row4['PFID'], PDO::PARAM_INT);
				$result6->execute();
				$symptoms = "";
				while ($row6 = $result6->fetch())
				{
					$symptoms = $symptoms.$row6['Symptom'].", ";
				}
				$symptoms = substr($symptoms, 0, -2);

				if(is_null($row4['Pic']))
							$fpic = "http://ec2-52-37-68-149.us-west-2.compute.amazonaws.com/default.png";
						else
							$fpic = $row4['Pic'];

				$date = date("d/m/y", strtotime($row4['PrescriptionDate']));

				$previous[] = array('FName' => (string)$row5['FName'], 'LName' => (string)$row5['LName'], 'Pic' => (string)$fpic, 'AID' => (string)$row4['AID'], 'Date' => (string)$date, 'Symptom' => (string)$symptoms );

			}


			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Prescription List";
			$response['PreviousRecords'] = $previous;

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
