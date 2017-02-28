<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');
require('helperfunctions1.php');

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
      $pref = $obj['Pref'];
      $book = ($pref['Bookmark'])?1:0;
      if(!$book && !$obj['CommuID'] && $obj['count'] == 0){
        $today = (int)date('d');
        $cquery = $db->prepare("SELECT u.FName, u.LName, a.PrescriptionDate, a.ConditionID, u.Pic FROM appointment3 a INNER JOIN user u ON u.UserID = a.DID WHERE a.PrescriptionDate IS NOT NULL AND a.PID = :UserID AND a.ConditionID != 'rev' ORDER BY a.PrescriptionDate DESC LIMIT 0,1");
        $cquery->bindParam(":UserID", $obj['UserID'], PDO::PARAM_INT);
        $cquery->execute();
        $crow = $cquery->fetch();
        if($crow){
          $presday = (int)date('d', strtotime($crow['PrescriptionDate']));
          $diff = $today - $presday;
          $cquery1 = $db->prepare("SELECT a.ArID,a.Header,a.Summary,a.Link,a.Details,a.ImageLink FROM CaseArticles ca INNER JOIN Articles a ON a.ArID = ca.ArticleID WHERE ConditionID = :ConditionID AND Day = :Day AND ca.ArticleID IS NOT NULL");
          $cquery1->bindParam("ConditionID", $crow['ConditionID'], PDO::PARAM_STR);
          $cquery1->bindParam(":Day", $diff, PDO::PARAM_INT);
          $cquery1->execute();
        }
      }
      $article = new Article($obj['UserID'],$pref,$db, $obj['count']);
      if($obj['CommuID']){
        $articles = $article->getcommunityarticles($obj['CommuID']);
      }elseif($obj['ShrID']){
        $articles = $article->getsharedarticle($obj['ShrID']);
      }else if($obj['Tag']){
        $articles = $article->gettagarticle($obj['Tag']);
      }else{
        $articles = $article->getallarticles($book);
      }
      if($crow){
        while($crow1 = $cquery1->fetch()){
          $ca = array("CA" => 1,
          "Pic" => (is_null($crow['Pic']))?"http://ec2-52-37-68-149.us-west-2.compute.amazonaws.com/default.png":$crow['Pic'],
          "Author" => "Dr. ".(string)$crow['FName']." ".(string)$crow['LName'],
          "UserID" => 0,
          "LastEdited" => 0,
          "Header" => $crow1['Header'],
          "Summary" => $crow1['Summary'],
          "Link" => $crow1['Link'],
          "Details" => $crow1['Details'],
          "isPublic" => 0,
          "action" => -1,
          "ImageLink" => $crow1['ImageLink'],
          "CommuID" => 0);
          array_unshift($articles, $ca);
        }
      }
      $response['Articles'] = $articles;
      $obj['count']++;
      $response['count'] = $obj['count'];
      $response['ResponseCode'] = "200";
			$response['ResponseMessage'] = "Article List Data";
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
