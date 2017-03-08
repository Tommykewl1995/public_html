<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');


$json = file_get_contents('php://input');
$obj = json_decode($json, true);
$ids = array();

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
			$key_array = array();
			$result = $db->prepare("SELECT Phone, FName, LName, DOB, Gender from user where UserID = :UserID");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();
			$datetime = new DateTime(date("Y-m-d H:i:s"));
			$datetime1 = new DateTime($row['DOB']);
      $interval = $datetime1->diff($datetime);
      $interval = $interval->format('%y');
      $name = $row['FName']." ".$row['LName'];

			$query = $db->prepare("INSERT into patientform (Name, Phone, Age, Gender, PID) values (:Name, :Phone, :Age, :Gender, :PID)");
			$query->bindParam(':Name', $name, PDO::PARAM_STR);
			$query->bindParam(':Phone', $row['Phone'], PDO::PARAM_STR);
			$query->bindParam(':Age', $interval, PDO::PARAM_STR);
			$query->bindParam(':Gender', $row['Gender'], PDO::PARAM_STR);
			$query->bindParam(':PID', $obj['UserID'], PDO::PARAM_STR);
			$query->execute();
			$pfid = $db->lastInsertId();
			$maxcond = (count($obj['Conditions']) > 10)?10:count($obj['Conditions']);
      for($i=0; $i < $maxcond; $i++){
				$conditions = $obj['Conditions'][$i];
		 		$condprob = $conditions['CondProb']*100;
		 		$condname = $conditions['ConditionName'];
				if($condname && $condprob){
					$result5 = $db->prepare("INSERT INTO patientcondition (PFID, ConditionName, CondProb) VALUES (:PFID, :ConditionName, :CondProb)");
					$result5->bindParam(':PFID', $pfid, PDO::PARAM_STR);
					$result5->bindParam(':ConditionName', $condname, PDO::PARAM_STR);
					$result5->bindParam(':CondProb', $condprob, PDO::PARAM_STR);
					$result5->execute();
					$result15 = $db->prepare("INSERT INTO doctorcondition (PFID, ConditionName, CondProb) VALUES (:PFID, :ConditionName, :CondProb)");
					$result15->bindParam(':PFID', $pfid, PDO::PARAM_STR);
					$result15->bindParam(':ConditionName', $condname, PDO::PARAM_STR);
					$result15->bindParam(':CondProb', $condprob, PDO::PARAM_STR);
					$result15->execute();
				}
			}
      foreach($obj['Symptoms'] as $symptoms){
        if(!in_array($symptoms['SymptomID'], $key_array)){
        	$key_array[$i] = $symptoms['SymptomID'];
        	$i++;
          if(!is_null($symptoms['SymptomID']) && $symptoms['SymptomID'] != ""){
            $result3 = $db->prepare("SELECT SymptomName from symptoms where SymptomID = :SymptomID");
    				$result3->bindParam(':SymptomID', $symptoms['SymptomID'], PDO::PARAM_STR);
    				$result3->execute();
    				$row3 = $result3->fetch();
						if(!is_null($row3) && !is_null($row3['SymptomName'])){
							$result5 = $db->prepare("INSERT INTO patientfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
	    				$result5->bindParam(':PFID', $pfid, PDO::PARAM_STR);
	    				$result5->bindParam(':Symptom', $row3['SymptomName'], PDO::PARAM_STR);
	    				$result5->bindParam(':SymptomChoice', $symptoms['ChoiceID'], PDO::PARAM_STR);
	    				$result5->execute();
	    				$result15 = $db->prepare("INSERT INTO doctorfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
	    				$result15->bindParam(':PFID', $pfid, PDO::PARAM_STR);
	    				$result15->bindParam(':Symptom', $row3['SymptomName'], PDO::PARAM_STR);
	    				$result15->bindParam(':SymptomChoice', $symptoms['ChoiceID'], PDO::PARAM_STR);
	    				$result15->execute();
	    				$current[] = array('Symptom' => $row3['SymptomName'], 'SymptomChoice' => $symptoms['ChoiceID']);
						}
          }
				}
			}
      $id = array();
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Form Submitted";
      $response['Symptoms'] = $current;
			$response['PFID'] = (string)$pfid;
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
