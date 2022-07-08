<?php

/*

 	Service "LiqPay 1.4"
	for WhiteLion 1.2

*/

class liqpay_admin extends Controller {
				
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
    	$this->load->smodel('liqpay_model');
    	if(is_numeric($uri))
    	{
    		if($payment = $this->liqpay_model->getPayment($uri))
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
	    	$this->load->admin_view('list_view', array('payments' => $this->liqpay_model->getPayments()));
    	}
    }

    public function save_successPayStatusToCart()
    {
        if($_SESSION['user']->type == 1 && $this->data->post('service'))
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

            $this->db->cache_delete($_SESSION['alias']->alias, 'wl_aliases');
            if(isset($_SESSION['alias-cache'][$_SESSION['alias']->id]))
                unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Статус замовлення у корзині після успішної оплати оновлено';
        }
        $this->redirect();
    }

    public function __confirmPayment($payment_id)
    {
        return true;
    }
    
    public function __cancelPayment($payment_id)
    {
        return true;
    }

    public function test()
    {
        if(!$_SESSION['user']->admin)
            $this->redirect($_SESSION['alias']->alias);

        $_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Pay test' => '');
            $_SESSION['alias']->name = 'LiqPay pay selfTest';

        $res = false;
        $this->load->smodel('liqpay_model');
        if($pay_id = $this->data->post('pay_id'))
            if($pay = $this->liqpay_model->getPayment($pay_id))
            {
                $payment = array();
                $payment['version'] = 3;
                $payment['amount'] = $pay->amount;
                $payment['currency'] = 'UAH';
                $payment['transaction_id'] = 'WL test pay system';
                $payment['order_id'] = $pay_id;
                $payment['status'] = $this->data->post('status');
                $payment['action'] = 'buy';
                $payment['language'] = 'uk';

                $data = base64_encode( json_encode($payment) );
                $signature = base64_encode( sha1( $_SESSION['option']->private_key . $data . $_SESSION['option']->private_key , 1 ) );

                $curl_data = array('data' => $data, 'signature' => $signature);

                // echo "<pre>";
                // print_r($payment);
                // echo "</pre>";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, SITE_URL.$_SESSION['alias']->alias.'/validate/'.$pay_id);
                curl_setopt($ch, CURLOPT_USERAGENT, 'server');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                $res = curl_exec($ch);
                curl_close($ch);
            }

        $this->load->admin_view('test_view', compact('res'));
    }
	
}

?>