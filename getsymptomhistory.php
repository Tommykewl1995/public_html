<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
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
  if($obj['PFID']){
    $query = $db->prepare("SELECT CSNID FROM patientform WHERE PFID = :PFID");
    $query->bindParam(":PFID", $obj['PFID'], PDO::PARAM_INT);
    $query->execute();
    $que = $query->fetch();
    $pfid = $obj['PFID'];
  }else{
    $query = $db->prepare("SELECT pp.CPFID,pf.CSNID FROM patientprofile pp INNER JOIN patientform pf ON pp.CPFID = pf.PFID WHERE pp.PID = :PID");
    $query->bindParam(":PID", $obj['UserID'], PDO::PARAM_INT);
    $query->execute();
    $que = $query->fetch();
    $pfid = $que['CPFID'];
  }
  $result = $db->prepare("SELECT * FROM SymTracker st WHERE PFID = :PFID ORDER BY SID,SNID");
  $result->bindParam(":PFID", $pfid, PDO::PARAM_INT);
  $result->execute();
  $data = array();
  $sids = array();
  $initial = true;
  while($row = $result->fetch()){
    if($sid != $row['SID']){
      if(!$initial){
        if($que['CSNID'] > $snid){
          while($que['CSNID'] != $snid){
            $strengths[] = 0;
            $snid++;
          }
        }
        $result2 = $db->prepare("SELECT SymptomName FROM symptoms WHERE SID = :SID");
        $result2->bindParam(":SID", $sid,PDO::PARAM_STR);
        $result2->execute();
        $row2 = $result2->fetch();
        $symptoms[] = $row2['SymptomName'];
        $sids[] = $sid;
        $data[] = $strengths;
      }else{
        $initial = false;
      }
      $strengths = array();
      $snid = 0;
      $sid = $row['SID'];
    }
    if($row['SNID'] != ($snid+1)){
      while($row['SNID'] != ($snid+1)){
        $strengths[] = 0;
        $snid++;
      }
    }
    $strengths[] = (int)$row['Strength'];
    $snid++;
  }
  if($que['CSNID'] > $snid){
    while($que['CSNID'] != $snid){
      $strengths[] = 0;
      $snid++;
    }
  }
  $sids[] = $sid;
  $data[] = $strengths;
  for($i=1;$i <= $que['CSNID'];$i++){
    $m[] = $i;
  }
  $bigdata = array("Symptoms" => $symptoms, "SIDS" => $sids ,"SNIDS" => $m, "data" => $data);
  $response['Data'] = $bigdata;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "added track";
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
