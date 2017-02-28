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
			

$result = $db->prepare("DELETE FROM conditions");
$result->execute();
$result2 = $db->prepare("DELETE FROM conditionscategory");
$result2->execute();
				foreach ($obj['conditions'] as $row)
				{
					$result3 = $db->prepare("INSERT INTO conditions (ConditionID, ConditionName, Prevalence, Acuteness, Severity, SexFilter, Hint)
					 VALUES (:ConditionID, :ConditionName, :Prevalence, :Acuteness, :Severity, :SexFilter, :Hint)");
					$result3->bindParam(':ConditionID', $row['id'], PDO::PARAM_STR);
					$result3->bindParam(':ConditionName', $row['name'], PDO::PARAM_STR);
					$result3->bindParam(':Prevalence', $row['prevalence'], PDO::PARAM_STR);
					$result3->bindParam(':Acuteness', $row['acuteness'], PDO::PARAM_STR);
					$result3->bindParam(':Severity', $row['severity'], PDO::PARAM_STR);
					$result3->bindParam(':SexFilter', $row['sex_filter'], PDO::PARAM_STR);
					$result3->bindParam(':Hint', $row['extras']['hint'], PDO::PARAM_STR);
					$result3->execute();


					foreach ($row['categories'] as $row2)
					{
						$result5 = $db->prepare("INSERT INTO conditionscategory (ConditionID, ConditionName, ConditionCategory) VALUES
							(:ConditionID, :ConditionName, :ConditionCategory)");
						$result5->bindParam(':ConditionID', $row['id'], PDO::PARAM_STR);
						$result5->bindParam(':ConditionName', $row['name'], PDO::PARAM_STR);
						$result5->bindParam(':ConditionCategory', $row2, PDO::PARAM_STR);
						$result5->execute();
					}


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
