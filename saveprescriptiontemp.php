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
$tes = array();
$pres = array();

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
			$query = $db->prepare("SELECT PFID, PID, DID, NOW() as PrescriptionDate, ClinicID from appointment3 where AID = :AID");
			$query->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
			$query->execute();
			$pfid = $query->fetch();

$qdelete = $db->prepare("DELETE from doctortempmedicine where AID = :AID");
				$qdelete->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$qdelete->execute();

			foreach ($obj['Prescription'] as $prescription)
			{
				$query2 = $db->prepare("SELECT MID from medicine where Medicine = :Medicine");
				$query2->bindParam(':Medicine', $prescription['Medicine'], PDO::PARAM_STR);
				$query2->execute();
				$medicineid = $query2->fetch();
				if(!is_null($medicineid['MID']))
					$mid=$medicineid['MID'];
				else
				{
					$query3 = $db->prepare("INSERT into medicine (Medicine) values (:Medicine)");
					$query3->bindParam(':Medicine', $prescription['Medicine'], PDO::PARAM_STR);
					$query3->execute();
					$query4 = $db->prepare("SELECT MID from medicine where Medicine = :Medicine");
					$query4->bindParam(':Medicine', $prescription['Medicine'], PDO::PARAM_STR);
					$query4->execute();
					$row = $query4->fetch();
					$mid = $row['MID'];
				}
				
				$result = $db->prepare("INSERT into doctortempmedicine (AID, PFID, MID, Dosage, Type, Morning, Afternoon, Night, IsAfter, OnNeed, Days)
				 values (:AID, :PFID, :MID, :Dosage, :Type, :Morning, :Afternoon, :Night, :IsAfter, :OnNeed, :Days)");
				$result->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result->bindParam(':PFID', $pfid['PFID'], PDO::PARAM_STR);
				$result->bindParam(':MID', $mid, PDO::PARAM_STR);
				$result->bindParam(':Dosage', $prescription['Dosage'], PDO::PARAM_STR);
				$result->bindParam(':Type', $prescription['Type'], PDO::PARAM_STR);
				$result->bindParam(':Morning', $prescription['Morning'], PDO::PARAM_STR);
				$result->bindParam(':Afternoon', $prescription['Afternoon'], PDO::PARAM_STR);
				$result->bindParam(':Night', $prescription['Night'], PDO::PARAM_STR);
				$result->bindParam(':IsAfter', $prescription['IsAfter'], PDO::PARAM_STR);
				$result->bindParam(':OnNeed', $prescription['OnNeed'], PDO::PARAM_STR);
				$result->bindParam(':Days', $prescription['Days'], PDO::PARAM_STR);
				$result->execute();
				$pres[] = array('Medicine' => (string)$prescription['Medicine'], 'Dosage' => (string)$prescription['Dosage'], 'Type' => (string)$prescription['Type'], 'Morning' => (string)$prescription['Morning'], 'Afternoon' => (string)$prescription['Afternoon'], 'Night' => (string)$prescription['Night'],
						'IsAfter' => (string)$prescription['IsAfter'], 'OnNeed' => (string)$prescription['OnNeed'], 'Days' => (string)$prescription['Days']);
			}
$qdelete1 = $db->prepare("DELETE from doctortemptest where AID = :AID");
					$qdelete1->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
					$qdelete1->execute();
			if(!empty($obj['Test']))
			{
				foreach ($obj['Test'] as $test)
				{
					$query3 = $db->prepare("SELECT TID,TAB from test where Test = :Test");
					$query3->bindParam(':Test', $test['TestName'], PDO::PARAM_STR);
					$query3->execute();
					$t = $query3->fetch();

					if(!is_null($t['TID']))
						$tid=$t['TID'];
					else
					{
						$query5 = $db->prepare("INSERT into test (Test) values (:Test)");
						$query5->bindParam(':Test', $test['TestName'], PDO::PARAM_STR);
						$query5->execute();
						$query6 = $db->prepare("SELECT TID from test where Test = :Test");
						$query6->bindParam(':Test', $test['TestName'], PDO::PARAM_STR);
						$query6->execute();
						$row2 = $query6->fetch();
						$tid = $row2['TID'];
					}
					
					$result2 = $db->prepare("INSERT into doctortemptest (AID, PFID, TID, TestDate)
					 values (:AID, :PFID, :TID, Now())");
					$result2->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
					$result2->bindParam(':PFID', $pfid['PFID'], PDO::PARAM_STR);
					$result2->bindParam(':TID', $tid, PDO::PARAM_STR);
					$result2->execute();
					$tes[] = array('TestName' => (string)$test['TestName'], 'TAB' => (string)$t['TAB']);
				}
			}


			if(!is_null($obj['Comment']))
			{
				$qdelete2 = $db->prepare("DELETE from doctortempcomment where AID = :AID");
				$qdelete2->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$qdelete2->execute();
				$result3 = $db->prepare("INSERT into doctortempcomment (AID, PFID, Comment)
				 values (:AID, :PFID, :Comment)");
				$result3->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result3->bindParam(':PFID', $pfid['PFID'], PDO::PARAM_STR);
				$result3->bindParam(':Comment', $obj['Comment'], PDO::PARAM_STR);
				$result3->execute();
			}

			if(!is_null($obj['Notes']))
			{
				$qdelete3 = $db->prepare("DELETE from doctortempnotes where AID = :AID");
				$qdelete3->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$qdelete3->execute();
				$result3 = $db->prepare("INSERT into doctortempnotes (AID, Notes)
				 values (:AID, :Notes)");
				$result3->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$result3->bindParam(':Notes', $obj['Notes'], PDO::PARAM_STR);
				$result3->execute();
			}


			$result4 = $db->prepare("SELECT  u.FName, u.LName, u.Pic, u.Email, u.Phone, dp.RegNo, dp.RegAssoc, dp.RegYear, dp.SecPhoneNo, dp.DoctorSign from user u inner join doctorprofile dp
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

			$result6 = $db->prepare("SELECT  Address, City, PinCode from clinics where ClinicID=:ClinicID");
			$result6->bindParam(':ClinicID', $pfid['ClinicID'], PDO::PARAM_INT);
			$result6->execute();
			$row6 = $result6->fetch();

			$result7 = $db->prepare("SELECT  u.FName, u.LName, pp.Address2, pp.City, u.DOB, pp.Height, pp.Weight, pp.BloodGroup, u.Gender from user u inner join patientprofile pp
				where UserID=:UserID and u.UserID = pp.PID");
			$result7->bindParam(':UserID', $pfid['PID'], PDO::PARAM_STR);
			$result7->execute();
			$row7 = $result7->fetch();

			$date = date("d/m/y", strtotime($pfid['PrescriptionDate']));
			$response['MedDate'] = (string)$date;
			$datetime = new DateTime(date("Y-m-d H:i:s"));
			$datetime1 = new DateTime($row7['DOB']);
      $age = $datetime1->diff($datetime);
      $age = $age->format('%y');
			$phone = ($row4['SecPhoneNo'])?$row4['SecPhoneNo']:$row4['Phone'];

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Data Temporarily Saved";
			$name = "Dr. ".$row4['FName']." ".$row4['LName'];
			$response['AID'] = (string)$obj['AID'];
			$response['Name'] = (string)$name;
			$response['Pic'] = (string)$row4['Pic'];
			$response['Degree'] = (string)$degree;
			$response['Email'] = (string)$row4['Email'];
			$response['Phone'] = (string)$phone;
			$response['RegNo'] = (string)$row4['RegNo'];
			$response['RegAssoc'] = (string)$row4['RegAssoc'];
			$response['RegYear'] = (string)$row4['RegYear'];
			$response['Sign'] = (string)$row4['DoctorSign'];
			$response['Address'] = $row6['Address'].", ".$row6['City']."-".$row6['PinCode'];

			$response['PatientName'] = $row7['FName']." ".$row7['LName'];
			$response['PatientAddress'] = $row7['Address2'].", ".$row7['City'];
			$response['PatientDOB'] = (string)$row7['DOB'];
			$response['PatientAge'] = (string)$age;
			$response['PatientBloodGroup'] = (string)$row7['BloodGroup'];
			$response['PatientGender'] = (string)$row7['Gender'];
			$response['PatientHeight'] = (string)$row7['Height'];
			$response['PatientWeight'] = (string)$row7['Weight'];


			$response['Prescription'] = $pres;
			$response['Test'] = $tes;


			if(is_null($obj['Comment']))
				$response['Comment'] = "";
			else
				$response['Comment'] = (string)$obj['Comment'];

			if(is_null($obj['Notes']))
				$response['Notes'] = array("text" => "", "files" => array());
			else
				$response['Notes'] = json_decode((string)$obj['Notes']);

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
	