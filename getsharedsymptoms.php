<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

function getsymp($aid, $pfid, $pid, $time, $db){
	$result5 = $db->prepare(" SELECT FName, LName, Pic from user u where UserID = :UserID");
	$result5->bindParam(':UserID', $pid, PDO::PARAM_INT);
	$result5->execute();
	$row5 = $result5->fetch();

	$result6 = $db->prepare(" SELECT Symptom from patientfinalsymptom where PFID = :PFID and SymptomChoice='present' LIMIT 0,5");
	$result6->bindParam(':PFID', $pfid, PDO::PARAM_INT);
	$result6->execute();
	$symptoms = "";
	while ($row6 = $result6->fetch()){
		$symptoms = $symptoms.$row6['Symptom'].", ";
	}
	$symptoms = substr($symptoms, 0, -2);

	if(is_null($row4['Pic'])){
		$fpic = "http://52.24.83.227/default.png";
	}else{
		$fpic = $row4['Pic'];
	}
	return array('FName' => (string)$row5['FName'], 'LName' => (string)$row5['LName'], 'Pic' => (string)$fpic, 'AID' => (string)$aid, 'PID' => (string)$pid, 'PFID' => (string)$pfid, 'Symptom' => (string)$symptoms, 'Time' => ($time*1000));
}

$sharedsymptoms = array();
$today = date("Y-m-d H:i:s", strtotime("today 12:00 A.M."));
$tomorrow = date("Y-m-d H:i:s", strtotime("tomorrow 12:00 A.M."));
try{
	if($obj['ClinicID']){
		$result4 = $db->prepare("SELECT AID, PID, PFID, AppointmentDate from appointment3 where ClinicID=:ClinicID and Status='Active' order by AID desc");
		$result4->bindParam(':ClinicID', $obj['ClinicID'], PDO::PARAM_INT);
		$result4->execute();
		while ($row4 = $result4->fetch()){
			$sharedsymptoms[] = getsymp($row4['AID'], $row4['PFID'], $row4['PID'], strtotime($row4['AppointmentDate']), $db);
		}
		$todaysymp = array();
		$result5 = $db->prepare("SELECT AID, PID, PFID, AppointmentDate from appointment3 where ClinicID=:ClinicID and Status='Confirm' and AppointmentDate IS NOT NULL order by AID desc");
		$result5->bindParam(':ClinicID', $obj['ClinicID'], PDO::PARAM_INT);
		$result5->execute();
		while ($row4 = $result4->fetch()){
			$todaysymp[] = getsymp($row4['AID'], $row4['PFID'], $row4['PID'], strtotime($row4['AppointmentDate']), $db);
		}
		$response['TodaySymptoms'] = $todaysymp;
	}else{
		$result4 = $db->prepare(" SELECT AID, PID, PFID, AppointmentDate from appointment3 where DID=:DID and Status='Confirm' order by AID desc");
		$result4->bindParam(':DID', $obj['UserID'], PDO::PARAM_INT);
		$result4->execute();
		while ($row4 = $result4->fetch()){
			$sharedsymptoms[] = getsymp($row4['AID'], $row4['PFID'], $row4['PID'], strtotime($row4['AppointmentDate']), $db);
		}
	}
	$response['ResponseCode'] = "200";
	$response['ResponseMessage'] = "Shared Symptoms List";
	$response['SharedSymptoms'] = $sharedsymptoms;
	$status['Status'] = $response;
	header('Content-type: application/json');
	echo json_encode($status);
}catch(PDOException $ex){
	$response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
	echo json_encode($status);
}
// }
