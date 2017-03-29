<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');
//$json=$_GET ['json'];
require('db_config.php');

$json = file_get_contents('php://input');
$obj = json_decode($json, true);
// date_default_timezone_set("Asia/Kolkata");

try{
  $k = "Address_failure,e314;Address_invalid,e304;Amount_difference,e702;Authentication_error,e303;Authentication_incomplete,e335;Authentication_service_unavailable,e334;Awaiting_processing,e505;Bank_denied,e312;Bank_server_error,e208;Batch_error,e216;Brand_invalid,e201;Card_fraud_suspected,e324;Card_issuer_timed_out,e218;Card_not_enrolled,e900;Card_number_invalid,e305;Checksum_failure,e213;Communication_error,e210;Curl_call_failure,e214;Curl_error_card_verification,e203;Curl_error_enrolled,e205;Curl_error_not_enrolled,e204;Cutoff_error,e206;Cvc_address_failure,e315;Cvc_failure,e313;Duplicate_transaction,e504;Expired_card,e311;Expiry_date_low_funds,e336;Incomplete_bank_response,e219;Incomplete_data,e712;Insufficient_funds,e706;Insufficient_funds_authentication_failure,e719;Insufficient_funds_expiry_invalid,e713;Insufficient_funds_invalid_cvv,e718;International_card_not_allowed,e903;Invalid_account_number,e717;Invalid_amount,e715;Invalid_card_name,e709;Invalid_card_type,e902;Invalid_contact,e333;Invalid_email_id,e331;Invalid_expiry_date,e323;Invalid_fax,e332;Invalid_login,e327;Invalid_pan,e707;Invalid_pin,e710;Invalid_transaction_type,e207;Invalid_user_defined_data,e711;Invalid_zip,e714;Issuer_declined_low_funds,e329;Lost_card,e310;Merchant_invalid_pg,e200;Network_error,e211;No_bank_response,e209;No_error,e000;Not_captured,e337;Parameters_mismatch,e328;Password_error,e326;Payment_gateway_validation_failure,e330;PayUMoney_api_error,e600;Permitted_bank_settings_error,e716;Pin_retries_exceeded,e708;Prefered_gateway_not_set,e800;Receipt_number_error,e704;Reserved_usage_error,e215;Restricted_card,e325;Retry_limit_exceeded,e901;Risk_denied_pg,e307;Secure_3d_authentication_error,e317;Secure_3d_cancelled,e302;Secure_3d_card_type,e322;Secure_3d_format_error,e319;Secure_3d_incorrect,e301;Secure_3d_not_enrolled,e316;Secure_3d_not_supported,e318;Secure_3d_password_error,e300;Secure_3d_server_error,e321;Secure_3d_signature_error,e320;Secure_hash_failure,e700;Secure_hash_skipped,e701;Server_communication_error,e212;System_error_pg,e309;Tranportal_id_error,e217;Transaction_aborted,e502;Transaction_cancelled,e503;Transaction_failed,e308;Transaction_invalid,e202;Transaction_invalid_pg,e306;Transaction_number_error,e703;Unknown_error,e501;Unknown_error_pg,e500;User_profile_settings_error,e705";
$array = explode(';', $k);
$seq = '';
foreach($array as $arr){
  $temp = explode(",", $arr);
  $seq.="'".$temp[1]."' => '".$temp[0]."', ";
}
echo $seq;
}catch(PDOException $ex){
  $response['ResponseCode'] = "500";
    $response['ResponseMessage'] = "An Error occured!" . $ex; //user friendly message
    $status['Status'] = $response;
    header('Content-type: application/json');
  echo json_encode($status);
}
