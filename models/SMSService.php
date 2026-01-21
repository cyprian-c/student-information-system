<?php
require_once __DIR__ . '/../config/sms_config.php';

class SMSService
{
    private $username;
    private $apiKey;
    private $senderId;
    private $apiUrl;

    public function __construct()
    {
        $this->username = AT_USERNAME;
        $this->apiKey = AT_API_KEY;
        $this->senderId = AT_SENDER_ID;
        $this->apiUrl = getATApiUrl();
    }

    /**
     * Send SMS to a phone number
     * 
     * @param string $phoneNumber - Format: +254700000000
     * @param string $message - SMS message (max 160 chars for 1 SMS)
     * @return array - Response from API
     */
    public function sendSMS($phoneNumber, $message)
    {
        if (!SMS_ENABLED) {
            $this->logSMS($phoneNumber, $message, 'SMS Disabled');
            return ['status' => 'disabled', 'message' => 'SMS service is disabled'];
        }

        // Format phone number
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        if (!$phoneNumber) {
            return ['status' => 'error', 'message' => 'Invalid phone number'];
        }

        // Prepare data
        $data = [
            'username' => $this->username,
            'to' => $phoneNumber,
            'message' => $message,
            'from' => $this->senderId
        ];

        // Make API request
        try {
            $response = $this->makeRequest('/messaging', $data);
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
        }
        return $results;
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If starts with 0, replace with +254
        if (substr($phone, 0, 1) === '0') {
            $phone = '+254' . substr($phone, 1);
        }

        // If doesn't start with +, add +254
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+254' . $phone;
        }

        // Validate length (should be +254XXXXXXXXX = 13 chars)
        if (strlen($phone) !== 13) {
            return false;
        }

        return $phone;
    }

    /**
     * Make HTTP request to Africa's Talking API
     */
    private function makeRequest($endpoint, $data)
    {
        $url = $this->apiUrl . $endpoint;

        $headers = [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . $this->apiKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception('API Error: ' . ($result['message'] ?? 'Unknown error'));
        }

        return $result;
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
     * Check SMS balance (for production)
     */
    public function checkBalance()
    {
        try {
            // This requires a different endpoint - implement if needed
            return ['status' => 'success', 'balance' => 'Check your dashboard'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
