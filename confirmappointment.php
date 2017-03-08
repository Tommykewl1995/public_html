<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require_once 'Bcrypt.php';

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
  $time = (int)$obj['Time'];
  $date = date("Y-m-d H:i:s", $time);
  $result = $db->prepare("UPDATE appointment3 SET DID = :DID, AppointmentDate = :AppointmentDate, Status = 'Confirm' WHERE AID = :AID");
  $result->bindParam(':AID', $obj['AID'], PDO::PARAM_INT);
  $result->bindParam(':AppointmentDate', $date, PDO::PARAM_INT);
  $result->bindParam(':DID', $obj['DID'], PDO::PARAM_INT);
  $result->execute();
  $result4 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (18,:ID,:UserID)");
  $result4->bindParam(":UserID", $obj['DID'],PDO::PARAM_INT);
  $result4->bindParam(":ID", $aid, PDO::PARAM_INT);
  $result4->execute();
  $nid = $db->lastInsertId();
  $result = $db->prepare("SELECT *, NOW() as now FROM Notifications WHERE NID = :NID"); //LIMIT ".$offset.",10");
  $result->bindParam(":NID", $nid, PDO::PARAM_INT);
  $result->execute();
  $row = $result->fetch();
  $data = getnotifications($row, $db);
  $query11 = $db->prepare("SELECT FName, LName from user where UserID = :UserID");
  $query11->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
  $query11->execute();
  $row33 = $query11->fetch();
  $response['CurlResponse'] = json_decode(pushnotification($obj['DID'], 'Symptom Share Notification', $row33['FName']." ".$row33['LName']." has shared Symptoms with you", $data, $db), true);
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Appointment Confirmed";
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($status);
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
  $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($response);
}
