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
			$result = $db->prepare("SELECT FName, LName, Phone, Email, DOB, Gender, Pic FROM user WHERE UserID = :ID");
			$result->bindParam(':ID', $obj['ID'], PDO::PARAM_INT);
			$result->execute();
      $row = $result->fetch();
			$datetime = new DateTime(date("Y-m-d H:i:s"));
			$datetime1 = new DateTime($row['DOB']);
			$interval = $datetime1->diff($datetime);
			$interval = $interval->format('%y');
			$data = array("UserID" => $obj['ID'], "First Name" => $row['FName'], "Last Name" => $row['LName'], "Phone" => $row['Phone'], "Email" => $row['Email'], "Date of Birth" => $row['DOB'], "Sex" => $row['Gender'], "Pic" => $row['Pic'], "Age" => $interval);
			if($obj['CommuID']){
				$result2 = $db->prepare("SELECT UserType FROM Dconnection WHERE UserID = :ID AND CommuID = :CommuID");
				$result2->bindParam(":ID", $obj['ID'], PDO::PARAM_INT);
				$result2->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
				$result2->execute();
				$row2 = $result2->fetch();
				if($row2['UserType']){
					$data['relation'] = $row2['UserType'];
				}else{
					$result3 = $db->prepare("SELECT 1 FROM CommunityRequests WHERE UserID = :ID AND CommuID = :CommuID LIMIT 1");
					$result3->bindParam(":ID", $obj['ID'], PDO::PARAM_INT);
					$result3->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
					$result3->execute();
					$row3 = $result3->fetch();
					if($row3){
						$data['relation'] = 0.5;
					}else{
						$data['relation'] = -1;
					}
				}
			}
      $response['UserData'] = $data;
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "User-Data";
			$status['Status'] = $response;

			header('Content-type: application/json');
			echo json_encode($status);
		}
	catch(PDOException $ex)
		{
      http_response_code(500);
	    $status['Error'] = "An Error occured!" . $ex;
	    header('Content-type: application/json');
			echo json_encode($status);
		}
