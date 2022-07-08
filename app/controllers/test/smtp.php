<?php 

use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * 
 */
class smtp extends Controller
{
	private $to = 'info@webspirit.com.ua';
	
	function index()
	{
		ini_set('display_errors', 1);

		$to = $this->data->get('to');
		if(empty($to))
			$to = $this->to;

		$this->load->library('mail');
		$this->mail->subject('Test smtp mail');
		$this->mail->message('Test message smtp mail');
		$this->mail->to($to);

		var_dump($this->mail->send());
	}

	public function swift()
	{
		ini_set('display_errors', 1);

		$to = $this->data->get('to');
		if(empty($to))
			$to = $this->to;

        require_once SYS_PATH.'libraries/swiftmailer/autoload.php';

        $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
          // ->setAuthMode('XOAUTH2')
          ->setUsername('info@polycraft.ua')
          ->setPassword('T6&89ghT%6>)$5643');

          // Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);

		// Create a message
		$message = (new Swift_Message('Wonderful Test Swift Subject'))
		  ->setFrom('info@polycraft.ua')
		  ->setTo($to)
		  ->setBody('Here is the test message itself')
		  ;

		// Send the message
		$result = $mailer->send($message);
	}

	public function phpmailer()
	{
		ini_set('display_errors', 1);

		$to = $this->data->get('to');
		if(empty($to))
			$to = $this->to;

        require_once SYS_PATH.'libraries/phpmailer/vendor/autoload.php';

        $mail = new PHPMailer();
		try {
		    //Enable SMTP debugging
			//SMTP::DEBUG_OFF = off (for production use)
			//SMTP::DEBUG_CLIENT = client messages
			//SMTP::DEBUG_SERVER = client and server messages
		    $mail->SMTPDebug = 2;
		    $mail->isSMTP();                                      // Set mailer to use SMTP
		    $mail->Host = 'smtp.gmail.com';  					  // Specify main and backup SMTP servers
		    $mail->SMTPAuth = true;                               // Enable SMTP authentication
		    $mail->Username = 'info@polycraft.ua';                 // SMTP username
		    $mail->Password = 'T6&89ghT%6>)$5643';                 // SMTP password
		    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		    $mail->Port = 587;                                    // TCP port to connect to

		    //Recipients
		    $mail->setFrom('info@polycraft.ua', 'polycraft');
		    $mail->addAddress($to);     // Add a recipient

		    //Content
		    $mail->isHTML(true);                                  // Set email format to HTML
		    $mail->Subject = 'Here is the subject';
		    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		    if (!$mail->send()) {
			    echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			    echo 'Message sent!';
			}
		} catch (Exception $e) {
		    echo 'Message could not be sent.';
		    echo 'Mailer Error: ' . $mail->ErrorInfo;
		}
	}
}

 ?>