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

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP') && filter_var(getenv('HTTP_CLIENT_IP'), FILTER_VALIDATE_IP))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR') && filter_var(getenv('HTTP_X_FORWARDED_FOR'), FILTER_VALIDATE_IP))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED') && filter_var(getenv('HTTP_X_FORWARDED'), FILTER_VALIDATE_IP))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR') && filter_var(getenv('HTTP_FORWARDED_FOR'), FILTER_VALIDATE_IP))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED') && filter_var(getenv('HTTP_FORWARDED'), FILTER_VALIDATE_IP))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR') && filter_var(getenv('REMOTE_ADDR'), FILTER_VALIDATE_IP))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$query = $db->prepare("SELECT * FROM user WHERE Phone=:Phone");
$query->bindParam(':Phone', $obj['Phone'], PDO::PARAM_INT);
$query->execute();
$que = $query->fetch();

$query1 = $db->prepare("SELECT IsVerified FROM doctorprofile WHERE DID= :UserID");
$query1->bindParam(':UserID', $que['UserID'], PDO::PARAM_INT);
$query1->execute();
$row0 = $query1->fetch();

$response = array();
if($query->rowCount()==1){
	if($que['IsDoctor'] == 1 && $row0['IsVerified'] == 0){
		$response['ResponseCode'] = "201";
		$response['ResponseMessage'] = "Doctor not verified";
		 $response['Email'] = (string)$que['Email'];
                 $response['Isverified'] = (string)$row0['IsVerified'];
		$status['Status'] = $response;
		header('Content-type: application/json');
		echo json_encode($status);
	}else{
		if($obj['admin']){
			$que['IsDoctor'] = 1;
		}
		try{
			if ($que['IsDoctor'] == 1) {
				$result = $db->prepare("SELECT UserID, Phone, Password, Email, DeviceID, IsDoctor, IsUpdated from user where Phone=:Phone");
				$result->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
				$result->execute();
				$row = $result->fetch();
                                $result3 = $db->prepare("SELECT UserID, RegistrationID from registrationid where UserID=:UserID");
			        $result3->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
			        $result3->execute();
			        $row3= $result3->fetch();
				if($obj['Phone']==$row['Phone'] && Bcrypt::checkPassword($obj['Password'], $row['Password'])){
					$result2 = $db->prepare("UPDATE user SET DeviceID=:DeviceID where Phone=:Phone");
					$result2->bindParam(':DeviceID', $obj['DeviceID'], PDO::PARAM_STR);
					$result2->bindParam(':Phone', $obj['Phone'], PDO::PARAM_STR);
					$result2->execute();
					/*if($row3['UserID']){
						$result5 = $db->prepare("UPDATE registrationid SET RegistrationID=:RegistrationID where UserID=:UserID");
						$result5->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
						$result5->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
						$result5->execute();
					}else{
						$result4 = $db->prepare("INSERT into registrationid (UserID, RegistrationID) values (:UserID, :RegistrationID)");
						$result4->bindParam(':RegistrationID', $obj['RegistrationID'], PDO::PARAM_STR);
						$result4->bindParam(':UserID', $row['UserID'], PDO::PARAM_STR);
						$result4->execute();
					}*/
					$response['ResponseCode'] = "200";
					$response['ResponseMessage'] = "Successful Login";
					$response['UserID'] = (string)$row['UserID'];
					$response['IsDoctor'] = (string)$row['IsDoctor'];
					$response['IsUpdated'] = (string)$row['IsUpdated'];
                    $response['IsVerified'] = (string)$row0['IsVerified'];
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
				}else{
					$response['ResponseCode'] = "500";
					$response['ResponseMessage'] = "Phone and password mismatch! Please check";
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
				}
			}else{
				$response['ResponseCode'] = "201";
				$response['ResponseMessage'] = "Login Option only for Doctors";
				$status['Status'] = $response;
				header('Content-type: application/json');
				echo json_encode($status);
			}
		}catch(PDOException $ex){
			$response['ResponseCode'] = "500";
			$response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
			$status['Status'] = $response;
			header('Content-type: application/json');
			echo json_encode($response);
		}
	}
}else{
	$response['ResponseCode'] = "500";
	$response['ResponseMessage'] = "User not Registered, Please Register";
	$status['Status'] = $response;
	header('Content-type: application/json');
	echo json_encode($status);
}