<?php

class Signup extends Controller {

    private $errors = array();
    private $name = 'first_name, last_name'; // 'name'||'first_name, last_name' ім'я в одній змінній чи 2-х
    public $additionall = array('phone'); // false додаткові поля при реєстрації. Згодом можна використовувати у ідентифікації, тощо
    private $new_user_type = 4; // Ід типу новозареєстрованого користувача

    function _remap($method, $data = array())
    {
    	if(isset($_SESSION['option']->new_user_type))
    		$this->new_user_type = $_SESSION['option']->new_user_type;
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index()
    {
    	if(!$this->userIs())
    	{
    		if($_SESSION['option']->userSignUp)
    		{
    			$this->wl_alias_model->setContent(0, 202);
    			$this->load->library('facebook');
    			$this->load->library('googlesignin');
	        	if($this->googlesignin->clientId)
	        	{
	        		if(empty($_SESSION['alias']->meta))
	        			$_SESSION['alias']->meta = '<meta name="google-signin-client_id" content="'.$this->googlesignin->clientId.'">';
	        		else
	        			$_SESSION['alias']->meta .= ' <meta name="google-signin-client_id" content="'.$this->googlesignin->clientId.'">';
	        	}
    			if(isset($_GET['old']))
    			{
		    		if($_SESSION['option']->facebook_initialise)
		        		$this->load->view('profile/signup/index_view');
		        	else
		        		$this->load->view('profile/signup/email_view');
		        }
		        else
		        	$this->load->page_view('profile/login_view');
    		}
    		else
    			$this->load->page_404(false);
    	}
        else
        	$this->redirect('profile');
    }

    public function email()
    {
    	if(!$this->userIs())
    	{
    		$this->wl_alias_model->setContent();
    		$this->load->library('facebook');
        	$this->load->view('profile/signup/email_view');
    	}
        else
        	$this->redirect('profile');
    }

    public function process()
    {
		if(!$this->userIs())
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
		        if($this->name == 'name')
					$this->validator->setRules($this->text("Ім'я"), $this->data->post('name'), 'required');
				else
				{
					$this->validator->setRules($this->text("Ім'я"), $this->data->post('first_name'), 'required');
					$this->validator->setRules($this->text("Прізвище"), $this->data->post('last_name'), 'required');
				}
				$email = '';
		    	if($email = $this->data->post('email'))
		    		$email = strtolower($email);
				$this->validator->setRules('E-mail', $email, 'required|email');
				if(in_array('phone', $this->additionall))
					$this->validator->setRules($this->text('Контактний номер'), $this->data->post('phone'), 'phone');	
				$this->validator->setRules($this->text('Пароль'), $this->data->post('password'), 'required|5..20');
				$this->validator->password($this->data->post('password'), $this->data->post('re-password'));
		        if($this->validator->run())
		        {
		            $this->load->model('wl_user_model');
		            $info['email'] = $email;
			    	$info['name'] = $this->data->post('name');
			    	$info['password'] = $_POST['password'];
			    	if(isset($_POST['first_name']) && isset($_POST['last_name']))
			    		$info['name'] = $this->data->post('first_name') .' '. $this->data->post('last_name');
			    	$additionall = array();
			    	if(!empty($this->additionall))
					{
						foreach ($this->additionall as $key) {
							if($value = $this->data->post($key))
							{
								if($key == 'phone')
									$value = $this->validator->getPhone($value);
								$additionall[$key] = $value;
							}
						}
					}
	                if($user = $this->wl_user_model->add($info, $additionall, $this->new_user_type))
	                {
	                	if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => 0, 'type' => '__user_login')))
							foreach ($actions as $action) {
								$this->load->function_in_alias($action->alias2, '__user_login');
							}
							
	                	$this->load->library('mail');
						$info['auth_id'] = $user->auth_id;
						if($this->mail->sendTemplate('signup/user_signup', $user->email, $info))
						{
							$_SESSION['notify']->title = $this->text('Реєстрація пройшла успішно!');
							$_SESSION['notify']->success = $this->text('На поштову скриньку відправлено лист з <b>кодом підтвердження</b> та подальшими інструкціями. <br><br> <b>УВАГА!</b> Лист може знаходитися у папці <b>СПАМ!</b>');
						}
						else 
							$_SESSION['notify']->errors = $this->text('Виникла помилка при додаванні нового користувача');
	                }
	                else
	                	$_SESSION['notify']->errors = $this->wl_user_model->user_errors;
		        }
		        else
		            $_SESSION['notify']->errors = '<ul>'.$this->validator->getErrors('<li>', '</li>').'</ul>';
		    }
	        $this->redirect('signup');
		}
		$this->redirect('profile');
    }

	public function confirmed()
	{
		if($this->userIs() && isset($_POST['code']))
		{
			$_SESSION['notify'] = new stdClass();
			$this->load->model('wl_user_model');
			if($status = $this->wl_user_model->checkConfirmed($_SESSION['user']->email, $this->data->post('code')))
			{
				$_SESSION['notify']->success = $this->text('Підтвердження пройшло успішно!');
				$this->redirect($status->load);
			}
			else
			{
				$_SESSION['notify']->errors = $this->text('Код підтвердження не співпав!');
				$this->redirect();
			}
		}
		$this->load->page_404();
	}

	public function get_confirmed()
	{
		$_SESSION['notify'] = new stdClass();
		if (isset($_GET['code']) and isset($_GET['email']))
		{
			$this->load->model('wl_user_model');
			if ($status = $this->wl_user_model->checkConfirmed($this->data->get('email'), $this->data->get('code')))
			{
				if($_SESSION['language'] && !empty($_SESSION['user']->language) && $_SESSION['user']->language != $_SESSION['language']) {
					$_SESSION['language'] = $_SESSION['user']->language;
				}
				$_SESSION['notify']->success = $this->text('Підтвердження пройшло успішно!');

				if($_SESSION['language'] && !empty($_SESSION['user']->language) && $_SESSION['user']->language != $_SESSION['language']) {
					$this->redirect(SERVER_URL.$_SESSION['user']->language.'/profile', false);
				}
				$this->redirect('profile');
			}
			else
			{
				$_SESSION['notify']->errors = $this->text('Код підтвердження не співпав!');
				$this->redirect('login');
			}
		}
		$this->load->page_404();
	}

	public function check_email()
	{
		$this->load->model('wl_user_model');
		$res['result'] = $this->wl_user_model->userExists($this->data->post('email'));
		$res['message'] = $this->wl_user_model->user_errors;
		$this->load->json($res);
	}

	// function checkPhone()
	// {
	// 	$res = array('result' => false, 'message' => '');
	// 	$phone = $this->data->post('phone');
	// 	if(isset($phone)){
	// 		$this->load->model('wl_user_model');
	// 		$res['result'] = $this->wl_user_model->phoneExists($phone);
	// 		if(count($this->wl_user_model->user_errors) == 1) $res['message'] = $this->wl_user_model->user_errors[0];
	// 	}
	// 	header('Content-type: application/json');
	// 	echo json_encode($res);
	// 	exit;
	// }

	// public function phone_confirmed()
	// {
	// 	$res = array('result' => false, 'message' => '');
	// 	$phone = str_replace(' ', '', $this->data->post('phone'));
	// 	$id = $_SESSION['user']->id;
	// 	$this->db->executeQuery("SELECT * FROM wl_users WHERE phone = '{$phone}' AND `id` != $id");
	// 	if($this->db->numRows() == 0){
	// 		if(is_numeric($phone) && (strlen($phone) == 13) && isset($id)){
	//             $this->db->executeQuery("UPDATE wl_users SET phone = '{$phone}' WHERE id = $id");
	//             $_SESSION['code'] = rand(10000 , 99999);
	//             $this->load->library('turbosms');
	//             $this->turbosms->send($phone, $_SESSION['code']);
	//             $res['result'] = true;
	//             $res['message'] = 'Зараз на телефон вам прийде код підтвердження';
	//         }
	//         else $res['message'] = 'Не вірний формат телефону (Приклад: +380 12 345 6789).';
	//     } else $res['message'] = 'Користувач з таким телефоном вже існує';

	// 	header('Content-type: application/json');
	// 	echo json_encode($res);
	// 	exit;
	// }

	// public function code_confirmed()
	// {
	// 	$res = array('result' => false, 'message' => '');
	// 	$code = $this->data->post('code');
	// 	$id = $_SESSION['user']->id;
	// 	if($code == $_SESSION['code']){
	// 		$this->db->executeQuery("UPDATE wl_users SET confirmed = confirmed+2 WHERE id = $id ");

	// 		$res['result'] = true;
 //            $res['message'] = 'Успішно!';
 //            if(!isset($_SESSION['alias']->referTo))
 //            	$res['referTo'] = 'profile';
 //            else {
 //            	$res['referTo'] = $_SESSION['alias']->referTo;
 //            }
	// 	}
	// 	else $res['message'] = 'Код підтвердження не співпав';

	// 	header('Content-type: application/json');
	// 	echo json_encode($res);
	// 	exit;
	// }
}

?>