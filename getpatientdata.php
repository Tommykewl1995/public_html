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
			$result = $db->prepare("SELECT PID, PFID from appointment3 where AID=:AID");
			$result->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();

			$result2 = $db->prepare("SELECT u.FName, u.Phone, u.Email, u.LName, u.DOB, pp.Height, pp.Weight, u.Gender, pp.BloodGroup, pp.Allergies, pp.Hereditory, u.Pic
			from user u inner join patientprofile pp where u.UserID = pp.PID and pp.PID=:PID");
			$result2->bindParam(':PID', $row['PID'], PDO::PARAM_STR);
			$result2->execute();
			$row2 = $result2->fetch();

			$result3 = $db->prepare("SELECT s.SymptomID, pfs.Symptom, pfs.SymptomChoice from doctorfinalsymptom pfs inner join symptoms s
			on s.SymptomName = pfs.Symptom where PFID=:PFID");
			$result3->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result3->execute();
			$symptoms = "";
			while ($row3 = $result3->fetch())
			{
				$symptoms = $symptoms.$row3['Symptom'].", ";
				$sym[] = array('SymptomID' => (string)$row3['SymptomID'], 'Symptom' => (string)$row3['Symptom'], 'SymptomChoice' => (string)$row3['SymptomChoice']);
			}
			$symptoms = substr($symptoms, 0, -2);

			$result4 = $db->prepare("SELECT c.ConditionID, pc.ConditionName, pc.CondProb from doctorcondition pc inner join conditions c
			on c.ConditionName=pc.ConditionName where pc.PFID=:PFID order by pc.CondProb desc");
			$result4->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result4->execute();
			while ($row4 = $result4->fetch())
			{
				$condition[] = array('ConditionID' => (string)$row4['ConditionID'], 'ConditionName' => (string)$row4['ConditionName'], 'CondProb' => (string)$row4['CondProb']);
			}

			$result5 = $db->prepare("SELECT pq.PQID, pq.PFID, pq.PQuery, pq.PQImg, dq.DQID, dq.DQuery from patientquery pq left outer join doctorquery dq on pq.PQID=dq.PQID where pq.PFID=:PFID order by pq.PQDateTime, dq.DQDateTime");
			$result5->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result5->execute();
			$key_array = array();
			$i = 0;
			while ($row5 = $result5->fetch()){
				if(!in_array($row5['PQID'], $key_array)){
		      $key_array[$i] = $row5['PQID'];
		      $i++;
					$query[] = array('PQID' => (string)$row5['PQID'], 'PFID' => (string)$row5['PFID'], 'PQuery' => (string)$row5['PQuery'], 'PQImg' => (string)$row5['PQImg'], 'DQID' => (string)$row5['DQID'], 'DQuery' => (string)$row5['DQuery']);
				}
			}
			$result6 = $db->prepare("SELECT pq.PQID, pq.PFID, pq.PQuery, pq.PQImg, dq.DQID, dq.DQuery from patientquery pq right outer join doctorquery dq on pq.PQID=dq.PQID where dq.PFID=:PFID order by pq.PQDateTime, dq.DQDateTime");
			$result6->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result6->execute();
			while ($row6 = $result6->fetch()){
				if(!in_array($row6['PQID'], $key_array))
				{
					$key_array[$i] = $row6['PQID'];
					$i++;
					$query[] = array('PQID' => (string)$row6['PQID'], 'PFID' => (string)$row6['PFID'], 'PQuery' => (string)$row6['PQuery'], 'PQImg' => (string)$row6['PQImg'], 'DQID' => (string)$row6['DQID'], 'DQuery' => (string)$row6['DQuery']);
				}
			}
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Data";
			$name = $row2['FName']." ".$row2['LName'];
			$response['Name'] = $name;
			$response['PFID'] = $row['PFID'];
			$response['query'] = $query;
			$response['Phone'] = $row2['Phone'];
			$response['Email'] = $row2['Email'];

			if(!is_null($row2['DOB']))
			{
				$newDate = date("d-m-Y", strtotime($row2['DOB']));
				$response['DOB'] = (string)$newDate;
				$datetime = new DateTime(date("Y-m-d H:i:s"));
				$datetime1 = new DateTime($row2['DOB']);
            	$interval = $datetime1->diff($datetime);
            	$interval = $interval->format('%y');
            	$response['Age'] = (string)$interval;
			}
			else
			{
				$response['DOB'] = "";
				$response['Age'] = "";
			}

			if(!is_null($row2['BloodGroup']))
				$response['BloodGroup'] = (string)$row2['BloodGroup'];
			else
				$response['BloodGroup'] = "";

			if(!is_null($row2['Pic']))
				$response['Pic'] = (string)$row2['Pic'];
			else
				$response['Pic'] = "http://ec2-52-37-68-149.us-west-2.compute.amazonaws.com/default.png";

			if(!is_null($row2['Allergies']))
				$response['Allergies'] = (string)$row2['Allergies'];
			else
				$response['Allergies'] = "";

			if(!is_null($row2['Hereditory']))
				$response['Hereditory'] = (string)$row2['Hereditory'];
			else
				$response['Hereditory'] = "";

			if(!is_null($row2['Height']))
				$response['Height'] = (string)$row2['Height'];
			else
				$response['Height'] = "";

			if(!is_null($row2['Weight']))
				$response['Weight'] = (string)$row2['Weight'];
			else
				$response['Weight'] = "";

			if(!is_null($row2['Gender']))
				$response['Gender'] = (string)$row2['Gender'];
			else
				$response['Gender'] = "";


			if(!is_null($symptoms))
				$response['Symptom'] = (string)$symptoms;
			else
				$response['Symptom'] = "";

			// foreach ($obj['Symptom'] as $symp)
			// {
			// 	$result5 = $db->prepare("SELECT SymptomName from symptoms where SymptomID=:SymptomID");
			// 	$result5->bindParam(':SymptomID', $symp['id'], PDO::PARAM_STR);
			// 	$result5->execute();
			// 	$row5 = $result5->fetch();
			// 	$sy[] = array('ConditionID' => (string)$row4['ConditionID'], 'ConditionName' => (string)$row4['ConditionName'], 'CondProb' => (string)$row4['CondProb']);
			// }

			if(empty($condition))
			{
				$condition[] = array('ConditionID' => "0", 'ConditionName' => "Not Found", 'CondProb' => "NA");
			}
			$response['Condition'] = $condition;
			$response['DetailSymptom'] = $sym;


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
