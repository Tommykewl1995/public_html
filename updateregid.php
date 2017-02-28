<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require('helperfunctions1.php');

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
        $result3 = $db->prepare("SELECT RegistrationID from registrationid where  UserID=:UserID");
        $result3->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
        $result3->execute();
        $row3 = $result3->fetch();

        if($row3['RegistrationID']!=$obj['RegistrationID'])
        {
            $result4 = $db->prepare("UPDATE registrationid SET RegistrationID=:RegistrationID where UserID=:UserID");
            $result4->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
            $result4->bindParam(':RegistrationID', $obj['RegistrationID'] , PDO::PARAM_STR);
            $result4->execute();
        }
        $response['ResponseCode'] = "200";
        $response['ResponseMessage'] = "Patient Symptoms Submitted";

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
