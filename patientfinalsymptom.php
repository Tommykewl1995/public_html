<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$json = file_get_contents('php://input');
$obj = json_decode($json, true);
$ids = array();
$i = 0;
$key_array = array();
$symptoms = array();
$current = array();
$return = array();
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
			foreach($obj['Symptoms'] as $symptoms){
					if(!in_array($symptoms['SymptomID'], $key_array))
		            {
		            	$key_array[$i] = $symptoms['SymptomID'];
		            	$i++;
		            	if(!is_null($symptoms['SymptomID']) && $symptoms['SymptomID'] != "")
		            	{
		            		$result3 = $db->prepare("SELECT SymptomName from symptoms where SymptomID = :SymptomID");
							$result3->bindParam(':SymptomID', $symptoms['SymptomID'], PDO::PARAM_STR);
							$result3->execute();
							$row3 = $result3->fetch();
							$result5 = $db->prepare("INSERT INTO patientfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							$result5->bindParam(':PFID', $obj['PFID'], PDO::PARAM_STR);
							$result5->bindParam(':Symptom', $row3['SymptomName'], PDO::PARAM_STR);
							$result5->bindParam(':SymptomChoice', $symptoms['ChoiceID'], PDO::PARAM_STR);
							$result5->execute();
							$result15 = $db->prepare("INSERT INTO doctorfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							$result15->bindParam(':PFID', $obj['PFID'], PDO::PARAM_STR);
							$result15->bindParam(':Symptom', $row3['SymptomName'], PDO::PARAM_STR);
							$result15->bindParam(':SymptomChoice', $symptoms['ChoiceID'], PDO::PARAM_STR);
							$result15->execute();
							$current[] = array('Symptom' => $row3['SymptomName'], 'SymptomChoice' => $symptoms['ChoiceID']);
		            	}
					}
			}
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Symptoms Submitted";
			$response['Symptoms'] = $current;

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
