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
  if($obj['Like']){
    if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
      $response['ResponseCode'] = "400";
      $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
      $status['Status'] = $response;
      header('Content-type: application/json');
      echo json_encode($status);
      die();
    }
    $str = '+';
    $result = $db->prepare("INSERT INTO Likes (AppUserID,ShrID) VALUES (:UserID,:ShrID)");
    $result->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
    $result->execute();
    $response['ResponseMessage'] = "Article Liked";
    $likeid = $db->lastInsertId();
    $response['LikeId'] = $likeid;
    $result3 = $db->prepare("SELECT UserID FROM ShareArticle WHERE ShrID = :ShrID");
    $result3->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
    $result3->execute();
    $row3 = $result3->fetch();
    if($row3['UserID'] != $obj['UserID']){
      $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (14,:ID,:UserID)");
      $result2->bindParam(":ID", $likeid,PDO::PARAM_INT);
      $result2->bindParam(":UserID", $row3['UserID'], PDO::PARAM_INT);
      $result2->execute();
      $nid = $db->lastInsertId();
      $result = $db->prepare("SELECT *, NOW() as now FROM Notifications WHERE NID = :NID"); //LIMIT ".$offset.",10");
      $result->bindParam(":NID", $nid, PDO::PARAM_INT);
      $result->execute();
      $row = $result->fetch();
      $data = getnotifications($row, $db);
      //$response['Curlresponse'] = json_decode(pushnotification($row3['UserID'], 'Like Notification', "User has liked your Article", "Like", $data, null, $db), true);
      // $query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
      // $query10->bindParam(':UserID', $row3['UserID'], PDO::PARAM_STR);
      // $query10->execute();
      // $row22 = $query10->fetch();

      // $registrationIds[] = $row22['RegistrationID'];

      // $message = "User has liked your Article";

      // $url = 'https://fcm.googleapis.com/fcm/send';
      // //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
      // $server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

      // define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
      //  define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

      //  $fields = array(

      //  "registration_ids" => $registrationIds ,
      //  "priority" => "high",
      //  "notification" => array( "title" => "Like Notification", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
      //  "data" => $data,
      //  );

      //  $headers = array(
      //  GOOGLE_GCM_URL,
      //  'Content-Type: application/json',
      //  'Authorization: key=' . GOOGLE_API_KEY
      //  );

      //  $ch = curl_init();
      //  curl_setopt($ch, CURLOPT_URL, GOOGLE_GCM_URL);
      //  curl_setopt($ch, CURLOPT_POST, true);
      //  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      //  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      //  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      //  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

      //  $result5 = curl_exec($ch);
      //  if ($result5 === FALSE) {
      //  die('Problem occurred: ' . curl_error($ch));
      //  }

      //  curl_close($ch);
      //  $response['CurlResponse'] = $result5;
    }
  }else{
    $str = '-';
    $result = $db->prepare("INSERT INTO UnLikes (LikeID,AppUserID,ShrID,T1)
    SELECT LikeID,AppUserID,ShrID,T1 FROM Likes WHERE ShrID = :ShrID AND AppUserID = :UserID");
    $result->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
    $result->execute();
    $result2 = $db->prepare("DELETE FROM Likes WHERE ShrID = :ShrID AND AppUserID = :UserID");
    $result2->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result2->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
    $result2->execute();
    $response['ResponseMessage'] = "Article UnLiked";
  }
  $result3 = $db->prepare("UPDATE ShareArticle SET LikesCount = (LikesCount".$str."1) WHERE ShrID = :ShrID");
  $result3->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
  $result3->execute();
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
