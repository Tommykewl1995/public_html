<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require('helperfunctions1.php');
require('Razor.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

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
			$payment = 0;
			$stage = null;
			if(isset($obj['razor'])){
				$razor = $obj['razor'];
				$razoru = new Utility();
				$verified = $razoru->verifyPaymentSignature($razor);
				if($verified){
					$result = $db->prepare("UPDATE payu SET razor_payment_id = :razorpay_payment_id WHERE razor_order_id = :razorpay_order_id");
					$result->bindParam(":razorpay_payment_id", $razor['razorpay_payment_id'], PDO::PARAM_STR);
					$result->bindParam(":razorpay_order_id", $razor['razorpay_order_id'], PDO::PARAM_STR);
					$result->execute();
					$payment = $razor['Payment'];
					$stage = 'patient';
				}else{
					$response['ResponseCode'] = "401";
					$response['ResponseMessage'] = "Payment Failed"; //user friendly message
					$status['Status'] = $response;
					header('Content-type: application/json');
					echo json_encode($status);
					die();
				}
			}
			$time = (int)$obj['Time'];
		  $date = date("Y-m-d H:i:s", $time);
			if(empty($obj['DID'])){
				$result6 = $db->prepare("INSERT INTO appointment3 (PID, PFID, Status, ClinicID, AppointmentDate, Payment, PaymentStage) VALUES (:PID, :PFID, 'Active', :ClinicID, :AppointmentDate, :Payment, :PaymentStage)");
			}else{
				$result6 = $db->prepare("INSERT INTO appointment3 (DID, PID, PFID, Status, ClinicID, AppointmentDate, Payment, PaymentStage) VALUES (:DID, :PID, :PFID, 'Active', :ClinicID, :AppointmentDate, :Payment, :PaymentStage)");
				$result6->bindParam(':DID', $obj['DID'], PDO::PARAM_INT);
			}
			$result6->bindParam(':PID', $obj['UserID'], PDO::PARAM_INT);
			$result6->bindParam(':ClinicID', $obj['ClinicID'], PDO::PARAM_INT);
			$result6->bindParam(':PFID', $obj['PFID'], PDO::PARAM_INT);
			$result6->bindParam(':AppointmentDate', $date, PDO::PARAM_INT);
			$result6->bindParam(':Payment', $payment, PDO::PARAM_INT);
			$result6->bindParam(':PaymentStage', $stage, PDO::PARAM_STR);
			$result6->execute();
			$aid = $db->lastInsertId();
			$result2 = $db->prepare("UPDATE patientprofile SET CPFID = :PFID WHERE PID = :PID");
			$result2->bindParam(":PFID", $obj['PFID'], PDO::PARAM_INT);
			$result2->bindParam(":PID", $obj['UserID'], PDO::PARAM_INT);
			$result2->execute();
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Symptoms Submitted";
			$response['AID'] = $aid;
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
