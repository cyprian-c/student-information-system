<?php
require_once __DIR__ . '/../config/sms_config.php';

class SMSService
{
    private $partnerId;
    private $apiKey;
    private $shortcode;
    private $apiUrl;

    public function __construct()
    {
        $this->partnerId = ADVANTA_PARTNER_ID;
        $this->apiKey = ADVANTA_API_KEY;
        $this->shortcode = ADVANTA_SHORTCODE;
        $this->apiUrl = getAdvantaApiUrl();
    }

    /**
     * Send SMS to a phone number
     * 
     * @param string $phoneNumber - Format: 254700000000 (without +)
     * @param string $message - SMS message (max 160 chars for 1 SMS)
     * @return array - Response from API
     */
    public function sendSMS($phoneNumber, $message)
    {
        if (!SMS_ENABLED) {
            $this->logSMS($phoneNumber, $message, 'SMS Disabled');
            return ['status' => 'disabled', 'message' => 'SMS service is disabled'];
        }

        // Format phone number for Advanta (254XXXXXXXXX without +)
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        if (!$phoneNumber) {
            return ['status' => 'error', 'message' => 'Invalid phone number'];
        }

        // Prepare data for Advanta API
        $data = [
            'apikey' => $this->apiKey,
            'partnerID' => $this->partnerId,
            'message' => $message,
            'shortcode' => $this->shortcode,
            'mobile' => $phoneNumber
        ];

        // Make API request
        try {
            $response = $this->makeRequest($data);
            $this->logSMS($phoneNumber, $message, json_encode($response));
            return $response;
        } catch (Exception $e) {
            $this->logSMS($phoneNumber, $message, 'Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation($phoneNumber, $studentName, $amount, $receiptNumber, $balance)
    {
        $message = "Dear Parent,\n\n";
        $message .= "Payment of KSh " . number_format($amount, 2) . " received for " . $studentName . ".\n";
        $message .= "Receipt No: " . $receiptNumber . "\n";
        $message .= "Balance: KSh " . number_format($balance, 2) . "\n\n";
        $message .= "Thank you.\n" . SCHOOL_NAME;

        return $this->sendSMS($phoneNumber, $message);
    }

    /**
     * Send fee balance reminder SMS
     */
    public function sendBalanceReminder($phoneNumber, $studentName, $totalFee, $amountPaid, $balance, $dueDate)
    {
        $message = "Dear Parent,\n\n";
        $message .= "Fee reminder for " . $studentName . ":\n";
        $message .= "Total: KSh " . number_format($totalFee, 2) . "\n";
        $message .= "Paid: KSh " . number_format($amountPaid, 2) . "\n";
        $message .= "Balance: KSh " . number_format($balance, 2) . "\n";
        $message .= "Due: " . date('d/m/Y', strtotime($dueDate)) . "\n\n";
        $message .= "Please clear to avoid inconvenience.\n" . SCHOOL_NAME;

        return $this->sendSMS($phoneNumber, $message);
    }

    /**
     * Send bulk SMS to multiple recipients
     */
    public function sendBulkSMS($phoneNumbers, $message)
    {
        $results = [];
        foreach ($phoneNumbers as $number) {
            $results[] = $this->sendSMS($number, $message);
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }
        return $results;
    }

    /**
     * Format phone number for Advanta (254XXXXXXXXX without +)
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Remove + sign if present
        $phone = str_replace('+', '', $phone);

        // If starts with 0, replace with 254
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        // If doesn't start with 254, add it
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }

        // Validate length (should be 254XXXXXXXXX = 12 chars)
        if (strlen($phone) !== 12) {
            return false;
        }

        return $phone;
    }

    /**
     * Make HTTP request to Advanta SMS API
     */
    private function makeRequest($data)
    {
        $url = $this->apiUrl;

        // Advanta uses POST with JSON or form data
        // Check Advanta docs for exact format - this uses JSON
        $jsonData = json_encode($data);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Content-Length: ' . strlen($jsonData)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Curl error: ' . $error);
        }

        curl_close($ch);

        // Parse response
        $result = json_decode($response, true);

        // Check if response is valid
        if ($httpCode !== 200) {
            throw new Exception('API Error (HTTP ' . $httpCode . '): ' . ($result['message'] ?? $response));
        }

        // Advanta typically returns success/error in response
        if (isset($result['success']) && $result['success'] === false) {
            throw new Exception('SMS Failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'status' => 'success',
            'response' => $result,
            'message' => $result['message'] ?? 'SMS sent successfully'
        ];
    }

    /**
     * Log SMS activity
     */
    private function logSMS($phoneNumber, $message, $response)
    {
        // Create logs directory if it doesn't exist
        $logDir = dirname(SMS_LOG_FILE);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = date('Y-m-d H:i:s') . " | ";
        $logEntry .= "To: " . $phoneNumber . " | ";
        $logEntry .= "Message: " . substr($message, 0, 50) . "... | ";
        $logEntry .= "Response: " . $response . "\n";

        file_put_contents(SMS_LOG_FILE, $logEntry, FILE_APPEND);
    }

    /**
     * Check SMS balance (if Advanta provides this endpoint)
     */
    public function checkBalance()
    {
        // Implement if Advanta has a balance check API
        // Check Advanta documentation for the endpoint
        try {
            $balanceUrl = 'https://api.advantasms.com/api/services/getbalance';

            $data = [
                'apikey' => $this->apiKey,
                'partnerID' => $this->partnerId
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $balanceUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            return $result;
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get delivery report (if Advanta provides this)
     */
    public function getDeliveryReport($messageId)
    {
        // Implement if needed based on Advanta documentation
        return ['status' => 'info', 'message' => 'Check Advanta dashboard for delivery reports'];
    }
}
