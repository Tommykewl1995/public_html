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
    $result1 = $db->prepare("SELECT ReqID FROM CommunityRequests WHERE DID = :DID AND UserID = :UserID AND CommuID = :CommuID AND Status IN (0,2)");
    $result1->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
    $result1->bindParam(":DID",$obj['UserID'],PDO::PARAM_INT);
    $result1->bindParam(":CommuID",$obj['CommuID'],PDO::PARAM_INT);
    $result1->execute();
    $row = $result1->fetch();
    if($row){
      $response['Alert'] = "You have already sent connection request";
      continue;
    }
    $check =  $db->prepare("SELECT IsDoctor FROM user WHERE UserID = :UserID");
    $check->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
    $check ->execute();
    $check1 = $check->fetch();
    if($check1){
          $query = $db->prepare("INSERT INTO CommunityRequests (DID,UserID,CommuID,Status) VALUES (:DID,:UserID,:CommuID,'2')");
          $query->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
          $query->bindParam(":DID",$obj['UserID'],PDO::PARAM_INT);
          $query->bindParam(":CommuID",$obj['CommuID'],PDO::PARAM_INT);
          $query->execute();
          
    }
    else{
          $query = $db->prepare("INSERT INTO CommunityRequests (DID,UserID,CommuID) VALUES (:DID,:UserID,:CommuID)");
          $query->bindParam(":UserID",$list[$i],PDO::PARAM_INT);
          $query->bindParam(":DID",$obj['UserID'],PDO::PARAM_INT);
          $query->bindParam(":CommuID",$obj['CommuID'],PDO::PARAM_INT);
          $query->execute();
    }
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
    //$response['CurlResponse'] = json_decode(pushnotification($list[$i], 'Doctor Community Request', "Doctor has requested to join Community", "Doctor Request", $data, null, $db), true);
          $query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
          $query10->bindParam(':UserID', $list[$i], PDO::PARAM_STR);
          $query10->execute();
          $row22 = $query10->fetch();

          $registrationIds[] = $row22['RegistrationID'];
           
          if(check1){
          $message = $row33['FName']." ".$row33['LName']." has requested you to join his Community";
          }
          else{
          $message = "Dr.".$row33['FName']." ".$row33['LName']." has requested you to join his Community";
          }
         

          $url = 'https://fcm.googleapis.com/fcm/send';
          //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
          $server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

          define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
           define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

           $fields = array(
           "registration_ids" => $registrationIds ,
           "priority" => "high",
           "notification" => array( "title" => "Doctor Community Request", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
           "data" => $data,
           );

           $headers = array(
           GOOGLE_GCM_URL,
           'Content-Type: application/json',
           'Authorization: key=' . GOOGLE_API_KEY
           );

           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL, GOOGLE_GCM_URL);
           curl_setopt($ch, CURLOPT_POST, true);
           curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
           curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

           $result5 = curl_exec($ch);
           if ($result5 === FALSE) {
           die('Problem occurred: ' . curl_error($ch));
           }

           curl_close($ch);
           $response['CurlResponse'] = $result5;
          
  
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = .$message. ;
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
