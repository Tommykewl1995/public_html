  <?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require('helperfunctions1.php');
date_default_timezone_set('Asia/Kolkata');
$registrationIds = array();
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
  $aid = $obj['ArID'];
  $valid = 1;
  switch($obj['Type']){
    case 0:
    $aid = createarticle($obj['UserID'],$obj['Header'],$obj['Summary'],$obj['Link'],$obj['Details'],$obj['Preferences'],$obj['ImageLink'], $db);
    if($aid == 0 && strlen($obj['Link']) != 0){
      $valid = 0;
    }
    case 0:
    case 1:
    $isAuthor = 1;
    $ispublic = $obj['IsPublic'];
    break;
    case 2:
    $isAuthor = 0;
    $ispublic = 0;
    $query = $db->prepare("SELECT ArID,UserID,IsAuthor FROM ShareArticle WHERE ShrID = :ShrID");
    $query->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
    $query->execute();
    $row = $query->fetch();
    if((int)$row['UserID'] == $obj['UserID'] && (int)IsAuthor == 1){
      $isAuthor = 1;
    }
    $aid = $row['ArID'];
  }
  if($valid === 1)
  {
    $response['ShrId'] = sharearticle($obj['UserID'],$obj['Summary'],$aid,$isAuthor,$ispublic,$obj['CommuID'], $db);
    if($obj['Type'] == 2){
      $query2 = $db->prepare("SELECT UserID FROM Articles WHERE ArID = :ArID");
      $query2->bindParam(":ArID", $aid, PDO::PARAM_INT);
      $query2->execute();
      $row2 = $query2->fetch();
      if($row2['UserID'] != $obj['UserID'])
      {
        $result4 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (16,:UserID,:ID)");
        $result4->bindParam(":UserID", $response['ShrId'],PDO::PARAM_INT);
        $result4->bindParam(":ID", $row2['UserID'], PDO::PARAM_INT);
        $result4->execute();
        $nid = $db->lastInsertId();
        $result = $db->prepare("SELECT *, NOW() as now FROM Notifications WHERE NID = :NID"); //LIMIT ".$offset.",10");
        $result->bindParam(":NID", $nid, PDO::PARAM_INT);
        $result->execute();
        $row = $result->fetch();
        $data = getnotifications($row, $db);
        //$response['CurlResponse'] = json_decode(pushnotification($row2['UserID'], 'Share Notification', "User has shared your Article", $data, $db), true);
        $query11 = $db->prepare("SELECT FName, LName from user where UserID = :UserID");
      $query11->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
      $query11->execute();
      $row23 = $query11->fetch();
      if($obj['UserID'] == '1'){

      }
        $query10 = $db->prepare("SELECT r.RegistrationID from registrationid r inner join Dconnection where CommuID = :CommuID and UserType=1");
      $query10->bindParam(':CommuID', $obj['CommuID'], PDO::PARAM_STR);
      $query10->execute();
      while($row22 = $query10->fetch())
      {
        $registrationIds[] = $row22['RegistrationID'];
      }
      $message = "Dr. ".$row23['FName']." ".$row23['LName']." has shared an Article";

      $url = 'https://fcm.googleapis.com/fcm/send';
      //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
      $server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

      define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
       define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

       $fields = array(

       "registration_ids" => $registrationIds ,
       "priority" => "high",
       "notification" => array( "title" => "Share Notification", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
       "data" => array("message" =>$message, "title" => "Share Notification", "image"=> $img_url),
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
    }
    $response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "Article Shared";
  }else{
    $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "Please check your link, enter it with http:// or https://";
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
