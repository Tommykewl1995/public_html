<?php
/*
Dconnection Table :
UserType :
0 : follower
1 : connection
2 : admin
3 : creator
*/

define( 'API_ACCESS_KEY', 'AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM' );
define( 'USERNAME', 'kapbulk' );
define( 'PASSWORD', 'kap@user!23' );
define("GOOGLE_API_KEY", "AIzaSyBKh75Fb7Ly6njtZYviL-CIN9ewkhPpTeM");
define("GOOGLE_GCM_URL", "https://fcm.googleapis.com/fcm/send");

class Console{
  function __construct() {
    $this->data = [];
    $this->count = 0;
  }
  function console($string){
    $this->data[] = (string)$this->count." : ".$string;
  }
  function flush(){
    return $this->data;
  }
}

function getnotifications($row, $db){
  switch ($row['Type']) {
    case 0:
      $result2 = $db->prepare("SELECT b.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,u.Pic
      FROM Booking b
      INNER JOIN user u
      ON u.UserID = b.DID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Your Appointment Request at ".$tim." has been accepted by ".$row2['DFullName'];
      $pic = $row2['Pic'];
      break;

    case 1:
      $result2 = $db->prepare("SELECT fa.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,u.Pic
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.DID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Your Appointment Request at ".$tim." has been rejected by ".$row2['DFullName'];
      $pic = $row2['Pic'];
      break;

    case 2:
      $result2 = $db->prepare("SELECT fa.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,u.Pic
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.DID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Your Appointment Request at ".$tim." has been rescheduled by ".$row2['DFullName'];
      $pic = $row2['Pic'];
      break;

    case 3:
      $result2 = $db->prepare("SELECT b.SlotID,CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName,u.Pic
      FROM Booking b
      INNER JOIN user u
      ON u.UserID = b.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Patient ".$row2['PFullName']." has accepted reschedule timing proposed by you at ".$tim;
      $pic = $row2['Pic'];
      break;

    case 4:
      $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName,u.Pic
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $statement = "Patient ".$row2['PFullName']." has rejected reschedule timing proposed by you";
      $pic = $row2['Pic'];
      break;

    case 5:
      $result2 = $db->prepare("SELECT bh.SlotID,CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName,u.Pic
      FROM bookinghistory bh
      INNER JOIN user u
      ON u.UserID = bh.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Patient ".$row2['PFullName']." has cancelled the booking at ".$tim;
      $pic = $row2['Pic'];
      break;

    case 6:
      $result2 = $db->prepare("SELECT bh.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,u.Pic
      FROM bookinghistory bh
      INNER JOIN user u
      ON u.UserID = bh.DID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Your Booking at ".$tim." has been cancelled by ".$row2['DFullName'];
      $pic = $row2['Pic'];
      break;

    case 7:
      $result2 = $db->prepare("SELECT bh.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,u.Pic
      FROM bookinghistory bh
      INNER JOIN user u
      ON u.UserID = bh.DID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Reminder! Your Booking with ".$row2['DFullName']." is at time ".$tim;
      $pic = $row2['Pic'];
      break;
    case 8:
      $result2 = $db->prepare("SELECT bh.SlotID,CONCAT_WS(' ', 'Dr.', u.FName, u.LName)
      AS DFullName,u.Pic
      FROM bookinghistory bh
      INNER JOIN user u
      ON u.UserID = bh.DID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Share your Experience with RxHEalth for your recent appointment with ".$row2['DFullName']." at ".$tim;
      $pic = $row2['Pic'];
      break;
    case 9:
      $result2 = $db->prepare("SELECT b.SlotID,CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName,u.Pic
      FROM Booking b
      INNER JOIN user u
      ON u.UserID = b.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $tim = slottotime((float)$row2['SlotID']);
      $statement = "Patient ".$row2['PFullName']." has booked at slot ".$tim;
      $pic = $row2['Pic'];
      break;

    case 10:
      $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
      AS PFullName,u.Pic
      FROM failedappointments fa
      INNER JOIN user u
      ON u.UserID = fa.PID
      WHERE AID = :AID");
      $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $statement = "Patient ".$row2['PFullName']." has cancelled his appointment";
      $pic = $row2['Pic'];
      break;

    case 11:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,cd.Name,u.Pic
    FROM CommunityRequests cr
    INNER JOIN user u
    ON u.UserID = cr.DID
    INNER JOIN ComDetails cd
    ON cr.CommuID = cd.CommuID
    WHERE ReqID = :ReqID");
    $result2->bindParam(":ReqID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = "Doctor ".$row2['FullName']." has requested to join Community ".$row2['Name'];
    $pic = $row2['Pic'];
    break;

    case 12:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,cd.Name,u.Pic
    FROM CommunityRequests cr
    INNER JOIN user u
    ON u.UserID = cr.UserID
    INNER JOIN ComDetails cd
    ON cr.CommuID = cd.CommuID
    WHERE ReqID = :ReqID");
    $result2->bindParam(":ReqID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = $row2['FullName']." has accepted your request to join Community ".$row2['Name'];
    $pic = $row2['Pic'];
    break;

    case 13:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,cd.Name,u.Pic
    FROM Dconnection dc
    INNER JOIN user u
    ON u.UserID = dc.UserID
    INNER JOIN ComDetails cd
    ON dc.CommuID = cd.CommuID
    WHERE CID = :ReqID");
    $result2->bindParam(":ReqID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = $row2['FullName']." has followed Community ".$row2['Name'];
    $pic = $row2['Pic'];
    break;

    case 14:
    $result2 = $db->prepare("SELECT l.ShrID, CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM Likes l
    INNER JOIN user u
    ON u.UserID = l.AppUserID
    WHERE l.LikeID = :LikeID");
    $result2->bindParam(":LikeID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $extra = array("ShrID" => $row2['ShrID']);
    $statement = $row2['FullName']." has liked Your Article";
    $pic = $row2['Pic'];
    break;

    case 15:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic,c.ShrID,c.IsAnony
    FROM Comments c
    INNER JOIN user u
    ON u.UserID = c.UserID
    WHERE ComID = :UserID");
    $result2->bindParam(":UserID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    if($row2['IsAnony'] == '0'){
      $statement = $row2['FullName']." has commented on Your Article";
    }else{
      $statement = "Anonymous has commented on Your Article";
    }
    $pic = $row2['Pic'];
    $extra = array('ShrID' => $row2['ShrID']);
    break;

    case 16:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM ShareArticle sa
    INNER JOIN user u
    ON u.UserID = sa.UserID
    WHERE sa.ShrID = :UserID");
    $result2->bindParam(":UserID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = $row2['FullName']." has shared Your Article";
    $pic = $row2['Pic'];
    $extra = array("ShrID" => $row['ID']);
    break;

    case 17:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', FName, LName)
    AS FullName,Pic
    FROM user
    WHERE UserID = :UserID");
    $result2->bindParam(":UserID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = $row2['FullName']." has replied to Your Comment";
    $pic = $row2['Pic'];
    break;

    case 18:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM appointment3 a
    INNER JOIN user u
    ON u.UserID = a.PID
    WHERE a.AID = :AID");
    $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = $row2['FullName']." has shared Symptoms with you";
    $pic = $row2['Pic'];
    $extra = array("AID" => $row['ID']);
    break;

    case 19:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM appointment3 a
    INNER JOIN user u
    ON u.UserID = a.DID
    WHERE a.AID = :AID");
    $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = "Dr. ".$row2['FullName']." has sent you a Prescription";
    $pic = $row2['Pic'];
    $extra = array("AID" => $row['ID']);
    break;

    case 20:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM ShareArticle sa
    INNER JOIN user u
    ON u.UserID = sa.UserID
    WHERE sa.ShrID = :UserID");
    $result2->bindParam(":UserID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = "Dr. ".$row2['FullName']." has shared an Article";
    $pic = $row2['Pic'];
    $extra = array("ShrID" => $row['ID']);
    break;

    case 21:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM appointment3 a
    INNER JOIN user u
    ON u.UserID = a.PID
    WHERE a.AID = :AID");
    $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = $row2['FullName']." has sent you a Query on shared symptoms.";
    $pic = $row2['Pic'];
    $extra = array("AID" => $row['ID']);
    break;

    case 22:
    $result2 = $db->prepare("SELECT CONCAT_WS(' ', u.FName, u.LName)
    AS FullName,u.Pic
    FROM appointment3 a
    INNER JOIN user u
    ON u.UserID = a.DID
    WHERE a.AID = :AID");
    $result2->bindParam(":AID", $row['ID'],PDO::PARAM_INT);
    $result2->execute();
    $row2 = $result2->fetch();
    $statement = "Dr. ".$row2['FullName']." has replied on your symptoms.";
    $pic = $row2['Pic'];
    $extra = array("AID" => $row['ID']);
    break;

    default:
      $result2 = $db->prepare("SELECT cd.Name,t.CommuID,t.Title,t.Tip FROM Tip t INNER JOIN ComDetails cd ON cd.CommuID = t.CommuID WHERE TipID = :TipID");
      $result2->bindParam(":TipID", $row['ID'],PDO::PARAM_INT);
      $result2->execute();
      $row2 = $result2->fetch();
      $statement = $row2['Title'];
      $extra = array("Tip" => $row2['Tip'], "CommuID" => $row2['CommuID'], "Name" => $row2['Name']);
      break;
  }
  $color = ($row['IsViewed'])?"#ffffff":"#f6f6f6";
  $time = abs(strtotime($row['now']) - strtotime($row['NGT']));
  $data = array("Summary" => $statement, "NID" => $row['NID'], "Viewed" => $color, "NGT" => $time,"Pic" => $pic, "Type" => $row['Type'], "Extra" => $extra);
  return array("viewed" => $row['IsViewed'], "Data" => $data);
}

function hashtag($str){
  // $str = strip_tags(html_entity_decode($str));
  $str = str_replace("\n","</br>",$str);

  return preg_replace("/#([A-Za-z0-9\/\.]*)/", "<font style='color : blue'>#$1</font>", $str);
}
function hashtag1($str){
  // $str = strip_tags(html_entity_decode($str));
  //$str = str_replace("\n","</br>",$str);

  return preg_replace("/#([A-Za-z0-9\/\.]*)/", "<font style='color : blue'>#$1</font>", $str);
}

function sendsms($senderid = "RxHealth",$dest_mobileno, $sms){
  $url = sprintf("http://123.63.33.43/blank/sms/user/urlsmstemp.php?username=XXXXXX&pass=XXXXX&senderid=XXXXX&dest_mobileno=XXXXX&message=XXXXXX&mtype=UNI&response=Y", USERNAME, PASSWORD, $senderid, $dest_mobileno, $message, urlencode($sms) );
  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch,CURLOPT_TIMEOUT, '3');
  $content = trim(curl_exec($ch));
  curl_close($ch);
  return $content;
}

function pushnotification($userid, $title, $message, $data, $db, $regids = null){
      if($regids){
        $registrationIds = $regids;
      }else{
        $query10 = $db->prepare("SELECT RegistrationID from registrationid where UserID = :UserID");
        $query10->bindParam(":UserID", $userid, PDO::PARAM_INT);
  			$query10->execute();
        while($que = $query10->fetch()){
          $registrationIds[] = $que['RegistrationID'];
        }
      }
      //$token[] = $que['RegistrationID'];
      // prep the bundle

       $fields = array(
       "registration_ids" => $registrationIds,
       "priority" => "high",
       "notification" => array( "title" => $title, "body" => $message, "sound" => "default", "click_action" =>"FCM_PLUGIN_ACTIVITY", "icon" =>"fcm_push_icon", "iconColor" => "blue" ),
       "data" => $data,
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

      $response['ResponseCode'] = "200";
      $response['ResponseMessage'] = "Data Saved";
      $response['CurlResponse'] = $result5;
      return $result5;
}

class Article {
  function __construct( $userid, $pref = null, $db, $offset) {
    $this->basestatement = "SELECT distinct(sa.ShrID),sa.CommuID,sa.isPublic, u.Pic,u.UserID,NOW() AS now, CONCAT_WS(' ',u.FName,u.LName) AS FullName,u.IsDoctor,sa.T1,a.Header,sa.Summary,a.Link,a.Details,a.ImageLink,sa.UserID,sa.IsAuthor,sa.LikesCount,sa.CommentCount FROM ShareArticle sa INNER JOIN user u ON sa.UserID = u.UserID INNER JOIN Articles a ON a.ArID = sa.ArID";
    $this->userid = $userid;
    $this->preferences = $pref;
    $this->db = $db;
    $offset = (int)$offset;
    $this->numberofarticles="5";
    $this->offset = $offset*5;
  }

  private function getliked(){
    $result2 = $this->db->prepare("SELECT ShrID FROM Likes WHERE AppUserID = :UserID");
    $result2->bindParam(":UserID", $this->userid,PDO::PARAM_INT);
    $result2->execute();
    $liked = array();
    while($row2 = $result2->fetch()){
      $liked[] = $row2['ShrID'];
    }
    return $liked;
  }

  private function getbookmarked(){
    $result = $this->db->prepare("SELECT ShrID FROM bookmark WHERE UserID = :UserID");
    $result->bindParam(":UserID", $this->userid, PDO::PARAM_INT);
    $result->execute();
    $bookmarked = array();
    while($row = $result->fetch()){
      $bookmarked[] = $row['ShrID'];
      $this->bookstring.=(string)$row['ShrID'].",";
    }
    $this->bookstring = substr($this->bookstring,0,-1);
    return $bookmarked;
  }
  private function fetchit($all,$coid=null,$shrit = null,$bookmarkenabled = 0,$artids = null){
    $statement = $this->basestatement;
    $result1 = $this->db->prepare("SELECT CommuID,UserType FROM Dconnection WHERE UserID = :UserID");
    $result1->bindParam(":UserID",$this->userid,PDO::PARAM_INT);
    $result1->execute();
    $connect = array();
    while($row1 = $result1->fetch()){
      $connect[$row1['CommuID']] = $row1['UserType'];
      $list.=$row1['CommuID'].",";
    }
    if($list){
      $list = substr($list,0,-1);
    }
    $liked = $this->getliked();
    $bookmarked = $this->getbookmarked();
    if($this->preferences){
      if($this->preferences['tagids']){
        $statement.=" INNER JOIN ArticleTags at ON at.ArID = sa.ArID WHERE at.TagID IN (".$this->preferences['tagids'].") ";
      }
      if($this->preferences['doctorids']){
        $statement.=($this->preferences['tagids'])?"AND ":" WHERE ";
        $statement.="sa.UserID IN (".$this->preferences['doctorids'].") ";
      }
      $statement.=" AND ";
    }else{
      $statement.=" WHERE ";
    }
    if($all){
      if($list){
        $statement.= "(isPublic = 1 OR CommuID IN (".$list."))";
      }else{
        $statement.= "isPublic = 1";
      }
      if($artids){
        $statement.= " AND a.ArID IN (".$artids.") ";
      }
    }else if($shrit){
      $statement.="ShrID = ".$shrit;
    }else{
      $statement.= "CommuID = ".$coid;
    }
    $statement.=" ORDER BY sa.T1 DESC LIMIT ".$this->offset.",".$this->numberofarticles;
    $result = $this->db->prepare($statement);
    $result->execute();
    $articles = array();
    // $articles[] = $statement;
    while($row = $result->fetch()){
      $ifliked = (in_array($row['ShrID'],$liked))?1:0;
      $ifbookmarked = (in_array($row['ShrID'],$bookmarked))?1:0;
      if(!$bookmarkenabled || $ifbookmarked){
        if($connect[$row['CommuID']] !== null){
          switch($connect[$row['CommuID']]){
            case 0:
            if($row['isPublic'] == '1'){
              $temp = 3;
            }else{
              $temp = 2;
            }
            break;
            case 1:
            $temp = 3;
            break;
            case 3:
            case 2:
            $temp = 4;
            break;
          }
        }else{
          $temp = 1;
        }
        $pic = $row['Pic'];
        $time = abs(strtotime($row['now']) - strtotime($row['T1']));
        $articles[] = array('ShrID' => $row['ShrID'],
        'Pic' => $pic,
        'Author' => $row['FullName'],
        'UserID' => $row['UserID'],
        'LastEdited' => $time,
        'Header' => $row['Header'],
        'Summary' => $row['Summary'],
        'Link' => $row['Link'],
        'Details' => $row['Details'],
        'isPublic' => $row['isPublic'],
        'IsAuthor' =>$row['IsAuthor'],
        'action' => $temp,
        'ImageLink' => $row['ImageLink'],
        'Liked' => $ifliked,
        'Bookmarked' => $ifbookmarked,
        'Likes' => $row['LikesCount'],
        'Comments' => $row['CommentCount'],
        'CommuID' => $row['CommuID']);
      }
    }
    return $articles;
  }

  function getallarticles($book,$artids = null){
    if($artids){
      return$this->fetchit(true,null,null,0,$artids);
    }
    return $this->fetchit(true,null,null,$book);
  }

  function getcommunityarticles($commuid){
    return $this->fetchit(false,$commuid);
  }

  function getsharedarticle($id){
    return $this->fetchit(false,null,$id);
  }

  function gettagarticle($tag){
    // if(strpos($tag, "#") === false){
    //   $tag = str_replace(",",",#",$tag);
    //   $tag = "#".$tag;
    // }
    $tag = str_replace(",","','",$tag);
    $tag = "'".$tag."'";
    $result = $this->db->prepare("SELECT ArID FROM Articles WHERE Preferences IN (".$tag.") GROUP BY ArID");
    $result->execute();
    while($row = $result->fetch()){
      $artids.=$row['ArID'].",";
    }
    $artids = substr($artids,0,-1);
    $tem = $this->getallarticles(0,$artids);
    return $tem;
  }
}

class Search{
  function __construct( $data, $db, $all) {
    if($all == 0){
      $this->query = "%".$data."%";
    }
    $this->all = $all;
    $this->db = $db;
  }

  function gettags(){
    $statement = "SELECT * FROM Tags";
    if($this->all == 0){
      $statement.=" WHERE Tag LIKE :Data";
    }
    $statement.=" GROUP BY Tag LIMIT 0,5";
    $result = $this->db->prepare($statement);
    if($this->all == 0){
      $result->bindParam(":Data", $this->query,PDO::PARAM_STR);
    }
    $result->execute();
    $tags = array();
    while($row = $result->fetch()){
      $temp = substr($row['Tag'], 1);
      $tags[] = array('Name' => $temp, 'TagID' => $row['TagID'], 'ArID' => $row['ArID']);
    }
    return $tags;
  }

  function getpeople($isdoc){
    $h = !$isdoc;
    $statement = "SELECT UserID,CONCAT_WS(' ',FName,LName) AS FullName, Pic FROM user";
    if($this->all == 0){
      $statement.= " WHERE CONCAT_WS(' ',FName,LName) LIKE :Data AND IsDoctor = :is LIMIT 0,5";
    }
    $result = $this->db->prepare($statement);
    if($this->all == 0){
      $result->bindParam(":Data", $this->query,PDO::PARAM_STR);
      $result->bindParam(":is", $h,PDO::PARAM_STR);
    }
    $result->execute();
    $people = array();
    while($row = $result->fetch()){
      $people[] = array('Name' => $row['FullName'], 'UserID' => $row['UserID'], "Pic" => $row['Pic']);
    }
    return $people;
  }

  function getcommunities(){
    $statement = "SELECT Name,CommuID FROM ComDetails";
    if($this->all == 0){
      $statement.= " WHERE Name LIKE :Data LIMIT 0,5";
    }
    $result = $this->db->prepare($statement);
    if($this->all == 0){
      $result->bindParam(":Data", $this->query,PDO::PARAM_STR);
    }
    $result->execute();
    $communities = array();
    while($row = $result->fetch()){
      $communities[] = array('Name' => $row['Name'], 'CommuID' => $row['CommuID']);
    }
    return $communities;
  }

  function getspecialities(){
    $statement = "SELECT SpecID,Speciality FROM speciality";
    if($this->all == 0){
      $statement.= " WHERE Speciality LIKE :Data LIMIT 0,5";
    }
    $result = $this->db->prepare($statement);
    if($this->all == 0){
      $result->bindParam(":Data", $this->query,PDO::PARAM_STR);
    }
    $result->execute();
    $specialities = array();
    while($row = $result->fetch()){
      $specialities[] = array('SpecID' => $row['SpecID'], 'Speciality' => $row['Speciality']);
    }
    return $specialities;
  }
}

function createarticle($userid,$header,$summary,$link,$details,$preference,$imagelink, $db, $timestamp = null){
  if ($link == "" || filter_var($link, FILTER_VALIDATE_URL) === $link) {
    preg_match_all('/(?<!\w)#\w+/',$details.' '.$summary,$matches);
    $match = $matches[0];
    $summary = hashtag($summary);
    $details = hashtag1($details);
    if($timestamp){
      $result = $db->prepare("INSERT INTO Articles (UserID,Header,Summary,Link,Details, Preferences,ImageLink,AGT)
      VALUES (:UserID,:Header,:Summary,:Link,:Details, :Preferences,:ImageLink,:AGT)");
      $result->bindParam(':AGT', $timestamp,PDO::PARAM_STR);
    }else{
      $result = $db->prepare("INSERT INTO Articles (UserID,Header,Summary,Link,Details, Preferences,ImageLink)
      VALUES (:UserID,:Header,:Summary,:Link,:Details, :Preferences,:ImageLink)");
    }
    $result->bindParam(':UserID', $userid,PDO::PARAM_INT);
    $result->bindParam(':Header', $header,PDO::PARAM_STR);
    $result->bindParam(':Summary', $summary,PDO::PARAM_STR);
    $result->bindParam(':Link', $link,PDO::PARAM_STR);
    $result->bindParam(':Details', $details,PDO::PARAM_STR);
    $result->bindParam(':ImageLink', $imagelink,PDO::PARAM_STR);
    $result->bindParam(':Preferences', $preference,PDO::PARAM_STR);
    $result->execute();
    $aid = $db->lastInsertId();
    for($i = 0; $i < count($match);$i++){
      $query = $db->prepare("INSERT INTO Tags (Tag,ArID) VALUES (:Tag,:ArID)");
      $query->bindParam(":Tag", $match[$i], PDO::PARAM_STR);
      $query->bindParam(":ArID", $aid, PDO::PARAM_INT);
      $query->execute();
    }
    return $aid;
  } else {
    return 0;
  }
}

/**
 * Community class
 */
class Community{
  function __construct($db, $commuid = null, $creatorid = null){
    $this->db = $db;
    $this->commuid = $commuid;
    $this->creatorid = $creatorid;
    if(is_null($creatorid) && !is_null($commuid)){
      $query = $this->db->prepare("SELECT CreatorID FROM ComDetails WHERE CommuID = :CommuID");
      $query->bindParam(":CommuID", $commuid, PDO::PARAM_INT);
      $query->execute();
      $que = $query->fetch();
      $this->creatorid = $que['CreatorID'];
    }
  }

  public static function createcommunity($name, $type, $creatorid, $db, $status = null){
    if(is_null($status)){
      $status = "Welcome";
    }
    $result = $db->prepare("INSERT INTO ComDetails (CreatorID, Name, ComType, Status) VALUES (:CreatorID, :Name, :ComType, :Status)");
    $result->bindParam(":CreatorID", $creatorid, PDO::PARAM_INT);
    $result->bindParam(":Name", $name, PDO::PARAM_STR);
    $result->bindParam(":ComType", $type, PDO::PARAM_INT);
    $result->bindParam(":Status", $status, PDO::PARAM_STR);
    $result->execute();
    return new Community($db, $db->lastInsertId());
  }

  function adddoctorstocommunity(){

  }

  function addclinicstocommunity($clinic){
    $temppin = (string)generatePIN(4);
    $name = strtolower(strtok($clinic['Name'], " "))."@".$temppin;
    $password = generatePIN(8);
    $password1 = Bcrypt::hashPassword($password);
    $result = $this->db->prepare("INSERT INTO clinics (CommuID, ClinicName, Summary, Address, City, PinCode, ClinicEmail, ClinicPhone, ClinicLogo, AssistName, AssistPassword) VALUES (:CommuID, :ClinicName, :Summary, :Address, :City, :PinCode, :ClinicEmail, :ClinicPhone, :ClinicLogo, :AssistName, :AssistPassword)")
    $result->bindParam(":CommuID", $this->commuid, PDO::PARAM_INT);
    $result->bindParam(":ClinicName", $clinic['Name'], PDO::PARAM_STR);
    $result->bindParam(":Summary", $clinic['Summary'], PDO::PARAM_STR);
    $result->bindParam(":Address", $clinic['Address'], PDO::PARAM_STR);
    $result->bindParam(":City", $clinic['City'], PDO::PARAM_STR);
    $result->bindParam(":PinCode", $clinic['PinCode'], PDO::PARAM_STR);
    $result->bindParam(":ClinicEmail", $clinic['ClinicEmail'], PDO::PARAM_STR);
    $result->bindParam(":ClinicPhone", $clinic['ClinicPhone'], PDO::PARAM_STR);
    $result->bindParam(":ClinicLogo", $clinic['ClinicLogo'], PDO::PARAM_STR);
    $result->bindParam(":AssistName", $name, PDO::PARAM_STR);
    $result->bindParam(":AssistPassword", $password1, PDO::PARAM_STR);
  }

  function removeclinicsfromcommunity($clinicid){
    $result = $this->db->prepare("DELETE FROM clinicdoctors WHERE ClinicID = :ClinicID");
    $result->bindParam(":ClinicID", $clinicid, PDO::PARAM_INT);
    $result->execute();
    $result1 = $this->db->prepare("DELETE FROM clinics WHERE ClinicID = :ClinicID");
    $result1->bindParam(":ClinicID", $clinicid, PDO::PARAM_INT);
    $result1->execute();
  }

  function editclinics($clinic){
    $result = $this->db->prepare("UPDATE clinics SET ClinicName = :ClinicName, Summary = :Summary, Address = :Address, City = :City, PinCode = :PinCode, ClinicEmail = :ClinicEmail, ClinicPhone = :ClinicPhone, ClinicLogo = :ClinicLogo, AssistName = :AssistName, AssistPassword = :AssistPassword WHERE ClinicID = :ClinicID");
    $result->bindParam(":ClinicID", $clinic['ClinicID'], PDO::PARAM_INT);
    $result->bindParam(":ClinicName", $clinic['Name'], PDO::PARAM_STR);
    $result->bindParam(":Summary", $clinic['Summary'], PDO::PARAM_STR);
    $result->bindParam(":Address", $clinic['Address'], PDO::PARAM_STR);
    $result->bindParam(":City", $clinic['City'], PDO::PARAM_STR);
    $result->bindParam(":PinCode", $clinic['PinCode'], PDO::PARAM_STR);
    $result->bindParam(":ClinicEmail", $clinic['ClinicEmail'], PDO::PARAM_STR);
    $result->bindParam(":ClinicPhone", $clinic['ClinicPhone'], PDO::PARAM_STR);
    $result->bindParam(":ClinicLogo", $clinic['ClinicLogo'], PDO::PARAM_STR);
    $result->bindParam(":AssistName", $name, PDO::PARAM_STR);
    $result->bindParam(":AssistPassword", $password1, PDO::PARAM_STR);
  }

  function addmemberstocommunity($commuid, $userid, $type){
    $result = $this->db->prepare("INSERT INTO Dconnection (CommuID,UserID,UserType) VALUES (:CommuID, :UserID, :UserType)");
    $result->bindParam(":CommuID", $commuid, PDO::PARAM_INT);
    $result->bindParam(":UserID", $userid, PDO::PARAM_INT);
    $result->bindParam(":UserType", $type, PDO::PARAM_INT);
    $result->execute();
    return $this->db->lastInsertId();
  }

  function removemembersfromcommunity($commuid, $userid){
    $result = $this->db->prepare("DELETE FROM Dconnection WHERE CommuID = :CommuID AND UserID = :UserID");
    $result->bindParam(":CommuID", $commuid, PDO::PARAM_INT);
    $result->bindParam(":UserID", $userid, PDO::PARAM_INT);
    $result->execute();
    return $this->db->lastInsertId();
  }

  function editmembers($commuid, $userid, $type){
    $result = $this->db->prepare("UPDATE Dconnection SET UserType = :UserType WHERE CommuID = :CommuID AND UserID = :UserID");
    $result->bindParam(":CommuID", $commuid, PDO::PARAM_INT);
    $result->bindParam(":UserID", $userid, PDO::PARAM_INT);
    $result->bindParam(":UserType", $type, PDO::PARAM_INT);
    $result->execute();
  }
}


function createcommunity($name,$userid,$Type,$db){
  $result = $db->prepare("INSERT INTO ComDetails (Name,ComType) VALUES (:Name,:ComType)");
  $result->bindParam(":Name", $name, PDO::PARAM_STR);
  $result->bindParam(":ComType", $Type, PDO::PARAM_STR);
  $result->execute();
  $commuid = $db->lastInsertId();
  addmemberstocommunity($commuid,$userid,3,$db);
  return $commuid;
}

function sharearticle($userid,$summary = null,$aid,$author,$public,$comid,$db){
   if(!$summary){
     $query = $db->prepare("SELECT UserID,Summary FROM Articles WHERE ArID = :ArID");
     $query->bindParam(":ArID",$aid,PDO::PARAM_INT);
     $query->execute();
     $row = $query->fetch();
     $summary = $row['Summary'];
   }
   $result = $db->prepare("INSERT INTO ShareArticle (UserID,Summary,ArID,IsAuthor,isPublic,CommuID)
   VALUES (:UserID,:Summary,:ArID,:IsAuthor,:isPublic,:CommuID)");
   $result->bindParam(':UserID', $userid,PDO::PARAM_INT);
   $result->bindParam(':Summary', $summary,PDO::PARAM_STR);
   $result->bindParam(':ArID', $aid,PDO::PARAM_INT);
   $result->bindParam(':IsAuthor', $author,PDO::PARAM_INT);
   $result->bindParam(':isPublic', $public,PDO::PARAM_INT);
   $result->bindParam(':CommuID', $comid,PDO::PARAM_INT);
   $result->execute();
   $shrid = $db->lastInsertId();
   return $shrid;
}

function getcommunities($UserId,$db)
{
  $myCommunities = array();
  $otherCommunities = array();
  $following = array();
  $results = $db->prepare("SELECT * FROM Dconnection WHERE UserID = :UserID AND CommuID != 1");
  $results->bindParam(":UserID", $UserId, PDO::PARAM_INT);
  $results->execute();
  while($row = $results->fetch()){
    $usertype = $row['UserType'];
    $details = $db->prepare("SELECT * FROM ComDetails WHERE CommuID = :ComID");
    $details->bindParam(":ComID",$row['CommuID'],PDO::PARAM_INT);
    $details->execute();
    $detail = $details->fetch();
    if($usertype == 3){
      $myCommunities[] = array("CID" => $row['CID'],"CommuID" => $row['CommuID'],"Type" => $detail['ComType'],"Name" => $detail['Name']);
    }
    elseif ($usertype == 2) {
      $otherCommunities[] = array("CID" => $row['CID'],"CommuID" => $row['CommuID'],"Type" => $detail['ComType'],"Name" => $detail['Name'], "IsAdmin" => "true");
    }
    elseif ( $usertype == 1) {
      $otherCommunities[] = array("CID" => $row['CID'],"CommuID" => $row['CommuID'],"Type" => $detail['ComType'], "Name" => $detail['Name'], "IsAdmin" => "false");
    }
    elseif ($usertype == 0) {
      $following[] = array("CID" => $row['CID'],"CommuID" => $row['CommuID'],"Type" => $detail['ComType'], "Name" => $detail['Name']);
    }
  }
  return [$myCommunities,$otherCommunities,$following];

}

function generatePIN($digits){
      $i = 0; //counter
      $pin = ""; //our default pin is blank.
      while($i < $digits){
          //generate a random number between 0 and 9.
        $pin .= mt_rand(0, 9);
        $i++;
      }
      return $pin;
  }


function sendotp($phone,$otp_code,$type,$doctor = null){
    $otpData = array();

  //$otp_code = generatePIN(4);
  if($type == "pin"){
    $message = urlencode("OTP verification code for RxHealth ". $otp_code);
  }elseif ($type == "refer"){
    $message = urlencode("Love this app .Download now http://tinyurl.com/rxhlth " );
  }elseif ($type == "selfCreated" ){
    $message = urlencode("Your profile is successfully created on RxHealth having userId ".$phone. " and password ". $otp_code);
  }else if ($type == "doctorCreated"){
    $message = urlencode($doctor." requests you to join him on RxHealth with user id ".$phone. " and password ". $otp_code ." Login and stay connected http://tinyurl.com/rxhlth " );
  }

  $postData = array(
      'authkey' => '120841A6OG9IViGkvK579eee11',
      'mobiles' => $phone,
      'message' => $message,
      'sender' => "RxHLTH",
      'route' => $route
  );

  //API URL
  $url="https://control.msg91.com/api/sendhttp.php";

  // init the resource
  $ch = curl_init();
  curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $postData
      //,CURLOPT_FOLLOWLOCATION => true
  ));


  //Ignore SSL certificate verification
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


  //get response
  $output = curl_exec($ch);

  //Print error if any
  if(curl_errno($ch))
  {
      echo 'error:' . curl_error($ch);
  }

  curl_close($ch);

  return [$otp_code,$message];

          //$otp_code = "2527";
  //$res = sendsms($phone, $otp_code,$senderId);
}
?>
