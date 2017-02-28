<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
require('db_config.php');
require('helperfunctions1.php');
date_default_timezone_set('Asia/Kolkata');
/*
Codes:
DoctorAccept : 0
DoctorReject : 1
DoctorReschedule : 2
PatientRescheduleAccept : 3
PatientRescheduleReject : 4
PatientBookingCancelled : 5
DoctorBookingCancelled : 6
PatientBookingReminder : 7
PatientBookingDone : 8
PatientBookedAuto : 9
PatientPreCancelAppointment : 10
DoctorConnectionRequest : 11
PatientConnectionAcceptNotification : 12
Patient Follow Notification : 13
Like Notification : 14
Comment Notification : 15
Share Notification : 16
Reply Notification : 17
Symptom Share Notification : 18
*/
$json = file_get_contents('php://input');
$obj = json_decode($json, true);
try{
  if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
    $response['ResponseCode'] = "400";
    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($status);
    die();
  }
  //$offset = (int)$obj['count'] * 10;
  $result = $db->prepare("SELECT *, NOW() as now FROM Notifications WHERE UserID = :UserID ORDER BY NID DESC "); //LIMIT ".$offset.",10");
  $result->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
  $result->execute();
  $noti = array();
  $count = 0;
  while($row = $result->fetch()){
    $temp = getnotifications($row, $db);
    if($temp['Data.Expired']==0){
    $noti[] = $temp['Data'];
    $count+=($temp['viewed'])?0:1;}
  }
  //$obj['count']++;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Notifications Data";
  $response['Notifications'] = $noti;
  $response['Count'] = $count;
  //$response['Offset'] = $offset
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
