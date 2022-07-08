<?php

class save extends Controller {

	public $errors = array();
    private $file_allowed_ext = ['jpg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    private $attach = [];

    function _remap($method)
    {
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index()
    {
    	$formName = $this->data->uri(1);

    	if($formName != '')
        {
            $form = $this->db->getAllDataById('wl_forms', $formName, 'name');
            if($form && $form->table != '' && $form->type > 0 && $form->type_data > 0)
            {
                if($form->captcha && !$this->userIs())
                {
                    $this->load->library('recaptcha');
                    if($this->recaptcha->check($this->data->post('g-recaptcha-response')) == false)
                    {
                        $this->errors[] = $this->text('Заповніть "Я не робот"');
                    }
                }

                $this->db->select('wl_fields as f', '*', $form->id, 'form');
                $this->db->join('wl_input_types', 'name as type_name', '#f.input_type');
                if($fields = $this->db->get('array'))
                {
                	$data = $data_id = array();
                	foreach ($fields as $field) {
                		$input_data = null;

                        if($field->type_name == 'file' && !empty($_FILES[$field->name]['name']))
                        {
                            $path_info = pathinfo($_FILES[$field->name]['name']);
                            $extension = $path_info['extension'];
                            if($extension == 'jpeg')
                                $extension = 'jpg';
                            if(in_array($extension, $this->file_allowed_ext))
                                $input_data = sha1($form->name .' save ' . $field->name . time()) . '.'.$extension;
                        }
                        else
                        {
                    		if($form->type == 1)
                                $input_data = $this->data->get($field->name);
                    		elseif($form->type == 2)
                                $input_data = $this->data->post($field->name);
                        }
                		if($field->required && $input_data === null)
                			$this->errors[] = $this->text("Field '{$field->title}' is required!");

                		if($input_data)
                        {
                            $data[$field->name] = $input_data;
                			$data_id[$field->name] = $field->id;
                		}
                	}

                	if(!empty($data) && empty($this->errors))
                    {
            			if($form->type_data == 1)
                        {
                            foreach ($data as $field => $value) {
                                $row['field'] = $data_id[$field];
                                $row['value'] = $value;
                                $this->db->insertRow($form->table, $row);
                            }
                        } 
                        elseif($form->type_data == 2)
                        {
                            $data['date_add'] = time();
                            $data['language'] = isset($_SESSION['language']) ? $_SESSION['language'] : null;
                            $data['new'] = 1;
                            $data['user_id'] = $this->userIs() ? $_SESSION['user']->id : 0;
                            $data['id'] = $this->db->insertRow($form->table, $data);

                            foreach ($fields as $field) {
                                if($field->type_name == 'file' && !empty($_FILES[$field->name]['name']) && !empty($data[$field->name]))
                                {
                                    $file_name = $data['id'] .'-'. $field->name .'-'. $data[$field->name];

                                    $path = 'files';
                                    if(!is_dir($path))
                                        mkdir($path, 0777);

                                    $path .= "/form_" . $form->name;
                                    if(!is_dir($path)) {
                                        mkdir($path, 0777);
                                    }

                                    $uploaded = false;
                                    $path .= "/" . $file_name;
                                    $file = $_FILES[$field->name]["tmp_name"];
                                    if(is_uploaded_file($file)) {
                                        if(move_uploaded_file($file, $path)) {
                                            $uploaded = true;
                                        }
                                    }
                                    if($uploaded)
                                    {
                                        $this->attach[$path] = $field->name;
                                        $this->db->updateRow($form->table, [$field->name => $file_name], $data['id']);
                                    }
                                    else
                                        $this->db->updateRow($form->table, [$field->name => ''], $data['id']);
                                }
                            }
                        }
                	}
                    else
                    {
                        if ($form->success == '4')
                            $this->load->json(array('errors' => implode('</p><p>', $this->errors)));
                        else
                            $this->load->notify_view(array('errors' => implode('</p><p>', $this->errors)));
                    }
                    $where['form'] = $form->id;
                    $where['active'] = 1;

                    if($form->send_sms == 1 && $form->sms_text != '' &&($data['tel'] || $data['phone']))
                    {
                        $phone = $data['tel'] ?: $data['phone'];

                        if(substr($phone, 0, 1) == '0')
                            $phone = "+38" . $phone;
                        elseif(substr($phone, 0, 2) == '80')
                            $phone = "+3" . $phone;

                        $this->load->library('turbosms');
                        $this->turbosms->send($phone, $form->sms_text);
                    }

                	$mails = $this->db->getAllDataByFieldInArray('wl_mail_active', $where);
                    if(!empty($mails))
                    {
                        $this->load->library('mail');
                        foreach ($mails as $key => $mail) 
                        {
                            if($mail = $this->db->getAllDataById('wl_mail_templates', $mail->template))
                            {
                                $join['template'] = $mail->id;
                                if($mail->multilanguage == 1)
                                    $join['language'] = $_SESSION['language'];

                                $message = $this->db->getAllDataById('wl_mail_templats_data', $join);
                                $mail->subject = $message->title;
                                $mail->message = $message->text;
                                $mail->template = $mail->id;
                                $mail->attach = $this->attach;

                                $data['date_add'] = date('d.m.Y H:i', $data['date_add']);
                                if(!$sendMail = $this->mail->sendMailTemplate($mail, $data))
                                    exit('Error sending mail! Data saved successfully.');
                            }    
                        }
                    }
                    switch ($form->success) 
                    {
                        case '1':
                            $this->redirect();
                            break;
                        case '2':
                            $lang = $_SESSION['language'];
                            $text = $_SESSION['all_languages'] ? json_decode($form->success_data)->$lang : $form->success_data;
                            $this->load->notify_view(array('success' => $text));
                            break;
                        case '3':
                            header("Location:".SITE_URL.$form->success_data);
                            break;
                        case '4':
                            $lang = $_SESSION['language'];
                            $text = $_SESSION['all_languages'] ? json_decode($form->success_data)->$lang : $form->success_data;
                            $this->load->json(array('success' => $text));
                            break;
                    }
                }
            }
        }
    }

    public function test_mail()
    {
        if($this->userCan())
        {
            $this->load->library('validator');
            if($to = $this->data->get('to'))
            {
                if(!$this->validator->email('email', $to))
                    $to = 'levso7@gmail.com';
            }
            else
                $to = 'levso7@gmail.com';

            echo "email to: ".$to;
            echo "<pre>";
            
            if($this->validator->email('email', $to))
            {
                $this->load->library('mail');
                print_r( $this->mail->sendTemplate('test_mail', $to, [], true) );
            }
        }
        else
            $this->redirect('login');
    }
}

?>