<?php
// SMS API Configuration
define('SMS_API_KEY', 'your_api_key_here');
define('SMS_API_SECRET', 'your_api_secret_here');
define('SMS_SENDER_ID', 'EDUSCHOOL');
define('SMS_API_URL', 'https://api.example.com/sms/send');

// Rate limiting - maximum number of SMS per hour
define('SMS_RATE_LIMIT', 100);

// SMS Templates
define('SMS_TEMPLATES', [
    'ATTENDANCE_ALERT' => 'Dear {parent_name}, your child {student_name} has been marked as {status} on {date}. Please contact the school if you need further information.',
    'STUDENT_CARD_READY' => 'Dear {parent_name}, the ID card for {student_name} has been generated and is ready for collection. Please visit the school office to collect it.',
    'GENERAL_ANNOUNCEMENT' => 'EduSchool: {message}',
]);
?> 