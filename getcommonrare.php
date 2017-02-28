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


$common = array();
$rare = array();
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
			foreach ($obj['conditions'] as $condition)
			{
				$result = $db->prepare(" SELECT Prevalence from conditions where ConditionID=:ConditionID ");
				$result->bindParam(':ConditionID', $condition['ConditionID'], PDO::PARAM_STR);
				$result->execute();
				$row = $result->fetch();
				if ($row['Prevalence'] == "common" || $row['Prevalence'] == "moderate")
				{
					$common[] = array('ConditionID'=> (string)$condition['ConditionID'], 'ConditionName' => (string)$condition['ConditionName'], 'CondProb' => (string)$condition['CondProb'], 'match' => (string)$condition['match'] );
				}
				if ($row['Prevalence'] == "rare" || $row['Prevalence'] == "very_rare")
				{
					$rare[] = array('ConditionID'=> (string)$condition['ConditionID'], 'ConditionName' => (string)$condition['ConditionName'], 'CondProb' => (string)$condition['CondProb'], 'match' => (string)$condition['match'] );
				}
			}
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Condition Data";
			$response['Common'] = $common;
			$response['Rare'] = $rare;

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
