<?php

/*

 	Service "Shop product price per user type 1.0"
	for WhiteLion 1.0

*/

class price_per_type_admin extends Controller {
				
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
    	$this->wl_alias_model->setContent();
    	$_SESSION['alias']->breadcrumb = array('Керування ціною' => '');
    	$this->load->admin_view('index_view');
    }
	
	public function saveForProduct()
	{
		$this->load->smodel('ppt_model');
		if($this->ppt_model->saveForProduct())
			$this->load->ajax(array('result' => true));
	}
	
	public function saveForShop()
	{
		$this->load->smodel('ppt_model');
		if($this->ppt_model->saveForShop())
			$this->load->ajax(array('result' => true));
	}

	public function deleteForProduct()
	{
		$this->load->smodel('ppt_model');
		if($this->ppt_model->deleteForProduct())
			$this->load->ajax(array('result' => true));
	}

	public function __tab_product(&$product)
    {   
        $product->price_per_type = new stdClass();
        $product->price_per_type->userTypes = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
        $product->price_per_type->shopData = $product->price_per_type->productData = array();
        if($rows = $this->db->getAllDataByFieldInArray($_SESSION['service']->table, $product->wl_alias, 'shop_alias'))
            foreach ($rows as $row) {
                $product->price_per_type->shopData[$row->user_type] = array('change_price' => $row->change_price, 'price' => $row->price, 'currency' => $row->currency);
            }
        if($rows = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_product', array('product_alias' => $product->wl_alias, 'product_id' => $product->id)))
            foreach ($rows as $row) {
                $product->price_per_type->productData[$row->user_type] = array('change_price' => $row->change_price, 'price' => $row->price, 'currency' => $row->currency);
            }

        ob_start();
        $this->load->view('admin/__tab_product_prices', array('product' => $product));
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = 'Ціна від типу клієнта';
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }
	
}

?>