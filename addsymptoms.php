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
			$result = $db->prepare("DELETE FROM symptoms");
$result->execute();
$result2 = $db->prepare("DELETE FROM symptomschildren");
$result2->execute();
				foreach ($obj as $row)
				{
					$result3 = $db->prepare("INSERT INTO symptoms (SymptomID, SymptomName, SymptomCategory, SymptomSexFilter, SymptomImageUrl, SymptomImageSource, SymptomParentID, SymptomParentRelation)
					 VALUES (:SymptomID, :SymptomName, :SymptomCategory, :SymptomSexFilter, :SymptomImageUrl, :SymptomImageSource, :SymptomParentID, :SymptomParentRelation)");
					$result3->bindParam(':SymptomID', $row['id'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomName', $row['name'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomCategory', $row['category'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomSexFilter', $row['sex_filter'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomImageUrl', $row['image_url'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomImageSource', $row['image_source'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomParentID', $row['parent_id'], PDO::PARAM_STR);
					$result3->bindParam(':SymptomParentRelation', $row['parent_relation'], PDO::PARAM_STR);
					$result3->execute();


					foreach ($row['children'] as $row2)
					{
						$result5 = $db->prepare("INSERT INTO symptomschildren (SymptomID, SymptomName, SymptomChildID, SymptomParentRelation) VALUES
							(:SymptomID, :SymptomName, :SymptomChildID, :SymptomParentRelation)");
						$result5->bindParam(':SymptomID', $row['id'], PDO::PARAM_STR);
						$result5->bindParam(':SymptomName', $row['name'], PDO::PARAM_STR);
						$result5->bindParam(':SymptomChildID', $row2['id'], PDO::PARAM_STR);
						$result5->bindParam(':SymptomParentRelation', $row2['parent_relation'], PDO::PARAM_STR);
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
