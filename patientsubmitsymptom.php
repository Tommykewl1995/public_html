
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
			$result = $db->prepare("SELECT Phone, FName, LName, DOB, Gender from user where UserID = :UserID");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();
			$datetime = new DateTime(date("Y-m-d H:i:s"));
			$datetime1 = new DateTime($row['DOB']);
            $interval = $datetime1->diff($datetime);
            $interval = $interval->format('%y');
            $name = $row['FName']." ".$row['LName'];

			$query = $db->prepare(" INSERT into patientform (Name, Phone, Age, Gender, PID) values (:Name, :Phone, :Age, :Gender, :PID)");
			$query->bindParam(':Name', $name, PDO::PARAM_STR);
			$query->bindParam(':Phone', $row['Phone'], PDO::PARAM_STR);
			$query->bindParam(':Age', $interval, PDO::PARAM_STR);
			$query->bindParam(':Gender', $row['Gender'], PDO::PARAM_STR);
			$query->bindParam(':PID', $obj['UserID'], PDO::PARAM_STR);
			$query->execute();
			$pfid = $db->lastInsertId();
			foreach($obj['Symptoms'] as $symptoms)
			{
            	if(!is_null($symptoms['Symptom']) && $symptoms['Symptom'] != "")
            	{
					$result3 = $db->prepare("SELECT SymptomID from symptoms where SymptomName = :Symptom");
					$result3->bindParam(':Symptom', $symptoms['Symptom'], PDO::PARAM_STR);
					$result3->execute();
					$row3 = $result3->fetch();
					$ids[] = array('id' => (string)$row3['SymptomID']);

					$result5 = $db->prepare("INSERT INTO patientsymptom (PFID, Symptom) VALUES (:PFID, :Symptom)");
					$result5->bindParam(':PFID', $pfid, PDO::PARAM_STR);
					$result5->bindParam(':Symptom', $symptoms['Symptom'], PDO::PARAM_STR);
					$result5->execute();
				}
			}


            $id = array();
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Form Submitted";
			$response['PFID'] = (string)$pfid;
			$response['sex']=(string)$row['Gender'];
			$response['age']=(string)$interval;
			if(is_null($ids))
				$response['id'] = $id;
			else
				$response['id'] = $ids;


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
