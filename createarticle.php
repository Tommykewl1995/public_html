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
  if($obj['time']){
    $time = date("Y-m-d H:i:s", $obj['time']);
  }else{
    $time = null;
  }
  $response['ArticleId'] = createarticle($obj['UserID'],$obj['Header'],$obj['Summary'],$obj['Link'],$obj['Details'], $obj['Preferences'], $obj['ImageLink'], $db, $time);
  if(!$obj['Link']){
    $obj['Link'] = "";
  }
  if($response['ArticleId'] == 0 && strlen($obj['Link']) != 0){
    $response['ResponseCode'] = 500;
    $response['ResponseMessage'] = "Not a valid link";
  }else{
    $response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "Article Inserted";
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
