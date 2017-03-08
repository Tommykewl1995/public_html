<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require('helperfunctions1.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');
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
			$time = (int)$obj['Time'];
		  $date = date("Y-m-d H:i:s", $time);
			if(empty($obj['DID'])){
				$result6 = $db->prepare("INSERT INTO appointment3 (PID, PFID, Status, ClinicID, AppointmentDate) VALUES (:PID, :PFID, 'Active', :ClinicID, :AppointmentDate)");
			}else{
				$result6 = $db->prepare("INSERT INTO appointment3 (DID, PID, PFID, Status, ClinicID, AppointmentDate) VALUES (:DID, :PID, :PFID, 'Active', :ClinicID, :AppointmentDate)");
				$result6->bindParam(':DID', $obj['DID'], PDO::PARAM_INT);
			}
			$result6->bindParam(':PID', $obj['UserID'], PDO::PARAM_INT);
			$result6->bindParam(':ClinicID', $obj['ClinicID'], PDO::PARAM_INT);
			$result6->bindParam(':PFID', $obj['PFID'], PDO::PARAM_INT);
			$result6->bindParam(':AppointmentDate', $date, PDO::PARAM_INT);
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
