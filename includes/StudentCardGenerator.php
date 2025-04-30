<?php
// Simple FPDF implementation
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
        // Basic output handling
    }
}

class StudentCardGenerator extends FPDF {
    private $schoolName;
    private $schoolLogo;
    private $cardWidth = 85.6; // Width in mm (ID-1 card size)
    private $cardHeight = 53.98; // Height in mm (ID-1 card size)

    public function __construct($schoolName, $schoolLogo = null) {
        parent::__construct('L', 'mm', array($this->cardWidth, $this->cardHeight));
        $this->schoolName = $schoolName;
        $this->schoolLogo = $schoolLogo;
    }

    public function generateCard($studentData) {
        $this->AddPage();
        
        // Set background color
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, $this->cardWidth, $this->cardHeight, 'F');
        
        // Add school logo if provided
        if ($this->schoolLogo && file_exists($this->schoolLogo)) {
            $this->Image($this->schoolLogo, 5, 5, 20);
        }
        
        // School name
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(30, 5);
        $this->Cell(50, 5, $this->schoolName, 0, 1, 'L');
        
        // Student photo
        if (isset($studentData['photo_path']) && file_exists($studentData['photo_path'])) {
            $this->Image($studentData['photo_path'], $this->cardWidth - 25, 5, 20);
        }
        
        // Student information
        $this->SetFont('Arial', '', 8);
        $this->SetXY(5, 15);
        
        $this->Cell(20, 5, 'Name:', 0, 0);
        $this->Cell(50, 5, $studentData['first_name'] . ' ' . $studentData['last_name'], 0, 1);
        
        $this->Cell(20, 5, 'Admission No:', 0, 0);
        $this->Cell(50, 5, $studentData['admission_number'], 0, 1);
        
        $this->Cell(20, 5, 'Class:', 0, 0);
        $this->Cell(50, 5, $studentData['class'], 0, 1);
        
        $this->Cell(20, 5, 'Section:', 0, 0);
        $this->Cell(50, 5, $studentData['section'], 0, 1);
        
        // Card number
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(5, $this->cardHeight - 10);
        $this->Cell(30, 5, 'Card No: ' . $studentData['card_number'], 0, 0);
        
        // Add barcode if available
        if (isset($studentData['barcode'])) {
            $this->SetXY($this->cardWidth - 30, $this->cardHeight - 10);
            $this->Cell(25, 5, $studentData['barcode'], 0, 0, 'R');
        }
    }

    public function outputCard($filename = 'student_card.pdf') {
        $this->Output('F', $filename);
        return $filename;
    }
}