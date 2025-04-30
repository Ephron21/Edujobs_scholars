CREATE TABLE IF NOT EXISTS student_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(50) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    conduct_score INT,
    
    -- Core Subjects
    kinyarwanda_score DECIMAL(5,2),
    kiswahili_score DECIMAL(5,2),
    literature_score DECIMAL(5,2),
    entrepreneurship_score DECIMAL(5,2),
    gsc_score DECIMAL(5,2),
    
    -- Non-Core Subjects
    english_score DECIMAL(5,2),
    french_score DECIMAL(5,2),
    ict_score DECIMAL(5,2),
    physical_education_score DECIMAL(5,2),
    religion_score DECIMAL(5,2),
    
    -- Additional Fields
    total_score DECIMAL(5,2),
    percentage DECIMAL(5,2),
    position VARCHAR(20),
    class_teacher_remarks TEXT,
    parent_signature BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 