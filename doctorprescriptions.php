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
			$result4 = $db->prepare(" SELECT AID, DID, PFID,PID, PrescriptionDate from appointment3 where DID=:DID and PrescriptionDate is not NULL group by AID order by AID desc");
			$result4->bindParam(':DID', $obj['UserID'], PDO::PARAM_INT);
			$result4->execute();
			while ($row4 = $result4->fetch())
			{
				$result5 = $db->prepare(" SELECT u.FName, u.LName, u.Pic from user u inner join patientprofile pp where u.UserID = :UserID and u.UserID = pp.PID");
				$result5->bindParam(':UserID', $row4['PID'], PDO::PARAM_INT);
				$result5->execute();
				$row5 = $result5->fetch();
				$date = '';
				$result6 = $db->prepare(" SELECT Symptom from patientfinalsymptom where PFID = :PFID ");
				$result6->bindParam(':PFID', $row4['PFID'], PDO::PARAM_INT);
				$result6->execute();
				$symptoms = "";
				while ($row6 = $result6->fetch())
				{
					$symptoms = $symptoms.$row6['Symptom'].", ";
				}
				$symptoms = substr($symptoms, 0, -2);
				$result3 = $db->prepare("SELECT m.Medicine,dm.Dosage,dm.Type,dm.Morning,dm.Afternoon,dm.Night,dm.IsAfter,dm.OnNeed,dm.Days FROM doctormedicine dm inner join medicine m ON m.MID = dm.MID WHERE dm.AID = :BHID");
				$result3->bindParam(':BHID', $row4['AID'],PDO::PARAM_INT);
				$result3->execute();
				while($row = $result3->fetch()){
					$date = date("d/m/y", strtotime($row4['PrescriptionDate']));
					$rx[] = array("Medicine" => $row['Medicine'], "Dosage" => (string)$row['Dosage']." ".(string)$row['Type'], "Morning" => $row['Morning'], "Night" => $row['Night'], "Afternoon" => $row['Afternoon'], "IsAfter" => $row['IsAfter'], "OnNeed" => $row['OnNeed'], "Days" => $row['Days']);
				}
				if(is_null($row5['Pic'])){
					$fpic = "http://ec2-52-37-68-149.us-west-2.compute.amazonaws.com/default.png";
				}else{
					$fpic = $row5['Pic'];
				}

				$previous[] = array('Date' => $date, 'FName' => (string)$row5['FName'], 'LName' => (string)$row5['LName'], 'Pic' => (string)$fpic, 'AID' => (string)$row4['AID'], 'Rx' => $rx, 'Symptom' => (string)$symptoms );

			}


			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Doctor Prescription List";

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
