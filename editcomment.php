<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');

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
  if($obj['RepID']){
    $result2 = $db->prepare("UPDATE Reply SET Reply = :Reply WHERE RepID = :RepID");
    $result2->bindParam(":Reply", $obj['Comment'],PDO::PARAM_STR);
    $result2->bindParam(":RepID", $obj['RepID'],PDO::PARAM_INT);
    $result2->execute();
  }else{
    $result2 = $db->prepare("UPDATE Comments SET Comment = :Comment WHERE ComID = :ComID");
    $result2->bindParam(":Comment", $obj['Comment'],PDO::PARAM_STR);
    $result2->bindParam(":ComID", $obj['ComID'],PDO::PARAM_INT);
    $result2->execute();
  }
  $response['ResponseMessage'] = "Comment Edited Successfully";
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
