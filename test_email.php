<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load configuration
$config = require __DIR__ . '/config.php';

try {
    $mail = new PHPMailer(true);

    // Server settings
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = $config['email']['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['email']['username'];
    $mail->Password = $config['email']['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['email']['port'];

    // Recipients
    $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
    $mail->addAddress($config['email']['username']); // Send test email to yourself

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Allowance Tracker - Test Email';
    $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #2563eb;">Email Configuration Test</h2>
            <p>This is a test email from your Allowance Tracker application.</p>
            <p>If you received this email, your email configuration is working correctly!</p>
            <p>The following features will now be enabled:</p>
            <ul>
                <li>Task completion notifications to parents</li>
                <li>Task verification notifications to children</li>
                <li>Weekly allowance payout notifications</li>
            </ul>
        </div>';

    $mail->send();
    echo "Test email sent successfully!\n";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}
