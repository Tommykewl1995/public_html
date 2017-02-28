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
  if($obj['RepID']){
    $result2 = $db->prepare("INSERT INTO DeletedReplies (RepID,ComID,UserID,Reply,IsAnony,T1,T2)
    SELECT RepID,ComID,UserID,Reply,IsAnony,T1,T2 FROM Reply WHERE RepID = :RepID");
    $result2->bindParam(":RepID", $obj['RepID'],PDO::PARAM_INT);
    $result2->execute();
    $result4 = $db->prepare("DELETE FROM Reply WHERE RepID = :RepID");
    $result4->bindParam(":RepID",$obj['RepID'],PDO::PARAM_INT);
    $result4->execute();
  }else{
    $result = $db->prepare("SELECT ShrID FROM Comments WHERE ComID = :ComID");
    $result->bindParam(":ComID", $obj['ComID'],PDO::PARAM_INT);
    $result->execute();
    $row = $result->fetch();
    $result2 = $db->prepare("INSERT INTO DeletedComments (ComID,ShrID,UserID,Comment,IsAnony,T1,T2)
    SELECT ComID,ShrID,UserID,Comment,IsAnony,T1,T2 FROM Comments WHERE ComID = :ComID");
    $result2->bindParam(":ComID", $obj['ComID'],PDO::PARAM_INT);
    $result2->execute();
    $result4 = $db->prepare("DELETE FROM Comments WHERE ComID = :ComID");
    $result4->bindParam(":ComID",$obj['ComID'],PDO::PARAM_INT);
    $result4->execute();
    $result1 = $db->prepare("UPDATE ShareArticle SET CommentCount = CommentCount-1 WHERE ShrID = :ShrID");
    $result1->bindParam(":ShrID", $row['ShrID'],PDO::PARAM_INT);
    $result1->execute();
  }
  $response['ResponseMessage'] = "Comment Deleted Successfully";
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
