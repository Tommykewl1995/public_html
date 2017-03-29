<?php
class Utility
{
    const SHA256 = 'sha256';
    const SECRET = "l3NKVw06Tq6ud9cfetQcbvjk";
    public function verifyPaymentSignature($attributes)
    {
        $expectedSignature = $attributes['razorpay_signature'];
        $orderId = $attributes['razorpay_order_id'];
        $paymentId = $attributes['razorpay_payment_id'];
        $payload = $orderId . '|' . $paymentId;
        return self::verifySignature($payload, $expectedSignature);
    }
    public function verifySignature($payload, $expectedSignature)
    {
        $actualSignature = hash_hmac(self::SHA256, $payload, self::SECRET);
        // Use lang's built-in hash_equals if exists to mitigate timing attacks
        if (function_exists('hash_equals')){
            $verified = hash_equals($actualSignature, $expectedSignature);
        }else{
            $verified = $this->hashEquals($actualSignature, $expectedSignature);
        }
        return $verified;
    }
    private function hashEquals($actualSignature, $expectedSignature)
    {
        if (strlen($expectedSignature) === strlen($actualSignature))
        {
            $res = $expectedSignature ^ $actualSignature;
            $return = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--)
            {
                $return |= ord($res[$i]);
            }
            return ($return === 0);
        }
        return false;
    }
}
?>