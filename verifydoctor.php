<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require_once 'Bcrypt.php';
include('helperfunctions1.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);


try{
$password = generatePIN(8);
list($otp_code,$message) = sendotp( $obj['Phone'],$password,"selfCreated");
$password1 = Bcrypt::hashPassword($password);
$response['password'] = $password1;
$result = $db->prepare("UPDATE user SET Password = :Password WHERE UserID = :UserID");
$result->bindParam(":Password", $password1, PDO::PARAM_STR);
$result->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
$result->execute();
$result2 = $db->prepare("UPDATE doctorprofile SET IsVerified = 1 WHERE DID = :UserID");
$result2->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
$result2->execute();
$response['kk'] = $obj['UserID'];

  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Doctor verified successfully";
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
