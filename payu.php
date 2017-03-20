<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
require('db_config.php');
require('helperfunctions1.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');
// Merchant key here as provided by Payu
$MERCHANT_KEY = "hDkYGPQe";

// Merchant Salt as provided by Payu
$SALT = "yIEkykqEH3";

$error = array('e314' => 'Address_failure', 'e304' => 'Address_invalid', 'e702' => 'Amount_difference', 'e303' => 'Authentication_error', 'e335' => 'Authentication_incomplete', 'e334' => 'Authentication_service_unavailable', 'e505' => 'Awaiting_processing', 'e312' => 'Bank_denied', 'e208' => 'Bank_server_error', 'e216' => 'Batch_error', 'e201' => 'Brand_invalid', 'e324' => 'Card_fraud_suspected', 'e218' => 'Card_issuer_timed_out', 'e900' => 'Card_not_enrolled', 'e305' => 'Card_number_invalid', 'e213' => 'Checksum_failure', 'e210' => 'Communication_error', 'e214' => 'Curl_call_failure', 'e203' => 'Curl_error_card_verification', 'e205' => 'Curl_error_enrolled', 'e204' => 'Curl_error_not_enrolled', 'e206' => 'Cutoff_error', 'e315' => 'Cvc_address_failure', 'e313' => 'Cvc_failure', 'e504' => 'Duplicate_transaction', 'e311' => 'Expired_card', 'e336' => 'Expiry_date_low_funds', 'e219' => 'Incomplete_bank_response', 'e712' => 'Incomplete_data', 'e706' => 'Insufficient_funds', 'e719' => 'Insufficient_funds_authentication_failure', 'e713' => 'Insufficient_funds_expiry_invalid', 'e718' => 'Insufficient_funds_invalid_cvv', 'e903' => 'International_card_not_allowed', 'e717' => 'Invalid_account_number', 'e715' => 'Invalid_amount', 'e709' => 'Invalid_card_name', 'e902' => 'Invalid_card_type', 'e333' => 'Invalid_contact', 'e331' => 'Invalid_email_id', 'e323' => 'Invalid_expiry_date', 'e332' => 'Invalid_fax', 'e327' => 'Invalid_login', 'e707' => 'Invalid_pan', 'e710' => 'Invalid_pin', 'e207' => 'Invalid_transaction_type', 'e711' => 'Invalid_user_defined_data', 'e714' => 'Invalid_zip', 'e329' => 'Issuer_declined_low_funds', 'e310' => 'Lost_card', 'e200' => 'Merchant_invalid_pg', 'e211' => 'Network_error', 'e209' => 'No_bank_response', 'e000' => 'No_error', 'e337' => 'Not_captured', 'e328' => 'Parameters_mismatch', 'e326' => 'Password_error', 'e330' => 'Payment_gateway_validation_failure', 'e600' => 'PayUMoney_api_error', 'e716' => 'Permitted_bank_settings_error', 'e708' => 'Pin_retries_exceeded', 'e800' => 'Prefered_gateway_not_set', 'e704' => 'Receipt_number_error', 'e215' => 'Reserved_usage_error', 'e325' => 'Restricted_card', 'e901' => 'Retry_limit_exceeded', 'e307' => 'Risk_denied_pg', 'e317' => 'Secure_3d_authentication_error', 'e302' => 'Secure_3d_cancelled', 'e322' => 'Secure_3d_card_type', 'e319' => 'Secure_3d_format_error', 'e301' => 'Secure_3d_incorrect', 'e316' => 'Secure_3d_not_enrolled', 'e318' => 'Secure_3d_not_supported', 'e300' => 'Secure_3d_password_error', 'e321' => 'Secure_3d_server_error', 'e320' => 'Secure_3d_signature_error', 'e700' => 'Secure_hash_failure', 'e701' => 'Secure_hash_skipped', 'e212' => 'Server_communication_error', 'e309' => 'System_error_pg', 'e217' => 'Tranportal_id_error', 'e502' => 'Transaction_aborted', 'e503' => 'Transaction_cancelled', 'e308' => 'Transaction_failed', 'e202' => 'Transaction_invalid', 'e306' => 'Transaction_invalid_pg', 'e703' => 'Transaction_number_error', 'e501' => 'Unknown_error', 'e500' => 'Unknown_error_pg', 'e705' => 'User_profile_settings_error');
$posted = array();
$succ = false;
if(!empty($_POST)) {
	foreach($_POST as $key => $value) {
		$posted[$key] = $value;
	}
	// Hash Sequence
	$hashSequence = "status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key";
	$hashVarsSeq = explode('|', $hashSequence);
	$hash_string = '';
	foreach($hashVarsSeq as $hash_var) {
		$hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
		$hash_string .= '|';
	}
	$hash_string = $SALT."|".$hash_string;
	$hash = strtolower(hash('sha512', $hash_string));

	if($posted['status'] == 'success' && $posted['hash'] == $hash){
		$time = (int)$posted['udf5'];
		$date = date("Y-m-d H:i:s", $time);
		if(empty($posted['udf2'])){
			$result6 = $db->prepare("INSERT INTO appointment3 (PID, PFID, Status, ClinicID, AppointmentDate) VALUES (:PID, :PFID, 'Active', :ClinicID, :AppointmentDate)");
		}else{
			$result6 = $db->prepare("INSERT INTO appointment3 (DID, PID, PFID, Status, ClinicID, AppointmentDate) VALUES (:DID, :PID, :PFID, 'Active', :ClinicID, :AppointmentDate)");
			$result6->bindParam(':DID', $obj['DID'], PDO::PARAM_INT);
		}
		$result6->bindParam(':PID', $posted['udf4'], PDO::PARAM_INT);
		$result6->bindParam(':ClinicID', $posted['udf3'], PDO::PARAM_INT);
		$result6->bindParam(':PFID', $posted['udf1'], PDO::PARAM_INT);
		$result6->bindParam(':AppointmentDate', $date, PDO::PARAM_INT);
		$result6->execute();
		$aid = $db->lastInsertId();
		$result2 = $db->prepare("UPDATE patientprofile SET CPFID = :PFID WHERE PID = :PID");
		$result2->bindParam(":PFID", $posted['udf1'], PDO::PARAM_INT);
		$result2->bindParam(":PID", $posted['udf4'], PDO::PARAM_INT);
		$result2->execute();
		$result = $db->prepare("INSERT INTO payu (txnid, payuMoneyId) VALUES (:txnid, :payuMoneyId)");
		$result->bindParam(":txnid", $posted['txnid'], PDO::PARAM_STR);
		$result->bindParam(":payuMoneyId", $posted['payuMoneyId'], PDO::PARAM_STR);
		$result->execute();
		$succ = true;
	}
}
?>
<html>
	<head>
		
	</head>
	<body style="display:flex;justify-content: center;align-items: center;flex-direction: column;">
		<img src="<?php echo ($succ)?'https://cdn2.iconfinder.com/data/icons/strategy-management/512/mission-512.png':'https://media.giphy.com/media/gMq5BfFDVXi8M/giphy.gif'; ?>">
		<div><?php echo (isset($posted['Error']))?$error[$posted['Error']]:"No Response" ?></div>
	</body>
</html>