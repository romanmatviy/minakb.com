<?php

class Reset extends Controller {

    public function index()
    {
		if($this->userIs())
		{
            header("Location: ". SITE_URL);
            exit();
        }
        else
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
            $this->load->page_view('profile/reset/index_view');
        }
    }

	// етап 1:
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
			$this->validator->setRules('email', $this->data->post('email'), 'required|email');
			if($this->validator->run() && empty($this->errors))
			{
				$this->load->model('wl_user_model');
				if($user = $this->wl_user_model->reset($this->data->post('email')))
				{
					$info['id'] = $user->id;
					$info['name'] = $user->name;
					$info['reset_key'] = $user->reset_key;
					$info['reset_expires'] = $user->reset_expires;
					$this->load->library('mail');
					if($this->mail->sendTemplate('reset/sent_code', $user->email, $info))
						$_SESSION['notify']->success = $this->text('На поштову скриньку Вам відправлено лист з КОДОМ ВІДНОВЛЕННЯ та подальшими інструкціями.<br>УВАГА! Лист може знаходитися у папці СПАМ!');
					else
						$_SESSION['notify']->errors = $this->text('Помилка при відправленні пошти');
				}
				else
					$_SESSION['notify']->errors = $this->text('Емейл').' <strong>'.$this->data->post('email'). '</strong> '.$this->text('у базі не знайдено! Перевірте правильність електронної пошти.');
			}
			else
				$_SESSION['notify']->errors = $this->validator->getErrors();
		}
		$this->redirect();
	}

	// етап2:
	public function go()
	{
    	$_SESSION['alias']->code = 201;

		if(!isset($_SESSION['notify']->from))
			$_SESSION['notify'] = new stdClass();

		if(isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['code']))
		{
            $user = $this->db->getAllDataById('wl_users', $this->data->get('id'));
            if($user)
            {
				if($_GET['code'] == $user->reset_key) // Якщо співпадають ключі відновлення
				{
					if($user->reset_expires > time()) // Чи ключ відновлення не застарів
					{
						$_SESSION['reset'] = $user->id;
						$this->load->view('profile/reset/reset_2_view', array('user' => $user));
						exit();
					}
					else
						$_SESSION['notify']->errors = $this->text('Ключ відновлення НЕ ДІЙСНИЙ! Повторіть процедуру відновлення паролю заново.');
				}
				else
					$_SESSION['notify']->errors = $this->text('Ключ відновлення НЕ КОРЕКТНИЙ! Перевірте адресу відновлення.');
			}
			else
				$_SESSION['notify']->errors = $this->text('Ключ відновлення НЕ КОРЕКТНИЙ! Перевірте адресу відновлення.');
		}
		else
			$_SESSION['notify']->errors = $this->text('Помилка доступу! Перевірте введені дані щераз.');
		$this->redirect('reset');
	}

	public function SetNewPassword()
	{
		$_SESSION['notify'] = new stdClass();
		$_SESSION['notify']->from = 'SetNewPassword';
		
		if(isset($_SESSION['reset']) && isset($_POST['id']) && is_numeric($_POST['id']) && $_SESSION['reset'] == $_POST['id'])
		{
			$this->load->library('validator');
			$this->validator->setRules('Пароль', $this->data->post('password'), 'required|5..20');
			$this->validator->password($this->data->post('password'), $this->data->post('re-password'));
	        if($this->validator->run())
	        {
				$this->load->model('wl_user_model');
				if($user = $this->db->getAllDataById('wl_users', $this->data->post('id')))
				{
					$_SESSION['reset'] = 0;
					if($_POST['secret_key'] == $user->reset_key && $user->reset_expires > time())
					{
						$this->db->register('reset', $user->password, $user->id);
						$auth_id = md5($_POST['password'].'|'.$user->email);
						$password = $this->wl_user_model->getPassword($user->id, $user->email, $_POST['password']);
						$this->db->updateRow('wl_users', array('password' => $password, 'auth_id' => $auth_id), $user->id);

						if($info = $this->db->select('wl_user_info', "value as phone", ['user' => $user->id, 'field' => 'phone'])
                                ->limit(1)
                                ->get())
                			$user->phone = $info->phone;

						$this->wl_user_model->setSession($user);

						$this->load->library('mail');
						$this->mail->sendTemplate('reset/notify_success', $user->email, ['name' => $user->name]);

						$_SESSION['notify']->title = $this->text('Відновлення паролю');
						$_SESSION['notify']->success = $this->text('Новий пароль встановлено!');
						$this->redirect('profile');
					}
					else
					{
						$_SESSION['notify']->errors = $this->text('Помилка встановлення нового паролю: Ключ відновлення НЕ ДІЙСНИЙ або ЗАСТАРІВ.');
						$this->redirect('reset');
					}
				}
			}
	        else
	            $_SESSION['notify']->errors = '<ul>'.$this->validator->getErrors('<li>', '</li>').'</ul>';
	        $this->redirect();
		}
		else
		{
			$_SESSION['reset'] = 0;
			$this->redirect('reset');
		}
	}

	public function __get_SiteMap_Links()
    {
        $row = array();
        $row['link'] = $_SESSION['alias']->alias;
        $row['alias'] = $_SESSION['alias']->id;
        $row['content'] = 0;
        return array($row);
    }

    public function __get_Search($content = 0)
    {
    	$search = new stdClass();
		$search->id = $_SESSION['alias']->id;
		$search->link = $_SESSION['alias']->alias;
		$search->date = 0;
		$search->author = 1;
		$search->author_name = '';
		$search->additional = false;
		$search->folder = false;
		if(isset($_SESSION['option']->folder))
			$search->folder = $_SESSION['option']->folder;
		return $search;
    }

}

?>