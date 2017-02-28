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
  $articles = array();
  $query = $db->prepare("SELECT * FROM Articles WHERE AGT IS NULL AND ArID NOT IN (SELECT ArID FROM ShareArticle GROUP BY ArID)");
  $query->execute();
  while($que = $query->fetch()){
    $articles[] = array("ArID" => $que['ArID'],
    "Header" => $que['Header'],
    "Summary" => $que['Summary'],
    "Link" => $que['Link'],
    "Details" => $que['Details'],
    "ImageLink" => $que['ImageLink']);
  }
  $response['Case Articles'] = $articles;
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Case Articles Served";
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
