<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
require('db_config.php');
require('helperfunctions.php');
/*
Codes:
DoctorAccept : 0
DoctorReject : 1
DoctorReschedule : 2
PatientRescheduleAccept : 3
PatientRescheduleReject : 4
PatientBookingCancelled : 5
DoctorBookingCancelled : 6
PatientBookingReminder : 7
PatientBookingDone : 8
Others : >8
*/
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
  $result = $db->prepare("SELECT * FROM Notifications WHERE NID = :NID");
  $result->bindParam(":NID", $obj['NID'],PDO::PARAM_INT);
  $result->execute();
  $temp = array();
  $row = $result->fetch();
  if(!$row['IsViewed']){
    $query = $db->prepare("UPDATE Notifications SET IsViewed = 1 WHERE NID = :NID");
    $query->bindParam(":NID", $obj['NID'],PDO::PARAM_INT);
    $query->execute();
  }
  switch ($row['Type']) {
    case 0:
    case 7:
      $result2 = $db->prepare("SELECT b.AID,b.BookingDate,b.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,c.ClinicName,CONCAT_WS(' ',c.Address1,c.Address2,c.City,'-',c.PinCode,'<br>Email:',c.ClinicEmail,'<br>Phone:',c.ClinicPhone)
      AS ClinicFullAddress
      FROM Booking b
      INNER JOIN user u
      ON u.UserID = b.DID
      INNER JOIN clinics c
      ON b.ClinicID = c.ClinicID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['BookingDate'],
      "SlotID" => $tim,
      "DoctorName" => $row2['DFullName'],
      "ClinicName" => $row2['ClinicName'],
      "ClinicAddress" => $row2['ClinicFullAddress']);
      break;

    case 1:
      $result2 = $db->prepare("SELECT fa.DID, fa.AID,fa.ShareDate,fa.AppointDate,fa.SlotID, CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName, c.ClinicName,
      CONCAT_WS(' ',c.Address1,c.Address2,c.City,'-',c.PinCode,'<br>Email:',c.ClinicEmail,'<br>Phone:',c.ClinicPhone)
      AS ClinicFullAddress
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.DID
      INNER JOIN clinics c
      ON fa.ClinicID = c.ClinicID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $result3 = $db->prepare("SELECT SpecID FROM doctorspec WHERE DID = :DID");
      $result3->bindParam(":DID", $row2['DID'], PDO::PARAM_INT);
      $result3->execute();
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['AppointDate'],
      "ShareDate" => $row2['ShareDate'],
      "SlotID" => $tim,
      "DoctorName" => $row2['DFullName'],
      "ClinicName" => $row2['ClinicName'],
      "ClinicAddress" => $row2['ClinicFullAddress']);
      $others = array();
      while($row3 = $result3->fetch()){
        $result4 = $db->prepare("SELECT distinct(DID) FROM doctorspec WHERE SpecID = :SpecID and DID != :DID");
        $result4->bindParam(":SpecID", $row3['SpecID'], PDO::PARAM_INT);
        $result4->bindParam(":DID", $row2['DID'], PDO::PARAM_INT);
        $result4->execute();
        while($row4 = $result4->fetch()){
          $result5 = $db->prepare("SELECT Filled FROM Slots WHERE DID = :DID and SlotID = :SlotID");
          $result5->bindParam(":DID", $row4['DID'], PDO::PARAM_INT);
          $result5->bindParam("SlotID", $row2['SlotID'], PDO::PARAM_INT);
          $result5->execute();
          $row5 = $result5->fetch();
          if((float)$row5['Filled'] < 3){
            $others[] = getdoctorlist($row4['DID'],$db);
          }
        }
      }
      $temp['action'] = $others;
      break;

    case 2:
      $result2 = $db->prepare("SELECT fa.DID,fa.AID,fa.ShareDate,fa.AppointDate,fa.SlotID, CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName, c.ClinicName,CONCAT_WS(' ',c.Address1,c.Address2,c.City,'-',c.PinCode,'<br>Email:',c.ClinicEmail,'<br>Phone:',c.ClinicPhone)
      AS ClinicFullAddress
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.DID
      INNER JOIN clinics c
      ON fa.ClinicID = c.ClinicID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $reschedule = array();
      $result3 = $db->prepare("SELECT RescheduleDate,SlotID FROM reschedule WHERE AID = :AID");
      $result3->bindParam(":AID", $row2['AID'],PDO::PARAM_INT);
      $result3->execute();
      while($row3 = $result3->fetch()){
        $reschedule[] = array("Date" => (string)$row3['RescheduleDate'], "SlotID" => $row3['SlotID']);
      }
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['AppointDate'],
      "ShareDate" => $row2['ShareDate'],
      "SlotID" => $tim,
      "DoctorName" => $row2['DFullName'],
      "ClinicName" => $row2['ClinicName'],
      "ClinicAddress" => $row2['ClinicFullAddress']);
      $temp['action'] = $reschedule;
      break;

    case 3:
    case 9:
      $result2 = $db->prepare("SELECT b.AID,b.BookingDate,b.PFID,b.SlotID,CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName
      FROM Booking b
      INNER JOIN user u
      ON u.UserID = b.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['BookingDate'],
      "SlotID" => $tim,
      "PFID" => $row2['PFID'],
      "PatientName" => $row2['PFullName']);
      break;

    case 4:
    case 10:
      $result2 = $db->prepare("SELECT fa.AID,fa.ShareDate,fa.AppointDate,fa.SlotID, fa.PFID, CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['AppointDate'],
      "ShareDate" => $row2['ShareDate'],
      "SlotID" => $tim,
      "PFID" => $row2['PFID'],
      "PatientName" => $row2['PFullName']);
      break;

    case 5:
      $result2 = $db->prepare("SELECT b.AID,b.BookingDate,b.SlotID,b.PFID,CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName
      FROM bookinghistory b
      INNER JOIN user u
      ON u.UserID = b.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['BookingDate'],
      "PFID" => $row2['PFID'],
      "SlotID" => $tim,
      "PatientName" => $row2['PFullName']);
      break;

    case 6:
    case 8:
      $result2 = $db->prepare("SELECT b.AID,b.BookingDate,b.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,c.ClinicName,CONCAT_WS(' ',c.Address1,c.Address2,c.City,'-',c.PinCode,'<br>Email:',c.ClinicEmail,'<br>Phone:',c.ClinicPhone)
      AS ClinicFullAddress
      FROM bookinghistory b
      INNER JOIN user u
      ON u.UserID = b.DID
      INNER JOIN clinics c
      ON b.ClinicID = c.ClinicID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $temp['Details'] = array("NGT" => (string)$row['NGT'],
      "AppointmentID" => $row2['AID'],
      "AppointmentDate" => $row2['BookingDate'],
      "SlotID" => $tim,
      "DoctorName" => $row2['DFullName'],
      "ClinicName" => $row2['ClinicName'],
      "ClinicAddress" => $row2['ClinicFullAddress']);
      break;

    default:
      $temp = array("Detail" => "Coming Soon...");
      break;
  }
  $response['ResponseCode'] = "200";
  $response['ResponseMessage'] = "Notifications Data";
  $response['Notifications'] = $temp;
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
