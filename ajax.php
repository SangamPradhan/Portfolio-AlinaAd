<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require 'vendor/autoload.php';

// Load .env credentials
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get POST data
$firstName = $_POST["first_name"] ?? '';
$lastName  = $_POST["last_name"] ?? '';
$email     = $_POST["email"] ?? '';
$subject   = $_POST["subject"] ?? '';
$message   = $_POST["message"] ?? '';

$fullName = trim($firstName . ' ' . $lastName);

// Validation
if (!$email || !$message || !$subject || !$firstName) {
	http_response_code(400);
	echo json_encode(["status" => "error", "message" => "Missing required fields."]);
	exit;
}

$mail = new PHPMailer(true);

try {
	$mail->isSMTP();
	$mail->Host       = $_ENV['SMTP_HOST'];
	$mail->SMTPAuth   = true;
	$mail->Username   = $_ENV['SMTP_USERNAME'];
	$mail->Password   = $_ENV['SMTP_PASSWORD'];
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port       = $_ENV['SMTP_PORT'];

	$mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
	$mail->addAddress($_ENV['SMTP_TO_EMAIL'], $_ENV['SMTP_TO_NAME']);

	$mail->Subject = $subject;
	$mail->isHTML(true); // Send email as HTML

	$mail->Body = "
	<h2>New Contact Message from Portfolio</h2>
	<p><strong>Name:</strong> {$fullName}</p>
	<p><strong>Email:</strong> {$email}</p>
	<p><strong>Subject:</strong> {$subject}</p>
	<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p> 
	";

	$mail->AltBody = "Name: {$fullName}\nEmail: {$email}\nSubject: {$subject}\nMessage:\n{$message}";


	$mail->send();

	echo json_encode(["status" => "success", "message" => "Message sent successfully."]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		"status" => "error",
		"message" => "Mailer Error: {$mail->ErrorInfo}"
	]);
}

exit;

// Uncomment the following lines if you want to use session messages instead of JSON response

// if ($mail->send()) {
//     $_SESSION['success_message'] = "Thank you for contacting us. Your message has been sent!";
//     header("Location: " . $_SERVER['HTTP_REFERER']); // redirect back to previous page (form page)
//     exit;
// } else {
//     $_SESSION['error_message'] = "Mailer Error: {$mail->ErrorInfo}";
//     header("Location: " . $_SERVER['HTTP_REFERER']);
//     exit;
// }
