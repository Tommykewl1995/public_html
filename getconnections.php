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

function getdata($commuid,$type, $db, $userid){
  $result = $db->prepare("SELECT CONCAT_WS(' ', u.FName,u.LName) AS FullName, d.UserID, u.Pic
  FROM Dconnection d
  INNER JOIN user u
  ON u.UserID = d.UserID
  WHERE d.CommuID IN (:CommuID)
  AND UserType = :UserType AND d.UserID != :UserID");
  $result->bindParam(":CommuID", $commuid,PDO::PARAM_STR);
  $result->bindParam(":UserType", $type,PDO::PARAM_INT);
  $result->bindParam(":UserID", $userid,PDO::PARAM_INT);
  $result->execute();
  $temp = array();
  while($row = $result->fetch()){
    $pic = ($row['Pic'])?$row['Pic']:'dxhealth.esy.es/default.png';
    $temp[] = array("UserID" => $row['UserID'], "FullName" => $row['FullName'], "Pic" => $pic);
  }
  return $temp;
}

function getfromcommuid($db, $commuid, $userid, $require){
  if(in_array('follower',$require)){
    $data['Followers'] = getdata($commuid,0, $db, $userid);
  }
  if(in_array('connection',$require)){
    $data['Connection'] = getdata($commuid,1, $db, $userid);
  }
  if(in_array('admin',$require)){
    $data['Admins'] = getdata($commuid,2, $db, $userid);
  }
  if(in_array('creator',$require)){
    $data['Creator'] = getdata($commuid,3, $db, $userid);
  }
  return $data;
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
  if(!$obj['require']){
    $obj['require'] = array('follower', 'connection', 'admin', 'creator');
  }
   if(!$obj['is']){
    $obj['is'] = "2,3";
  }
  if($obj['CommuID']){
    $data = getfromcommuid($db, (string)$obj['CommuID'], $obj['UserID'], $obj['require']);
  }else{
    $string = '';
    $query = $db->prepare("SELECT distinct(CommuID) FROM Dconnection WHERE UserID = :UserID AND UserType IN (".$obj['is'].")");
    $query->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
    $query->execute();
    while($que = $query->fetch()){
      $string.= $que['CommuID'].',';
    }
    if($string != ''){
      $data = getfromcommuid($db, $string, $obj['UserID'], $obj['require']);
    }
  }
  $response['ConnectionData'] = $data;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Connections Data";
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
