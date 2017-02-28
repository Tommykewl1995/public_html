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
date_default_timezone_set('Asia/Kolkata');

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  return $angle * $earthRadius;
}

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
      if($obj['DID']){
        $obj['UserID'] = $obj['DID'];
      }
			$result = $db->prepare("SELECT u.FName,u.LName,u.Phone, u.Email, u.Gender,u.DOB,dp.Summary,dp.ExStart,dp.ExEnd,dp.Fee,dp.RegNo,dp.RegAssoc,dp.RegYear,u.Pic,dp.DoctorSign,dp.IsVerified,dp.SecPhoneNo,dp.CurrClinicID,c.Council from doctorprofile dp inner join user u on dp.DID = u.UserID left join council c on c.CouncilID = dp.RegAssoc where dp.DID=:UserID");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
			$result->execute();
			$row = $result->fetch();
			$result2 = $db->prepare(" SELECT ds.SpecID, s.Speciality FROM doctorspec ds inner join speciality s where ds.SpecID = s.SpecID and ds.DID=:UserID");
			$result2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result2->execute();
			if(is_null($row['Pic']))
				$fpic = "http://ec2-52-37-68-149.us-west-2.compute.amazonaws.com/default.png";
			else
				$fpic = $row['Pic'];
			while ($row2 = $result2->fetch()){
				$speciality[] = array('name' => (string)$row2['Speciality']);
			}
			$result4 = $db->prepare(" SELECT dd.DegreeID, Degree FROM doctordegree dd inner join degree d on dd.DegreeID
				where dd.DegreeID = d.DegreeID and DID=:UserID");
			$result4->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result4->execute();
			while ($row4 = $result4->fetch()){
				$degree[] = array('name' => (string)$row4['Degree']);
			}
			$result5 = $db->prepare(" SELECT DocAffil FROM doctoraffil where DID=:UserID");
			$result5->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result5->execute();
			while ($row5 = $result5->fetch()){
				$affiliation[] = array('name' => (string)$row5['DocAffil'] );
			}
      $clinics = array();
			$result6 = $db->prepare("SELECT * FROM clinics c INNER JOIN speciality sp ON sp.SpecID = c.ClinicSpec where c.DID=:UserID");
			$result6->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result6->execute();
			while ($row6 = $result6->fetch()){
        if($row['CurrClinicID'] == $row6['ClinicID']){
          $curr = 1;
        }else{
          $curr = 0;
        }
				$clinics[] = array("ClinicID" => $row6['ClinicID'],
        "name" => $row6['ClinicName'],
        "ClinicSpecID" => $row6['ClinicSpec'],
        "ClinicSpec" => $row6['Speciality'],
        "Summary" => $row6['Summary'],
        "Address" => (string)$row6['Address'],
				"City" => $row6['City'],
        "PinCode" => $row6['PinCode'],
        "ClinicEmail" => $row6['ClinicEmail'],
        "ClinicPhone" => $row6['ClinicPhone'],
        "ClinicLogo" => $row6['ClinicLogo'],
        "Curr" => $curr,
        "CurrClinicID" => $row['CurrClinicID']);
			}
			$datetime = new DateTime(date("Y-m-d H:i:s"));
			$datetime1 = new DateTime($row['ExStart']);
			$interval = $datetime1->diff($datetime);
			$interval = $interval->format('%y');

			$response['DoctorData'] = array("DID" => $obj['UserID'],
			"Name" => "Dr. ".(string)$row['FName']." ".(string)$row['LName'],
			"First Name" => $row['FName'],
			"Last Name" => $row['LName'],
			"Phone" => $row['Phone'],
      "Secondary Phone" => $row['SecPhoneNo'],
			"Email" => $row['Email'],
			"Sex" => $row['Gender'],
			"Date of Birth" => date("Y-m-d", strtotime($row['DOB'])),
			"Summary" => $row['Summary'],
			"Experience" => (string)$interval." years",
			"ExStart" => $row['ExStart'],
			"ExEnd" => $row['ExEnd'],
			"Fee" => $row['Fee'],
			"Registration Number" => $row['RegNo'],
			"Association" => $row['Council'],
                        "AssociationID" => $row['RegAssoc'],
			"Registration Year" => $row['RegYear'],
			"Pic" => $fpic,
			"DoctorSign" => $row['DoctorSign'],
			"IsVerified" => $row['IsVerified'],
			"Speciality" => $speciality,
			"Degree" => $degree,
			"Affiliation" => $affiliation,
			"Clinics" => $clinics);
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Doctor-Data";
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
