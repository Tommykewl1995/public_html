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
  $result =  $db->prepare("SELECT UserType FROM DConnection WHERE UserID = :UserID AND CommuID = :CommuID");
  $result->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
  $result->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
  $result->execute();
  $row = $result->fetch();
  if($row['UserType'] == 3){
    $result1 = $db->prepare("SELECT ShrID FROM ShareArticle WHERE CommuID = :CommuID");
    $result1->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $result1->execute();
    while($row1 = $result1->fetch()){
      $result3 = $db->prepare("INSERT INTO ShareArticleArchive (ShrID,UserID,Summary,ArID,T1,T2,IsAuthor,isPublic,LikesCount,CommentCount,CommuID)
      SELECT ShrID,UserID,Summary,ArID,T1,T2,IsAuthor,isPublic,LikesCount,CommentCount,CommuID
      FROM ShareArticle
      WHERE ShrID = :ShrID");
      $result3->bindParam(":ShrID", $row1['ShrID'],PDO::PARAM_INT);
      $result3->execute();
      $list = $list.(string)$row1['ShrID'].",";
    }
    $list = substr($list,0,-1);
    $result2 = $db->prepare("DELETE FROM ShareArticle WHERE ShrID IN (".$list.")");
    $result2->execute();
    // $result3 = $db->prepare("DELETE FROM SharedInCommunity WHERE CommuID = :CommuID");
    // $result3->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    // $result3->execute();
    $result4 = $db->prepare("DELETE FROM ComSpec WHERE CommuID = :CommuID");
    $result4->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $result4->execute();
    $result5 = $db->prepare("DELETE FROM DConnection WHERE CommuID = :CommuID");
    $result5->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $result5->execute();
    $result6 = $db->prepare("DELETE FROM ComDetails WHERE CommuID = :CommuID");
    $result6->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $result6->execute();
    $response['ResponseMessage'] = "Community Deleted Successfully";
  }else{
    $response['ResponseMessage'] = "User don't have community deletion Rights";
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Community Created";
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
