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
  if(!$obj['CommuID']){
    $query = $db->prepare("SELECT CommuID FROM ComDetails WHERE CreatorID = :UserID AND ComType = 0");
    $query->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
    $query->execute();
    $row = $query->fetch();
    $obj['CommuID'] = $row['CommuID'];
  }
  $query2 = $db->prepare("SELECT UserType FROM Dconnection WHERE CommuID = :CommuID AND UserID = :UserID");
  $query2->bindParam(":CommuID", $obj['CommuID'], PDO::PARAM_INT);
  $query2->bindParam(":UserID", $obj['ID'], PDO::PARAM_INT);
  $query2->execute();
  $row2 = $query2->fetch();
  if($row2){
    $response['UserType'] =$row2['UserType'];
  }else{
    if(!$obj['CommuID']){
      $query3 = $db->prepare("SELECT ReqID FROM CommunityRequests WHERE DID = :DID AND UserID = :UserID");
      $query3->bindParam(":DID", $obj['UserID'], PDO::PARAM_INT);
      $query3->bindParam(":UserID", $obj['ID'], PDO::PARAM_INT);
      $query3->execute();
      $row3 = $query3->fetch();
      if($row3){
        $response['UserType'] = 4;
      }
    }
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Community Request Sent";
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
