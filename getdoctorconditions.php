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
  $data = array();
  $data[] = array("ConditionID" => "gen", "ConditionName" => "General");
  $data[] = array("ConditionID" => "rev", "ConditionName" => "Revisit");
  if($obj['all']){
    $query2 = $db->prepare("SELECT ConditionName,ConditionID FROM conditions");
  }else{
    $query = $db->prepare("SELECT SpecID FROM doctorspec WHERE DID = :DID");
    $query->bindParam(":DID", $obj['UserID'], PDO::PARAM_INT);
    $query->execute();
    $specs = "";
    while($que = $query->fetch()){
      $specs.=(string)$que['SpecID'].",";
    }
    $specs = substr($specs,0,-1);
    $query2 = $db->prepare("SELECT c.ConditionName,c.ConditionID FROM conditionscategory cc INNER JOIN conditions c ON c.ConditionID = cc.ConditionID WHERE cc.SpecID IN (:Specs)");
    $query2->bindParam(":Specs", $specs, PDO::PARAM_INT);
  }
  $query2->execute();
  while($que2 = $query2->fetch()){
    $data[] = array("ConditionID" => $que2['ConditionID'], "ConditionName" => $que2['ConditionName']);
  }
  $response['Conditions'] = $data;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Conditions Data";
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
