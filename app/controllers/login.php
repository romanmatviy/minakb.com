<?php

/*
 * Контроллер використовується для POST авторизації.
 */

class Login extends Controller {

    /*
     * Метод за замовчуванням. Якщо сесії не існує то виводим форум для входу.
     */
    public function index()
    {
    	if(!empty($_POST['password']))
    	{
    		$this->process();
    		exit;
    	}

    	$this->wl_alias_model->setContent(0, 202);
    	
        if($this->userIs())
        	$this->__after_login();
        elseif($_SESSION['option']->userSignUp)
        {
        	$this->load->library('facebook');
        	$this->load->library('googlesignin');
        	if($this->googlesignin->clientId)
        	{
        		if(empty($_SESSION['alias']->meta))
        			$_SESSION['alias']->meta = '<meta name="google-signin-client_id" content="'.$this->googlesignin->clientId.'">';
        		else
        			$_SESSION['alias']->meta .= ' <meta name="google-signin-client_id" content="'.$this->googlesignin->clientId.'">';
        	}
            $this->load->page_view('profile/login_view');
        }
        else
            $this->load->view('admin/wl_users/login_page');
    }

    /*
     * Оброблюємо вхідні POST параметри.
     */
    public function process()
    {
    	$_SESSION['notify'] = new stdClass();
    	$this->load->library('recaptcha');
		if($this->recaptcha->check($this->data->post('g-recaptcha-response')) == false)
		{
			$_SESSION['notify']->errors = $this->text('Заповніть "Я не робот"');
		}
		else
		{
	        $this->load->library('validator');
			$login_by = $email_phone = false;
	    	if($email_phone = $this->data->post('email'))
			{
				$email_phone = strtolower($email_phone);
				$this->validator->setRules('E-mail', $email_phone, 'required|email');
				$login_by = 'email';
			}
			elseif ($email_phone = $this->data->post('phone'))
			{
				$this->validator->setRules($this->text('Телефон', 0), $email_phone, 'required|phone');
				$email_phone = $this->validator->getPhone($email_phone);
				$login_by = 'phone';
			}
			elseif ($email_phone = $this->data->post('email_phone'))
			{
				if($email_phone = $this->validator->getPhone($email_phone))
				{
					$login_by = 'phone';
					$this->validator->setRules($this->text('Телефон', 0), $email_phone, 'required|phone');
				}
				elseif($email_phone = $this->data->post('email_phone'))
				{
					$email_phone = strtolower($email_phone);
					// $this->validator->setRules('E-mail', $email_phone, 'required|email');
					if($this->validator->email($this->text('Телефон або E-mail', 0), $email_phone))
						$login_by = 'email';
				}
				else
				{
					$this->validator->setRules($this->text('Телефон або E-mail', 0), '', 'required');
				}
			}
	        $this->validator->setRules($this->text('Поле пароль'), $this->data->post('password'), 'required|5..40');

	        if($this->validator->run() && !empty($email_phone))
	        {
	            $this->load->model('wl_user_model');
	            if($user = $this->wl_user_model->login($login_by, $email_phone))
	            {
	            	if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => 0, 'type' => '__user_login')))
						foreach ($actions as $action) {
							$this->load->function_in_alias($action->alias2, '__user_login');
						}

					if(!isset($_POST['ajax']))
						$this->__after_login($user);
					else
					{
						$res['result'] = true;
						$this->load->json($res);
					}
					exit;
	            }
	            else
	            {
					if($this->data->post('json'))
					{
						$res['result'] = false;
						$res['login_error'] = $this->wl_user_model->user_errors;
						$this->load->json($res);
					}
					else
	           			$_SESSION['notify']->errors = $this->wl_user_model->user_errors;
	            }
	        }
	        else
	        {
				if($this->data->post('json'))
				{
					$res['result'] = false;
					$res['login_error'] = $this->validator->getErrors('');
					$this->load->json($res);
				} else
	       			$_SESSION['notify']->errors = $this->validator->getErrors();
	        }
	    }
	    if($redirect = $this->data->post('redirect'))
			$this->redirect($redirect);
		else
        	$this->redirect('login');
    }

	public function confirmed()
	{
		$_SESSION['alias']->code = 201;
		if($this->userIs())
			$this->load->view('profile/signup/confirmed_view');
		else
			$this->load->redirect('login');
	}

	public function emailSend()
	{
		$_SESSION['alias']->code = 201;
		$_SESSION['notify'] = new stdClass();
		if ($this->userIs() && $_SESSION['user']->status != 1)
		{
			$user = $this->db->getAllDataById('wl_users', $_SESSION['user']->id);

			$this->load->library('mail');
			$info['name'] = $user->name;
			$info['email'] = $user->email;
			$info['auth_id'] = $user->auth_id;
			if($this->mail->sendTemplate('signup/user_signup', $user->email, $info))
				$_SESSION['notify']->success = 'Лист з кодом підтвердження відправлено.<br>Увага! Повідомлення може знаходитися у папці СПАМ.';
			else
				$_SESSION['notify']->errors = 'Виникла помилка при відправленні листа';
		}
		$this->redirect();
	}

	public function facebook()
	{
		$_SESSION['alias']->code = 201;
		$res = array('result' => false, 'message' => 'Error validate facebook access Token');
		$this->load->library('facebook');
		if($_SESSION['option']->userSignUp && $_SESSION['option']->facebook_initialise)
		{
			$user_profile = null;

			if ($accessToken = $this->data->post('accessToken'))
			{
				$this->facebook->setAccessToken($accessToken);

				try {
					$user_profile = $this->facebook->api('/me?fields=email,id,name,link');
				} catch (FacebookApiException $e) {
					error_log($e);
					$user_profile = null;
				}
			}

			if ($user_profile)
			{
				$this->load->model('wl_user_model');
				if($user = $this->wl_user_model->login('facebook', $user_profile['id']))
				{
					$res['result'] = true;
					if(!empty($user_profile['link']))
                    	$this->setAdditional($_SESSION['user']->id, 'facebook_link', $user_profile['link']);
					if(empty($_SESSION['user']->photo))
					{
						$facebookPhotoLink = 'https://graph.facebook.com/'.$user_profile['id'].'/picture?width=9999';
						$this->wl_user_model->setPhotoByLink($facebookPhotoLink);
					}

					if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => 0, 'type' => '__user_login')))
						foreach ($actions as $action) {
							$this->load->function_in_alias($action->alias2, '__user_login');
						}

					if(!isset($_POST['ajax']))
						$this->__after_login($user);
				}
				else
				{
					$info = array();
					$info['email'] = $user_profile['email'];
				    $info['name'] = $user_profile['name'];
				    $info['status'] = 1;
				    $additionall['facebook'] = $user_profile['id'];
				    if(!empty($user_profile['link']))
				    	$additionall['facebook_link'] = $user_profile['link'];
				    if(empty($info['email']))
				    	$info['email'] = $user_profile['id'] . '@facebook.com';
					if($user = $this->wl_user_model->add($info, $additionall, 0, false, 'by facebook'))
					{
						$res['result'] = true;
						$this->wl_user_model->setSession($user);
						$auth_id = md5($_SESSION['user']->email.'|facebook auto login|auth_id|'.time());
						setcookie('auth_id', $auth_id, time() + 3600*24*31, '/');
						$this->db->updateRow('wl_users', array('auth_id' => $auth_id), $_SESSION['user']->id);

						$facebookPhotoLink = 'https://graph.facebook.com/'.$user_profile['id'].'/picture?width=9999';
						$this->wl_user_model->setPhotoByLink($facebookPhotoLink);

						if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => 0, 'type' => '__user_login')))
							foreach ($actions as $action) {
								$this->load->function_in_alias($action->alias2, '__user_login');
							}

						if(!isset($_POST['ajax']))
							$this->__after_login($user);
					}
					else
						$res['message'] = $this->wl_user_model->user_errors;
				}
			}
			else
			{
				// $statusUrl = $facebook->getLoginStatusUrl();
				$loginUrl = $this->facebook->getLoginUrl();
				header('Location: '.$loginUrl);
				exit;
			}
		}
		else
			$res['message'] = 'Login by facebook is closed';
		$this->load->json($res);
	}

	public function google()
	{
		$_SESSION['alias']->code = 201;
		$res = array('result' => false, 'message' => 'Error validate google access Token');
		if($_SESSION['option']->userSignUp)
		{
			$this->load->library('googlesignin');
			if($user = $this->googlesignin->validate())
			{
				$this->load->model('wl_user_model');
				if($status = $this->wl_user_model->login('google', $user['id']))
				{
					if($status->id != 1 && $user['verified_email'])
					{
						$auth_id = md5($_SESSION['user']->email.'|google auto login|auth_id|'.time());
						setcookie('auth_id', $auth_id, time() + 3600*24*31, '/');

						$this->db->updateRow('wl_users', array('status' => 1, 'auth_id' => $auth_id), $_SESSION['user']->id);
					}
					if(!empty($user['picture']) && empty($_SESSION['user']->photo))
						$this->wl_user_model->setPhotoByLink($user['picture']);
					$res['result'] = true;
				}
				else
				{
					$info = array();
					$info['email'] = $user['email'];
				    $info['name'] = $user['name'];
				    $info['status'] = 1;
				    $info['photo'] = NULL;
				    $additionall['google'] = $user['id'];
				    $additionall['google_link'] = $user['link'];
				    $additionall['gender'] = $user['gender'];
					if($__user = $this->wl_user_model->add($info, $additionall, 0, false, 'by google'))
					{
						$this->wl_user_model->setSession($__user);
						$auth_id = md5($_SESSION['user']->email.'|google auto login|auth_id|'.time());
						setcookie('auth_id', $auth_id, time() + 3600*24*31, '/');
						$this->db->updateRow('wl_users', array('status' => 1, 'auth_id' => $auth_id), $_SESSION['user']->id);

						if(!empty($user['picture']) && empty($__user->photo))
							$this->wl_user_model->setPhotoByLink($user['picture']);

						if(!isset($_POST['ajax']))
							$this->redirect($__user->load);
						else
							$res['result'] = true;
					}
					else
						$res['message'] = $this->wl_user_model->user_errors;
				}
			}
		}
		else
			$res['message'] = 'Login by google is closed';
		$this->load->json($res);
	}

	private function __after_login($user = null)
	{
		if($redirect = $this->data->post('redirect'))
			$this->redirect($redirect);
		elseif($redirect = $this->data->get('redirect'))
			$this->redirect($redirect);
		elseif(!empty($user))
		{
			if($_SESSION['language'] && !empty($_SESSION['user']->language) && $_SESSION['user']->language != $_SESSION['language']) {
				$this->redirect(SERVER_URL.$_SESSION['user']->language.'/'.$user->load, false);
			}
			$this->redirect($user->load);
		}
		elseif($this->userCan('admin'))
			$this->redirect('admin');
		else
			$this->redirect('profile');
	}

}

?>