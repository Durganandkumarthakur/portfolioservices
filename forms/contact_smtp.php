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
$gmail_username = 'durganandpatna6@gmail.com'; // Replace with your Gmail address
$gmail_password = 'vgngtjwaipcztmro'; // Replace with your Gmail App Password

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

if ($gmail_username === '' || $gmail_password === '') {
  http_response_code(500);
  exit('SMTP credentials are not configured. Please set $gmail_username and $gmail_password in contact_smtp.php.');
}

$email_subject = 'Portfolio Contact: ' . $subject;
$email_body = "You have received a new message from your portfolio contact form.\n\n";
$email_body .= "Name: {$name}\n";
$email_body .= "Email: {$email}\n";
$email_body .= "Subject: {$subject}\n\n";
$email_body .= "Message:\n{$message}\n";

$headers = array(
  'From: Durganand Devs Portfolio <' . $gmail_username . '>',
  'Reply-To: ' . $name . ' <' . $email . '>',
  'MIME-Version: 1.0',
  'Content-Type: text/plain; charset=UTF-8',
  'X-Mailer: PHP/' . phpversion()
);

try {
  smtp_send($receiving_email_address, $gmail_username, $gmail_password, $email_subject, $email_body, $headers);
  echo 'OK';
} catch (Exception $e) {
  http_response_code(500);
  echo 'Message could not be sent. ' . $e->getMessage();
}

function smtp_send($to, $username, $password, $subject, $body, array $headers) {
  $host = 'smtp.gmail.com';
  $port = 587;
  $timeout = 30;
  $socket = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

  if (!is_resource($socket)) {
    throw new Exception('SMTP connection failed: ' . $errstr . ' (' . $errno . ')');
  }

  stream_set_timeout($socket, $timeout);
  smtp_expect($socket, 220);
  smtp_write($socket, 'EHLO localhost');
  smtp_expect($socket, 250);
  smtp_write($socket, 'STARTTLS');
  smtp_expect($socket, 220);

  if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    throw new Exception('Unable to enable TLS encryption for SMTP connection.');
  }

  smtp_write($socket, 'EHLO localhost');
  smtp_expect($socket, 250);
  smtp_write($socket, 'AUTH LOGIN');
  smtp_expect($socket, 334);
  smtp_write($socket, base64_encode($username));
  smtp_expect($socket, 334);
  smtp_write($socket, base64_encode($password));
  smtp_expect($socket, 235);
  smtp_write($socket, 'MAIL FROM:<' . $username . '>');
  smtp_expect($socket, 250);
  smtp_write($socket, 'RCPT TO:<' . $to . '>');
  smtp_expect($socket, array(250, 251));
  smtp_write($socket, 'DATA');
  smtp_expect($socket, 354);

  $message = '';
  $message .= 'Subject: ' . $subject . "\r\n";
  foreach ($headers as $header) {
    $message .= $header . "\r\n";
  }
  $message .= "\r\n" . $body . "\r\n.\r\n";

  smtp_write($socket, $message);
  smtp_expect($socket, 250);
  smtp_write($socket, 'QUIT');
  smtp_expect($socket, 221);
  fclose($socket);
}

function smtp_write($socket, $command) {
  fwrite($socket, $command . "\r\n");
}

function smtp_expect($socket, $expected_codes) {
  $expected_codes = (array) $expected_codes;
  $response = '';

  while (($line = fgets($socket, 515)) !== false) {
    $response .= $line;
    if (isset($line[3]) && $line[3] === ' ') {
      break;
    }
  }

  $code = (int) substr($response, 0, 3);
  if (!in_array($code, $expected_codes, true)) {
    throw new Exception('SMTP error: ' . trim($response));
  }

  return $response;
}
