<?php

/*

 	Service "NovaPay 1.0"
	for WhiteLion 1.0

*/

class novapay_admin extends Controller {
				
    function _remap($method, $data = array())
    {
    	$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index($uri)
    {
    	$this->load->smodel('novapay_model');
    	if(is_numeric($uri))
    	{
    		$payment = $this->novapay_model->getPayment($uri);
    		if($payment)
    		{
    			$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Оплата #'.$uri => '');
				$_SESSION['alias']->name .= '. Оплата #'.$uri;

    			$this->load->admin_view('detal_view', array('payment' => $payment));
    		}
    		$this->load->page_404();
    	}
    	else
    	{
            $_SESSION['option']->paginator_per_page = 50;
	    	$this->load->admin_view('list_view', array('payments' => $this->novapay_model->getPayments()));
    	}
    }

    public function complete_hold()
    {
        if ($id = $this->data->post('id')) {
            $this->load->smodel('novapay_model');
            if($payment = $this->novapay_model->getPayment($id))
            {
                if($payment->check)
                {
                    $_SESSION['notify'] = new stdClass();
                    if($this->novapay_model->complete_hold($payment))
                        $_SESSION['notify']->success = 'Відправлено запит на підтвердження платежу. Результат розгляду очікуйте за 2-3 хв (оновіть сторінку)';
                    else
                        $_SESSION['notify']->errors = 'Помилка підтвердження платежу. Перевірте статус оплати та спробуйте ще раз';
                }
                else
                {
                    echo "bad payment signature";
                    exit;
                }
            }
            else
            {
                echo "bad payment id. check post data";
                exit;
            }
        }
        $this->redirect();
    }

    public function confirm_delivery_hold()
    {
        if ($id = $this->data->post('id')) {
            $this->load->smodel('novapay_model');
            if($payment = $this->novapay_model->getPayment($id))
                if($pay = $this->novapay_model->confirm_delivery_hold($payment))
                {
                    $this->load->function_in_alias($pay->cart_alias, '__set_Payment', $pay, true);
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = "Відправлено запит на підтвердження платежу. <br>Результат розгляду очікуйте за 2-3 хв (оновіть сторінку)<hr>ТТН Нової пошти: <strong>{$pay->express_waybill}</strong>";
                }
        }
        $this->redirect();
    }

    public function void()
    {
        if ($id = $this->data->post('id')) {
            $this->load->smodel('novapay_model');
            if($payment = $this->novapay_model->getPayment($id))
                if($this->novapay_model->void($payment))
                {
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = 'Відправлено запит на скасування платежу. Результат розгляду очікуйте за 2-3 хв (оновіть сторінку)';
                }
        }
        $this->redirect();
    }

    public function get_status()
    {
        if ($id = $this->data->post('id')) {
            $this->load->smodel('novapay_model');
            if($payment = $this->novapay_model->getPayment($id))
            {
                $debug = false;
                if(!empty($_POST['debug']))
                    $debug = true;
                if($pay = $this->novapay_model->get_status($payment, $debug))
                {
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = "Статус оновлено: <strong>{$pay->status}</strong>";
                }
            }
        }
        $this->redirect();
    }

    public function save_options()
    {
        if($_SESSION['user']->type == 1 && $this->data->post('service'))
        {
            if($this->data->post('mode'))
            {
                $value = $this->data->post('mode');

                $where = array('alias' => $_SESSION['alias']->id, 'name' => 'mode');
                $where['service'] = $this->data->post('service');
                if($option = $this->db->getAllDataById('wl_options', $where))
                {
                    if($option->value != $value)
                        $this->db->updateRow('wl_options', array('value' => $value), $option->id);
                }
                else
                {
                    $where['value'] = $value;
                    $this->db->insertRow('wl_options', $where);
                }
            }
            if($this->data->post('service'))
            {
                $value = $this->data->post('status');

                $where = array('alias' => $_SESSION['alias']->id, 'name' => 'successPayStatusToCart');
                $where['service'] = $this->data->post('service');
                if($option = $this->db->getAllDataById('wl_options', $where))
                {
                    if($option->value != $value)
                        $this->db->updateRow('wl_options', array('value' => $value), $option->id);
                }
                else
                {
                    $where['value'] = $value;
                    $this->db->insertRow('wl_options', $where);
                }
            }
            

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Статус замовлення у корзині після успішної оплати оновлено';
        }
        $this->redirect();
    }

    public function save_RSAkeys()
    {
        if($_SESSION['user']->admin)
        {
            $_SESSION['notify'] = new stdClass();
            $path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys';
            if(!is_dir($path))
                mkdir($path, 0777);
            $path .= DIRSEP;
            if(!empty($_FILES['public_key']))
                if(move_uploaded_file($_FILES['public_key']['tmp_name'], $path.'public.pem'))
                    $_SESSION['notify']->success = "Публічний RSA ключ оновлено";
            if(!empty($_FILES['private_key']))
                if(move_uploaded_file($_FILES['private_key']['tmp_name'], $path.'private.pem'))
                {
                    if(empty($_SESSION['notify']->success))
                        $_SESSION['notify']->success = "Приватний RSA ключ оновлено";
                    else
                        $_SESSION['notify']->success .= "<br>Приватний RSA ключ оновлено";
                }
        }
        $this->redirect();
    }

    public function test_rsa_keys($value='')
    {
        $_SESSION['notify'] = new stdClass();
        $path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP;
        if (file_exists($path.'private.pem') && file_exists($path.'public.pem'))
        {
            $data = 'test data';
            $private_key = openssl_pkey_get_private (file_get_contents($path.'private.pem'), $_SESSION['option']->privatePassphrase);
            $public_key = openssl_pkey_get_public (file_get_contents($path.'public.pem'));

            try {
                $binary_signature = "";
                openssl_sign($data, $binary_signature, $private_key, OPENSSL_ALGO_SHA1);

                // Check signature
                $ok = openssl_verify($data, $binary_signature, $public_key, OPENSSL_ALGO_SHA1);
                if ($ok == 1)
                    $_SESSION['notify']->success = "Ключі доступу коректні / так має бути";
                elseif ($ok == 0)
                    $_SESSION['notify']->errors = "Ключі доступу не відповідають один одному. Перевірте пароль захисту private rsa key (якщо є)";
                else
                    $_SESSION['notify']->errors = "Помилка формату private/public.pem RSA ключа. Перевірте файли ключів";
            } catch (Exception $E) {
                $_SESSION['notify']->errors = $E->getMessage();
            }
        }
        else
            $_SESSION['notify']->errors = "File private.pem/public.pem not find";
        $this->redirect();
    }

    public function public_pem()
    {
        $path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP;
        if(file_exists($path.'public.pem'))
        {
            header("Content-type: text/plain");
            readfile($path.'public.pem');
            exit();
        }
        else
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->errors = "File public.pem not find";
            $this->redirect();
        }
    }

    public function private_pem()
    {
        $path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP;
        if(file_exists($path.'private.pem'))
        {
            header("Content-type: text/plain");
            readfile($path.'private.pem');
            exit();
        }
        else
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->errors = "File private.pem not find";
            $this->redirect();
        }
    }
	
}

?>