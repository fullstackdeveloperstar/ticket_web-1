<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email_model extends CI_Model
{
	public function __construct()
    {
        parent::__construct();
    }

	public function sendEmail($email, $subject = "", $body = ""){
		
		$mail = new PHPMailer(); // create a new object
		$mail->IsSMTP(); // enable SMTP
		// $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true; // authentication enabled
		$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
		$mail->Host = "smtp.gmail.com";
		$mail->Port = 465; // or 587
		$mail->IsHTML(true);
		$mail->Username = "rubby.star.sg@gmail.com";
		$mail->Password = "***";
		$mail->SetFrom("rubby.star.sg@gmail.com");
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->AddAddress($email);

		 if(!$mail->Send()) {
		   return false;
		 } else {
		    return true;
		 }
	}
}
