<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

function addtips($t, $db){
  $temp = $db->prepare("INSERT INTO Tip (Tip) VALUES (:Tip)");
  $temp->bindParam(":Tip", $t, PDO::PARAM_STR);
  $temp->execute();
  return $db->lastInsertId();
}

try{
  $sec = (int)$obj['Secondary'];
  $query2 = $db->prepare("SELECT CAID,Tips FROM CaseArticles WHERE ConditionID =:ConditionID AND Day = :Day AND Tips IS NOT NULL");
  $query2->bindParam(":ConditionID", $obj['ConditionID'], PDO::PARAM_STR);
  $query2->bindParam(":Day", $obj['Day'], PDO::PARAM_INT);
  $query2->execute();
  if($row1 = $query2->fetch()){
    $tips = json_decode($row1['Tips']);
    $tipid = addtips($obj['Tip'], $db);
    if(count($tips) == 1 && $sec == 1){
      $tips[] = $tipid;
    }else{
      $tips[$sec] = $tipid;
    }
    $tips = json_encode($tips);
    $query3 = $db ->prepare("UPDATE CaseArticles SET Tips =:Tips WHERE CAID =:CAID");
    $query3->bindParam(":Tips", $tips, PDO::PARAM_STR);
    $query3->bindParam(":CAID", $row1['CAID'], PDO::PARAM_INT);
    $query3->execute();
    $response['ResponseCode'] = "200";
    $response['ResponseMessage'] = "Case Tips Edited";
  }else{
    if($sec == 1){
      $response['ResponseCode'] = "400";
      $response['ResponseMessage'] = "First add Primary Tip before adding Secondary Tip";
    }else{
      $tipid = addtips($obj['Tip'], $db);
      $tips = json_encode(array($tipid));
      $query = $db->prepare("INSERT INTO CaseArticles (ConditionID,Day,Tips) VALUES (:ConditionID, :Day, :Tips)");
      $query->bindParam(":ConditionID", $obj['ConditionID'], PDO::PARAM_STR);
      $query->bindParam(":Day", $obj['Day'], PDO::PARAM_INT);
      $query->bindParam(":Tips", $tips, PDO::PARAM_STR);
      $query->execute();
      $response['CAID'] = $db->lastInsertId();
      $response['ResponseCode'] = "200";
      $response['ResponseMessage'] = "Case Tips Added";
    }
  }
  $status['Status'] = $response;
  header('Content-type: application/json');
  echo json_encode($status);
}catch(PDOException $ex){
	$response['ResponseCode'] = "500";
  $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
  $status['Status'] = $response;
  header('Content-type: application/json');
	echo json_encode($response);
}
