<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Require Composer's autoload file
require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);

  // Validate email
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Please enter a valid email address.";
    exit;
  }

  // Send notification email via PHPMailer
  $mail = new PHPMailer(true);

  try {
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['SMTP_PORT'];

    $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($_ENV['SMTP_FROM_EMAIL'], 'Newsletter Admin');
    $mail->addReplyTo($email, 'Newsletter Subscriber');
    $mail->Subject = "New Newsletter Subscription";
    $mail->isHTML(true);
    $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Newsletter Subscription</title>
<style>
  body {
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    background: #f6f8fa;
    padding: 0; margin: 0;
    color: #24292f;
  }
  .container {
    max-width: 560px;
    margin: 32px auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(30,34,90,0.09);
    padding: 32px 24px 24px 24px;
  }
  .header {
    font-size: 1.7em;
    font-weight: 700;
    color: #0077c5;
    margin-bottom: 10px;
  }
  hr {
    border: 0;
    border-top: 2px solid #1CA8DD;
    margin: 0 0 24px 0;
  }
  .info-table {
    width: 100%;
    border-collapse: collapse;
  }
  .info-table td {
    padding: 12px 0;
    vertical-align: top;
    font-size: 16px;
  }
  .label {
    font-weight: 600;
    color: #0F4C81;
    width: 120px;
  }
  .value {
    color: #24292f;
  }
  .email-link { 
    color: #0077c5; 
    text-decoration: underline; 
    font-size: 16px;
  }
  .timestamp {
    color: #656d76;
    font-size: 14px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e1e4e8;
  }
</style>
</head>
<body>
  <div class="container">
    <div class="header">🎉 New Newsletter Subscription</div>
    <hr>
    <table class="info-table">
      <tr>
        <td class="label">Email:</td>
        <td class="value">
          <a href="mailto:' . htmlspecialchars($email) . '" class="email-link">' . htmlspecialchars($email) . '</a>
        </td>
      </tr>
      <tr>
        <td class="label">Subscribed:</td>
        <td class="value">' . date('F j, Y \a\t g:i A') . '</td>
      </tr>
    </table>
    <div class="timestamp">
      This subscriber has opted in to receive your newsletter updates.
    </div>
  </div>
</body>
</html>
';

    $mail->send();

    // Return success response
    http_response_code(200);
    echo "Thank you for subscribing to our newsletter!";
  } catch (Exception $e) {
    http_response_code(500);
    echo "Subscription failed. Please try again later.";
    exit;
  }
} else {
  http_response_code(403);
  echo "Invalid request method.";
}
