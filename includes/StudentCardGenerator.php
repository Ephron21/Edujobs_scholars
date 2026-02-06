<?php
require_once 'SMSService.php';

// Simple FPDF implementation for mock purposes
class FPDF {
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        // Basic initialization
    }

    public function AddPage() {
        // Basic page addition
    }

    public function SetFillColor($r, $g, $b) {
        // Basic color setting
    }

    public function Rect($x, $y, $w, $h, $style = '') {
        // Basic rectangle drawing
    }

    public function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '') {
        // Basic image handling
    }

    public function SetFont($family, $style = '', $size = 0) {
        // Basic font setting
    }

    public function SetXY($x, $y) {
        // Basic position setting
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        // Basic cell drawing
    }

    public function Output($dest = '', $name = '', $isUTF8 = false) {
        // Basic output handling - in our mock version, we'll just return success
        return true;
    }
}

class StudentCardGenerator {
    private $db;
    private $smsService;
    private $cardWidth = 85.6; // Width in mm (ID-1 card size)
    private $cardHeight = 53.98; // Height in mm (ID-1 card size)
    private $schoolName = 'EduJobs Scholars Academy';
    private $schoolLogo = 'assets/img/school-logo.png';

    public function __construct($db) {
        $this->db = $db;
        $this->smsService = new SMSService($db);
    }

    public function generateCard($studentId) {
        try {
            // Check if student exists
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'students'
            ");
            $stmt->execute();
            $tableExists = (bool)$stmt->fetchColumn();
            
            if (!$tableExists) {
                return ['success' => false, 'message' => 'Students table does not exist. Please run database setup first.'];
            }
            
            // Get student data
            $stmt = $this->db->prepare("
                SELECT * FROM students WHERE student_id = ?
            ");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student) {
                return ['success' => false, 'message' => 'Student not found'];
            }
            
            // Check if student_cards table exists
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'student_cards'
            ");
            $stmt->execute();
            $tableExists = (bool)$stmt->fetchColumn();
            
            // Create table if it doesn't exist
            if (!$tableExists) {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS student_cards (
                        card_id INT AUTO_INCREMENT PRIMARY KEY,
                        student_id INT NOT NULL,
                        card_number VARCHAR(50) UNIQUE NOT NULL,
                        issue_date DATE NOT NULL,
                        expiry_date DATE NOT NULL,
                        status ENUM('Active', 'Expired', 'Lost', 'Suspended') NOT NULL DEFAULT 'Active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
            }
            
            // Generate card number
            $cardNumber = 'EDUSC-' . date('Y') . '-' . str_pad($studentId, 5, '0', STR_PAD_LEFT);
            
            // Set issue and expiry dates
            $issueDate = date('Y-m-d');
            $expiryDate = date('Y-m-d', strtotime('+1 year'));
            
            // Check if card already exists
            $stmt = $this->db->prepare("
                SELECT card_id FROM student_cards WHERE student_id = ? AND status = 'Active'
            ");
            $stmt->execute([$studentId]);
            $existingCard = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingCard) {
                // Update existing card
                $stmt = $this->db->prepare("
                    UPDATE student_cards 
                    SET issue_date = ?, expiry_date = ?, updated_at = NOW()
                    WHERE card_id = ?
                ");
                $stmt->execute([$issueDate, $expiryDate, $existingCard['card_id']]);
                $cardId = $existingCard['card_id'];
            } else {
                // Create new card record
                $stmt = $this->db->prepare("
                    INSERT INTO student_cards (student_id, card_number, issue_date, expiry_date)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$studentId, $cardNumber, $issueDate, $expiryDate]);
                $cardId = $this->db->lastInsertId();
            }
            
            // In a real application, we would generate a PDF here
            // For this mock implementation, we'll just return success
            
            // Send SMS notification
            try {
                $this->smsService->sendStudentCardNotification($studentId);
            } catch (Exception $e) {
                // Log but continue - SMS failure shouldn't stop card generation
                error_log("SMS notification failed: " . $e->getMessage());
            }
            
            return [
                'success' => true, 
                'message' => 'Student card generated successfully',
                'card_id' => $cardId,
                'card_number' => $cardNumber,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error generating card: ' . $e->getMessage()];
        }
    }
}