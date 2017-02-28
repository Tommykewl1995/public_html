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
			if($obj['FName']){
				$resultu = $db->prepare("UPDATE user SET FName = :FName, LName = :LName where UserID = :UserID");
				$resultu->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
				$resultu->bindParam(':FName', $obj['FName'], PDO::PARAM_STR);
				$resultu->bindParam(':LName', $obj['LName'], PDO::PARAM_STR);
				$resultu->execute();
				$resultu2 = $db->prepare("UPDATE doctorprofile SET SecPhoneNo = :SecPhone where DID = :UserID");
				$resultu2->bindParam(':SecPhone', $obj['SecPhone'], PDO::PARAM_STR);
				$resultu2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
				$resultu2->execute();
			}
			if($obj['Pic']){
				$resultu = $db->prepare("UPDATE user SET Pic= :Pic where UserID = :UserID");
				$resultu->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
				$resultu->bindParam(':Pic', $obj['Pic'], PDO::PARAM_STR);
				$resultu->execute();
			}
			if($obj['DOB']){
				$datetime2 = new DateTime($obj['DOB']);
				$datetime2 = $datetime2->format('Y-m-d');
				$resultu = $db->prepare("UPDATE user SET DOB = :DOB, Gender = :Sex where UserID = :UserID");
				$resultu->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
				$resultu->bindParam(':Sex', $obj['Sex'], PDO::PARAM_STR);
				$resultu->bindParam(':DOB', $datetime2, PDO::PARAM_STR);
				$resultu->execute();
			}
			$query = $db->prepare("SELECT 1 FROM verify WHERE DeviceID = :DeviceID");
			$query->bindParam(":DeviceID", $obj['DeviceID'], PDO::PARAM_STR);
			$query->execute();
			if($que = $query->fetch()){
				$datetime1 = $obj['ExStart'];
				$datetime1 = strtotime($datetime1);
				$datetime1 = date('Y-m-d',$datetime1);
				$datetime1 = new DateTime($obj['ExStart']);
				$datetime1 = $datetime1->format('Y-m-d');
				$resultu = $db->prepare("UPDATE user SET FName = :FName, Pic= :Pic, LName = :LName, DOB = :DOB, Gender = :Sex where UserID = :UserID");
				$resultu->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
				$resultu->bindParam(':Sex', $obj['Sex'], PDO::PARAM_INT);
				$resultu->bindParam(':Pic', $obj['Pic'], PDO::PARAM_STR);
				$resultu->bindParam(':DOB', $datetime2, PDO::PARAM_STR);
				$resultu->bindParam(':FName', $obj['FName'], PDO::PARAM_STR);
				$resultu->bindParam(':LName', $obj['LName'], PDO::PARAM_STR);
				$resultu->execute();
				if($obj['Summary']){
					$result = $db->prepare("UPDATE doctorprofile SET Summary = :Summary WHERE DID = :UserID");
					$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
					$result->bindParam(':Summary', $obj['Summary'], PDO::PARAM_STR);
					$result->execute();
				}
				$result = $db->prepare("UPDATE doctorprofile SET ExStart=:ExStart, Summary = :Summary, RegNo = :RegNo, RegAssoc = :RegAssoc, RegYear= :RegYear, DoctorSign = :DoctorSign where DID=:UserID");
				$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
				$result->bindParam(':ExStart', $datetime1, PDO::PARAM_STR);
				$result->bindParam(':RegNo', $obj['RegNo'], PDO::PARAM_STR);
				$result->bindParam(':RegAssoc', $obj['RegAssoc'], PDO::PARAM_STR);
				$result->bindParam(':RegYear', $obj['RegYear'], PDO::PARAM_STR);
				$result->bindParam(':Summary', $obj['Summary'], PDO::PARAM_STR);
                                $result->bindParam(':DoctorSign', $obj['DoctorSign'], PDO::PARAM_STR);
				$result->execute();
				$result1 = $db->prepare("DELETE from doctorspec where DID=:UserID");
				$result1->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
				$result1->execute();
				foreach ($obj['Speciality'] as $row2)
				{
					$result21 = $db->prepare("SELECT SpecID from speciality where Speciality = :Speciality");
					$result21->bindParam(':Speciality', $row2['name'], PDO::PARAM_STR);
					$result21->execute();
					$row21 = $result21->fetch();
					$result2 = $db->prepare("INSERT INTO doctorspec (DID,SpecID) VALUES (:UserID, :SpecID)");
					$result2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
					$result2->bindParam(':SpecID', $row21['SpecID'], PDO::PARAM_STR);
					$result2->execute();
				}
				$result5 = $db->prepare("DELETE from doctordegree where DID=:UserID");
				$result5->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
				$result5->execute();
				foreach ($obj['Degree'] as $row4)
				{
					$result23 = $db->prepare("SELECT DegreeID from degree where Degree = :Degree");
					$result23->bindParam(':Degree', $row4['name'], PDO::PARAM_STR);
					$result23->execute();
					$row23 = $result23->fetch();
					$result6 = $db->prepare("INSERT INTO doctordegree (DID,DegreeID) VALUES (:UserID, :DegreeID)");
					$result6->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
					$result6->bindParam(':DegreeID', $row23['DegreeID'], PDO::PARAM_STR);
					$result6->execute();
				}
				$result7 = $db->prepare("DELETE from doctoraffil where DID=:UserID");
				$result7->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
				$result7->execute();

				foreach ($obj['Affiliation'] as $row5)
				{
					$result24 = $db->prepare("SELECT AffilID from affiliations where Affiliation = :Affiliation");
					$result24->bindParam(':Affiliation', $row5['name'], PDO::PARAM_STR);
					$result24->execute();
					$row24 = $result24->fetch();
					$result8 = $db->prepare("INSERT INTO doctoraffil (DID,AffilID,DocAffil) VALUES (:UserID, :AffilID, :DocAffil)");
					$result8->bindParam(':DocAffil', $row5['name'], PDO::PARAM_STR);
					$result8->bindParam(':AffilID', $row24['AffilID'], PDO::PARAM_STR);
					$result8->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
					$result8->execute();
				}
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Updated Successfully";


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
