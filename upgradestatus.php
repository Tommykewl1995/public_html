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

function changestatus($usertype,$commuid,$id){
  $result3 = $db->prepare("UPDATE DConnection SET UserType = :UserType WHERE CommuID = :CommuID AND UserID = :UserID");
  $result3->bindParam(":CommuID",$commuid, PDO::PARAM_INT);
  $result3->bindParam(":UserID",$id, PDO::PARAM_INT);
  $result3->bindParam(":UserType",$usertype, PDO::PARAM_INT);
  $result3->execute();
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
  $result = $db->prepare("SELECT UserType FROM DConnection WHERE CommuID = :CommuID AND UserID = :UserID");
  $result->bindParam(":CommuID",$obj['CommuID'], PDO::PARAM_INT);
  $result->bindParam(":UserID",$obj['UserID'], PDO::PARAM_INT);
  $result->execute();
  $row = $result->fetch();
  $result2 = $db->prepare("SELECT UserType FROM DConnection WHERE CommuID = :CommuID AND UserID = :UserID");
  $result2->bindParam(":CommuID",$obj['CommuID'], PDO::PARAM_INT);
  $result2->bindParam(":UserID",$obj['ID'], PDO::PARAM_INT);
  $result2->execute();
  $row2 = $result2->fetch();
  switch($obj['Action']){
    case "makeadmin":
    if($row['UserType'] > 1){
      if($row2['UserType'] == 1){
        changestatus(2,$obj['CommuID'],$obj['ID']);
        $response['ResponseMessage'] = "Admin Created";
      }else{
        $response['ResponseMessage'] = "Only Connections can be made Admin";
      }
    }else{
      $response['ResponseMessage'] = "Only Admins and Creators can make others Admin";
    }
    break;
    case "removeadmin":
    if($row['UserType'] > 2){
      if($row2['UserType'] == 2){
        changestatus(1,$obj['CommuID'],$obj['ID']);
        $response['ResponseMessage'] = "Admin Removed";
      }else{
        $response['ResponseMessage'] = "Only Admins can be Removed";
      }
    }elseif($obj['UserID'] == $obj['ID']){
      if($row2['UserType'] == 2){
        changestatus(1,$obj['CommuID'],$obj['ID']);
        $response['ResponseMessage'] = "Admin Removed";
      }else{
        $response['ResponseMessage'] = "Only Admins can be Removed";
      }
    }else{
      $response['ResponseMessage'] = "Only Creators or Admin Himself can revoke Admin Status";
    }
    break;
    case "changecreator":
    if($row['UserType'] > 2){
      if($row2['UserType'] == 2){
        changestatus(2,$obj['CommuID'],$obj['UserID']);
        changestatus(3,$obj['CommuID'],$obj['ID']);
        $response['ResponseMessage'] = "Creator Changed";
      }else{
        $response['ResponseMessage'] = "Only Admins can be made creators";
      }
    }else{
      $response['ResponseMessage'] = "Only Creator can change Creator";
    }
    break;
    default:
    $response['ResponseMessage'] = "Invalid action specified";
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
