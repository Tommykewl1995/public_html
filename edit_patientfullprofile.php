<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);
date_default_timezone_set('Asia/Kolkata');

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
			$datetime2 = new DateTime($obj['DOB']);
			$datetime2 = $datetime2->format('Y-m-d');
            $result = $db->prepare("UPDATE patientprofile SET Height = :Height, Weight = :Weight, BloodGroup = :BloodGroup,  Address1 = :Address1, Address2 = :Address2, City = :City, PinCode = :PinCode, Allergies = :Allergies, Hereditory = :Hereditory where PID = :UserID");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
			$result->bindParam(':Height', $obj['Height'], PDO::PARAM_STR);
			$result->bindParam(':Weight', $obj['Weight'], PDO::PARAM_STR);
			$result->bindParam(':BloodGroup', $obj['BloodGroup'], PDO::PARAM_STR);
			$result->bindParam(':Address1', $obj['Address1'], PDO::PARAM_STR);
			$result->bindParam(':Address2', $obj['Address2'], PDO::PARAM_STR);
			$result->bindParam(':City', $obj['City'], PDO::PARAM_STR);
	        $result->bindParam(':PinCode', $obj['PinCode'], PDO::PARAM_INT);
	        $result->bindParam(':Allergies', $obj['Allergies'], PDO::PARAM_STR);
	        $result->bindParam(':Hereditory', $obj['Hereditory'], PDO::PARAM_STR);
			$result->execute();

			$result2 = $db->prepare("UPDATE user SET FName = :FName, LName = :LName, Email = :Email, Pic = :Pic, DOB = :DOB, Gender = :Gender where UserID = :UserID");
			$result2->bindParam(':UserID', $obj['UserID'], PDO::PARAM_INT);
			$result2->bindParam(':Pic', $obj['Pic'], PDO::PARAM_STR);
			$result2->bindParam(':DOB', $datetime2, PDO::PARAM_STR);
			$result2->bindParam(':FName', $obj['FName'], PDO::PARAM_STR);
			$result2->bindParam(':Gender', $obj['Gender'], PDO::PARAM_INT);
			$result2->bindParam(':LName', $obj['LName'], PDO::PARAM_STR);
                        $result2->bindParam(':Email', $obj['Email'], PDO::PARAM_STR);
			$result2->execute();

			$response['ResponseMessage'] = "Info Updated Successfully";
			$response['ResponseCode'] = "200";
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
