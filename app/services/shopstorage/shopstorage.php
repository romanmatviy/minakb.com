<?php

/*

 	Service "Shop Storage 1.2"
	for WhiteLion 1.0

*/

class shopstorage extends Controller {
				
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

    public function __get_Search($content)
    {
    	return false;
    }

	public function __get_Invoices_to_Product($id = 0)
	{
		$productId = 0;
		$userType = $_SESSION['user']->type ?? $_SESSION['option']->new_user_type;
		if(is_array($id))
		{
			if(isset($id['id']))
				$productId = $id['id'];
			if(isset($id['user_type']))
				$userType = $id['user_type'];
			if(isset($id['autoAddInvoice']) && isset($_SESSION['option']->autoAddInvoice) && $_SESSION['option']->autoAddInvoice == 1)
			{
				$this->load->smodel('storage_model');
				$storage = $this->storage_model->getStorage();
				$invoice = new stdClass();
				$invoice->id = $invoice->price_out = $invoice->price_in = $invoice->amount_free = 0;
				$invoice->storage = $_SESSION['alias']->id;
				$invoice->storage_alias = $_SESSION['alias']->alias;
				$invoice->storage_name = $storage->name;
				$invoice->storage_time = $storage->time;
				return array($invoice);
			}
		}
		else
			$productId = $id;
		if($productId > 0)
		{
			$this->load->smodel('storage_model');
			$_SESSION['option']->paginator_per_page = 0;
			return $this->storage_model->getProducts($productId, $userType);
		}
		return false;
	}

	public function __get_Invoice($id = 0)
	{
		$invoiceId = 0;
		$userType = $_SESSION['user']->type ?? $_SESSION['option']->new_user_type;
		if(is_array($id))
		{
			if(isset($id['id']))
				$invoiceId = $id['id'];
			if(isset($id['user_type']))
				$userType = $id['user_type'];
		}
		elseif(is_numeric($id))
			$invoiceId = $id;
		if($invoiceId > 0)
		{
			$this->load->smodel('storage_model');
			return $this->storage_model->getInvoice($invoiceId, $userType);
		}
		return false;
	}

	// Зарезервувати товар за номером Invoice
	// invoise, amount
	public function __set_Reserve($data = array())
	{
		$this->load->smodel('storage_model');
		return $this->storage_model->setReserve($data);
	}

	// Списати товар за номером Invoice
	// invoise, amount, reserve
	public function __set_Book($data = array())
	{
		$this->load->smodel('storage_model');
		return $this->storage_model->setBook($data);
	}

	public function __get_storage_info()
	{
		$this->load->smodel('storage_model');
		$storage = $this->storage_model->getStorage();
		$storage->alias = $_SESSION['alias']->alias;
		return $storage;
	}
	
}

?>