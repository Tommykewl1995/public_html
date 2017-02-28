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
    $result = $db->prepare("SELECT CommuID FROM Dconnection Where UserType = 1 and UserID = :UserID");
    $result->bindParam(":UserID", $obj['UserID'],PDO::PARAM_INT);
    $result->execute();
    while($row = $result->fetch()){
        $result1 = $db->prepare("SELECT dc.UserID, u.Pic, CONCAT_WS(' ', 'Dr.', u.FName, u.LName) AS FullName FROM Dconnection dc INNER JOIN user u ON u.UserID = dc.UserID Where dc.UserType = 3 and dc.CommuID = :CommuID");
        $result1->bindParam(":CommuID", $row['CommuID'] ,PDO::PARAM_INT);
        $result1->execute();
        $rohan=1;
        $row1 = $result1->fetch();
        $cgroups[] = array("UserID" => $row1['UserID'], "Pic" => $row1['Pic'], "FullName" => $row1['FullName']);
    }


    $response['ResponseCode'] = "200";
    $response['ResponseMessage'] = $rohan;
    $response['Value']=$cgroups;
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
