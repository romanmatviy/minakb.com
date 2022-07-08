<?php if (!defined('SYS_PATH')) exit('Access denied');


class turbosms {

	private $sender;
	private $login;
	private $password;

	public function __construct($cfg)
	{
		$this->sender = $cfg['sender'];
		$this->login = $cfg['login'];
		$this->password = $cfg['password'];
	}

	function send($phone, $text)
	{
		header ('Content-type: text/html; charset=utf-8');
		$client = new SoapClient ('http://turbosms.in.ua/api/wsdl.html');

		$auth = array(
			'login' => $this->login,
	        'password' => $this->password
    	);
    	$res = $client->Auth($auth);

    	$sms = array(
    		'sender' => $this->sender,
    		'destination' => $phone,
	        'text' => $text
    	);
    	$res = $client->SendSMS($sms);
	}

}

?>
