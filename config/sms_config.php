<?php
// SMS Configuration
define('SMS_API_KEY', 'YOUR_API_KEY');
define('SMS_API_SECRET', 'YOUR_API_SECRET');
define('SMS_SENDER_ID', 'YOUR_SENDER_ID');
define('SMS_API_URL', 'https://api.smsprovider.com/v1/send');

// SMS Templates
define('SMS_TEMPLATES', [
    'ATTENDANCE_ALERT' => 'Dear {parent_name}, {student_name} was {status} on {date}.',
    'STUDENT_CARD_READY' => 'Dear {parent_name}, {student_name}\'s ID card is ready for collection.',
    'TEACHER_ATTENDANCE' => 'Dear {teacher_name}, please mark your attendance for today.',
    'GENERAL_ANNOUNCEMENT' => 'School Announcement: {message}'
]);

// SMS Rate Limiting
define('SMS_RATE_LIMIT', 100); // Maximum SMS per hour
define('SMS_COOLDOWN', 3600); // Cooldown period in seconds 