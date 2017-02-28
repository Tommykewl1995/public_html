<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

$key_array = array();
$doctors = array();
$cond = array();
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
			foreach ($obj['Disease'] as $disease)
			{
				$result2 = $db->prepare("SELECT cc.ConditionCategory, s.SpecID from conditionscategory cc inner join speciality s
				 where cc.ConditionName = :ConditionName and s.Speciality = cc.ConditionCategory ");
				$result2->bindParam(':ConditionName', $disease['ConditionName'], PDO::PARAM_STR);
				$result2->execute();

				while ( $row2 = $result2->fetch())
				{
					$result = $db->prepare(" SELECT u.FName, u.LName, dp.ExStart, dp.Fee, dp.Pic, c.DID, c.ClinicID, c.ClinicName, c.Address2, (((acos(sin((:Latitude3*pi()/180)) * sin((c.ClinicLat*pi()/180))+cos((:Latitude4*pi()/180)) * cos((c.ClinicLat*pi()/180)) * cos(((:Longitude2 - c.ClinicLong)*pi()/180))))*180/pi())*60*1.1515)
						as Distance from clinics c inner join user u inner join doctorprofile dp inner join doctorspec ds
						WHERE ds.DID = u.UserID and c.DID = u.UserID and dp.DID=u.UserID and ds.SpecID = :SpecID");

					$result->bindParam(':Latitude3', $obj['Latitude'], PDO::PARAM_STR);
					$result->bindParam(':Latitude4', $obj['Latitude'], PDO::PARAM_STR);
					$result->bindParam(':Longitude2', $obj['Longitude'], PDO::PARAM_STR);
					$result->bindParam(':SpecID', $row2['SpecID'], PDO::PARAM_STR);
					$result->execute();
					while($row = $result->fetch())
					{
						$result3 = $db->prepare("SELECT s.Speciality from doctorspec ds inner join speciality s where s.SpecID=ds.SpecID and ds.DID=:DID ");
						$result3->bindParam(':DID', $row['DID'], PDO::PARAM_STR);
						$result3->execute();
						$doctorspec = "";
						while ($spec = $result3->fetch())
						{
							$doctorspec = $doctorspec.$spec['Speciality'].", ";
						}
						$doctorspec = substr($doctorspec, 0, -2);

						if(is_null($row['Pic']))
							$fpic = "http://ec2-52-37-68-149.us-west-2.compute.amazonaws.com/default.png";
						else
							$fpic = $row['Pic'];

						$name = "Dr."." ".$row['FName']." ".$row['LName'];

						$datetime = new DateTime(date("Y-m-d H:i:s"));
						$datetime1 = new DateTime($row['ExStart']);
			            $interval = $datetime1->diff($datetime);
			            $interval = $interval->format('%y');

			            if(is_null($row['Distance']))
			            	$distance = "0.1";
			            else
			            	$distance = sprintf("%.2f", $row['Distance']);

			            if(!in_array($row['DID'], $key_array))
			            {
			            	$key_array[$i] = $row['DID'];
			            	$i++;
							$doctor[] = array('Name' => (string)$name , 'Speciality' => (string)$doctorspec, 'Experience' => (string)$interval, 'Fee' => (string)$row['Fee'], 'DID' => (string)$row['DID'], 'ClinicID' => (string)$row['ClinicID'],
							'ClinicName' => (string)$row['ClinicName'], 'ClinicArea' => (string)$row['Address2'], 'Distance' => (string)$distance, 'Pic' => (string)$fpic);
			            	//$doctor[] = array('Speciality' => (string)$row2['ConditionCategory']);
			            }

					}
				}


			}
			//unique_multidim_array($doctor,'DID');
			//$doctors = array_unique($doctors);
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Home Data";

			$response['Doctors'] = $doctor;
			$response['Conditions'] = $cond;

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

		function unique_multidim_array($array, $key)
		{
    $temp_array = array();
    $i = 0;
    $j = 0;
    $key_array = array();

    foreach($array as $val)
    {
        if (!in_array($val[$key], $key_array))
        {
            $key_array[$i] = $val[$key];
            $temp_array[$j] = $val[$i];
            $j++;
        }
        $i++;
    }
    $response['Doctors'] = $temp_array;
    //return $temp_array;
}
// }
