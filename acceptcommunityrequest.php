<?php
/*
Table Community Requests
Status :
0 : Pending
1: Accepted
2: Rejected
*/
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
date_default_timezone_set('Asia/Kolkata');
require('helperfunctions1.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try{
  // if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
  //   $response['ResponseCode'] = "400";
  //   $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
  //   $status['Status'] = $response;
  //   header('Content-type: application/json');
  //   echo json_encode($status);
  //   die();
  // }
    $query = $db->prepare("SELECT * FROM Notifications WHERE NID = :NID");
    $query->bindParam(":NID", $obj['NID'],PDO::PARAM_INT);
    $query->execute();
    $row = $query->fetch();
    $query2 = $db->prepare("SELECT * FROM CommunityRequests WHERE ReqID = :ReqID");
    $query2->bindParam(":ReqID", $row['ID'],PDO::PARAM_INT);
    $query2->execute();
    $row2 = $query2->fetch();
    if($row2['Status'] > 2){
      $query3 = $db->prepare("SELECT CommuID FROM clinics WHERE ClinicID = :ClinicID");
      $query3->bindParam(":ClinicID", $row2['CommuID'], PDO::PARAM_INT);
      $query3->execute();
      $que3 = $query3->fetch();
      $row2['ClinicID'] = $row2['CommuID'];
      $row2['CommuID'] = $que3['CommuID'];
    }
    $community = new Community($db,$row2['CommuID']);
    $query3 = $db->prepare("SELECT UserType FROM Dconnection WHERE CommuID = :CommuID AND UserID = :UserID");
    $query3->bindParam(":CommuID", $row2['CommuID'],PDO::PARAM_INT);
    $query3->bindParam(":UserID", $row2['UserID'],PDO::PARAM_INT);
    $query3->execute();
    $row3 = $query3->fetch();
    if($obj['Accept']){
      if($row2['Status'] > 2){
        $community->adddoctorstocommunity($row2['UserID'], $row2['ClinicID']);
        if($row3){
          if($row3['UserType'] < 2){
            $community->editmembers($row2['UserID'], 2);
          }
        }else{
          $community->addmemberstocommunity($row2['UserID'], 2);
        }
      }else{
        if($row3){
          if($row3['UserType'] < 1){
            $community->editmembers($row2['UserID'], 1);
          }
        }else{
          $community->addmemberstocommunity($row2['UserID'], 1);
        }
      }
      $query = $db->prepare("SELECT ComType,Name FROM ComDetails WHERE CommuID = :CommuID");
      $query->bindParam(":CommuID", $row2['CommuID'], PDO::PARAM_INT);
      $query->execute();
      $que = $query->fetch();
      list($response['CommuID'], $response['Type'], $response['Name']) = [$row2['CommuID'], $que['ComType'], $que['Name']];
      $response['ResponseMessage'] = "Community Request Accepted";
      $status = ($row2['Status'] > 2)?4:1;
      $word = "accept";
      $result2 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (12,:ID,:UserID)");
      $result2->bindParam(":UserID",$row2['DID'],PDO::PARAM_INT);
      $result2->bindParam(":ID",$row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $nid = $db->lastInsertId();
      $result = $db->prepare("SELECT *, NOW() as now FROM Notifications WHERE NID = :NID"); //LIMIT ".$offset.",10");
      $result->bindParam(":NID", $nid, PDO::PARAM_INT);
      $result->execute();
      $row = $result->fetch();
      $data = getnotifications($row, $db);
      $response['CurlResponse'] = json_decode(pushnotification($row2['DID'], 'Community Request Accepted', "User has accepted your Community Request", $data, $db), true);
    }else{
      $response['ResponseMessage'] = "Community Request Rejected";
      $status = ($row2['Status'] > 2)?5:2;
    }
    $result3 = $db->prepare("UPDATE CommunityRequests SET Status=:Status WHERE ReqID = :ReqID");
    $result3->bindParam(":ReqID", $row['ID'],PDO::PARAM_INT);
    $result3->bindParam(":Status", $status,PDO::PARAM_INT);
    $result3->execute();
    $response['ResponseCode'] = "200";
    $stat['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($stat);
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
  echo json_encode($status);
}
