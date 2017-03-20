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

function addcron($statement, $time, $userid, $db){
	$cquery = $db->prepare("INSERT INTO CronJob (Statement,ExeT,UserID) VALUES(:Statement, :ExeT, :UserID)");
	$cquery->bindParam(":Statement", $statement, PDO::PARAM_STR);
	$cquery->bindParam(":ExeT", $time, PDO::PARAM_INT);
	$cquery->bindParam(":UserID", $userid, PDO::PARAM_INT);
	$cquery->execute();
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
			$query9 = $db->prepare("SELECT DID, PID from appointment3 where AID = :AID");
			$query9->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
			$query9->execute();
			$row = $query9->fetch();
			$appstatement = "UPDATE appointment3 SET Status='Complete', ConditionID = :ConditionID, PaymentStage = 'Doctor'";
			if(isset($obj['Payment'])){
				$appstatement.= ", PaymentStage = 'doctor', Payment = :Payment";
			}
			$appstatement.=" WHERE AID = :AID";
			if($obj['CWP']){
				$query11 = $db->prepare($appstatement);
				$query11->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query11->bindParam(':ConditionID', $obj['Condition'], PDO::PARAM_STR);
				if(isset($obj['Payment'])){
					$query11->bindParam(':Payment', $obj['Payment'], PDO::PARAM_INT);
				}
				$query11->execute();
			}else{
				$query = $db->prepare("SELECT * from doctortempmedicine where AID = :AID");
				$query->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query->execute();
				while ($prescription = $query->fetch()){
					$result = $db->prepare("INSERT into doctormedicine (AID, PFID, MID, Dosage, Type, Morning, Afternoon, Night, IsAfter, OnNeed, Days)
					 values (:AID, :PFID, :MID, :Dosage, :Type, :Morning, :Afternoon, :Night, :IsAfter, :OnNeed, :Days)");
					$result->bindParam(':AID', $prescription['AID'], PDO::PARAM_STR);
					$result->bindParam(':PFID', $prescription['PFID'], PDO::PARAM_STR);
					$result->bindParam(':MID', $prescription['MID'], PDO::PARAM_STR);
					$result->bindParam(':Dosage', $prescription['Dosage'], PDO::PARAM_STR);
					$result->bindParam(':Type', $prescription['Type'], PDO::PARAM_STR);
					$result->bindParam(':Morning', $prescription['Morning'], PDO::PARAM_STR);
					$result->bindParam(':Afternoon', $prescription['Afternoon'], PDO::PARAM_STR);
					$result->bindParam(':Night', $prescription['Night'], PDO::PARAM_STR);
					$result->bindParam(':IsAfter', $prescription['IsAfter'], PDO::PARAM_STR);
					$result->bindParam(':OnNeed', $prescription['OnNeed'], PDO::PARAM_STR);
					$result->bindParam(':Days', $prescription['Days'], PDO::PARAM_STR);
					$result->execute();
					$mquery = $db->prepare("SELECT Medicine FROM medicine WHERE MID = :MID");
					$mquery->bindParam(":MID", $prescription['MID'], PDO::PARAM_INT);
					$mquery->execute();
					$mrow = $mquery->fetch();
					$rootstatement = "Time to take ".$prescription['Dosage']." ".$prescription['Type']." of ".$mrow['Medicine'].".Make sure to take it ";
					$rootstatement.=($prescription['IsAfter'] == 1)?"after ":"before ";
					$now = strtotime("now");
					if($prescription['Morning']){
						$statement = $rootstatement."breakfast";
						$time = ($prescription['IsAfter'] == 1)?strtotime("today 9:00 am"):strtotime("today 10:00 am");
						if($now >= $time){
							$time = strtotime("+1 Day", $time);
						}
						for($i = 0;$i < $prescription['Days'];$i++){
							addcron($statement, $time, $row['PID'], $db);
							$time = strtotime("+1 Day", $time);
						}
					}
					if($prescription['Afternoon']){
						$statement = $rootstatement."lunch";
						$time = ($prescription['IsAfter'] == 1)?strtotime("tomorrow 12:00 pm"):strtotime("tomorrow 1:00 pm");
						if($now >= $time){
							$time = strtotime("+1 Day", $time);
						}
						for($i = 0;$i < $prescription['Days'];$i++){
							addcron($statement, $time, $row['PID'], $db);
							$time = strtotime("+1 Day", $time);
						}
					}
					if($prescription['Night']){
						$statement = $rootstatement."dinner";
						$time = ($prescription['IsAfter'] == 1)?strtotime("tomorrow 8:00 pm"):strtotime("tomorrow 9:00 pm");
						if($now >= $time){
							$time = strtotime("+1 Day", $time);
						}
						for($i = 0;$i < $prescription['Days'];$i++){
							addcron($statement, $time, $row['PID'], $db);
							$time = strtotime("+1 Day", $time);
						}
					}
				}
				$query2 = $db->prepare("DELETE from doctortempmedicine where AID = :AID");
				$query2->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query2->execute();

				$query3 = $db->prepare("SELECT * from doctortemptest where AID = :AID");
				$query3->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query3->execute();
				$hour = (int)date("H");
				while($test = $query3->fetch()){
					$result2 = $db->prepare("INSERT into doctortest (AID, PFID, TID, TestDate)
					 values (:AID, :PFID, :TID, Now())");
					$result2->bindParam(':AID', $test['AID'], PDO::PARAM_STR);
					$result2->bindParam(':PFID', $test['PFID'], PDO::PARAM_STR);
					$result2->bindParam(':TID', $test['TID'], PDO::PARAM_STR);
					$result2->execute();
					$tquery = $db->prepare("SELECT Test FROM test WHERE TID = :TID");
					$tquery->bindParam(":TID", $test['TID'], PDO::PARAM_INT);
					$tquery->execute();
					$trow = $tquery->fetch();
					$statement = "Did you take your ".$trow['Test']." test. If not, then doctor recommends you to take your prescribed test as soon as possible";
					$statement1 = "Did you take your ".$trow['Test']." test. Share your test reports with doctor";
					if($hour < 17 && $hour >= 5){
						$time = strtotime("today 5 pm");
						addcron($statement, $time, $row['PID'], $db);
						$time = strtotime("tomorrow 8 am");
						addcron($statement1, $time, $row['PID'], $db);
					}else if($hour < 5){
						$time = strtotime("today 8 am");
						addcron($statement, $time, $row['PID'], $db);
						$time = strtotime("today 5 pm");
						addcron($statement1, $time, $row['PID'], $db);
					}else{
						$time = strtotime("tomorrow 8 am");
						addcron($statement, $time, $row['PID'], $db);
						$time = strtotime("tomorrow 5 pm");
						addcron($statement1, $time, $row['PID'], $db);
					}
				}
				$query4 = $db->prepare("DELETE from doctortemptest where AID = :AID");
				$query4->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query4->execute();

				$query5 = $db->prepare("SELECT * from doctortempcomment where AID = :AID");
				$query5->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query5->execute();
				while($comment = $query5->fetch()){
					$result3 = $db->prepare("INSERT into doctorcomment (AID, PFID, Comment)
					 values (:AID, :PFID, :Comment)");
					$result3->bindParam(':AID', $comment['AID'], PDO::PARAM_STR);
					$result3->bindParam(':PFID', $comment['PFID'], PDO::PARAM_STR);
					$result3->bindParam(':Comment', $comment['Comment'], PDO::PARAM_STR);
					$result3->execute();
				}
				$query6 = $db->prepare("DELETE from doctortempcomment where AID = :AID");
				$query6->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query6->execute();

				$query7 = $db->prepare("SELECT * from doctortempnotes where AID = :AID");
				$query7->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query7->execute();
				while($notes = $query7->fetch()){
					$result4 = $db->prepare("INSERT into doctornotes (AID, Notes)
					 values (:AID, :Notes)");
					$result4->bindParam(':AID', $notes['AID'], PDO::PARAM_STR);
					$result4->bindParam(':Notes', $notes['Notes'], PDO::PARAM_STR);
					$result4->execute();
				}
				$query8 = $db->prepare("DELETE from doctortempnotes where AID = :AID");
				$query8->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query8->execute();
				// API access key from Google API's Console
				define( 'API_ACCESS_KEY', 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM' );

				$query11 = $db->prepare($appstatement);
				$query11->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
				$query11->bindParam(':ConditionID', $obj['Condition'], PDO::PARAM_STR);
				if(isset($obj['Payment'])){
					$query11->bindParam(':Payment', $obj['Payment'], PDO::PARAM_INT);
				}
				$query11->execute();

				$query12 = $db->prepare("SELECT FName, LName from user where UserID = :UserID");
	      $query12->bindParam(':UserID', $row['DID'], PDO::PARAM_STR);
	      $query12->execute();
	      $row33 = $query12->fetch();

				$result44 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (19,:ID,:UserID)");
	      $result44->bindParam(":UserID", $row['PID'],PDO::PARAM_INT);
	      $result44->bindParam(":ID", $obj['AID'], PDO::PARAM_INT);
	      $result44->execute();
				$nid = $db->lastInsertId();

				$query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
				$query10->bindParam(':UserID', $row['PID'], PDO::PARAM_STR);
				$query10->execute();
				$row2 = $query10->fetch();

				$registrationIds[] = $row2['RegistrationID'];

				$message = "You have received a prescription from Dr.".$row33['FName']." ".$row33['LName'];

				$url = 'https://fcm.googleapis.com/fcm/send';
				//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
				$server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';

				define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
				 define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

				 $fields = array(

				 "registration_ids" => $registrationIds ,
				 "priority" => "high",
				 "notification" => array( "title" => "Prescription received", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
				 "data" => array("message" =>$message, "title" => "Prescription received"),
				 );

				 $headers = array(
				 GOOGLE_GCM_URL,
				 'Content-Type: application/json',
				 'Authorization: key=' . GOOGLE_API_KEY
				 );

				 $ch = curl_init();
				 curl_setopt($ch, CURLOPT_URL, GOOGLE_GCM_URL);
				 curl_setopt($ch, CURLOPT_POST, true);
				 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

				 $result5 = curl_exec($ch);
				 if ($result5 === FALSE) {
				 die('Problem occurred: ' . curl_error($ch));
				 }

				 curl_close($ch);
				 $response['CurlResponse'] = $result5;
			}
			// if($obj['Condition'] != 'rev'){
			// 	$cquery1 = $db->prepare("SELECT Tips,Day FROM CaseArticles WHERE ConditionID = :ConditionID");
			// 	$cquery1->bindParam("ConditionID", $obj['Condition'], PDO::PARAM_STR);
			// 	$cquery1->execute();
			// 	$now = strtotime("now");
			// 	while($crow1 = $cquery1->fetch()){
			// 		if($crow1['Tips']){
			// 			$tips = json_decode($crow1['Tips']);
			// 			$cquery = $db->prepare("SELECT Tip FROM Tip WHERE TipID = :TipID");
			// 			$cquery->bindParam("TipID", $tips[0], PDO::PARAM_INT);
			// 			$cquery->execute();
			// 			$crow = $cquery->fetch();
			// 			if($crow){
			// 				$t12pm = strtotime("today 12 pm");
			// 				if($crow1['Day'] == 0){
			// 					$time = ($now > $t12pm)?$now:$t12pm;
			// 				}else{
			// 					$time = strtotime('+'.$crow1['Day']." Day", $t12pm);
			// 				}
			// 				addcron($crow['Tip'], $time, $row['PID'], $db);
			// 			}
			// 			if($tips[1]){
			// 				$cquery2 = $db->prepare("SELECT Tip FROM Tip WHERE TipID = :TipID");
			// 				$cquery2->bindParam("TipID", $tips[1], PDO::PARAM_INT);
			// 				$cquery2->execute();
			// 				$crow2 = $cquery2->fetch();
			// 				if($crow2){
			// 					$t1pm = strtotime("today 1 pm");
			// 					if($crow1['Day'] == 0){
			// 						$time = ($now > $t1pm)?$now:$t1pm;
			// 					}else{
			// 						$time = strtotime('+'.$crow1['Day']." Day", $t1pm);
			// 					}
			// 					addcron($crow2['Tip'], $time, $row['PID'], $db);
			// 				}
			// 			}
			// 		}
			// 	}
			// }
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Data Saved";

			$status['Status'] = $response;
			header('Content-type: application/json');
			echo json_encode($status);
		}
	catch(PDOException $ex)
		{
			$response['ResponseCode'] = "500";
		    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
		    $status['Status'] = $response;
		    header('Content-type: application/json');
			echo json_encode($response);
		}
