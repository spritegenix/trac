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

// // Set content type for JSON response
// header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize and validate input
  $name = strip_tags(trim($_POST["name"]));
  $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
  $organisation = strip_tags(trim($_POST["organisation"]));
  $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
  $mobile = trim($_POST["mobile"]);
  $message = trim($_POST["message"]);

  // Optional fields
  $designation = isset($_POST["designation"]) ? strip_tags(trim($_POST["designation"])) : "";
  $alternateEmail = isset($_POST["alternateEmail"]) ? filter_var(trim($_POST["alternateEmail"]), FILTER_SANITIZE_EMAIL) : "";
  $alternatePhone = isset($_POST["alternatePhone"]) ? trim($_POST["alternatePhone"]) : "";
  $users = isset($_POST["users"]) ? trim($_POST["users"]) : "";
  $city = isset($_POST["city"]) ? strip_tags(trim($_POST["city"])) : "";
  $state = isset($_POST["state"]) ? strip_tags(trim($_POST["state"])) : "";
  $address = isset($_POST["address"]) ? strip_tags(trim($_POST["address"])) : "";
  $subscribe = isset($_POST["subscribe"]) ? ($_POST["subscribe"] === '1') : false;

  // Validate required fields
  if (empty($name) || empty($mobile) || empty($organisation) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Please complete all required fields and provide a valid email address.";
    exit;
  }

  // Send email via PHPMailer
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
    $mail->addAddress($_ENV['SMTP_FROM_EMAIL'], 'Admin');
    $mail->addReplyTo($email, $name);
    $mail->isHTML(true);
    $mail->Subject = "New Partnership Inquiry - " . $organisation;
    $mail->isHTML(true);

    // Build additional info section
    $additionalInfo = '';
    if ($designation) $additionalInfo .= '<tr><td class="label">Designation:</td><td class="value">' . htmlspecialchars($designation) . '</td></tr>';
    if ($alternateEmail) $additionalInfo .= '<tr><td class="label">Alternate Email:</td><td class="value"><a href="mailto:' . htmlspecialchars($alternateEmail) . '" class="email-link">' . htmlspecialchars($alternateEmail) . '</a></td></tr>';
    if ($alternatePhone) $additionalInfo .= '<tr><td class="label">Alternate Phone:</td><td class="value">' . htmlspecialchars($alternatePhone) . '</td></tr>';
    if ($users) $additionalInfo .= '<tr><td class="label">Number of Users:</td><td class="value">' . htmlspecialchars($users) . '</td></tr>';
    if ($city) $additionalInfo .= '<tr><td class="label">City:</td><td class="value">' . htmlspecialchars($city) . '</td></tr>';
    if ($state) $additionalInfo .= '<tr><td class="label">State:</td><td class="value">' . htmlspecialchars($state) . '</td></tr>';
    if ($address) $additionalInfo .= '<tr><td class="label">Address:</td><td class="value">' . nl2br(htmlspecialchars($address)) . '</td></tr>';

    $subscribeStatus = $subscribe ? 'Yes' : 'No';

    $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Partnership Inquiry</title>
<style>
  body {
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    background: #f6f8fa;
    padding: 0; margin: 0;
    color: #24292f;
  }
  .container {
    max-width: 600px;
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
    padding: 7px 0;
    vertical-align: top;
    font-size: 15px;
  }
  .label {
    font-weight: 600;
    color: #0F4C81;
    width: 140px;
  }
  .value {
    color: #24292f;
  }
  .email-link { color: #0077c5; text-decoration: underline; }
  .message-box {
    margin-top: 12px;
    background: #f3f3f3;
    border-radius: 5px;
    padding: 12px 16px;
    font-size: 16px;
    color: #454545;
    border: 1px solid #e1e4e8;
    min-height: 38px;
    line-height: 1.6;
    font-family: inherit;
    white-space: pre-line;
  }
  .section-title {
    font-size: 1.2em;
    font-weight: 600;
    color: #0F4C81;
    margin: 24px 0 12px 0;
  }
</style>
</head>
<body>
  <div class="container">
    <div class="header">New Partnership Inquiry</div>
    <hr>
    
    <div class="section-title">Contact Information</div>
    <table class="info-table">
      <tr>
        <td class="label">Name:</td>
        <td class="value">' . htmlspecialchars($name) . '</td>
      </tr>
      <tr>
        <td class="label">Organization:</td>
        <td class="value">' . htmlspecialchars($organisation) . '</td>
      </tr>
      <tr>
        <td class="label">Email:</td>
        <td class="value"><a href="mailto:' . htmlspecialchars($email) . '" class="email-link">' . htmlspecialchars($email) . '</a></td>
      </tr>
      <tr>
        <td class="label">Mobile:</td>
        <td class="value">' . htmlspecialchars($mobile) . '</td>
      </tr>
      ' . $additionalInfo . '
      <tr>
        <td class="label">Newsletter:</td>
        <td class="value">' . $subscribeStatus . '</td>
      </tr>
    </table>
    
    ' . ($message ? '<div class="section-title">Requirements</div>
    <div class="message-box">' . nl2br(htmlspecialchars($message)) . '</div>' : '') . '
  </div>
</body>
</html>
';

    $mail->send();

    http_response_code(200);
    echo "Email sent successfully";
  } catch (Exception $e) {
    http_response_code(500);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    exit;
  }
} else {
  http_response_code(405);
  echo "Method not allowed. Please use POST.";
}
