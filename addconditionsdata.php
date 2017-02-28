<?php  
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
include('db_config.php');


$json = file_get_contents('php://input');
$obj = json_decode($json, true);

	try 
		{
			foreach ($obj['conditions'] as $row) 
			{
				$result3 = $db->prepare("INSERT INTO conditiondata (ConditionName, URL, Data)
				 VALUES (:ConditionName, :URL, :Data)");
				//$result3->bindParam(':ConditionID', $row['id'], PDO::PARAM_STR);
				$result3->bindParam(':ConditionName', $row['name'], PDO::PARAM_STR);
				$result3->bindParam(':URL', $row['url'], PDO::PARAM_STR);
				$result3->bindParam(':Data', $row['selection3'], PDO::PARAM_STR);
				$result3->execute();

				$response['ResponseCode'] = "200";
				$response['ResponseMessage'] = "Added Successfully";
				

				$status['Status'] = $response;
				header('Content-type: application/json');
				echo json_encode($status);			
			}
		}
	catch(PDOException $ex) 
		{
			$response['ResponseCode'] = "500";
		    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
		    $status['Status'] = $response;
		    header('Content-type: application/json');
			echo json_encode($response);
		}
// }