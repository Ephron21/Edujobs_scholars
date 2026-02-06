<?php
// Check if SMS config exists, if not, define default values
if (!file_exists(__DIR__ . '/config/sms_config.php')) {
    // Default SMS API Configuration
    define('SMS_API_KEY', 'demo_api_key');
    define('SMS_API_SECRET', 'demo_api_secret');
    define('SMS_SENDER_ID', 'EDUSCHOOL');
    define('SMS_API_URL', 'https://api.example.com/sms/send');
    
    // Default rate limiting - maximum number of SMS per hour
    define('SMS_RATE_LIMIT', 100);
    
    // Default SMS Templates
    define('SMS_TEMPLATES', [
        'ATTENDANCE_ALERT' => 'Dear {parent_name}, your child {student_name} has been marked as {status} on {date}. Please contact the school if you need further information.',
        'STUDENT_CARD_READY' => 'Dear {parent_name}, the ID card for {student_name} has been generated and is ready for collection. Please visit the school office to collect it.',
        'GENERAL_ANNOUNCEMENT' => 'EduSchool: {message}',
    ]);
} else {
    require_once __DIR__ . '/config/sms_config.php';
}

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
        try {
            // Check if sms_logs table exists
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'sms_logs'
            ");
            $stmt->execute();
            $tableExists = (bool)$stmt->fetchColumn();
            
            // Create table if it doesn't exist
            if (!$tableExists) {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS sms_logs (
                        sms_id INT AUTO_INCREMENT PRIMARY KEY,
                        recipient_type VARCHAR(20) NOT NULL,
                        recipient_id INT NOT NULL,
                        phone_number VARCHAR(20) NOT NULL,
                        message TEXT NOT NULL,
                        status VARCHAR(10) NOT NULL DEFAULT 'Pending',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
            }
            
            // Now log the SMS
            $stmt = $this->db->prepare("
                INSERT INTO sms_logs (recipient_type, recipient_id, phone_number, message, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$recipientType, $recipientId, $phoneNumber, $message, $status]);
        } catch (PDOException $e) {
            // Silently fail - we don't want to break the application if logging fails
            error_log("SMS logging failed: " . $e->getMessage());
        }
    }

    private function checkRateLimit() {
        try {
            // Check if sms_logs table exists
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'sms_logs'
            ");
            $stmt->execute();
            $tableExists = (bool)$stmt->fetchColumn();
            
            if (!$tableExists) {
                // Table doesn't exist, rate limit not applied
                return true;
            }
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM sms_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['count'] < SMS_RATE_LIMIT;
        } catch (PDOException $e) {
            // In case of error, allow the SMS to be sent
            error_log("SMS rate limit check failed: " . $e->getMessage());
            return true;
        }
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