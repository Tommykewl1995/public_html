<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require('helperfunctions1.php')
date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);

try{
  $community = new Community($db, $obj['CommuID']);
  $result1 = $db->prepare("SELECT UserType FROM Dconnection WHERE UserID = :UserID AND CommuID = :CommuID");
  $result1->bindParam(":UserID", $obj['ID'],PDO::PARAM_INT);
  $result1->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
  $result1->execute();
  $row1 = $result1->fetch();
  if($obj['UserID'] == $obj['ID']){
    if($row1['UserType'] == 3){
      $response['ResponseMessage'] = "Creator cannot remove himself from community";
      $response['ResponseCode'] = "500";
    }elseif($row1['UserType'] == 2){
      $community->removedoctorsfromcommunity($obj['ID']);
    }else{
      $community->removemembersfromcommunity($obj['ID']);
      $response['ResponseMessage'] = "User removed successfully";
      $response['ResponseCode'] = "200";
    }
  }else{
    $result = $db->prepare("SELECT UserType FROM Dconnection WHERE UserID = :UserID AND UserType IN (2,3) AND CommuID = :CommuID");
    $result->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result->bindParam(":CommuID", $obj['CommuID'],PDO::PARAM_INT);
    $result->execute();
    $row = $result->fetch();
    if($row && $row['Usertype'] > $row1['UserType']){
      if($row1['UserType'] == 2){
        $community->removedoctorsfromcommunity($obj['ID']);
      }else{
        $community->removemembersfromcommunity($obj['ID']);
      }
      $response['ResponseMessage'] = "User removed successfully";
      $response['ResponseCode'] = "200";
    }else{
      $response['ResponseMessage'] = "User doesn't have rights to remove other users";
      $response['ResponseCode'] = "500";
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
  echo json_encode($status);
}
