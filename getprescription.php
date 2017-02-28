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
$prescription = array();
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
			$quer = $db->prepare("SELECT IsDoctor from user where UserID = :UserID");
			$quer->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$quer->execute();
			$isdoctor = $quer->fetch();

			$query = $db->prepare("SELECT * from appointment3 where AID = :AID");
			$query->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
			$query->execute();
			$pfid = $query->fetch();

				$result = $db->prepare("SELECT m.Medicine, dtm.Dosage, dtm.Type, dtm.Morning, dtm.Afternoon, dtm.Night, dtm.IsAfter, dtm.OnNeed, dtm.Days
				 from doctormedicine dtm inner join medicine m where dtm.AID = :AID and m.MID = dtm.MID order by dtm.DMID");
				$result->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result->execute();

				while ($row = $result->fetch())
				{
					$prescription[] = array('Medicine' => (string)$row['Medicine'], 'Dosage' => (string)$row['Dosage'], 'Type' => (string)$row['Type'], 'Morning' => (string)$row['Morning'], 'Afternoon' => (string)$row['Afternoon'], 'Night' => (string)$row['Night'],
						'IsAfter' => (string)$row['IsAfter'], 'OnNeed' => (string)$row['OnNeed'], 'Days' => (string)$row['Days']);
				}
				$result2 = $db->prepare("SELECT t.Test, t.TAB, dtt.TestDate from doctortest dtt inner join test t where dtt.AID = :AID and t.TID = dtt.TID order by dtt.DTID");
				$result2->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result2->execute();

				while ($row2 = $result2->fetch())
				{
					if(is_null($row2['TAB']))
						$tab = "";
					else
						$tab = $row2['TAB'];

					$test[] = array('TestName' => (string)$row2['Test'], 'TAB' => (string)$tab);
				}

				$result3 = $db->prepare("SELECT Comment from doctorcomment where AID = :AID");
				$result3->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result3->execute();
				$row3 = $result3->fetch();

				$result13 = $db->prepare("SELECT Notes from doctornotes where AID = :AID");
				$result13->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result13->execute();
				$row13 = $result13->fetch();

			$result4 = $db->prepare("SELECT  u.FName, u.LName, u.Email, u.Phone, dp.RegNo, dp.RegAssoc, dp.RegYear, dp.SecPhoneNo, dp.DoctorSign from user u inner join doctorprofile dp
				where UserID=:UserID and u.UserID = dp.DID");
			$result4->bindParam(':UserID', $pfid['DID'], PDO::PARAM_STR);
			$result4->execute();
			$row4 = $result4->fetch();

			$result5 = $db->prepare(" SELECT dd.DegreeID, Degree FROM doctordegree dd inner join degree d on dd.DegreeID
				where dd.DegreeID = d.DegreeID and DID=:UserID");
			$result5->bindParam(':UserID', $pfid['DID'], PDO::PARAM_STR);
			$result5->execute();
			$degree = "";
			while ($row5 = $result5->fetch())
			{
				$degree = $degree.$row5['Degree'].", ";
			}
			$degree = substr($degree, 0, -2);

			$result6 = $db->prepare("SELECT * from clinics where ClinicID=:ClinicID");
			$result6->bindParam(':ClinicID', $pfid['ClinicID'], PDO::PARAM_STR);
			$result6->execute();
			$row6 = $result6->fetch();

			$result7 = $db->prepare("SELECT  u.FName, u.LName, pp.Address2, pp.City, u.DOB, pp.Height, pp.Weight, pp.BloodGroup, u.Gender from user u inner join patientprofile pp
				where UserID=:UserID and u.UserID = pp.PID");
			$result7->bindParam(':UserID', $pfid['PID'], PDO::PARAM_STR);
			$result7->execute();
			$row7 = $result7->fetch();

			$date = date("d/m/y", strtotime($pfid['PrescriptionDate']));
			$response['MedDate'] = (string)$date;
			$newDate = date("d-m-Y", strtotime($row7['DOB']));
			$datetime = new DateTime(date("Y-m-d H:i:s"));
			$datetime1 = new DateTime($row7['DOB']);
	    $age = $datetime1->diff($datetime);
	    $age = $age->format('%y');
			$phone = ($row6['ClinicPhone'])?$row6['ClinicPhone']:(($row4['SecPhoneNo'])?$row4['SecPhoneNo']:$row4['Phone']);
                        $email = ($row6['ClinicEmail'])?$row6['ClinicEmail']:$row4['Email'];
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Prescription Data";
			$name = "Dr. ".$row4['FName']." ".$row4['LName'];
			$response['Name'] = (string)$name;
			$response['Degree'] = (string)$degree;
			$response['Email'] = (string)$email;
			$response['Phone'] = (string)$phone;
			$response['RegNo'] = (string)$row4['RegNo'];
			$response['RegAssoc'] = (string)$row4['RegAssoc'];
			$response['RegYear'] = (string)$row4['RegYear'];
			$response['Sign'] = (string)$row4['DoctorSign'];
			$response['Address'] = ($row6)?$row6['Address'].", ".$row6['City']."-".$row6['PinCode']:"";

			$response['PatientName'] = $row7['FName']." ".$row7['LName'];
			$response['PatientAddress'] = $row7['Address2'].", ".$row7['City'];
			$response['PatientDOB'] = (string)$newDate;
			$response['PatientAge'] = (string)$age;
			$response['PatientBloodGroup'] = (string)$row7['BloodGroup'];
			$response['PatientGender'] = (string)$row7['Gender'];
			$response['PatientHeight'] = (string)$row7['Height'];
			$response['PatientWeight'] = (string)$row7['Weight'];

$response['Prescription'] = $prescription;
			$response['Test'] = $test;
			if(!is_null($row3['Comment']))
				$response['Comment'] = (string)$row3['Comment'];
			else
				$response['Comment'] = "";

			if($isdoctor['IsDoctor']==0)
			{
				$response['Notes'] = "";
			}
			else
			{
				if(!is_null($row13['Notes']))
					$response['Notes'] = json_decode((string)$row13['Notes']);
				else
					$response['Notes'] = array("text" => "", "files" => array());
			}

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
