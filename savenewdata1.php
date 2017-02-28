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
			$query2 = $db->prepare("SELECT UserID from user where Phone=:Phone");
			$query2->bindParam(':Phone', $obj['phone'], PDO::PARAM_STR);
			$query2->execute();
			$row = $query2->fetch();

			// $result = $db->prepare("INSERT INTO patientform (Name, Phone, Age, Gender, PID) VALUES (:Name, :Phone, :Age, :Gender, :PID)");
			// $result->bindParam(':Name', $obj['name'], PDO::PARAM_STR);
			// $result->bindParam(':Phone', $obj['phone'], PDO::PARAM_STR);
			// $result->bindParam(':Age', $obj['age'], PDO::PARAM_STR);
			// $result->bindParam(':Gender', $obj['sex'], PDO::PARAM_STR);
			// $result->bindParam(':PID', $row['UserID'], PDO::PARAM_STR);
			// $result->execute();
			$pfid = 99;
			//$db->lastInsertId();

			foreach($obj['symptoms'] as $symptoms)
			{
					if(!in_array($symptoms['id'], $key_array))
		            {
		            	$key_array[$i] = $symptoms['id'];
		            	$i++;
		            	if(!is_null($symptoms['id']) && $symptoms['id'] != "")
		            	{
							$result5 = $db->prepare("INSERT INTO patientfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							$result5->bindParam(':PFID', $pfid, PDO::PARAM_STR);
							$result5->bindParam(':Symptom', $symptoms['name'], PDO::PARAM_STR);
							$result5->bindParam(':SymptomChoice', $symptoms['choice_id'], PDO::PARAM_STR);
							$result5->execute();
							$result15 = $db->prepare("INSERT INTO doctorfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							$result15->bindParam(':PFID', $pfid, PDO::PARAM_STR);
							$result15->bindParam(':Symptom', $symptoms['name'], PDO::PARAM_STR);
							$result15->bindParam(':SymptomChoice', $symptoms['choice_id'], PDO::PARAM_STR);
							$result15->execute();
		            	}
					}
			}

			$count = count($obj['conditions']);
			if($count<=10)
			{
				foreach($obj['conditions'] as $conditions)
				{
						$condprob = $conditions['CondProb'];
						$result5 = $db->prepare("INSERT INTO patientcondition (PFID, ConditionName, CondProb) VALUES (:PFID, :ConditionName, :CondProb)");
						$result5->bindParam(':PFID', $pfid, PDO::PARAM_STR);
						$result5->bindParam(':ConditionName', $conditions['ConditionName'], PDO::PARAM_STR);
						$result5->bindParam(':CondProb', $condprob, PDO::PARAM_STR);
						$result5->execute();
						$result15 = $db->prepare("INSERT INTO doctorcondition (PFID, ConditionName, CondProb) VALUES (:PFID, :ConditionName, :CondProb)");
						$result15->bindParam(':PFID', $pfid, PDO::PARAM_STR);
						$result15->bindParam(':ConditionName', $conditions['ConditionName'], PDO::PARAM_STR);
						$result15->bindParam(':CondProb', $condprob, PDO::PARAM_STR);
						$result15->execute();
				}
			}
			else
			{
				for ($i=0; $i < 10; $i++)
				{
					$conditions = $obj['conditions'];
				 		$condprob = $obj['conditions'][$i]['CondProb'];
				 		$condname = $obj['conditions'][$i]['ConditionName'];
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

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "New Patient Data Submitted";
			//$response['PFID'] = (string)$pfid;

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
