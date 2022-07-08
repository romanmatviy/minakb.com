<?php

/*

 	Service "LiqPay 1.4"
	for WhiteLion 1.2

*/

class liqpay extends Controller {
				
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
        $id = $this->data->uri(2);
        if(is_numeric($id) && $id > 0)
        {
            $this->load->smodel('liqpay_model');
            if($pay = $this->liqpay_model->validate($id))
                $this->load->function_in_alias($pay->cart_alias, '__set_Payment', $pay, true);
        }
        else
            $this->load->page_404(false);
    }

    public function __get_Search($content)
    {
    	return false;
    }

    public function __get_Payment($cart)
    {
        $this->wl_alias_model->setContent();
        $_SESSION['alias']->link = $_SESSION['alias']->alias;
        
        if(!empty($cart->payed) && $cart->payed > 0 && $cart->payed < $cart->total)
            $cart->total -= $cart->payed;

        $this->load->smodel('liqpay_model');
        $pay = $this->liqpay_model->create($cart);
        $pay->return_url = $cart->return_url;
        $this->load->page_view('liqpay_form_view', array('pay' => $pay));
    	return true;
    }

    public function __get_info($payment_id=0)
    {
        if($payment_id)
        {
            $this->load->smodel('liqpay_model');
            if($pay = $this->liqpay_model->getPayment($payment_id))
            {
                $this->wl_alias_model->setContent();
                $pay->name = $_SESSION['alias']->name;
                $pay->info = 'Сума: <b>'.$pay->amount.' грн</b> </p>';
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