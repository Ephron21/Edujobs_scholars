<?php
require_once 'SMSService.php';

class AttendanceManager {
    private $db;
    private $smsService;

    public function __construct($db) {
        $this->db = $db;
        $this->smsService = new SMSService($db);
    }

    public function markStudentAttendance($studentId, $status, $date = null, $remarks = '') {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        // Check if attendance already marked for the day
        $stmt = $this->db->prepare("
            SELECT attendance_id 
            FROM attendance 
            WHERE student_id = ? AND date = ?
        ");
        $stmt->execute([$studentId, $date]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Attendance already marked for this date'];
        }

        // Mark attendance
        $stmt = $this->db->prepare("
            INSERT INTO attendance (student_id, date, status, remarks)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $date, $status, $remarks]);

        // Send SMS notification to parent if student is absent
        if ($status === 'Absent') {
            $this->smsService->sendAttendanceAlert($studentId, $status, $date);
        }

        return ['success' => true, 'message' => 'Attendance marked successfully'];
    }

    public function markTeacherAttendance($teacherId, $status, $date = null, $remarks = '') {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        // Check if attendance already marked for the day
        $stmt = $this->db->prepare("
            SELECT attendance_id 
            FROM attendance 
            WHERE teacher_id = ? AND date = ?
        ");
        $stmt->execute([$teacherId, $date]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Attendance already marked for this date'];
        }

        // Mark attendance
        $stmt = $this->db->prepare("
            INSERT INTO attendance (teacher_id, date, status, remarks)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$teacherId, $date, $status, $remarks]);

        return ['success' => true, 'message' => 'Attendance marked successfully'];
    }

    public function getStudentAttendance($studentId, $startDate = null, $endDate = null) {
        if ($startDate === null) {
            $startDate = date('Y-m-01'); // First day of current month
        }
        if ($endDate === null) {
            $endDate = date('Y-m-t'); // Last day of current month
        }

        $stmt = $this->db->prepare("
            SELECT a.*, s.first_name, s.last_name
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            WHERE a.student_id = ? AND a.date BETWEEN ? AND ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$studentId, $startDate, $endDate]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherAttendance($teacherId, $startDate = null, $endDate = null) {
        if ($startDate === null) {
            $startDate = date('Y-m-01'); // First day of current month
        }
        if ($endDate === null) {
            $endDate = date('Y-m-t'); // Last day of current month
        }

        $stmt = $this->db->prepare("
            SELECT a.*, t.first_name, t.last_name
            FROM attendance a
            JOIN teachers t ON a.teacher_id = t.teacher_id
            WHERE a.teacher_id = ? AND a.date BETWEEN ? AND ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$teacherId, $startDate, $endDate]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClassAttendance($classId, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $stmt = $this->db->prepare("
            SELECT s.student_id, s.first_name, s.last_name, s.admission_number,
                   a.status, a.remarks
            FROM students s
            LEFT JOIN attendance a ON s.student_id = a.student_id 
                AND a.date = ?
            WHERE s.class_id = ?
            ORDER BY s.first_name, s.last_name
        ");
        $stmt->execute([$date, $classId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceSummary($startDate = null, $endDate = null) {
        if ($startDate === null) {
            $startDate = date('Y-m-01'); // First day of current month
        }
        if ($endDate === null) {
            $endDate = date('Y-m-t'); // Last day of current month
        }

        // Get student attendance summary
        $stmt = $this->db->prepare("
            SELECT 
                s.class_id,
                c.class_name,
                COUNT(DISTINCT s.student_id) as total_students,
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late_count
            FROM students s
            JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN attendance a ON s.student_id = a.student_id 
                AND a.date BETWEEN ? AND ?
            GROUP BY s.class_id, c.class_name
        ");
        $stmt->execute([$startDate, $endDate]);
        $studentSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get teacher attendance summary
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT t.teacher_id) as total_teachers,
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late_count
            FROM teachers t
            LEFT JOIN attendance a ON t.teacher_id = a.teacher_id 
                AND a.date BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $teacherSummary = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'student_summary' => $studentSummary,
            'teacher_summary' => $teacherSummary
        ];
    }
} 