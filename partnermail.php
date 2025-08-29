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

// Enable error logging for debugging
error_log("POST data received: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if reCAPTCHA token is provided
  if (!isset($_POST['recaptcha_token']) || empty($_POST['recaptcha_token'])) {
    http_response_code(400);
    error_log("reCAPTCHA token missing from POST data");
    echo "reCAPTCHA token missing. Please try again.";
    exit;
  }

  $recaptcha_token = $_POST['recaptcha_token'];
  error_log("reCAPTCHA token received: " . substr($recaptcha_token, 0, 20) . "...");

  $url = 'https://www.google.com/recaptcha/api/siteverify';
  $data = array(
    'secret' => '6LecHbcrAAAAACPZnLi6zBh-_3SE3vzMWbzRazy_', // Move this to .env file
    'response' => $recaptcha_token
  );

  $options = array(
    'http' => array(
      'method' => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => http_build_query($data)
    )
  );

  $context = stream_context_create($options);
  $verify = file_get_contents($url, false, $context);
  $captcha_success = json_decode($verify);

  error_log("reCAPTCHA verification response: " . print_r($captcha_success, true));

  if (!$captcha_success || $captcha_success->success == false) {
    http_response_code(400);
    $error_msg = "reCAPTCHA verification failed.";
    if (isset($captcha_success->{'error-codes'})) {
      $error_msg .= " Errors: " . implode(', ', $captcha_success->{'error-codes'});
    }
    error_log("reCAPTCHA verification failed: " . $error_msg);
    echo $error_msg;
    exit;
  }

  // Sanitize and validate input
  $name = strip_tags(trim($_POST["name"]));
  $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
  $organisation = strip_tags(trim($_POST["organisation"]));
  $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
  $mobile = trim($_POST["mobile"]);
  $message = trim($_POST["message"]);

  // Optional fields
  $designation = isset($_POST["designation"]) ? strip_tags(trim($_POST["designation"])) : "";
  $users = isset($_POST["users"]) ? trim($_POST["users"]) : "";
  $city = isset($_POST["city"]) ? strip_tags(trim($_POST["city"])) : "";
  $state = isset($_POST["state"]) ? strip_tags(trim($_POST["state"])) : "";
  $address = isset($_POST["address"]) ? strip_tags(trim($_POST["address"])) : "";
  $subscribe = isset($_POST["subscribe"]) ? ($_POST["subscribe"] === '1') : false;

  // Validate required fields
  if (empty($name) || empty($mobile) || empty($organisation) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
    $mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            color: #2c5aa0;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 10px;
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
        }
        .field-row {
            margin: 15px 0;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .field-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #555555;
            display: inline-block;
            width: 150px;
            vertical-align: top;
        }
        .value {
            color: #333333;
            display: inline-block;
            max-width: 400px;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            color: #666666;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h3 class='header'>New Partnership Inquiry</h3>
        
        <div class='field-row'>
            <span class='label'>Name:</span>
            <span class='value'>$name</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Organisation:</span>
            <span class='value'>$organisation</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Email:</span>
            <span class='value'>$email</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Mobile:</span>
            <span class='value'>$mobile</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Designation:</span>
            <span class='value'>$designation</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Number of Users:</span>
            <span class='value'>$users</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>City:</span>
            <span class='value'>$city</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>State:</span>
            <span class='value'>$state</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Address:</span>
            <span class='value'>$address</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Requirements:</span>
            <span class='value'>$message</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Newsletter:</span>
            <span class='value'>" . ($subscribe ? 'Yes' : 'No') . "</span>
        </div>
        
        <div class='footer'>
            <p>This inquiry was submitted through the TRAC Security partnership form.</p>
            <p>Please respond within 24-48 hours for best customer experience.</p>
        </div>
    </div>
</body>
</html>
";

    $mail->send();

    error_log("Email sent successfully to partners@threatresq.com");
    http_response_code(200);
    echo "Thank you! We will contact you soon.";
  } catch (Exception $e) {
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
    http_response_code(500);
    echo "Message could not be sent. Please try again later.";
  }
} else {
  http_response_code(405);
  echo "Method not allowed. Please use POST.";
}
