<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get input from command line
function getInput($prompt) {
    fwrite(STDOUT, $prompt . ": ");
    return trim(fgets(STDIN));
}

function testEmailSettings($settings) {
    try {
        $mail = new PHPMailer(true);

        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = $settings['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['username'];
        $mail->Password = $settings['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $settings['port'];

        $mail->setFrom($settings['from_email'], $settings['from_name']);
        $mail->addAddress($settings['test_recipient']);

        $mail->isHTML(true);
        $mail->Subject = 'Allowance Tracker - Test Email';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #2563eb;">Email Configuration Test</h2>
                <p>This is a test email from your Allowance Tracker application.</p>
                <p>If you received this email, your email configuration is working correctly!</p>
            </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "\nMailer Error: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n=== Allowance Tracker Email Setup ===\n\n";

// Get Gmail credentials
do {
    $gmail = getInput("Enter your Gmail address");
    if (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        echo "Error: Invalid email address. Please enter a valid Gmail address.\n";
    }
} while (!filter_var($gmail, FILTER_VALIDATE_EMAIL));

do {
    $appPassword = getInput("Enter your 16-character App Password (the one that looks like: xxxx xxxx xxxx xxxx)");
    $appPassword = str_replace(' ', '', $appPassword);
    if (strlen($appPassword) !== 16) {
        echo "Error: App Password must be exactly 16 characters (excluding spaces). Please try again.\n";
    }
} while (strlen($appPassword) !== 16);

$settings = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => $gmail,
    'password' => $appPassword,
    'from_email' => $gmail,
    'from_name' => 'Allowance Tracker',
    'test_recipient' => $gmail
];

echo "\nTesting email settings with:\n";
echo "Email: " . $settings['username'] . "\n";
echo "SMTP Host: " . $settings['host'] . "\n";
echo "SMTP Port: " . $settings['port'] . "\n\n";

if (testEmailSettings($settings)) {
    echo "\nEmail test successful! A test email has been sent to {$settings['test_recipient']}\n";
    
    // Update config.php
    $configFile = __DIR__ . '/config.php';
    $config = require $configFile;
    
    $config['email'] = [
        'host' => $settings['host'],
        'port' => $settings['port'],
        'username' => $settings['username'],
        'password' => $settings['password'],
        'from_email' => $settings['from_email'],
        'from_name' => $settings['from_name'],
        'encryption' => 'tls'
    ];
    
    file_put_contents($configFile, '<?php return ' . var_export($config, true) . ';');
    
    echo "\nConfiguration has been updated in config.php\n";
    echo "\nEmail notifications are now enabled!\n";
} else {
    echo "\nEmail test failed. Please check your settings and try again.\n";
    echo "Common issues:\n";
    echo "1. Incorrect App Password - make sure to enter it exactly as shown by Google\n";
    echo "2. Gmail account security settings blocking access\n";
    echo "3. Network connectivity issues\n";
}