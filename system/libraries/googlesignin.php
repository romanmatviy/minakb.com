<?php 
/**
 * 
 */
class googlesignin
{

	public $clientId = false;
	
	function __construct($cfg)
	{
		if($cfg['clientId'] != 'GOOGLE_CLIENT_ID')
			$this->clientId = $cfg['clientId'];
	}

	public function validate()
	{
		if(!empty($_POST['accessToken']))
		{
			$userInfo = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$_POST['accessToken']), true);
			if($userInfo && !empty($userInfo['id']))
				return $userInfo;
		}
		return false;
	}

}
 ?>