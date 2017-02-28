<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
//require('helperfunctions.php');
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

  $result2 = $db->prepare("SELECT u.Pic, u.FName, u.LName, u.Email, u.Phone from user u WHERE u.UserID = :UserID");
  $result2->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
  $result2->execute();
  $row2 = $result2->fetch();

  $result = $db->prepare("SELECT CommuID, Name, Status FROM ComDetails WHERE CreatorID = :CreatorID");
  $result->bindParam(":CreatorID", $obj['UserID'], PDO::PARAM_INT);
  $result->execute();
  $row = $result->fetch();
  $response['DID'] = $obj['UserID'];
  $response['CommuID'] = $row['CommuID'];
  $response['FName'] = $row2['FName'];
  $response['LName'] = $row2['LName'];
  $response['Email'] = $row2['Email'];
  $response['Phone'] = $row2['Phone'];
  $response['Name'] = $row['Name'];
  $response['Pic'] = $row2['Pic'];
  $response['Status'] = $row['Status'];
  $response['ResponseMessage'] = "Community Data";
  $response['ResponseCode'] = "200";
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($status);
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "An Error occurred!" . $ex; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
  echo json_encode($status);
}
