<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Faqat POST so\'rovlariga ruxsat berilgan']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['firstName', 'lastName', 'email', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $errors[] = ucfirst($field) . ' maydoni to\'ldirilishi shart';
    }
}

// Validate email format
if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email manzili noto\'g\'ri formatda';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sanitize input data
$firstName = htmlspecialchars(trim($input['firstName']), ENT_QUOTES, 'UTF-8');
$lastName = htmlspecialchars(trim($input['lastName']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(trim($input['message']), ENT_QUOTES, 'UTF-8');

// Email configuration
$to = 'info@orzutech.uz';
$subject = 'Yangi fikr-mulohaza - Orzutech veb-saytidan';

// Create email content
$email_content = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #ef7918; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #ef7918; }
        .value { margin-top: 5px; padding: 10px; background-color: white; border-left: 4px solid #ef7918; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Yangi fikr-mulohaza</h2>
            <p>Orzutech veb-saytidan</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Ism:</div>
                <div class='value'>{$firstName}</div>
            </div>
            <div class='field'>
                <div class='label'>Familiya:</div>
                <div class='value'>{$lastName}</div>
            </div>
            <div class='field'>
                <div class='label'>Email manzili:</div>
                <div class='value'>{$email}</div>
            </div>
            <div class='field'>
                <div class='label'>Xabar:</div>
                <div class='value'>{$message}</div>
            </div>
        </div>
        <div class='footer'>
            <p>Bu xabar Orzutech veb-saytining fikr-mulohaza formasi orqali yuborilgan</p>
            <p>Sana: " . date('Y-m-d H:i:s') . "</p>
        </div>
    </div>
</body>
</html>
";

// Email headers
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: Orzutech Website <noreply@orzutech.uz>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'X-Priority: 3',
    'X-MSMail-Priority: Normal'
];

// Send email
$mail_sent = mail($to, $subject, $email_content, implode("\r\n", $headers));

if ($mail_sent) {
    // Log successful submission (optional)
    $log_entry = date('Y-m-d H:i:s') . " - Feedback sent from: {$firstName} {$lastName} ({$email})\n";
    file_put_contents('feedback_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Xabaringiz muvaffaqiyatli yuborildi! Tez orada javob beramiz.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Xabar yuborishda xatolik yuz berdi. Iltimos, qaytadan urinib ko\'ring.'
    ]);
}
?>
