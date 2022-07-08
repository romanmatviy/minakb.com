<?php

/*

 	Service "PayPal 1.0"
	for WhiteLion 1.0

*/

class paypal extends Controller {
				
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
    	$this->load->page_404();
    }

    public function validate()
    {
        $this->load->smodel('paypal_model');
        if($pay = $this->paypal_model->validate())
            $this->load->function_in_alias($pay->cart_alias, '__set_Payment', $pay, true);
    }

    public function __get_Search($content)
    {
    	return false;
    }

    public function __get_Payment($cart)
    {
        $this->wl_alias_model->setContent();
        $_SESSION['alias']->link = $_SESSION['alias']->alias;
        
        $this->load->smodel('paypal_model');
        $pay = $this->paypal_model->create($cart);
        $pay->return_url = $cart->return_url;
        $this->load->page_view('paypal_form_view', array('pay' => $pay));
    	return true;
    }

    public function __get_info($payment_id=0)
    {
        if($payment_id)
        {
            $this->load->smodel('paypal_model');
            if($pay = $this->paypal_model->getPayment($payment_id))
            {
                $this->wl_alias_model->setContent();
                $pay->name = $_SESSION['alias']->name;
                $pay->info = 'Сума: <b>'.$pay->amount.' '.$pay->currency_code.'</b> </p>';
                $pay->info .= '<p>Статус оплати: <b>'.$pay->status.'</b> від <b>'.date('d.m.Y H:i', $pay->date_edit).'</b> </p>';
                $pay->info .= '<p>Відповідь банку: <b>'.$pay->comment.'</b> </p>';
                $pay->info .= '<p>Заявку на оплату сформовано: <b>'.date('d.m.Y H:i', $pay->date_add).'</b>';
                $pay->admin_link = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$pay->id;
                return $pay;
            }
        }
        return false;
    }
}

?>