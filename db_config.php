<?php
$db_host = 'mysql:host=mysql.hostinger.in;dbname=u824038781_rxnew;charset=utf8'; //hostname
$db_user = "u824038781_rohan"; // username
$db_password = "rohan123"; // password
// $db_name = "u824038781_rx"; //database name
$db = new PDO($db_host, $db_user, $db_password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));