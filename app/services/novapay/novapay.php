<?php

/*

 	Service "Nova Pay 1.0"
	for WhiteLion 1.0

*/

class novapay extends Controller {
				
    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index($uri)
    {
    	$this->load->page_404(false);
    }

    public function validate()
    {
        // file_put_contents('novapay.log', file_get_contents('php://input').PHP_EOL, FILE_APPEND);
        // file_put_contents('novapay.log', print_r (apache_request_headers(), true).PHP_EOL.PHP_EOL, FILE_APPEND);
        $novapay = json_decode(file_get_contents('php://input'));
        $id = $novapay->external_id ?? 0;
        if(is_numeric($id) && $id > 0)
        {
            $this->load->smodel('novapay_model');
            if($pay = $this->novapay_model->validate($novapay))
            {
                $this->load->function_in_alias($pay->cart_alias, '__set_Payment', $pay, true);
                echo "ok NovaPay ".$id;
            }
        }
        else
            echo "empty or incorrect input data";
        exit;
    }

    public function test_validate()
    {
        $path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP.'novapay_public_key.pem';
        if(!file_exists($path))
        {
            echo "novapay_public_key.pem not found";
            exit;
        }

        $this->load->view('test_validate_view', ['novapay_public_key' => $path]);
        exit;
    }

    public function test()
    {
        if($this->userCan())
        {
            $_SESSION['option']->testPay = 1;
            $this->load->smodel('novapay_model');
                
            $pay = new stdClass();
            $pay->id = 1;
            $pay->amount = 100;
            $pay->cart['user_name'] = $_SESSION['user']->name;
            $pay->cart['user_phone'] = $_SESSION['user']->phone ?? '';

            $product = new stdClass();
            $product->description = 'Test product';
            $product->count = 2;
            $product->price = 50;
            $pay->products = [$product];

            $pay->delivery = new stdClass();
            $pay->delivery->volume_weight = $this->data->post('volume_weight');
            $pay->delivery->weight = $this->data->post('weight');
            $pay->delivery->recipient_city = $this->data->post('recipient_city');
            $pay->delivery->recipient_warehouse = $this->data->post('recipient_warehouse');

            $this->load->view('test_view', ['pay' => $pay]);

            if($this->data->post('weight'))
            {
                $pay->novapay_id = $this->novapay_model->create_novapay_session($pay);
                $this->novapay_model->create_novapay_payment($pay);
            }
        }
        exit;
    }

    public function __get_Search($content)
    {
    	return false;
    }

    public function __get_Payment($cart)
    {
        if(!empty($cart->cart['user_phone']))
        {
            $this->load->library('validator');
            $cart->cart['user_phone'] = '+'.$this->validator->getPhone($cart->cart['user_phone']);
        }
        
        $this->load->smodel('novapay_model');
        if($novapay_id = $this->novapay_model->create_novapay_session($cart))
            if($pay = $this->novapay_model->create($novapay_id, $cart))
            {
                $pay->novapay_id = $novapay_id;
                $pay->delivery = new stdClass();
                $pay->delivery->volume_weight = 0;
                $pay->delivery->weight = 0;
                $pay->delivery->recipient_city = '';
                $pay->delivery->recipient_warehouse = '';
                if (!empty($cart->cart['shipping_info'])) {
                    $shipping_info = unserialize($cart->cart['shipping_info']);
                    $pay->delivery->recipient_city = $shipping_info['city_ref'] ?? '';
                    $pay->delivery->recipient_warehouse = $shipping_info['warehouse_ref'] ?? '';
                }

                $pay->products = [];
                foreach ($cart->cart['products'] as $cp) {
                    $product = new stdClass();
                    $product->description = $cp->info->name;
                    $product->count = $cp->quantity;
                    $product->price = $cp->price_num ?? $cp->price;
                    $pay->products[] = $product;

                    if (!empty($_SESSION['option']->id_option_weight) && !empty($cp->info->options)) {
                        foreach ($cp->info->options as $option) {
                            if($option->id == $_SESSION['option']->id_option_weight)
                            {
                                if(is_numeric($option->value))
                                    $pay->delivery->weight += $option->value;
                                break;
                            }
                        }
                    }
                    if (!empty($_SESSION['option']->id_option_volume_weight) && !empty($cp->info->options)) {
                        foreach ($cp->info->options as $option) {
                            if($option->id == $_SESSION['option']->id_option_volume_weight)
                            {
                                if(is_numeric($option->value))
                                    $pay->delivery->volume_weight += $option->value;
                                break;
                            }
                        }
                    }
                }
            
                if($result = $this->novapay_model->create_novapay_payment($pay))
                {
                    header ('HTTP/1.1 303 See Other');
                    header("Location: {$result->url}");
                    exit();
                }
            }
    	return true;
    }

    public function __get_info($payment_id=0)
    {
        if($payment_id)
        {
            $this->load->smodel('novapay_model');
            if($pay = $this->novapay_model->getPayment($payment_id))
            {
                $this->wl_alias_model->setContent();
                $pay->name = $_SESSION['alias']->name;
                $pay->info = 'Сума: <b>'.$pay->amount.' грн</b> </p>';
                $pay->info .= '<p>Статус оплати: <b>'.$pay->status.'</b> від <b>'.date('d.m.Y H:i', $pay->date_edit).'</b> </p>';
                $pay->info .= '<p>Заявку на оплату сформовано: <b>'.date('d.m.Y H:i', $pay->date_add).'</b>';
                $pay->admin_link = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$pay->id;
                return $pay;
            }
        }
        return false;
    }

}

?>