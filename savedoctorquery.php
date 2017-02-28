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


	try 
		{
			$pqid = $obj['PQID'];	
			if($obj['PQID']==null || $obj['PQID']=='0')
			{
				$result4 = $db->prepare("INSERT INTO patientquery (PFID, PQDateTime) VALUES (:PFID, Now())");
				$result4->bindParam(':PFID', $obj['PFID'], PDO::PARAM_STR);
				//$result5->bindParam(':PQImg', $obj['PQImg'], PDO::PARAM_STR);
				$result4->execute();
				$pqid = $db->lastInsertId();
			}
			
				$result5 = $db->prepare("INSERT INTO doctorquery (PFID, PQID, DQuery, DQDateTime) VALUES (:PFID, :PQID, :DQuery, Now())");
				$result5->bindParam(':PFID', $obj['PFID'], PDO::PARAM_STR);
				$result5->bindParam(':PQID', $pqid, PDO::PARAM_STR);
				$result5->bindParam(':DQuery', $obj['DQuery'], PDO::PARAM_STR);
				//$result5->bindParam(':PQImg', $obj['PQImg'], PDO::PARAM_STR);
				$result5->execute();

				// API access key from Google API's Console
			define( 'API_ACCESS_KEY', 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM' );

			$query9 = $db->prepare("SELECT AID, DID, PID from appointment3 where PFID = :PFID");
			$query9->bindParam(':PFID', $obj['PFID'], PDO::PARAM_STR);
			$query9->execute();
			$row = $query9->fetch();

			$query12 = $db->prepare("SELECT FName, LName from user where UserID = :UserID");
		      $query12->bindParam(':UserID', $row['DID'], PDO::PARAM_STR);
		      $query12->execute();
		      $row33 = $query12->fetch();	

		      $result44 = $db->prepare("INSERT INTO Notifications (Type,ID,UserID) VALUES (22,:ID,:UserID)");
		      $result44->bindParam(":UserID", $row['PID'],PDO::PARAM_INT);
		      $result44->bindParam(":ID", $row['AID'], PDO::PARAM_INT);
		      $result44->execute();
			$nid = $db->lastInsertId();		

			$query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
			$query10->bindParam(':UserID', $row['PID'], PDO::PARAM_STR);
			$query10->execute();
			$row2 = $query10->fetch();

			$registrationIds[] = $row2['RegistrationID'];

			$message = "Dr.".$row33['FName']." ".$row33['LName']." has replied on your symptoms.";

			$url = 'https://fcm.googleapis.com/fcm/send';
			//api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
			$server_key = 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM';
						
			define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
			 define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");
			 
			 $fields = array(
			 
			 "registration_ids" => $registrationIds ,
			 "priority" => "high",
			 "notification" => array( "title" => "Doctor's Message received", "body" => $message, "sound" =>"default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
			 "data" => array("message" =>$message, "title" => "Doctor's Message received"),
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
			
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Query Submitted";
			
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
			echo json_encode($status);
		}
