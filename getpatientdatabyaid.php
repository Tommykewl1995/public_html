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
$prescription=array();
$test = array();

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
			$result = $db->prepare("SELECT DID, PID, PFID, PrescriptionDate from appointment3 where AID=:AID");
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

			$result5 = $db->prepare("SELECT m.Medicine, dtm.Dosage, dtm.Type, dtm.Morning, dtm.Afternoon, dtm.Night, dtm.IsAfter, dtm.OnNeed, dtm.Days
				 from doctormedicine dtm inner join medicine m where dtm.AID = :AID and m.MID = dtm.MID order by dtm.DMID");
				$result5->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result5->execute();

				while ($row5 = $result5->fetch())
				{
					$prescription[] = array('Medicine' => (string)$row5['Medicine'], 'Dosage' => (string)$row5['Dosage'], 'Type' => (string)$row5['Type'], 'Morning' => (string)$row5['Morning'], 'Afternoon' => (string)$row5['Afternoon'], 'Night' => (string)$row5['Night'],
						'IsAfter' => (string)$row5['IsAfter'], 'OnNeed' => (string)$row5['OnNeed'], 'Days' => (string)$row5['Days']);
					$date = date("d/m/y", strtotime($row['PrescriptionDate']));
					$response['MedDate'] = (string)$date;
				}


				$result6 = $db->prepare("SELECT t.Test, t.TAB, dtt.TestDate from doctortest dtt inner join test t where dtt.AID = :AID and t.TID = dtt.TID order by dtt.DTID");
				$result6->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result6->execute();

				while ($row6 = $result6->fetch())
				{
					if(is_null($row6['TAB']))
						$tab = "";
					else
						$tab = $row6['TAB'];

					$test[] = array('TestName' => (string)$row6['Test'], 'TAB' => (string)$tab);
				}

				$result7 = $db->prepare("SELECT Comment from doctorcomment where AID = :AID");
				$result7->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result7->execute();
				$row7 = $result7->fetch();

				$result8 = $db->prepare("SELECT Notes from doctornotes where AID = :AID");
				$result8->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result8->execute();
				$row8 = $result8->fetch();

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Data";
			$name = $row2['FName']." ".$row2['LName'];
			$response['Name'] = $name;
			$response['PFID'] = $row['PFID'];
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

			if(empty($condition))
			{
				$condition[] = array('ConditionID' => "0", 'ConditionName' => "Not Found", 'CondProb' => "");
			}

			$response['Condition'] = $condition;
			$response['DetailSymptom'] = $sym;
			$response['Prescription'] = $prescription;
			$response['Test'] = $test;
			$response['Comment'] = (string)$row7['Comment'];
			$response['Notes'] = (string)$row8['Notes'];

			$result10 = $db->prepare("SELECT SNID FROM SymTracker WHERE PFID = :PFID");
		    $result10->bindParam(":PFID", $row['PFID'], PDO::PARAM_INT);
		    $result10->execute();
		    while($row10 = $result10->fetch())
		    {
				$result9 = $db->prepare("SELECT st.SID,st.Strength,st.T,s.SymptomName FROM SymTracker st INNER JOIN symptoms s ON s.SymptomID= st.SID WHERE st.SNID = :SNID");
			    $result9->bindParam(":SNID", $row10['SNID'], PDO::PARAM_INT);
			    $result9->execute();
			    $data = array();
			    while($row9 = $result9->fetch())
			    	{
				      $data[] = array("Name" => $row9['SymptomName'], "SID" => $row9['SID'], "Strength" => (int)$row9['Strength']);
				     	$t = $row9['T'];
			    	}
			    $trackerdata[] = array('Data' =>$data , 'Time'=>$t);
		    }
		    $response['T'] = $t;
		    $response['Data'] = $data;
		    $response['Tracker'] = $trackerdata;

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
