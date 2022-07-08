<?php

class subscribe extends Controller {
				
    private $additionall = array('phone'); // false додаткові поля при реєстрації. Згодом можна використовувати у ідентифікації, тощо
    private $new_user_type = 5; // Ід типу новозареєстрованого користувача
    private $special_types = array('order-call' => 6, 'price' => 7); // Ід спец типу (за полем type). Інакше false
    private $after_good_view = 'temp_subscribe_success';

    public function index()
    {
        $this->wl_alias_model->setContent(0, 202);
        $_SESSION['alias']->title = $_SESSION['alias']->name;
        
        $_SESSION['notify'] = new stdClass();

        $this->load->library('recaptcha');
        if($this->recaptcha->check($this->data->post('g-recaptcha-response')) == false)
        {
            $_SESSION['notify']->errors = $this->text('Заповніть "Я не робот"');
            $this->load->notify_view();
        }
        else
        {
    		$this->load->library('validator');
            $this->validator->setRules('email', $this->data->post('email'), 'required|email|3..40');

            if($this->validator->run())
            {
    			$this->load->model('wl_user_model');
                $info['email'] = $this->data->post('email');
                $info['name'] = $this->data->post('name');
                $info['password'] = '';
                $info['photo'] = '';
                $additionall = array();
                if(!empty($this->additionall))
                {
                    foreach ($this->additionall as $key)
                    {
                        $value = $this->data->post($key);
                        if($value)
                            $additionall[$key] = $value;
                    }
                }
                if($this->special_types && array_key_exists($this->data->post('type'), $this->special_types))
                    $this->new_user_type = $this->special_types[$this->data->post('type')];

                $_SESSION['notify']->title = $_SESSION['alias']->name;
                if($this->userIs())
                {
                    if(empty($_SESSION['user']->email))
                    {
                        $this->db->updateRow('wl_users', ['email' => $info['email']], $_SESSION['user']->id);
                        $_SESSION['user']->email = $info['email'];
                    }
                    $_SESSION['notify']->success = 'Дякуємо! Ваш email успішно доданий до бази!';
                }
                else
                {
                    if ($user = $this->wl_user_model->add($info, $additionall, $this->new_user_type, false))
                        $_SESSION['notify']->success = 'Дякуємо! Ваш email успішно доданий до бази!';
                    else
                        $_SESSION['notify']->success = 'Увага! Ваш email вже є у базі!';
                }
    			
                $this->load->notify_view();
    			// $this->load->view($this->after_good_view, array('user' => $user));
        	}
            else
            {
                $_SESSION['notify']->errors = 'Невірний формат email';
        		$this->load->notify_view();
        	}
        }
    }
	
}

?>