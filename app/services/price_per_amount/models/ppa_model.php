<?php 

class ppa_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getProduct($product)
	{
		$where = array();
		if(isset($product->wl_alias))
		{
			$where['product_alias'] = $product->wl_alias;
			$where['product_id'] = $product->id;
		}
		else if(isset($product->product_alias))
		{
			$where['product_alias'] = $product->product_alias;
			$where['product_id'] = $product->product_id;
		}
		if(!empty($where))
		{
			if($marketing = $this->db->getAllDataById($this->table(), $where))
			{
				$product->price_before = $product->price;
				$product->discount = 0;
				$product->marketing = unserialize($marketing->price);
				if($quantity = $this->data->post('quantity'))
					// if(empty($product->quantity))
						$product->quantity = $quantity;
				if(is_array($product->marketing) && isset($product->quantity))
				{
					krsort ($product->marketing);
					foreach ($product->marketing as $from => $price) {
						if($product->quantity >= $from)
						{
							if(!empty($product->currency))
							{
								$product->price = $price * $product->currency;
								$product->price = round($product->price * 20) / 20;
								$product->discount = ($product->price_before - $product->price) * $product->quantity;
							}
							else
							{
								$product->price = $price;
								$product->price = round($product->price * 20) / 20;
								$product->discount = ($product->price_before - $price) * $product->quantity;
							}
							break;
						}
					}
				}
			}
		}
		return $product;
	}

	public function getProducts($products, $currency, $all)
	{
		if(is_array($products) && is_object($products[0]))
		{
			$where = array();
			if(isset($products[0]->wl_alias))
			{
				$where['product_alias'] = $products[0]->wl_alias;
				if(!$all)
				{
					$where['product_id'] = array();
					foreach ($products as $product) {
						$where['product_id'][] = $product->id;
					}
				}
			}
			else if(isset($products[0]->product_alias))
			{
				$where['product_alias'] = $products[0]->product_alias;
				if(!$all)
				{
					$where['product_id'] = array();
					foreach ($products as $product) {
						$where['product_id'][] = $product->product_id;
					}
				}
			}
			if(!empty($where))
			{
				if($marketings = $this->db->getAllDataByFieldInArray($this->table(), $where))
					foreach ($products as $product) {
						foreach ($marketings as $marketing) {
							if($marketing->product_id == $product->id)
							{
								$prices = unserialize($marketing->price);
								ksort ($prices);
								if($currency && is_numeric($currency) && $currency > 0)
									foreach ($prices as $from => &$price)
										$price *= $currency;
								else if(!empty($product->currency))
									foreach ($prices as $from => &$price)
										$price *= $product->currency;
								$product->prices = $prices;
								break;
							}
						}
					}
			}
		}
		return $products;
	}

	public function save($id = 0)
	{
		$prices = array();
		if($max_i = $this->data->post('max_i'))
			for ($i=1; $i <= $max_i ; $i++) { 
				if($amount = $this->data->post('from-'.$i))
					$prices[$amount] = $this->data->post('price-'.$i);
			}
		for ($i=0; $i < 3 ; $i++) { 
			if($amount = $this->data->post('from-new-'.$i))
				$prices[$amount] = $this->data->post('price-new-'.$i);
		}
			

		$data = array();
		$data['product_alias'] = $this->data->post('product_alias');
		$data['product_id'] = $this->data->post('product_id');
		if($marketing = $this->db->getAllDataById($this->table(), $data))
		{
			if(!empty($prices))
			{
				ksort($prices);
				$prices = serialize($prices);
				if($prices != $marketing->price)
					$this->db->updateRow($this->table(), array('price' => $prices), $marketing->id);
			}
		}
		elseif(!empty($prices))
		{
			ksort($prices);
			$data['price'] = serialize($prices);
			$this->db->insertRow($this->table(), $data);
		}
	}

	public function delete($id)
	{
		if($this->db->deleteRow($this->table(), $id))
			return true;
		return false;
	}

}

?>