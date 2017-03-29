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

			$result = $db->prepare("SELECT DID, PID, PFID, Status from appointment3 where AID=:AID");
			$result->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();

			$result3 = $db->prepare("SELECT s.SymptomID, pfs.Symptom, pfs.SymptomChoice from patientfinalsymptom pfs inner join symptoms s on s.SymptomName = pfs.Symptom where PFID=:PFID");  
			$result3->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result3->execute();
			$symptoms = "";
			while ($row3 = $result3->fetch()) 
			{
				$symptoms = $symptoms.$row3['Symptom'].", ";
				$sym[] = array('SymptomID' => (string)$row3['SymptomID'], 'Symptom' => (string)$row3['Symptom'], 'SymptomChoice' => (string)$row3['SymptomChoice']);
			}
			$symptoms = substr($symptoms, 0, -2);

			$result4 = $db->prepare("SELECT c.ConditionID, pc.ConditionName, pc.CondProb from patientcondition pc inner join conditions c on c.ConditionName=pc.ConditionName where pc.PFID=:PFID order by pc.CondProb desc");
			$result4->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result4->execute();
			while ($row4 = $result4->fetch()) 
			{
				$condition[] = array('ConditionID' => (string)$row4['ConditionID'], 'ConditionName' => (string)$row4['ConditionName'], 'CondProb' => (string)$row4['CondProb']);
			}

			$result5 = $db->prepare("SELECT pq.PQID, pq.PFID, pq.PQuery, pq.PQImg, dq.DQID, dq.DQuery from patientquery pq left outer join doctorquery dq on pq.PQID=dq.PQID where pq.PFID=:PFID order by pq.PQDateTime, dq.DQDateTime");
			$result5->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result5->execute();
                        $i = 0;
                        $key_array = array();
                        while ($row5 = $result5->fetch()) 
			{
				if(!in_array($row5['PQID'], $key_array))
		            {
		            	$key_array[$i] = $row5['PQID'];
		            	$i++;
						$query[] = array('PQID' => (string)$row5['PQID'], 'PFID' => (string)$row5['PFID'], 'PQuery' => (string)$row5['PQuery'], 'PQImg' => (string)$row5['PQImg'], 'DQID' => (string)$row5['DQID'], 'DQuery' => (string)$row5['DQuery']);
					}
			}

			$result6 = $db->prepare("SELECT pq.PQID, pq.PFID, pq.PQuery, pq.PQImg, dq.DQID, dq.DQuery from patientquery pq right outer join doctorquery dq on pq.PQID=dq.PQID where dq.PFID=:PFID order by pq.PQDateTime, dq.DQDateTime");
			$result6->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result6->execute();
			while ($row6 = $result6->fetch()) 
			{
				if(!in_array($row6['PQID'], $key_array))
		            {
		            	$key_array[$i] = $row6['PQID'];
		            	$i++;
						$query[] = array('PQID' => (string)$row6['PQID'], 'PFID' => (string)$row6['PFID'], 'PQuery' => (string)$row6['PQuery'], 'PQImg' => (string)$row6['PQImg'], 'DQID' => (string)$row6['DQID'], 'DQuery' => (string)$row6['DQuery']);
					}
			}

			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Data";
			$response['PFID'] = $row['PFID'];
			$response['condition'] = $condition;
			$response['finalsymptoms'] = $sym;
			$response['Status'] = $row['Status'];
			$response['query'] = $query;
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