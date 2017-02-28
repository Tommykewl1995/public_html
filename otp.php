<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');
include('helperfunctions1.php');
$json = file_get_contents('php://input');
$obj = json_decode($json, true);
$senderId = "RxHLTH";
$route = "4";


$response = array();



try{
	if($obj['forgot']){
		$query = $db->prepare("SELECT UserID FROM user WHERE Phone = :Phone");
		$query->bindParam(":Phone", $obj['Phone'], PDO::PARAM_STR);
		$query->execute();
		$count = $query->rowCount();
		if($count==1){
			$pin = generatePIN(4);
			list($otp_code,$message) = sendotp( $obj['Phone'],$pin,"pin");
			$response['val'] = $otp_code;
			//$response['otp'] = $output;
			$response['number'] = $obj['Phone'];
			$response['ResponseMessage'] = "Sent SMS Successfully";
			$response['ResponseCode'] = "200";
		}
		else{
			$response['ResponseMessage'] = "Number not registered";
			$response['ResponseCode'] = "202";
		}
	}elseif ($obj['refer']){
		list($message) = sendotp( $obj['Phone'],1111,"refer");
		$response['number'] = $obj['Phone'];
		$response['ResponseMessage'] = "Sent SMS Successfully";
		$response['ResponseCode'] = "200";
	}else{
		$query1 = $db->prepare("SELECT * from user where Email=:Email OR Phone=:Phone");
		$query1->bindParam(':Email', $obj['Email'], PDO::PARAM_STR);
		$query1->bindParam(':Phone', $obj['Phone'], PDO::PARAM_INT);
		$query1->execute();
		$count1 = $query1->rowCount();
		if($count1 == 0){
			$pin = generatePIN(4);
			list($otp_code,$message) = sendotp( $obj['Phone'],$pin,"pin");
			$response['val'] = $otp_code;
			//$response['otp'] = $output;
			$response['number'] = $obj['Phone'];
			$response['ResponseMessage'] = "Sent SMS Successfully";
			$response['ResponseCode'] = "200";
		}
		else {
			$response['ResponseMessage'] = "Number already registered";
			$response['ResponseCode'] = "202";
		}
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
?>
