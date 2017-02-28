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
  if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
    $response['ResponseCode'] = "400";
    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($status);
    die();
  }
  $query = $db->prepare("SELECT CID FROM Dconnection WHERE  CommuID = :CommuID AND UserID = :UserID");
  $query->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
  $query->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
  $query->execute();
  $que = $query->fetch();
  if(!$que){
    $community = new Community($db, $obj['CommuID']);
    $response['CID'] = $community->addmemberstocommunity($obj['UserID'], 0);
    $result2 = $db->prepare("SELECT Name,ComType FROM ComDetails WHERE CommuID = :CommuID");
    $result2->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $response['Name'] = $row2['Name'];
    $response['Type'] = $row2['ComType'];
    $response['ResponseMessage'] = "Followed Community Successfully";
    $query = $db->prepare("SELECT CreatorID FROM ComDetails WHERE CommuID = :CommuID");
    $query->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $query->execute();
    $row = $query->fetch();
    $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (13,:ID,:UserID)");
    $result2->bindParam(":UserID",$row['CreatorID'],PDO::PARAM_INT);
    $result2->bindParam(":ID",$response['CID'],PDO::PARAM_INT);
    $result2->execute();
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
    //$response['CurlResponse'] = json_decode(pushnotification($row['UserID'], 'Follow Notification', "User has followed your Community", "Following", $data, null, $db), true);
    $query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
    $query10->bindParam(':UserID', $row['CreatorID'], PDO::PARAM_STR);
    $query10->execute();
    $row22 = $query10->fetch();

    $registrationIds[] = $row22['RegistrationID'];

    $message = $row33['FName']." ".$row33['LName']." has followed your Community";

    $url = 'https://fcm.googleapis.com/fcm/send';
    //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
    $server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

    define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
     define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

     $fields = array(

     "registration_ids" => $registrationIds ,
     "priority" => "high",
     "notification" => array( "title" => "Follow Notification", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
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
    $response['CurlResponse'] = json_decode($result5);
    $response['ResponseMessage'] = "User followed Community";
    $response['ResponseCode'] = "200";
  }else{
    $response['ResponseMessage'] = "User already present in this community";
    $response['ResponseCode'] = "500";
  }
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
