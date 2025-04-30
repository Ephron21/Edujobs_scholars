<?php
require_once 'config/sms_config.php';

class SMSService {
    private $db;
    private $apiKey;
    private $apiSecret;
    private $senderId;
    private $apiUrl;

    public function __construct($db) {
        $this->db = $db;
        $this->apiKey = SMS_API_KEY;
        $this->apiSecret = SMS_API_SECRET;
        $this->senderId = SMS_SENDER_ID;
        $this->apiUrl = SMS_API_URL;
    }

    public function sendSMS($phoneNumber, $message, $recipientType, $recipientId = null) {
        // Check rate limiting
        if (!$this->checkRateLimit()) {
            return ['success' => false, 'message' => 'Rate limit exceeded'];
        }

        // Prepare API request
        $data = [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'sender_id' => $this->senderId,
            'to' => $phoneNumber,
            'message' => $message
        ];

        // Send SMS via API
        $response = $this->makeAPIRequest($data);

        // Log the SMS
        $this->logSMS($recipientType, $recipientId, $phoneNumber, $message, $response['status']);

        return $response;
    }

    private function makeAPIRequest($data) {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode === 200,
            'status' => $httpCode === 200 ? 'Sent' : 'Failed',
            'response' => json_decode($response, true)
        ];
    }

    private function logSMS($recipientType, $recipientId, $phoneNumber, $message, $status) {
        $stmt = $this->db->prepare("
            INSERT INTO sms_logs (recipient_type, recipient_id, phone_number, message, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$recipientType, $recipientId, $phoneNumber, $message, $status]);
    }

    private function checkRateLimit() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM sms_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] < SMS_RATE_LIMIT;
    }

    public function sendAttendanceAlert($studentId, $status, $date) {
        // Get student and parent details
        $stmt = $this->db->prepare("
            SELECT s.first_name, s.last_name, s.parent_name, s.parent_phone
            FROM students s
            WHERE s.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || !$student['parent_phone']) {
            return ['success' => false, 'message' => 'Student or parent details not found'];
        }

        // Prepare message
        $message = str_replace(
            ['{parent_name}', '{student_name}', '{status}', '{date}'],
            [$student['parent_name'], $student['first_name'] . ' ' . $student['last_name'], $status, $date],
            SMS_TEMPLATES['ATTENDANCE_ALERT']
        );

        return $this->sendSMS($student['parent_phone'], $message, 'Student', $studentId);
    }

    public function sendStudentCardNotification($studentId) {
        // Get student and parent details
        $stmt = $this->db->prepare("
            SELECT s.first_name, s.last_name, s.parent_name, s.parent_phone
            FROM students s
            WHERE s.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || !$student['parent_phone']) {
            return ['success' => false, 'message' => 'Student or parent details not found'];
        }

        // Prepare message
        $message = str_replace(
            ['{parent_name}', '{student_name}'],
            [$student['parent_name'], $student['first_name'] . ' ' . $student['last_name']],
            SMS_TEMPLATES['STUDENT_CARD_READY']
        );

        return $this->sendSMS($student['parent_phone'], $message, 'Student', $studentId);
    }
} 