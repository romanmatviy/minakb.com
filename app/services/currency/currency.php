<?php

/*

 	Service "Currency 2.3"
	for WhiteLion 1.3

*/

class currency extends Controller {
				
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
        $this->__set_Currency();
    	$this->load->notify_view(['success' => $this->text('Курс валют оновлено')]);
    }

    public function __get_Search($content)
    {
    	return false;
    }

    public function __get_Currency($code)
    {
        $this->load->smodel('currency_model');
        return $this->currency_model->get($code);
    }

    public function __set_Currency()
    {
        $this->load->smodel('currency_model');
        $_SESSION['currency'] = $this->currency_model->getAll();
        return $_SESSION['currency'];
    }

    public function __page_before_init()
    {
        $this->__set_Currency();
        // для того щоб відключити кешування у сесії на 15 хв
        // $_SESSION['__page_before_init'][$_SESSION['alias']->id] = 0;
    }

}

?>