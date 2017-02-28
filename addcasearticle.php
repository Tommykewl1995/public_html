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

try{
  $query2 = $db->prepare("SELECT 1 FROM CaseArticles WHERE ConditionID =:ConditionID AND Day = :Day");
  $query2->bindParam(":ConditionID", $obj['ConditionID'], PDO::PARAM_STR);
  $query2->bindParam(":Day", $obj['Day'], PDO::PARAM_INT);
  $query2->execute();
  if($query2->rowCount() == 3){
            // $query3 = $db ->prepare("UPDATE CaseArticles SET ArticleID =:ArticleID WHERE CAID =:CAID");
            // $query3->bindParam(":ArticleID", $obj['ArticleID'], PDO::PARAM_INT);
            // $query3->bindParam(":CAID", $row1['CAID'], PDO::PARAM_INT);
            // $query3->execute();
           $response['ResponseCode'] = "400";
           $response['ResponseMessage'] = "Already 3 Case Articles inserted for this Condition and Day";
  }else{
  $query1 = $db->prepare("SELECT Day FROM CaseArticles WHERE ConditionID =:ConditionID AND ArticleID = :ArticleID");
  $query1->bindParam(":ConditionID", $obj['ConditionID'], PDO::PARAM_STR);
  $query1->bindParam(":ArticleID", $obj['ArticleID'], PDO::PARAM_INT);
  $query1->execute();
     if($row = $query1->fetch()){
       $response['ResponseCode'] = "400";
       $response['ResponseMessage'] = "Article already added for day ".$row['Day']." for given Condition";
     }else{
       $query = $db->prepare("INSERT INTO CaseArticles (ConditionID,Day,ArticleID) VALUES (:ConditionID, :Day, :ArticleID)");
       $query->bindParam(":ConditionID", $obj['ConditionID'], PDO::PARAM_STR);
       $query->bindParam(":Day", $obj['Day'], PDO::PARAM_INT);
       $query->bindParam(":ArticleID", $obj['ArticleID'], PDO::PARAM_INT);
       $query->execute();
       $response['CAID'] = $db->lastInsertId();
       $response['ResponseCode'] = "200";
       $response['ResponseMessage'] = "Case Articles Added";
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
