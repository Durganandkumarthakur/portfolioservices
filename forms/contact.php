<?php
header('Content-Type: text/plain; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Only POST requests are allowed.');
}

$receiving_email_address = 'durganandpatna6@gmail.com';

function clean_input($value) {
  return trim(str_replace(array("\r", "\n"), ' ', strip_tags($value ?? '')));
}

$name = clean_input($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = clean_input($_POST['subject'] ?? '');
$message = trim(strip_tags($_POST['message'] ?? ''));

if ($name === '' || !$email || $subject === '' || $message === '') {
  http_response_code(422);
  exit('Please complete all required fields with valid information.');
}

$email_subject = 'Portfolio Contact: ' . $subject;
$email_body = "You have received a new message from your portfolio contact form.\n\n";
$email_body .= "Name: {$name}\n";
$email_body .= "Email: {$email}\n";
$email_body .= "Subject: {$subject}\n\n";
$email_body .= "Message:\n{$message}\n";

$headers = array(
  'From: Durganand Devs Portfolio <no-reply@localhost>',
  'Reply-To: ' . $name . ' <' . $email . '>',
  'MIME-Version: 1.0',
  'Content-Type: text/plain; charset=UTF-8'
);

if (@mail($receiving_email_address, $email_subject, $email_body, implode("\r\n", $headers))) {
  echo 'OK';
} else {
  http_response_code(500);
  echo 'Message could not be sent. Please check your server mail configuration.';
}
