<?php

/*

 	Service "Shop product price per amount 1.1"
	for WhiteLion 1.0

*/

class price_per_amount extends Controller {
				
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

    public function __get_Search($content)
    {
    	return false;
    }

    public function __update_Product($product)
    {
        $this->load->smodel('ppa_model');
        return $this->ppa_model->getProduct($product);
    }

	public function __update_Products($products)
	{
		$this->load->smodel('ppa_model');
        $all = $currency = false;
        if(isset($products['all']) && isset($products['products']))
        {
            $all = true;
            if(isset($products['currency']))
                $currency = $products['currency'];
            $products = $products['products'];
        }
		return $this->ppa_model->getProducts($products, $currency, $all);
	}
	
}

?>