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
  $query = $db->prepare("SELECT pp.CPFID,pf.CSNID
    FROM patientprofile pp
    INNER JOIN patientform pf
    ON pp.CPFID = pf.PFID
    WHERE pp.PID = :PID");
  $query->bindParam(":PID", $obj['UserID'], PDO::PARAM_INT);
  $query->execute();
  $row = $query->fetch();
  if($row){
    $result = $db->prepare("SELECT st.SID,st.Strength,st.T,s.SymptomName FROM SymTracker st INNER JOIN symptoms s ON s.SymptomID= st.SID WHERE st.PFID = :PFID AND st.SNID = :SNID");
    $result->bindParam(":PFID", $row['CPFID'], PDO::PARAM_INT);
    $result->bindParam(":SNID", $row['CSNID'], PDO::PARAM_INT);
    $result->execute();
    $t = null;
    while($row1 = $result->fetch()){
      $data[] = array("Name" => $row1['SymptomName'], "SID" => $row1['SID'], "Strength" => (int)$row1['Strength']);
      $t = $row1['T'];
    }
    $response['PFID'] = $row['CPFID'];
    $response['SNID'] = $row['CSNID'];
    $response['T'] = $t;
    $response['Data'] = $data;
    $response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "tracker pre data";
  }else{
    $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "Patient Not Found";
  }
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
