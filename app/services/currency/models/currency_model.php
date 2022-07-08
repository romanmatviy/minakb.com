<?php 

class currency_model
{

	public function table($sufix = '')
	{
		return $_SESSION['service']->table.$sufix;
	}

	public function create()
	{
		$currency['code'] = $this->data->post('code');
		$currency['currency'] = $this->data->post('currency');
		$currency['day'] = strtotime('today');
		$id = $this->db->insertRow($this->table(), $currency);
		if($_SESSION['option']->saveToHistory)
		{
			$history['currency'] = $id;
			$history['value'] = $currency['currency'];
			$history['day'] = $currency['day'];
			$history['from'] = 'Користувач: '.$_SESSION['user']->id.'. '.$_SESSION['user']->name;
			$history['update'] = time();
			$this->db->insertRow($this->table('_history'), $history);
		}
		return true;
	}

	public function update($id, $newCurrency = -1)
	{
		if($newCurrency < 0)
			$currency['currency'] = $this->data->post('currency');
		else
			$currency['currency'] = $newCurrency;
		$currency['day'] = strtotime('today');
		$this->db->updateRow($this->table(), $currency, $id);
		if($_SESSION['option']->saveToHistory)
		{
			$history['currency'] = $id;
			$history['value'] = $currency['currency'];
			$history['day'] = $currency['day'];
			if($newCurrency < 0)
				$history['from'] = 'Користувач: '.$_SESSION['user']->id.'. '.$_SESSION['user']->name;
			else
				$history['from'] = 'Privat24';
			$history['update'] = time();
			$this->db->insertRow($this->table('_history'), $history);
		}
		return true;
	}

	public function delete($id)
	{
		if($_SESSION['option']->saveToHistory)
			$this->db->deleteRow($this->table('_history'), $id, 'currency');
		return $this->db->deleteRow($this->table(), $id);
	}

	public function updatePrivat24($code = false, $currency_all = false)
	{
		$sale = 1;
		if(empty($currency_all))
			$currency_all = $this->db->getAllData($this->table());
		if($currency_all)
		{
			$currency = $currencies = array();
			foreach ($currency_all as $row) {
				$currency[$row->code] = clone $row;
				$currencies[$row->code] = $row->currency;
			}

			$json = file_get_contents('https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5');
			if($privat24 = json_decode($json))
				foreach ($privat24 as $row) {
					if(isset($currency[$row->ccy]) && $currency[$row->ccy]->currency != $row->sale)
					{
						$this->update($currency[$row->ccy]->id, $row->sale);
						$currencies[$row->ccy] = $row->sale;
					}
					if($code == $row->ccy)
						$sale = $row->sale;
				}

			if($code == '*')
				return $currencies;
		}
		if($code)
			return $sale;
		return true;
	}

    public function get($code = false)
    {
    	$currency = $this->db->cache_get($code);
        if($currency !== NULL)
        {
        	if($_SESSION['option']->autoUpdate)
    		{
	    		$today = strtotime('today');
	    		if($currency->day != $today)
	    		{
	    			$currency->currency = $this->updatePrivat24($code);
	    			$currency->day = $today;
	    			$this->db->cache_add($code, $currency);
	    		}
	    	}
            return $currency->currency;
        }
    	if($currency = $this->db->getAllDataById($this->table(), $code, 'code'))
    	{
    		if($_SESSION['option']->autoUpdate)
    		{
	    		$today = strtotime('today');
	    		if($currency->day != $today)
	    		{
	    			$currency->currency = $this->updatePrivat24($code);
	    			$currency->day = $today;
	    			$this->db->cache_add($code, $currency);
	    		}
	    	}
    		return $currency->currency;
    	}
    	return false;
	}

    public function getAll()
    {
    	$update = false;
    	$list = $this->db->cache_get('all');
        if($list === NULL)
        {
        	$update = true;
        	$list = $this->db->getAllData($this->table());
        }
    	$currencies = [];
    	$today = strtotime('today');
    	if($list)
    		foreach ($list as $currency) {
	    		if($_SESSION['option']->autoUpdate && $currency->day != $today)
	    		{
		    		$currencies = $this->updatePrivat24('*', $list);
		    		$update = true;
		    		break;
	    		}
		    	$currencies[$currency->code] = $currency->currency;
		    }
		if($update)
		{
			$this->db->cache_delete_all();
			$this->db->cache_add('all', $list);
		}
    	return $currencies;
	}

}

?>