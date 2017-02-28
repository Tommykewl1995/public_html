<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];

include('db_config.php');
include('helperfunctions1.php');
date_default_timezone_set('Asia/Kolkata');
require_once 'Bcrypt.php';
$key_array=array();
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

	try
		{
			$query = $db->prepare("SELECT CurrClinicID FROM doctorprofile WHERE DID = :UserID");
			$query->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
			$query->execute();
			$que = $query->fetch();
			$query2 = $db->prepare("SELECT UserID from user where Phone=:Phone");
			$query2->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
			$query2->execute();
      $query3 = $db->prepare("SELECT CommuID from ComDetails where CreatorID = :UserID");
			$query3->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$query3->execute();
			$row3 = $query3->fetch();
			//$row = $query2->fetch();
			$count2 = $query2->rowCount();
			if($count2 == 0){
        $password = generatePIN(8);
        $password1 = Bcrypt::hashPassword($password);
				$obj['Sex'] = ($obj['Sex'])?$obj['Sex']:'male';
				$obj['DOB'] = (float)$obj['DOB'];
				$date = date('Y-m-d', $obj['DOB']);
				$result0 = $db->prepare("INSERT INTO user (FName, LName, Phone, Password, IsDoctor, IsRegistered, IsLoggedin, IsUpdated, IsSelfCreated, RegDate, Gender, DOB) VALUES (:FName, '', :Phone, :Password, 0, 1, 0, 0, 0, Now(), :Gender, :DOB)");
				$result0->bindParam(':FName', $obj['Name'], PDO::PARAM_STR);
				$result0->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
				$result0->bindParam(':Gender', $obj['Sex'], PDO::PARAM_STR);
				$result0->bindParam(':DOB', $date);
				$result0->bindParam(':Password', $password1, PDO::PARAM_STR);
				$result0->execute();
				$query0 = $db->prepare("SELECT UserID from user where Phone=:Phone");
				$query0->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
				$query0->execute();
				$row =  $query0->fetch();
				$result10 = $db->prepare("INSERT INTO Dconnection (CommuID, UserID, UserType) VALUES (1, :UserID, 0)");
		    $result10->bindParam(":UserID", $row['UserID'], PDO::PARAM_STR);
			  $result10->execute();
				$result2 = $db->prepare("INSERT INTO patientprofile (PID) VALUES (:PID)");
				$result2->bindParam(':PID', $row['UserID'], PDO::PARAM_INT);
				$result2->execute();
				$query5 = $db->prepare("SELECT * from user where UserID=:UserID");
				$query5->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
				$query5->execute();
				$row5 =  $query5->fetch();
        $doctorName =  "Dr. ".$row5['FName']." ".$row5['LName'];
				list($otp_code,$message) = sendotp( $obj['Phone'],$password, "doctorCreated", $doctorName);
			}
			else{
				$row = $query2->fetch();
			}



			$result = $db->prepare("INSERT INTO patientform (Name, Phone, Age, Gender, PID) VALUES (:Name, :Phone, :Age, :Gender, :PID)");
			$result->bindParam(':Name', $obj['Name'], PDO::PARAM_STR);
			$result->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
			$result->bindParam(':Age', $obj['Age'], PDO::PARAM_STR);
			$result->bindParam(':Gender', $obj['Sex'], PDO::PARAM_STR);
			$result->bindParam(':PID', $row['UserID'], PDO::PARAM_STR);
			$result->execute();
			$pfid = $db->lastInsertId();

			foreach($obj['Symptoms'] as $symptoms)
			{
					if(!in_array($symptoms['id'], $key_array))
		            {
		            	$key_array[$i] = $symptoms['id'];
		            	$i++;
		            	if(!is_null($symptoms['id']) && $symptoms['id'] != "")
		            	{
							$result25 = $db->prepare("INSERT INTO patientfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							$result25->bindParam(':PFID', $pfid, PDO::PARAM_STR);
							$result25->bindParam(':Symptom', $symptoms['name'], PDO::PARAM_STR);
							$result25->bindParam(':SymptomChoice', $symptoms['choice_id'], PDO::PARAM_STR);
							$result25->execute();
							$result35 = $db->prepare("INSERT INTO doctorfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							$result35->bindParam(':PFID', $pfid, PDO::PARAM_STR);
							$result35->bindParam(':Symptom', $symptoms['name'], PDO::PARAM_STR);
							$result35->bindParam(':SymptomChoice', $symptoms['choice_id'], PDO::PARAM_STR);
							$result35->execute();
		            	}
					}
			}

			$count = count($obj['Conditions']);
			if($count<=10)
			{
				foreach($obj['Conditions'] as $conditions)
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
					$conditions = $obj['Conditions'];
				 		$condprob = $obj['Conditions'][$i]['CondProb'];
				 		$condname = $obj['Conditions'][$i]['ConditionName'];
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
      $query4 = $db->prepare("SELECT CommuID from Dconnection where UserID=:UserID and UserType=1 and CommuID=:CommuID");
			$query4->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
			$query4->bindParam(':CommuID', $row3['CommuID'], PDO::PARAM_STR);
			$query4->execute();
			//$row = $query2->fetch();
			$count4 = $query4->rowCount();
      if($count4 == 0){
			$result2 = $db->prepare("INSERT INTO Dconnection (CommuID, UserID, UserType) VALUES (:CommuID, :UserID, 1)");
			$result2->bindParam(':CommuID', $row3['CommuID'], PDO::PARAM_STR);
			$result2->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
			$result2->execute();
                        }

			$result3 = $db->prepare("INSERT INTO appointment3 (DID, PID, PFID,Status, ClinicID) VALUES (:DID, :PID, :PFID,'Active', :ClinicID)");
			$result3->bindParam(':DID', $obj['UserID'], PDO::PARAM_STR);
			$result3->bindParam(':PID', $row['UserID'], PDO::PARAM_STR);
			$result3->bindParam(':PFID', $pfid, PDO::PARAM_STR);
                        $result3->bindParam(':ClinicID', $que['CurrClinicID'], PDO::PARAM_INT);
			$result3->execute();
			$aid = $db->lastInsertId();
			$result5 = $db->prepare("UPDATE patientprofile SET CPFID=:CPFID where PID=:PID");
			$result5->bindParam(':PID', $row['UserID'], PDO::PARAM_STR);
			$result5->bindParam(':CPFID', $pfid, PDO::PARAM_STR);
			$result5->execute();
			$response['CurrClinic'] = ($que['CurrClinicID'])?$que['CurrClinicID']:"shit";
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "New Patient Data Submitted";
			$response['PFID'] = (string)$pfid;
			$response['AID'] = (string)$aid;
			$response['usermessage'] = $message;
                        $response['otp'] = $otp_code;

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
