<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
require('db_config.php');
require('helperfunctions1.php');
$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try{
  $query = $db->prepare("INSERT INTO Tip (Title, Tip, CommuID) VALUES (:Title, :Tip, :CommuID)");
  $query->bindParam(":Tip", $obj['Text'], PDO::PARAM_STR);
  $query->bindParam(":Title", $obj['Title'], PDO::PARAM_STR);
  $query->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
  $query->execute();
  $tipid = $db->lastInsertId();
  switch((string)$obj['type']){
    case 'all':
    $result = $db->prepare("SELECT u.UserID,rid.RegistrationID
      FROM user u
      INNER JOIN registrationid rid
      ON rid.UserID = u.UserID");
    $result->execute();
    while($row = $result->fetch()){
      if($row['RegistrationID']){
        $regids[] = $row['RegistrationID'];
      }
      $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (-1,:id,:UserID)");
      $result2->bindParam(":id", $tipid, PDO::PARAM_INT);
      $result2->bindParam(":UserID", $row['UserID'], PDO::PARAM_INT);
      $result2->execute();
    }
    $response['CurlResponse'] = json_decode(pushnotification(null,$obj['Title'], $obj['Text'], null, $db, $regids), true);
    break;
    case 'doctors':
    $result = $db->prepare("SELECT u.UserID,rid.RegistrationID
      FROM user u
      INNER JOIN registrationid rid
      ON rid.UserID = u.UserID WHERE u.IsDoctor = 1");
    $result->execute();
    while($row = $result->fetch()){
      $regids[] = $row['RegistrationID'];
      $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (-1,:id,:UserID)");
      $result2->bindParam(":id", $tipid, PDO::PARAM_INT);
      $result2->bindParam(":UserID", $row['UserID'], PDO::PARAM_INT);
      $result2->execute();
    }
    $response['CurlResponse'] = json_decode(pushnotification(null,$obj['Title'], $obj['Text'], null, $db, $regids), true);
    break;
    case 'patients':
    $result = $db->prepare("SELECT u.UserID,rid.RegistrationID
      FROM user u
      INNER JOIN registrationid rid
      ON rid.UserID = u.UserID WHERE u.IsDoctor = 0");
    $result->execute();
    while($row = $result->fetch()){
      $regids[] = $row['RegistrationID'];
      $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (-1,:id,:UserID)");
      $result2->bindParam(":id", $tipid, PDO::PARAM_INT);
      $result2->bindParam(":UserID", $row['UserID'], PDO::PARAM_INT);
      $result2->execute();
    }
    $response['CurlResponse'] = json_decode(pushnotification(null,$obj['Title'], $obj['Text'], null, $db, $regids), true);
    break;
    case 'speciality':
    $query = $db->prepare("SELECT distinct(DID) FROM doctorspec WHERE SpecID IN (".(string)$obj['specids'].")");
    $query->execute();
    $doctors = "";
    while($que = $query->fetch()){
      $doctors.=(string)$que['DID'].",";
    }
    if($doctors != ""){
      $doctors = substr($doctors,0,-1);
      $result = $db->prepare("SELECT u.UserID,rid.RegistrationID
        FROM user u
        INNER JOIN registrationid rid
        ON rid.UserID = u.UserID WHERE u.UserID IN (".$doctors.")");
      $result->execute();
      while($row = $result->fetch()){
        $regids[] = $row['RegistrationID'];
        $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (-1,:id,:UserID)");
        $result2->bindParam(":id", $tipid, PDO::PARAM_INT);
        $result2->bindParam(":UserID", $row['UserID'], PDO::PARAM_INT);
        $result2->execute();
      }
      $response['CurlResponse'] = pushnotification(null,$obj['Title'], $obj['Text'], null, $db, $regids);
    }
    break;
    case 'custom':
    $result = $db->prepare("SELECT u.UserID,rid.RegistrationID
      FROM user u
      INNER JOIN registrationid rid
      ON rid.UserID = u.UserID WHERE u.UserID IN (".(string)$obj['userids'].")");
    $result->execute();
    while($row = $result->fetch()){
      $regids[] = $row['RegistrationID'];
      $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (-1,:id,:UserID)");
      $result2->bindParam(":id", $tipid, PDO::PARAM_INT);
      $result2->bindParam(":UserID", $row['UserID'], PDO::PARAM_INT);
      $result2->execute();
    }
    $response['CurlResponse'] = pushnotification(null,$obj['Title'], $obj['Text'], null, $db, $regids);
    break;
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Notifications Data";
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
