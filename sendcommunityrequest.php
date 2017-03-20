<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require('helperfunctions1.php');
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try{
  $list = $obj['To'];
  for($i=0;$i < count($list);$i++){
    if($obj['ClinicID']){
      $check2 = $db->prepare("SELECT 1 FROM clinicdoctors WHERE ClinicID = :ClinicID AND DID = :DID");
      $check2->bindParam(":ClinicID", $obj['ClinicID'], PDO::PARAM_INT);
      $check2->bindParam(":DID", $list[$i], PDO::PARAM_INT);
      $check2->execute();
      if($check3 = $check2->fetch()){
        $response['Alert'] = "Clinic doctor already added";
        $responses[] = $response;
        continue;
      }
      $id = $obj['ClinicID'];
      $k = 3;
      $title = "Clinic Doctor Request";
      $message = "Admin has requested to join clinic";
    }else{
      $check2 = $db->prepare("SELECT 1 FROM Dconnection WHERE CommuID = :CommuID AND UserID = :UserID AND UserType > 0");
      $check2->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
      $check2->bindParam(":UserID", $list[$i], PDO::PARAM_INT);
      $check2->execute();
      if($check3 = $check2->fetch()){
        $response['Alert'] = "Already in Community";
        $responses[] = $response;
        continue;
      }
      $title = 'Doctor Community Request';
      $message = "Doctor has requested to join Community";
      $id = $obj['CommuID'];
      $k = 0;
    }
    $result1 = $db->prepare("SELECT ReqID FROM CommunityRequests WHERE DID = :DID AND UserID = :UserID AND CommuID = :CommuID AND Status IN (0,1,3,4)");
    $result1->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
    $result1->bindParam(":DID",$obj['UserID'],PDO::PARAM_INT);
    $result1->bindParam(":CommuID",$id,PDO::PARAM_INT);
    $result1->execute();
    $row = $result1->fetch();
    if($row){
      $response['Alert'] = "You have already sent connection request";
      $responses[] = $response;
      continue;
    }
    $query = $db->prepare("INSERT INTO CommunityRequests (DID,UserID,CommuID,Status) VALUES (:DID,:UserID,:CommuID,:Type)");
    $query->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
    $query->bindParam(":DID",$obj['UserID'],PDO::PARAM_INT);
    $query->bindParam(":CommuID",$id,PDO::PARAM_INT);
    $query->bindParam(":Type",$k,PDO::PARAM_INT);
    $query->execute();
    $reqid = $db->lastInsertId();
    $result = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (11,:ID,:UserID)");
    $result->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
    $result->bindParam(":ID",$reqid,PDO::PARAM_INT);
    $result->execute();
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
    $response['CurlResponse'] = json_decode(pushnotification($list[$i], $title, $message, $data, $db), true);
    $response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "Community Request Sent";
    $responses[] = $response;
  }
  $status['Status'] = $responses;
  header('Content-type: application/json');
  echo json_encode($status);
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
  echo json_encode($status);
}
