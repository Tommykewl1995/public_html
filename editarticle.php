<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

function doit($aid,$shrid,$data,$db){
  $keys = array_keys($data);
  for($i=0;$i < count($keys);$i++){
    switch($keys[$i]){
      case "Header":
      $result2 = $db->prepare("UPDATE Articles SET Header = :Header WHERE ArID = :ArID");
      $result2->bindParam(":ArID", $aid,PDO::PARAM_INT);
      $result2->bindParam(":Header", $data['Header'],PDO::PARAM_STR);
      $result2->execute();
      break;
      case "Summary":
      $result2 = $db->prepare("UPDATE Articles SET Summary = :Summary WHERE ArID = :ArID");
      $result2->bindParam(":ArID", $aid,PDO::PARAM_INT);
      $result2->bindParam(":Summary", $data['Summary'],PDO::PARAM_STR);
      $result2->execute();
      $result3 = $db->prepare("UPDATE ShareArticle SET Summary = :Summary WHERE ShrID = :ShrID");
      $result3->bindParam(":ShrID", $shrid,PDO::PARAM_INT);
      $result3->bindParam(":Summary", $data['Summary'],PDO::PARAM_STR);
      $result3->execute();
      break;
      case "Link":
      $result2 = $db->prepare("UPDATE Articles SET Link = :Link WHERE ArID = :ArID");
      $result2->bindParam(":ArID", $aid,PDO::PARAM_INT);
      $result2->bindParam(":Link", $data['Link'],PDO::PARAM_STR);
      $result2->execute();
      break;
      case "Details":
      $result2 = $db->prepare("UPDATE Articles SET Details = :Details WHERE ArID = :ArID");
      $result2->bindParam(":ArID", $aid,PDO::PARAM_INT);
      $result2->bindParam(":Details", $data['Details'],PDO::PARAM_STR);
      $result2->execute();
      break;
      case "ImageLink":
      $result2 = $db->prepare("UPDATE Articles SET ImageLink = :ImageLink WHERE ArID = :ArID");
      $result2->bindParam(":ArID", $aid,PDO::PARAM_INT);
      $result2->bindParam(":ImageLink", $data['ImageLink'],PDO::PARAM_STR);
      $result2->execute();
      break;
    }
  }
}

function doit1($shrid,$data,$db){
  $result2 = $db->prepare("UPDATE ShareArticle SET Summary = :Summary WHERE ShrID = :ShrID");
  $result2->bindParam(":ShrID", $shrid,PDO::PARAM_STR);
  $result2->bindParam(":Summary", $data['Summary'],PDO::PARAM_STR);
  $result2->execute();
}

try{
  if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
    $response['ResponseCode'] = "400";
    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
    echo json_encode($status);
    die();
  }
  $shrid = $obj['ShrID'];
  $result = $db->prepare("SELECT ArID,UserID,IsAuthor FROM ShareArticle WHERE ShrID = :ShrID");
  $result->bindParam(":ShrID", $obj['ShrID'],PDO::PARAM_INT);
  $result->execute();
  $row = $result->fetch();
  $aid = $row['ArID'];
  $userID = $row['UserID'];
  $author = $row['IsAuthor'];
  if($userID == $obj['UserID']){
    if($author){
      doit($aid,$shrid,$obj['Data'],$db);
      $response['ResponseMessage'] = "Article Edited Successfully";
    }else{
      doit1($shrid,$obj['Data'],$db);
      $response['ResponseMessage'] = "Article Edited Successfully";
    }
    $result4 = $db->prepare("UPDATE ShareArticle SET T1 = NOW() WHERE ShrID = :ShrID");
    $result4->bindParam(":ShrID", $obj['ShrID'],PDO::PARAM_STR);
    $result4->execute();
  }else{
    $response['ResponseMessage'] = "Error: userid doesnt match";
  }
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
