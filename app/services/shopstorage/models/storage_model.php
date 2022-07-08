<?php 

class storage_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getStorage($id = 0)
	{
		if($id == 0) $id = $_SESSION['alias']->id;
		$this->db->select($this->table().' as s', '*', $id);
		$this->db->join('wl_users', 'name as user_name', '#s.user_add');
		$this->db->join('wl_ntkd', 'name, list as time', array('alias' => $_SESSION['alias']->id, 'content' => 0));
		$storage =  $this->db->get('single');
		if($storage)
		{
			if($_SESSION['option']->markUpByUserTypes)
			{
				if($markups = $this->db->getAllDataByFieldInArray($this->table('_markup'), $storage->id, 'storage'))
				{
					$storage->markup = array();
					foreach ($markups as $markup) {
						$storage->markup[$markup->user_type] = $markup->markup;
					}
				}
			}
		}
		return $storage;
	}

	public function getProducts($id, $user_type = 0)
	{
		if($user_type == 1)
			$user_type = 2;
		$where['storage'] = $_SESSION['alias']->id;
		if($id > 0) $where['product'] = $id;
		$this->db->select($this->table('_products'), '*', $where);
		if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
		{
			$start = 0;
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1) {
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			}
			$_SESSION['option']->paginator_total = $this->db->getCount($this->table('_products'), $_SESSION['alias']->id, 'storage');

			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}
		$this->db->join('s_shopstorage', 'currency, markup', $_SESSION['alias']->id);
		$this->db->join('wl_ntkd', 'name as storage_name, list as storage_time', array('alias' => $_SESSION['alias']->id, 'content' => 0));
		if($_SESSION['option']->markUpByUserTypes)
			$this->db->join($this->table('_markup'), 'markup', array('storage' => $_SESSION['alias']->id, 'user_type' => $user_type));
		$this->db->order('price_in ASC');
		$invoices = $this->db->get('array', false);
		$_SESSION['option']->paginator_total = $this->db->get('count');
		if($invoices && $user_type >= 0 && $_SESSION['option']->markUpByUserTypes)
		{
			foreach ($invoices as $invoice) {
				$invoice->storage_alias = $_SESSION['alias']->alias;
				if($invoice->price_out != 0)
				{
					$price_out = unserialize($invoice->price_out);
					if(isset($price_out[$user_type]))
						$invoice->price_out = $price_out[$user_type];
					else
						$invoice->price_out = end($price_out);
				}
				else
				{
					$invoice->price_out = $invoice->price_in;
					if($invoice->markup > 0)
						$invoice->price_out = round($invoice->price_in * ($invoice->markup + 100) / 100, 2);
				}
				$invoice->amount_free = $invoice->amount - $invoice->amount_reserved;
			}
		}
		elseif($invoices)
		{
			foreach ($invoices as $invoice) {
				$invoice->storage_alias = $_SESSION['alias']->alias;
				if($user_type >= 0 && empty($invoice->price_out))
				{
					$invoice->price_out = $invoice->price_in;
					if($invoice->markup > 0)
						$invoice->price_out = round($invoice->price_in * ($invoice->markup + 100) / 100, 2);
				}
				$invoice->amount_free = $invoice->amount - $invoice->amount_reserved;
			}
		}
		return $invoices;
	}

	public function getInvoice($id, $user_type = 0)
	{
		$this->db->select($this->table('_products').' as p', '*', $id);
		$this->db->join('s_shopstorage', 'currency, markup', $_SESSION['alias']->id);
		$this->db->join('wl_ntkd', 'name as storage_name, list as storage_time', array('alias' => $_SESSION['alias']->id, 'content' => 0));
		$this->db->join('wl_users as u1', 'name as manager_add_name', '#p.manager_add');
		$this->db->join('wl_users as u2', 'name as manager_edit_name', '#p.manager_edit');
		$invoice = $this->db->get('single');
		if($user_type >= 0 && $invoice && $_SESSION['option']->markUpByUserTypes)
		{
			if($user_type == 1)
				$user_type = 2;
			if($invoice->price_out != 0)
			{
				$price_out = unserialize($invoice->price_out);
				if(isset($price_out[$user_type]))
					$invoice->price_out = $price_out[$user_type];
				else
					$invoice->price_out = end($price_out);
			}
			else
			{
				$invoice->price_out = $invoice->price_in;
				if($user_type != 1)
					if($markup = $this->db->getAllDataById($this->table('_markup'), array('storage' => $invoice->storage, 'user_type' => $user_type)))
						$invoice->price_out = round($invoice->price_in * ($markup->markup + 100) / 100, 2);
			}
		}
		if($invoice)
		{
			$invoice->storage_alias = $_SESSION['alias']->alias;
			if($user_type >= 0 && empty($invoice->price_out))
			{
				$invoice->price_out = $invoice->price_in;
				if($invoice->markup > 0)
					$invoice->price_out = round($invoice->price_in * ($invoice->markup + 100) / 100, 2);
			}
			$invoice->amount_free = $invoice->amount - $invoice->amount_reserved;
		}
		else
		{
			$invoice = $this->db->select('wl_ntkd', 'name as storage_name, list as storage_time', array('alias' => $_SESSION['alias']->id, 'content' => 0))->get('single');
			$invoice->storage_alias = $_SESSION['alias']->alias;
		}
		return $invoice;
	}

	public function save($id = 0)
	{
		$data = array();
		$data['price_in'] = $this->data->post('price_in');

		if($this->data->post('priceMode') == 1)
		{
			if($_SESSION['option']->markUpByUserTypes)
			{
				$price_out = array();
				foreach ($_POST as $key => $value) {
					$key = explode('-', $key);
					if($key[0] == 'price_out' && isset($key[1]) && is_numeric($key[1]))
					{
						$key = $key[1];
						$price_out[$key] = $value;
					}
				}
				$data['price_out'] = serialize($price_out);
			}
			else
				$data['price_out'] = $this->data->post('price_out');
		}
		elseif($_SESSION['option']->markUpByUserTypes)
		{
			$price_out = array();
			if($markups = $this->db->getAllDataByFieldInArray($this->table('_markup'), $_SESSION['alias']->id, 'storage'))
			{
				foreach ($markups as $markup) {
					$price_out[$markup->user_type] = $data['price_in'] * ($markup->markup + 100) / 100;
				}
			}
			$data['price_out'] = serialize($price_out);
		}
		else
			$data['price_out'] = 0;
		
		$data['amount'] = $this->data->post('amount');
		$data['amount_reserved'] = 0;
		if($this->data->post('amount_reserved')) $data['amount_reserved'] = $this->data->post('amount_reserved');
		$data['date_in'] = 0;
		if($this->data->post('date_in'))
		{
			$date = explode('.', $this->data->post('date_in'));
			$date = mktime(0,0,0, $date[1], $date[0], $date[2]);
			if(is_numeric($date))
	            $data['date_in'] = $date;
	    }
        $data['manager_edit'] = $_SESSION['user']->id;
        $data['date_add'] = $data['date_edit'] = time();

       	if($id == 0)
		{
			$data['storage'] = $_SESSION['alias']->id;
			$data['product'] = $this->data->post('product-id');
			$data['date_out'] = 0;
			$data['manager_add'] = $_SESSION['user']->id;
			$data['date_add'] = $data['date_edit'] = time();
			return $this->db->insertRow($this->table('_products'), $data);
		}
		elseif($id > 0)
		{
			if($this->db->updateRow($this->table('_products'), $data, $id))
				return true;
		}
        return false;
	}

	public function delete($id)
	{
		if($this->db->deleteRow($this->table('_products'), $id)) return true;
		return false;
	}

	public function setReserve($data)
	{
		if(isset($data['invoice']) && isset($data['amount']))
		{
			$invoice = $this->db->getAllDataById($this->table('_products'), $data['invoice']);
			if($invoice)
			{
				$amount['amount_reserved'] = $invoice->amount_reserved + $data['amount'];
				$this->db->updateRow($this->table('_products'), $amount, $invoice->id);
				return true;
			}
		}
		return false;
	}

	public function setBook($data)
	{
		if(isset($data['invoice']) && isset($data['amount']))
		{
			if($invoice = $this->db->getAllDataById($this->table('_products'), $data['invoice']))
			{
				$amount['amount'] = $invoice->amount - $data['amount'];
				if(isset($data['reserve']) && $data['reserve']) $amount['amount_reserved'] = $invoice->amount_reserved - $data['amount'];
				$this->db->updateRow($this->table('_products'), $amount, $invoice->id);
				return true;
			}
		}
		return false;
	}

}

?>