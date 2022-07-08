<?php

class newsletter_admin extends Controller {

	private $sent_emails = '';

    function _remap($method, $data = array())
    {
    	$_SESSION['alias']->name = 'Розсилка';
    	$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index()
    {
    	$this->load->smodel('newsletter_model');
    	if(is_numeric($this->data->uri(2)))
    	{
    		if($template = $this->newsletter_model->get_template($this->data->uri(2)))
    		{
    			if($this->data->uri(3) == 'receivers')
	    		$this->load->admin_view('mail_receivers', array('template' => $template, 'mails' => $this->newsletter_model->getListActiveMails($template->to_user_types)));
	    	else
	    		$this->load->admin_view('mail_preview', array('template' => $template, 'mails' => $this->newsletter_model->getListActiveMails($template->to_user_types)));
    		}
	    	else
	    		$this->load->page_404(false);
	    }

		$this->load->admin_view('templates_list_view', ['templates' => $this->newsletter_model->get_templates()]);
    }

    public function files()
	{
		if($id = $this->data->uri(3))
			if(is_numeric($id))
			{
				$path = 'files/'.$_SESSION['alias']->alias.'/'.$id;
				if(file_exists($path))
				{
					if($file = $this->db->getAllDataById($_SESSION['service']->table.'_files', $id))
					{
						header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.$file->name.'"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($path));
						readfile($path);
					}
				}
				else
					echo "file error";
			}
		exit();
	}

    public function add()
    {
    	$this->load->admin_view('template_add_view');
    }

    public function insert()
    {
    	$message = array();
	   	$message['name'] = $this->data->post('name');
	   	$message['theme'] = $this->data->post('theme');
	   	$message['from'] = $message['text'] = '';
	   	$message['to_user_types'] = array();
	   	$user_types = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
	   	foreach ($user_types as $u_type) {
	   		$message['to_user_types'][] = $u_type->id;
	   	}
	   	$message['to_user_types'] = serialize($message['to_user_types']);
	   	$message['date_add'] = $message['date_edit'] = time();
	   	$id = $this->db->insertRow($_SESSION['service']->table.'_templates', $message);

	   	$this->redirect('admin/'.$_SESSION['alias']->alias.'/edit/'.$id);
    }

	public function edit()
	{
		if($id = $this->data->uri(3))
			if(is_numeric($id))
			{
				$this->load->smodel('newsletter_model');
				if($template = $this->newsletter_model->get_template($id))
					$this->load->admin_view('template_edit_view', array('template' => $template));
			}
		$this->load->page_404(false);
	}

	public function save()
    {
    	if($id = $this->data->post('id'))
    	{
	    	$message = array();
		   	$message['name'] = $this->data->post('name');
		   	$message['theme'] = $this->data->post('theme');
		   	$message['from'] = $this->data->post('from');
		   	$message['text'] = $this->data->post('text');
		   	$to_user_types = [];
		   	if(!empty($_POST['to']))
		   		foreach ($_POST['to'] as $to) {
		   			if(is_numeric($to))
		   				$to_user_types[] = $to;
		   		}
		   	$message['to_user_types'] = serialize($to_user_types);
		   	$message['date_edit'] = time();
		   	$this->db->updateRow($_SESSION['service']->table.'_templates', $message, $id);

		   	foreach ($_POST as $key => $value) {
		   		$key = explode('-', $key);
		   		if(count($key) == 3 && $key[0] == 'file' && $key[1] == 'name' && is_numeric($key[2]))
   					$this->db->updateRow($_SESSION['service']->table.'_files', ['name' => $value], $key[2]);
   			}

   			if(!empty($_POST['file-delete']) && is_numeric($_POST['file-delete']))
   			{
   				$this->db->deleteRow($_SESSION['service']->table.'_files', $_POST['file-delete']);
   				$path = 'files/'.$_SESSION['alias']->alias.'/'.$_POST['file-delete'];
				if(file_exists($path))
   					unlink($path);
   			}

		   	if(!empty($_FILES['files']['name']))
		   	{
		   		$path = 'files';
		   		if(!is_dir($path))
	            	mkdir($path, 0777);
	            $path .= '/'.$_SESSION['alias']->alias;
	            if(!is_dir($path))
	            	mkdir($path, 0777);

		   		$file = ['template' => $id];
		   		for ($i=0; $i < count($_FILES['files']['tmp_name']); $i++) {
		   			if(is_uploaded_file($_FILES['files']['tmp_name'][$i])) {
		   				$file['name'] = $_FILES['files']['name'][$i];
		   				$file_id = $this->db->insertRow($_SESSION['service']->table.'_files', $file);
			   			move_uploaded_file($_FILES['files']['tmp_name'][$i], $path.'/'.$file_id);
			   		}
		   		}
		   	}

		   	if($this->data->post('after') == 'sent')
		   		$this->redirect('admin/'.$_SESSION['alias']->alias.'/'.$id);
		   	else
		   		$this->redirect('admin/'.$_SESSION['alias']->alias.'/edit/'.$id);
		}
		echo "error `id`";
    }

	public function delete()
	{
		if($id = $this->data->post('id'))
			if($this->db->deleteRow($_SESSION['service']->table.'_templates', $id))
				$this->redirect('admin/'.$_SESSION['alias']->alias);
	}

	public function cancel_sendmail()
	{
		if($id = $this->data->get('id'))
			if($this->db->deleteRow($_SESSION['service']->table.'_log', $id))
			{
				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->success = 'Розсилку скасовано!';
				$this->redirect();
			}
		$this->redirect('admin/'.$_SESSION['alias']->alias);
	}

	public function history()
	{
		$logs = $this->db->select($_SESSION['service']->table.'_log as l')
						->join($_SESSION['service']->table.'_templates', 'name', '#l.template')
						->order('date DESC')
						->get('array');

		$this->load->admin_view('history_view', ['logs' => $logs]);
	}

	public function sendMail()
	{
		$this->load->smodel('newsletter_model');
		if($id = $this->data->post('id'))
			if($template = $this->newsletter_model->get_template($id))
			{
		    	$this->load->model('wl_user_model');
		        $manager = $this->wl_user_model->getInfo($_SESSION['user']->id, false);
		        $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $manager->email, $_POST['password']);
		        if($password != $manager->password)
		    	{
		            $_SESSION['notify']->errors = 'Невірний пароль';
		            $this->redirect();
		        }

		        if($all_users = $this->data->post('all_users'))
		        	if($all_users == 1)
		        		$this->newsletter_model->all_users = true;
				if($mails = $this->newsletter_model->getListActiveMails($template->to_user_types))
				{
					if(count($mails) <= $_SESSION['option']->sent_per_part)
						$this->send($mails, $template);
					else
					{
						$this->db->insertRow($_SESSION['service']->table.'_log',
								[	'template' => $template->id,
									'to_user_types' => serialize($template->to_user_types),
									'all_users' => (int) $this->newsletter_model->all_users,
									'emails_count' => count($mails),
									'emails_sent' => '0',
									'from' => $template->from,
									'date' => time()
								]);
						$_SESSION['notify'] = new stdClass();
						$_SESSION['notify']->success = 'Поставлено в чергу';
						$this->redirect();
					}
				}
			}
	}

	public function sendTestMail()
	{
		if($receiver = $this->db->getAllDataByFieldInArray('wl_users', $this->data->post('receiver')))
		{
			$this->load->smodel('newsletter_model');
			if($template = $this->newsletter_model->get_template($this->data->post('id')))
				$this->send($receiver, $template, 1);
		}
	}

	public function force_sendmail()
	{
		if($id = $this->data->get('id'))
			if($logs = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_log', ['template' => $id, 'emails_count' => '!emails_sent']))
			{
				$this->load->smodel('newsletter_model');
				if($template = $this->newsletter_model->get_template($id))
					if($mails = $this->newsletter_model->getListActiveMails($template->to_user_types, $logs[0]->emails_sent))
					{
						$this->db->updateRow($_SESSION['service']->table.'_log', ['emails_sent' => $logs[0]->emails_sent + $_SESSION['option']->sent_per_part], $logs[0]->id);
						$this->send($mails, $template);
					}
			}
		$this->redirect();
	}

	private function send($mails, $template, $test = 0)
	{
		$this->load->library('mail');
		foreach ($mails as $mail) {
			$mail->registered = date('d.m.Y H:i', $mail->registered);
			$msg_body = '<html><head></head><body>'.html_entity_decode($template->text).'</body></html>';
			$this->mail->params(array('id' => $mail->id, 'name' => $mail->name, 'email' => $mail->email, 'registered' => $mail->registered, 'date' => date('d.m.Y'), 'dateTime' => date('d.m.Y H:i'), 'auth_id' => $mail->auth_id));
			$this->mail->message($msg_body);
			$this->mail->subject($template->theme);
			$this->mail->fromName($template->from);
			$this->mail->to($mail->email);
			if($template->files)
				foreach ($template->files as $file) {
					$path = 'files/'.$_SESSION['alias']->alias.'/'.$file->id;
					if(file_exists($path))
						$this->mail->addAttach($path, $file->name);
				}
			$this->mail->send();
		}

		if(!$test)
			$this->db->updateRow($_SESSION['service']->table.'_templates', array('last_do' => time()), $template->id);

		$_SESSION['notify'] = new stdClass();
		$_SESSION['notify']->success = ($test == 1) ? 'Тестовий емейл відправлено' : 'Розсилку розіслано успішно!';
		$this->redirect();
	}

}

?>