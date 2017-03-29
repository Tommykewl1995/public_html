<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');
require_once 'Bcrypt.php';
require('helperfunctions1.php');

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
  $time = (int)$obj['Time'];
  $date = date("Y-m-d H:i:s", $time);
  $statement = "UPDATE appointment3 SET DID = :DID, AppointmentDate = :AppointmentDate, Status = 'Confirm'";
  if(isset($obj['Payment'])){
    $statement.=", PaymentStage = 'assistant', Payment = :Payment";
  }
  $statement.=" WHERE AID = :AID";
  $result = $db->prepare($statement);
  $result->bindParam(':AID', $obj['AID'], PDO::PARAM_INT);
  $result->bindParam(':AppointmentDate', $date, PDO::PARAM_INT);
  $result->bindParam(':DID', $obj['DID'], PDO::PARAM_INT);
  if(isset($obj['Payment'])){
    $result->bindParam(":Payment", $obj['Payment'], PDO::PARAM_INT);
  }
  $result->execute();
  $result2 = $db->prepare("SELECT u.FName,u.LName,c.ClinicName,a.PID FROM appointment3 a INNER JOIN user u ON u.UserID = a.DID INNER JOIN clinics c ON c.ClinicID = a.ClinicID WHERE a.AID = :AID");
  $result2->bindParam(':AID', $obj['AID'], PDO::PARAM_INT);
  $result2->execute();
  $row2 = $result2->fetch();
  $query11 = $db->prepare("SELECT Phone from user where UserID = :UserID");
  $query11->bindParam(':UserID', $row2['PID'], PDO::PARAM_STR);
  $query11->execute();
  $row33 = $query11->fetch();
  $datestring = date("jS M Y, g:i A", $time+19800);
  $statement = "Your Appointment is confirmed at ".$row2['ClinicName']." to Dr. ".$row2['FName']." ".$row2['LName']." on ".$datestring;
  $res = sendotp($row33['Phone'], $statement, "confirm");
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Appointment Confirmed";
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