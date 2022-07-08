<?php

/*

 	Service "Shop product price per amount 1.1"
	for WhiteLion 1.0

*/

class price_per_amount_admin extends Controller {
				
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
	
	public function save()
	{
		$this->load->smodel('ppa_model');
		$this->ppa_model->save();

		$_SESSION['notify'] = new stdClass();
		$_SESSION['notify']->success = 'Керування ціною оновлено!';

		$this->redirect("#tab-".$_SESSION['alias']->alias);
	}
	
	public function delete()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('ppa_model');
			if($this->ppa_model->delete($_POST['id']))
			{
				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->success = 'Керування ціною скасовано!';
			}
		}
		$this->redirect();
	}

	public function __tab_product($product)
    {   
        $this->load->smodel('ppa_model');
        ob_start();
        $this->load->view('admin/__tab_product_prices', array('product' => $this->ppa_model->getProduct($product)));
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = 'Керування ціною';
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }
	
}

?>