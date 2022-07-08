<?php

/*

 	Service "pb_payparts 1.0"
	for WhiteLion 1.2

*/

class pb_payparts extends Controller {
				
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

    public function test()
    {
        if($this->userCan())
        {
            $cart = new stdClass();
            $cart->id = 142;
            $cart->wl_alias = 15;
            $cart->total = 1000;
            $cart->payed = 200;
            $this->load->page_view('form_view', array('cart' => $cart));
        }
        else
            $this->load->page_404(false);
    }

    public function init()
    {
        if($cart_alias_id = $this->data->post('cart_id'))
        {
            $cart_alias_id = explode('-', $cart_alias_id);
            $cart_alias = $cart_alias_id[0];
            $cart_id = $cart_alias_id[1] ?? 0;
            if(is_numeric($cart_alias) && $cart_alias > 0 && is_numeric($cart_id) && $cart_id > 0)
            {
                if($cart = $this->load->function_in_alias($cart_alias, '__getById', $cart_id))
                {
                    $cart->wl_alias = $cart_alias;
                    // echo "<pre>";
                    // print_r($cart);
                    if(empty($cart->products))
                    {
                        $this->load->notify_view(['errors' => $this->text('Відсутні товари у замовленні!')]);
                        exit;
                    }
                    if($cart->payed >= $cart->total)
                    {
                        $this->load->notify_view(['success' => $this->text('Замовлення оплачено')]);
                        exit;
                    }
                    if($cart->payed > 0 && $cart->payed < $cart->total)
                        $cart->total -= $cart->payed;

                    $this->load->smodel('pb_payparts_model');
                    if($pay = $this->pb_payparts_model->create($cart))
                        if(!empty($pay['token']))
                            header('Location: https://payparts2.privatbank.ua/ipp/v2/payment?token=' . $pay['token']);
                    exit;
                }
            }
        }
        exit;
    }

    public function validate()
    {
        $id = $this->data->uri(2);
        if(is_numeric($id) && $id > 0)
        {
            $this->load->smodel('pb_payparts_model');
            if($pay = $this->pb_payparts_model->validate($id))
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

        if($cart->payed >= $cart->total)
        {
            $this->load->notify_view(['success' => $this->text('Замовлення оплачено')]);
            exit;
        }
        
        $this->load->page_view('form_view', array('cart' => $cart));
    	return true;
    }

    public function __get_info($payment_id=0)
    {
        if($payment_id)
        {
            $this->load->smodel('pb_payparts_model');
            if($pay = $this->pb_payparts_model->getPayment($payment_id))
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