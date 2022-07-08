<?php

/*

 	Service "pb_payparts 1.0"
	for WhiteLion 1.2

*/

class pb_payparts_admin extends Controller {
				
    function _remap($method, $data = array())
    {
        $this->wl_alias_model->setContent();
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
    	$this->load->smodel('pb_payparts_model');
    	if(is_numeric($uri))
    	{
    		$payment = $this->pb_payparts_model->getPayment($uri);
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
	    	$this->load->admin_view('list_view', array('payments' => $this->pb_payparts_model->getPayments()));
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

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Статус замовлення у корзині після успішної оплати оновлено';
        }
        $this->redirect();
    }
	
}

?>