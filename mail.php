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
  $recaptcha_token = $_POST['recaptcha_token'];
  $url = 'https://www.google.com/recaptcha/api/siteverify';
  $data = array(
    'secret' => '6LecHbcrAAAAACPZnLi6zBh-_3SE3vzMWbzRazy_',
    'response' => $recaptcha_token
  );

  $options = array(
    'http' => array(
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query($data)
    )
  );
  $context  = stream_context_create($options);
  $verify = file_get_contents($url, false, $context);
  $captcha_success = json_decode($verify);

  if ($captcha_success->success == false) {
    echo "<p>You are a bot! Go away!</p>";
  } else if ($captcha_success->success == true) {
    $name = strip_tags(trim($_POST["name"]));
    $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
    $organisation = strip_tags(trim($_POST["organisation"]));
    // $lastname = str_replace(array("\r", "\n"), array(" ", " "), $lastname);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    // $website = isset($_POST["website"]) && !empty(trim($_POST["website"])) ? trim($_POST["website"]) : "";
    // $subject =  trim($_POST["selected"]);
    $message = trim($_POST["message"]);
    $mobile = trim($_POST["mobile"]);

    // Validate fields
    if (empty($name) || empty($mobile) || empty($organisation)  || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
      echo "Please complete the form and try again.";
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
      $mail->Subject = "New Enquiry";
      $mail->isHTML(true);
      $mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Enquiry Details</title>
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
    padding: 7px 0;
    vertical-align: top;
    font-size: 15px;
  }
  .label {
    font-weight: 600;
    color: #0F4C81;
    width: 120px;
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
</style>
</head>
<body>
  <div class="container">
    <div class="header">New Enquiry Details</div>
    <hr>
    <table class="info-table">
      <tr>
        <td class="label">Name:</td>
        <td class="value">' . htmlspecialchars($name) . '</td>
      </tr>
      <tr>
        <td class="label">Email:</td>
        <td class="value"><a href="mailto:' . htmlspecialchars($email) . '" class="email-link">' . htmlspecialchars($email) . '</a></td>
      </tr>
      <tr>
        <td class="label">Mobile:</td>
        <td class="value">' . htmlspecialchars($mobile) . '</td>
      </tr>
      <tr>
        <td class="label">Organisation:</td>
        <td class="value">' . htmlspecialchars($organisation) . '</td>
      </tr>
      <tr>
        <td class="label">Message:</td>
        <td class="value"><div class="message-box">' . nl2br(htmlspecialchars($message)) . '</div></td>
      </tr>
    </table>
  </div>
</body>
</html>
';


      $mail->isHTML(true); // Ensure HTML format is enabled

      $mail->send();

      http_response_code(200);
      echo "Your inquiry has been submitted. We appreciate your interest and will respond soon.";
    } catch (Exception $e) {
      http_response_code(500);
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      exit;
    }
  } else {
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
  }
}
