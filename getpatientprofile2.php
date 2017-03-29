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
			if($obj['api_key'] != "5+`C%@>9RvJ'y?8:"){
				$response['ResponseCode'] = "400";
		    $response['ResponseMessage'] = "Invalid api_key"; //user friendly message
		    $status['Status'] = $response;
		    header('Content-type: application/json');
		    echo json_encode($status);
				die();
			}
			$result = $db->prepare("SELECT pp.PID, u.DOB, u.Pic, pp.Height, pp.Weight, pp.BloodGroup, u.Gender, pp.Address1, pp.Address2, pp.City, pp.PinCode, pp.Allergies, pp.Hereditory, u.FName, u.LName, u.Phone, u.Email
			  from user u inner join patientprofile pp where pp.PID=u.UserID and u.UserID= :UserID");
			$result->bindParam(':UserID', $obj['UserID'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();
			//$datetime1 = new DateTime($row['DOB']);
			//$dob = date_format($datetime1, "d/m/Y");
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient-Data";
			if(is_null($row['PID']))
				$response['PID'] = "";
			else
				$response['PID'] = (string)$row['PID'] ;

			if(is_null($row['Pic']))
				$response['Pic'] = "";
			else
				$response['Pic'] = (string)$row['Pic'] ;

			if(!is_null($row['DOB']))
				$response['DOB'] = $row['DOB'];
			else
				$response['DOB'] = "";

			if(!is_null($row['Height']))
				$response['Height'] = (string)$row['Height'];
			else
				$response['Height'] = "";

			if(!is_null($row['Weight']))
				$response['Weight'] = (string)$row['Weight'];
			else
				$response['Weight'] = "";

			if(!is_null($row['BloodGroup']))
				$response['BloodGroup'] = (string)$row['BloodGroup'];
			else
				$response['BloodGroup'] = "";

			if(!is_null($row['Gender']))
				$response['Gender'] = (string)$row['Gender'];
			else
				$response['Gender'] = "";

			if(!is_null($row['Address1']))
				$response['Address1'] = (string)$row['Address1'];
			else
				$response['Address1'] = "";

			if(!is_null($row['Address2']))
				$response['Address2'] = (string)$row['Address2'];
			else
				$response['Address2'] = "";

			if(!is_null($row['City']))
				$response['City'] = (string)$row['City'];
			else
				$response['City'] = "";

			if(!is_null($row['PinCode']))
				$response['PinCode'] = (string)$row['PinCode'];
			else
				$response['PinCode'] = "";

			if(!is_null($row['Allergies']))
				$response['Allergies'] = (string)$row['Allergies'];
			else
				$response['Allergies'] = "";

			if(!is_null($row['Hereditory']))
				$response['Hereditory'] = (string)$row['Hereditory'];
			else
				$response['Hereditory'] = "";

			if(!is_null($row['FName']))
				$response['FName'] = (string)$row['FName'];
			else
				$response['FName'] = "";

			if(!is_null($row['LName']))
				$response['LName'] = (string)$row['LName'];
			else
				$response['LName'] = "";

			if(!is_null($row['Phone']))
				$response['Phone'] = (string)$row['Phone'];
			else
				$response['Phone'] = "";

			if(!is_null($row['Email']))
				$response['Email'] = (string)$row['Email'];
			else
				$response['Email'] = "";
			// if(isset($obj['hash'])){
			// 	//"fLsQY1fm", "5JgED3elLS" => rohan
			// 	// "BDPg5XsA" "2kTBfOVz3k" => tamo test
			// 	// "epUh66ed" "iUEdomgRzR" => tamo prod
			// 	$response['Key'] = "fLsQY1fm";
			// 	$str = $response['Key']."|".$obj['hash']."||||||5JgED3elLS";
			// 	$str = str_replace("FName",$response['FName'],$str);
			// 	$email = (is_null($row['Email']))?'rohan@rxhealth.co':$row['Email'];
			// 	$str = str_replace("Email",$response['Email'],$email);
			// 	$hash = strtolower(hash('sha512', $str));
			// 	$response['Hash'] = $hash;
			// }
			if(isset($obj['order'])){
				$order = $obj['order'];
				$result3 = $db->prepare("SELECT razor_order_id FROM payu WHERE txnid = :txnid");
				$result3->bindParam(":txnid", $order['receipt'], PDO::PARAM_STR);
				$result3->execute();
				if($row3 = $result3->fetch()){
					$response['id'] = $row3['razor_order_id'];
				}else{
					$order['currency'] = "INR";
					$order['payment_capture'] = 1;
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => "https://api.razorpay.com/v1/orders",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => json_encode($order),
						CURLOPT_HTTPHEADER => array(
							"authorization: Basic cnpwX3Rlc3RfczdoUzZFT010bjM2UjQ6bDNOS1Z3MDZUcTZ1ZDljZmV0UWNidmpr",
							"cache-control: no-cache",
							"content-type: application/json"
						),
					));
					$res = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);
					if ($err) {
						$response['ResponseCode'] = "404";
						$response['ResponseMessage'] = "Curl Error";
						$response['Curl Error'] = json_decode($err, true);
					}else{
						$res = json_decode($res, true);
						if($res['receipt'] == $order['receipt']){
							$result2 = $db->prepare("INSERT INTO payu (txnid,razor_order_id) VALUES (:txnid,:razor_order_id)");
							$result2->bindParam(":txnid", $res['receipt'], PDO::PARAM_STR);
							$result2->bindParam(":razor_order_id", $res['id'], PDO::PARAM_STR);
							$result2->execute();
							$response['id'] = $res['id'];
						}else{
							$response['ResponseCode'] = "405";
							$response['ResponseMessage'] = "Curl request Tampered";
						}
					}
				}
			}
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
