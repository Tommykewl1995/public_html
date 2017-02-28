<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require('helperfunctions.php');
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
  $result2 = $db->prepare("SELECT r.Reply, r.IsAnony,r.T2, r.UserID, CONCAT_WS(' ',u.FName,u.LName) AS FullName, u.Pic FROM Reply r INNER JOIN user u ON r.UserID = u.UserID WHERE ComID = :ComID");
  $result2->bindParam(":ComID", $obj['ComID'], PDO::PARAM_INT);
  $result2->execute();
  $replies = array();
  while($row2 = $result2->fetch()){
    $replies[] = array("Reply" => (string)$row2['Reply'], "IsAnon" => $row2['IsAnony'], "LastEdited" => (string)$row2['T2'],
  "UserID" => $row2['UserID'], "FullName" => $row2['FullName'], "Pic" => $row2['Pic']);
  }
  $response['Replies'] = $replies;
  $response['ResponseMessage'] = "Comments Data";
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
