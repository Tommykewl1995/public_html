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
  $data = array();
  $query = $db->prepare("SELECT CCID, ConditionCategory FROM conditionscategory");
  $query->execute();
  while($que = $query->fetch()){
    $query2 = $db->prepare("SELECT SpecID FROM speciality WHERE Speciality = :Speciality");
    $query2->bindParam(":Speciality", $que['ConditionCategory'], PDO::PARAM_INT);
    $query2->execute();
    $count = "";
    if($que2 = $query2->fetch()){
      if(!is_null($que2['SpecID'])){
        $count = $que2['SpecID'];
        $query3 = $db->prepare("UPDATE conditionscategory SET SpecID = :SpecID WHERE CCID = :CCID");
        $query3->bindParam(":SpecID", $que2['SpecID'], PDO::PARAM_INT);
        $query3->bindParam(":CCID", $que['CCID'], PDO::PARAM_INT);
        $query3->execute();
      }
    }
    $data[] = array("Count" => $count);
  }
  $response['Data'] = $data;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Test Done";
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
