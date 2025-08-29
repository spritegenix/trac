<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Require Composer's autoload file
require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
try {
  $dotenv->load();
} catch (\Exception $e) {
  error_log('Error loading .env file: ' . $e->getMessage());
  http_response_code(500);
  echo "Server configuration error: Could not load environment variables.";
  exit;
}

// // Set content type for JSON response
// header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $recaptcha_token = $_POST['recaptcha_token'];

  $url = 'https://www.google.com/recaptcha/api/siteverify';
  $data = array(
    'secret' => '6LecHbcrAAAAACPZnLi6zBh-3SE3vzMWbzRazy_',
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
    echo "You are a bot! Go away!";
    exit;
  } else if ($captcha_success->success == true) {
    echo "reCAPTCHA verification successful!";
    exit;

    // Sanitize and validate input
    // $name = strip_tags(trim($_POST["name"]));
    // $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
    // $organisation = strip_tags(trim($_POST["organisation"]));
    // $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    // $mobile = trim($_POST["mobile"]);
    // $message = trim($_POST["message"]);

    // // Optional fields
    // $designation = isset($_POST["designation"]) ? strip_tags(trim($_POST["designation"])) : "";
    // $alternateEmail = isset($_POST["alternateEmail"]) ? filter_var(trim($_POST["alternateEmail"]), FILTER_SANITIZE_EMAIL) : "";
    // $alternatePhone = isset($_POST["alternatePhone"]) ? trim($_POST["alternatePhone"]) : "";
    // $users = isset($_POST["users"]) ? trim($_POST["users"]) : "";
    // $city = isset($_POST["city"]) ? strip_tags(trim($_POST["city"])) : "";
    // $state = isset($_POST["state"]) ? strip_tags(trim($_POST["state"])) : "";
    // $address = isset($_POST["address"]) ? strip_tags(trim($_POST["address"])) : "";
    // $subscribe = isset($_POST["subscribe"]) ? ($_POST["subscribe"] === '1') : false;

    // // Validate required fields
    // if (empty($name) || empty($mobile) || empty($organisation) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //   http_response_code(400);
    //   echo "Please complete all required fields and provide a valid email address.";
    //   exit;
    // }

    // // Send email via PHPMailer
    // $mail = new PHPMailer(true);

    // try {
    //   $mail->SMTPDebug = 2;  // Enable verbose debugging output
    //   $mail->isSMTP();
    //   $mail->Host = $_ENV['SMTP_HOST'];
    //   $mail->SMTPAuth = true;
    //   $mail->Username = $_ENV['SMTP_USERNAME'];
    //   $mail->Password = $_ENV['SMTP_PASSWORD'];
    //   $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    //   $mail->Port = $_ENV['SMTP_PORT'];

    //   $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
    //   $mail->addAddress('partners@threatresq.com', 'Admin');
    //   $mail->addReplyTo($email, $name);
    //   $mail->isHTML(true);
    //   $mail->Subject = "New Partnership Inquiry - " . $organisation;
    //   $mail->Body = "
    //     Name: $name<br>
    //     Organisation: $organisation<br>
    //     Email: $email<br>
    //     Mobile: $mobile<br>
    //     Designation: $designation<br>
    //     Number of Users: $users<br>
    //     City: $city<br>
    //     State: $state<br>
    //     Address: $address<br>
    //     Specific Requirements: $message<br>
    //     Subscribe to Newsletter: $subscribe<br>
    //   ";

    //   $mail->send();
    //   echo 'Message has been sent';

    //   http_response_code(200);
    //   echo "Thank you! We will contact you soon.";
    // } catch (Exception $e) {
    //   http_response_code(500);
    //   echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    //   error_log("PHPMailer Error: " . $mail->ErrorInfo);
    //   exit;
    // }
  } else {
    echo "reCAPTCHA verification failed. Please try again.";
  }
} else {
  http_response_code(405);
  echo "Method not allowed. Please use POST.";
}
