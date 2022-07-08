<?php

/*

 	Service "paypal 1.0"
	for WhiteLion 1.0

*/

class paypal_admin extends Controller {
				
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
    	$this->load->smodel('paypal_model');
    	if(is_numeric($uri))
    	{
    		if($payment = $this->paypal_model->getPayment($uri))
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
	    	$this->load->admin_view('list_view', array('payments' => $this->paypal_model->getPayments()));
    	}
    }
	
}

?>