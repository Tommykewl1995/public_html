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
  $result = $db->prepare("SELECT c.ComID, c.Comment, c.IsAnony,c.T2, c.UserID, CONCAT_WS(' ',u.FName,u.LName) AS FullName, u.Pic FROM Comments c INNER JOIN user u ON u.UserID = c.UserID WHERE c.ShrID = :ShrID");
  $result->bindParam(":ShrID", $obj['ShrID'], PDO::PARAM_INT);
  $result->execute();
  $data = array();
  while($row = $result->fetch()){
    $data[] = array("isrepopen" => 0,"Comment" => (string)$row['Comment'], "IsAnon" => $row['IsAnony'], "LastEdited" => (string)$row['T2'],
  "UserID" => $row['UserID'], "FullName" => $row['FullName'], "ComID" => $row['ComID'], "Pic" => $row['Pic']);
  }
  $response['Comments'] = $data;
  $response['lol'] = $lil;
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
