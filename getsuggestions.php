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
$ids = array();
$i = 0;
$key_array = array();
$symptoms = array();
$current = array();
$return = array();
$sym = array();
$mainsym = array();
$result_array=array();
$suggest = array();
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
			foreach($obj['Symptoms'] as $symptoms)
			{
					if(!in_array($symptoms['id'], $key_array))
		            {
		            	$key_array[$i] = $symptoms['id'];
		            	$i++;
		            	if(!is_null($symptoms['id']) && $symptoms['id'] != "")
		            	{
		            		$result3 = $db->prepare("SELECT SymptomName from symptoms where SymptomID = :SymptomID");
							$result3->bindParam(':SymptomID', $symptoms['id'], PDO::PARAM_STR);
							$result3->execute();
							$row3 = $result3->fetch();
							// $result5 = $db->prepare("INSERT INTO patientfinalsymptom (PFID, Symptom, SymptomChoice) VALUES (:PFID, :Symptom, :SymptomChoice)");
							// $result5->bindParam(':PFID', $obj['PFID'], PDO::PARAM_STR);
							// $result5->bindParam(':Symptom', $row3['SymptomName'], PDO::PARAM_STR);
							// $result5->bindParam(':SymptomChoice', $symptoms['ChoiceID'], PDO::PARAM_STR);
							// $result5->execute();
							$current[] = array('Symptom' => $row3['SymptomName'], 'SymptomChoice' => $symptoms['choice_id']);
		            	}
					}
			}

			$result = $db->prepare("SELECT DID,PFID from appointment3 where AID = :AID");
			$result->bindParam(':AID', $obj['AID'], PDO::PARAM_STR);
			$result->execute();
			$row = $result->fetch();

			$result2 = $db->prepare("SELECT * from doctorfinalsymptom where PFID = ANY(SELECT a.PFID FROM doctormedicine dm inner join appointment3 a on dm.AID=a.AID where DID=:DID) and PFID!=:PFID");
			$result2->bindParam(':DID', $row['DID'], PDO::PARAM_STR);
			$result2->bindParam(':PFID', $row['PFID'], PDO::PARAM_STR);
			$result2->execute();
			while($row2 = $result2->fetch())
			{
				$return[] = array('PFID' => $row2['PFID'], 'SymptomName' => $row2['Symptom'], 'SymptomChoice' => $row2['SymptomChoice']);
			}
			$max = count($return);

			for($k=0; $k<$max; $k++)
			{
				for ($j=$k; $j<$max; $j++)
				{
					if($return[$j]['PFID']==$return[$k]['PFID'])
					{
						$sym[] = array('PFID' => $return[$j]['PFID'], 'SymptomName' => $return[$j]['SymptomName'], 'SymptomChoice' => $return[$j]['SymptomChoice']);
					}
					else
						break;
				}
				$mainsym[] = $sym;
				$k=$j-1;
				$sym = array();
			}
			$main_count = count($mainsym);
			$count1 = count($current);
			for ($n=0; $n < $main_count; $n++)
			{
				$result_array=array();
				$prescription=array();
				$test=array();
				$count2 = count($mainsym[$n]);
				for ($l=0; $l < $count1; $l++)
				{
					for ($m=0; $m < $count2 ; $m++)
					{
						if($current[$l]['Symptom']==$mainsym[$n][$m]['SymptomName'] && $current[$l]['SymptomChoice']==$mainsym[$n][$m]['SymptomChoice'])
							$result_array[] = array('PFID' => $mainsym[$n][$m]['PFID'], 'SymptomName' => $mainsym[$n][$m]['SymptomName'], 'SymptomChoice' => $mainsym[$n][$m]['SymptomChoice']);
					}
				}
				$count_final = count($result_array);
				$match_percent = ($count_final*100)/$count1;
				if($match_percent>20)
				{
					$result19 = $db->prepare("SELECT AID from appointment3 where PFID=:PFID");
					$result19->bindParam(':PFID', $mainsym[$n][0]['PFID'], PDO::PARAM_STR);
					$result19->execute();
					$row19 = $result19->fetch();

					$match_percent = sprintf('%0.0f', $match_percent);
					$result20 = $db->prepare("SELECT m.Medicine, dtm.Dosage, dtm.Type, dtm.Morning, dtm.Afternoon, dtm.Night, dtm.IsAfter, dtm.OnNeed, dtm.Days
					 from doctormedicine dtm inner join medicine m where dtm.AID = :AID and m.MID = dtm.MID order by dtm.DMID");
					$result20->bindParam(':AID', $row19['AID'], PDO::PARAM_STR);
					$result20->execute();
					while ($row20 = $result20->fetch())
					{
						$dose = "";
		                $isafter = "";
		                if($row20['Morning']==1)
		                  $dose = $dose."Morning, ";
		                if($row20['Afternoon']==1)
		                  $dose = $dose."Afternoon, ";
		                if($row20['Night']==1)
		                  $dose = $dose."Night, ";
		                if($row20['SOS']==1)
		                  $dose = $dose."SOS, ";
		                if($row20['IsAfter']==1)
		                  $isafter = "After Food";
		                else
		                  $isafter = "Before Food";
		              	$dose = substr($dose, 0, -2);
						$prescription[] = array('Medicine' => (string)$row20['Medicine'], 'Dosage' => (string)$row20['Dosage'], 'Type' => (string)$row20['Type'], 'Morning' => (string)$row20['Morning'], 'Afternoon' => (string)$row20['Afternoon'], 'Night' => (string)$row20['Night'],
							'IsAfter' => (string)$row20['IsAfter'], 'OnNeed' => (string)$row20['OnNeed'], 'Days' => (string)$row20['Days'], 'Dose' => (string)$dose, 'When' => (string)$isafter);
						$date = date("d/m/y", strtotime($row20['MedDate']));
					}


					$result21 = $db->prepare("SELECT t.Test, t.TAB, dtt.TestDate from doctortest dtt inner join test t where dtt.AID = :AID and t.TID = dtt.TID order by dtt.DTID");
					$result21->bindParam(':AID', $row19['AID'], PDO::PARAM_STR);
					$result21->execute();

					while ($row21 = $result21->fetch())
					{
						if(is_null($row21['TAB']))
							$tab = "";
						else
							$tab = $row21['TAB'];

						$test[] = array('TestName' => (string)$row21['Test'], 'TAB' => (string)$tab);
					}

					$result22 = $db->prepare("SELECT Comment from doctorcomment where AID = :AID");
					$result22->bindParam(':AID', $row19['AID'], PDO::PARAM_STR);
					$result22->execute();
					$row22 = $result22->fetch();

					$result23 = $db->prepare("SELECT Notes from doctornotes where AID = :AID");
					$result23->bindParam(':AID', $row19['AID'], PDO::PARAM_STR);
					$result23->execute();
					$row23 = $result23->fetch();
					$suggest[] = array('AID' => $row19['AID'] , 'MatchPercent' => $match_percent , 'Prescription' => $prescription, 'PDate' => $date, 'Test' => $test,
						'Comment' => $row22['Comment'], 'Notes' => $row23['Notes'], 'Symptoms' => $mainsym[$n]);
				}
			}
			$response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Patient Symptoms Submitted";

			$response['Percentage'] = $main_count;
			$response['Check'] = $suggest;

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
