<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email_model extends CI_Model
{
	public function __construct()
    {
        parent::__construct();
    }

	public function sendEmail(){
		
		$mail = new PHPMailer(); // create a new object
		$mail->IsSMTP(); // enable SMTP
		$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true; // authentication enabled
		$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
		$mail->Host = "smtp.gmail.com";
		$mail->Port = 465; // or 587
		$mail->IsHTML(true);
		$mail->Username = "rubby.star.sg@gmail.com";
		$mail->Password = "soksunae";
		$mail->SetFrom("rubby.star.sg@gmail.com");
		$mail->Subject = "Test";
		$mail->Body = "hello";
		$mail->AddAddress("rubby.star@hotmail.com");

		 if(!$mail->Send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		 } else {
		    echo "Message has been sent";
		 }
	}
}
