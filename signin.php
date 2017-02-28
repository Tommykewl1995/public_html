<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require_once 'Bcrypt.php';
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);
if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
	$response['ResponseCode'] = "400";
	$response['ResponseMessage'] = "Invalid api_key"; //user friendly message
	$status['Status'] = $response;
	header('Content-type: application/json');
	echo json_encode($status);
	die();
}
$query = $db->prepare("Select * from user where Phone=:Phone");
$query->bindParam(':Phone', $obj['Phone'], PDO::PARAM_INT);
$query->execute();

$response = array();
if($query->rowCount()==1)
{
	try
		{
			$result = $db->prepare("SELECT UserID, Phone, Password, Email, DeviceID, IsDoctor, IsUpdated from user where Phone=:Phone");
			$result->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();

			$result3 = $db->prepare("SELECT UserID, RegistrationID from registrationid where UserID=:UserID");
			$result3->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
			$result3->execute();
			$row3= $result3->fetch();

	if($row[IsDoctor] ==1){
	                $result0 = $db->prepare("SELECT IsVerified FROM doctorprofile WHERE DID= :UserID");
                        $result0->bindParam(':UserID', $row['UserID'], PDO::PARAM_INT);
                        $result0->execute();
                        $row0 = $result0->fetch();

                        if($row0['IsVerified'] == 0){
		                      $response['ResponseCode'] = "201";
		                      $response['ResponseMessage'] = "Doctor not verified";
		                      $response['Email'] = (string)$row['Email'];
                                      $response['Isverified'] = (string)$row0['IsVerified'];
		                      $status['Status'] = $response;
		                      header('Content-type: application/json');
		                      echo json_encode($status);
                               }
                        else{
                             if(!is_null($row['DeviceID']))
			{
				if($obj['Phone']==$row['Phone'] && Bcrypt::checkPassword($obj['Password'], $row['Password']))
				{
					if($obj['DeviceID']==$row['DeviceID'])
					{

						if($row3['UserID'])
						{
							$result5 = $db->prepare("UPDATE registrationid SET RegistrationID=:RegistrationID where UserID=:UserID");
							$result5->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result5->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result5->execute();
						}

						else
						{
							$result4 = $db->prepare("INSERT into registrationid (UserID, RegistrationID) values (:UserID, :RegistrationID)");
							$result4->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result4->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result4->execute();
						}
						$response['ResponseCode'] = "200";
						$response['ResponseMessage'] = "Successful Login";
						$response['UserID'] = (string)$row['UserID'];
						$response['IsDoctor'] = (string)$row['IsDoctor'];
						$response['IsUpdated'] = (string)$row['IsUpdated'];
                                                $response['IsVerified'] = (string)$row0['IsVerified'];
						$status['Status'] = $response;
						header('Content-type: application/json');
						echo json_encode($status);
					}
					else
					{
						$response['ResponseCode'] = "202";
						$response['ResponseMessage'] = "User trying to Login from another device!";
						$response['Email'] = (string)$row['Email'];
						$status['Status'] = $response;
						header('Content-type: application/json');
						echo json_encode($status);
					}
				}
				else
				{
					$response['ResponseCode'] = "500";
					$response['ResponseMessage'] = "Phone and password mismatch! Please check";
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
				}

			}
			else
			{
				if($obj['Phone']==$row['Phone'] && Bcrypt::checkPassword($obj['Password'], $row['Password']))
				{
					$result2 = $db->prepare("UPDATE user SET DeviceID=:DeviceID where Phone=:Phone");
					$result2->bindParam(':DeviceID', $obj['DeviceID'], PDO::PARAM_STR);
					$result2->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
					$result2->execute();

					if($row3['UserID'])
						{
							$result5 = $db->prepare("UPDATE registrationid SET RegistrationID=:RegistrationID where UserID=:UserID");
							$result5->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result5->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result5->execute();
						}
					else
						{
							$result4 = $db->prepare("INSERT into registrationid (UserID, RegistrationID) values (:UserID, :RegistrationID)");
							$result4->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result4->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result4->execute();
						}

						$response['ResponseCode'] = "200";
						$response['ResponseMessage'] = "Successful Login";
						$response['UserID'] = (string)$row['UserID'];
						$response['IsDoctor'] = (string)$row['IsDoctor'];
						$response['IsUpdated'] = (string)$row['IsUpdated'];
                                                $response['IsVerified'] = (string)$row0['IsVerified'];
						$status['Status'] = $response;
						header('Content-type: application/json');
						echo json_encode($status);
				}
				else
				{
					$response['ResponseCode'] = "500";
					$response['ResponseMessage'] = "Phone and password mismatch! Please check";
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
				}

			}
                        }
    }
	else {
			if(!is_null($row['DeviceID']))
			{
				if($obj['Phone']==$row['Phone'] && Bcrypt::checkPassword($obj['Password'], $row['Password']))
				{
					if($obj['DeviceID']==$row['DeviceID'])
					{

						if($row3['UserID'])
						{
							$result5 = $db->prepare("UPDATE registrationid SET RegistrationID=:RegistrationID where UserID=:UserID");
							$result5->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result5->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result5->execute();
						}

						else
						{
							$result4 = $db->prepare("INSERT into registrationid (UserID, RegistrationID) values (:UserID, :RegistrationID)");
							$result4->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result4->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result4->execute();
						}
						$response['ResponseCode'] = "200";
						$response['ResponseMessage'] = "Successful Login";
						$response['UserID'] = (string)$row['UserID'];
						$response['IsDoctor'] = (string)$row['IsDoctor'];
						$response['IsUpdated'] = (string)$row['IsUpdated'];
						$status['Status'] = $response;
						header('Content-type: application/json');
						echo json_encode($status);
					}
					else
					{
						$response['ResponseCode'] = "202";
						$response['ResponseMessage'] = "User trying to Login from another device!";
						$response['Email'] = (string)$row['Email'];
						$status['Status'] = $response;
						header('Content-type: application/json');
						echo json_encode($status);
					}
				}
				else
				{
					$response['ResponseCode'] = "500";
					$response['ResponseMessage'] = "Phone and password mismatch! Please check";
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
				}

			}
			else
			{
				if($obj['Phone']==$row['Phone'] && Bcrypt::checkPassword($obj['Password'], $row['Password']))
				{
					$result2 = $db->prepare("UPDATE user SET DeviceID=:DeviceID where Phone=:Phone");
					$result2->bindParam(':DeviceID', $obj['DeviceID'], PDO::PARAM_STR);
					$result2->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
					$result2->execute();

					if($row3['UserID'])
						{
							$result5 = $db->prepare("UPDATE registrationid SET RegistrationID=:RegistrationID where UserID=:UserID");
							$result5->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result5->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result5->execute();
						}
					else
						{
							$result4 = $db->prepare("INSERT into registrationid (UserID, RegistrationID) values (:UserID, :RegistrationID)");
							$result4->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
							$result4->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
							$result4->execute();
						}

						$response['ResponseCode'] = "200";
						$response['ResponseMessage'] = "Successful Login";
						$response['UserID'] = (string)$row['UserID'];
						$response['IsDoctor'] = (string)$row['IsDoctor'];
						$response['IsUpdated'] = (string)$row['IsUpdated'];
						$status['Status'] = $response;
						header('Content-type: application/json');
						echo json_encode($status);
				}
				else
				{
					$response['ResponseCode'] = "500";
					$response['ResponseMessage'] = "Phone and password mismatch! Please check";
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
				}

			}
         }
		}
	catch(PDOException $ex)
		{
			$response['ResponseCode'] = "500";
		    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
		    $status['Status'] = $response;
		    header('Content-type: application/json');
			echo json_encode($response);
		}
}
else
{
		$response['ResponseCode'] = "500";
		$response['ResponseMessage'] = "User not Registered, Please Register";
		$status['Status'] = $response;
		header('Content-type: application/json');
		echo json_encode($status);
}
