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
  if($obj['reply']){
    $result2 = $db->prepare("INSERT INTO Reply (ComID,UserID,Reply,IsAnony) VALUES (:ComID,:UserID,:Reply,:Anon)");
    $result2->bindParam(":ComID", $obj['ComID'],PDO::PARAM_INT);
    $result2->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result2->bindParam(":Reply", $obj['Comment'],PDO::PARAM_STR);
    $result2->bindParam(":Anon", $obj['Anon'],PDO::PARAM_INT);
    $result2->execute();
    $response['RepID'] = $db->lastInsertId();
    $response['ResponseMessage'] = "Replied Successfully";
    $result3 = $db->prepare("SELECT UserID FROM Comments WHERE ComID = :ComID");
    $result3->bindParam(":ComID", $obj['ComID'], PDO::PARAM_INT);
    $result3->execute();
    $row3 = $result3->fetch();
    if($row3['UserID'] != $obj['UserID']){
      $result4 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (17,:UserID,:ID)");
      $result4->bindParam(":UserID", $response['RepID'],PDO::PARAM_INT);
      $result4->bindParam(":ID", $row3['UserID'], PDO::PARAM_INT);
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
      //$response['CurlResponse'] = json_decode(pushnotification($row3['UserID'], "Comment Notification", "User has commented on your Article", "Comment", $data, null, $db), true);
      $query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
      $query10->bindParam(':UserID', $row3['UserID'], PDO::PARAM_STR);
      $query10->execute();
      $row22 = $query10->fetch();

      $registrationIds[] = $row22['RegistrationID'];
      if($obj['Anon'] == '0'){
        $message = $row33['FName']." ".$row33['LName']." has commented on your Article";
      }else{
        $message = "Anonymous has commented on your Article";
      }

      $url = 'https://fcm.googleapis.com/fcm/send';
      //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
      $server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

      define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
       define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

       $fields = array(

       "registration_ids" => $registrationIds ,
       "priority" => "high",
       "notification" => array( "title" => "Comment Notification", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
       "data" => array("message" =>$message, "title" => "Comment Notification", "image"=> $img_url),
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
  }else{
    $result2 = $db->prepare("INSERT INTO Comments (ShrID,UserID,Comment,IsAnony) VALUES (:ShrID,:UserID,:Comment,:Anon)");
    $result2->bindParam(":ShrID", $obj['ShrID'],PDO::PARAM_INT);
    $result2->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result2->bindParam(":Comment", $obj['Comment'],PDO::PARAM_STR);
    $result2->bindParam(":Anon", $obj['Anon'],PDO::PARAM_INT);
    $result2->execute();
    $response['ComID'] = $db->lastInsertId();
    $result3 = $db->prepare("SELECT UserID FROM ShareArticle WHERE ShrID = :ShrID");
    $result3->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
    $result3->execute();
    $row3 = $result3->fetch();
    if($row3['UserID'] != $obj['UserID']){
      $result4 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (15,:UserID,:ID)");
      $result4->bindParam(":UserID", $response['ComID'],PDO::PARAM_INT);
      $result4->bindParam(":ID", $row3['UserID'], PDO::PARAM_INT);
      $result4->execute();
      $nid = $db->lastInsertId();
      $result = $db->prepare("SELECT * FROM Notifications WHERE NID = :NID"); //LIMIT ".$offset.",10");
      $result->bindParam(":NID", $nid, PDO::PARAM_INT);
      $result->execute();
      $row = $result->fetch();
      $data = getnotifications($row, $db);
      //$response['CurlResponse'] = json_decode(pushnotification($row3['UserID'], "Reply Notification", "User has replied on your Article", "Reply", $data, null, $db), true);
      $query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
      $query10->bindParam(':UserID', $row3['UserID'], PDO::PARAM_STR);
      $query10->execute();
      $row22 = $query10->fetch();

      $registrationIds[] = $row22['RegistrationID'];

      $message = "User has replied on your Article";

      $url = 'https://fcm.googleapis.com/fcm/send';
      //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
      $server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

      define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
       define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

       $fields = array(

       "registration_ids" => $registrationIds ,
       "priority" => "high",
       "notification" => array( "title" => "Reply Notification", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
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
    $result1 = $db->prepare("UPDATE ShareArticle SET CommentCount = CommentCount+1 WHERE ShrID = :ShrID");
    $result1->bindParam(":ShrID", $obj['ShrID'],PDO::PARAM_INT);
    $result1->execute();
    $response['ResponseMessage'] = "Commented Successfully";
  }
  $response['ResponseCode'] = "200";
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
