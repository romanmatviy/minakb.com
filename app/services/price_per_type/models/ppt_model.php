<?php 

class ppt_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getProduct($product)
	{
		$where = $data = array();
		if(isset($product->wl_alias))
		{
			$where['product_alias'] = $data['shop_alias'] = $product->wl_alias;
			$where['product_id'] = $product->id;
		}
		else if(isset($product->product_alias))
		{
			$where['product_alias'] = $data['shop_alias'] = $product->product_alias;
			$where['product_id'] = $product->product_id;
		}
		$where['user_type'] = $data['user_type'] = 4;
		if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0 && isset($_SESSION['user']->type))
			$where['user_type'] = $data['user_type'] = $_SESSION['user']->type;
		elseif(isset($_SESSION['option']->new_user_type))
    		$where['user_type'] = $data['user_type'] = $_SESSION['option']->new_user_type;
		if(!empty($where))
		{
			$product->marketing = false;
			if($marketing = $this->db->getAllDataById($this->table('_product'), $where))
			{
				$product->marketing = $marketing;
				if($marketing->change_price == '+'){
					if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && $marketing->currency)
					{
						if($marketing->currency == $product->currency)
							$product->price += $marketing->price;
						elseif(isset($_SESSION['currency'][$marketing->currency]) && isset($_SESSION['currency'][$product->currency]))
							$product->price += $marketing->price * $_SESSION['currency'][$marketing->currency] / $_SESSION['currency'][$product->currency];
						else
							$product->price += $marketing->price;
					}
					else
						$product->price += $marketing->price;
				}
				if($marketing->change_price == '*')
					$product->price *= $marketing->price;
				if($marketing->change_price == '=')
				{
					if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && $marketing->currency && isset($_SESSION['currency'][$marketing->currency]))
						$product->price = $marketing->price * $_SESSION['currency'][$marketing->currency] / $_SESSION['currency'][$product->currency];
					else
						$product->price = $marketing->price;
				}
			}
			elseif($marketing = $this->db->getAllDataById($this->table(), $data))
			{
				$product->marketing = $marketing;
				if($marketing->change_price == '+')
				{
					if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && $marketing->currency)
					{
						if($marketing->currency == $product->currency)
							$product->price += $marketing->price;
						elseif(isset($_SESSION['currency'][$marketing->currency]) && isset($_SESSION['currency'][$product->currency]))
							$product->price += $marketing->price * $_SESSION['currency'][$marketing->currency] / $_SESSION['currency'][$product->currency];
						else
							$product->price += $marketing->price;
					}
					else
						$product->price += $marketing->price;
				}
				if($marketing->change_price == '*')
					$product->price *= $marketing->price;
			}
		}
		return $product;
	}

	public function getProducts($products)
	{
		if(is_array($products))
		{
			$firstKey = -1;
			foreach($products as $key => $unused) {
	            $firstKey = $key;
	            break;
	        }
			if(!is_object($products[$firstKey]))
				return $products;

			$where = array();
			if(isset($products[$firstKey]->wl_alias))
			{
				$where['product_alias'] = $products[$firstKey]->wl_alias;
				$where['product_id'] = array();
				foreach ($products as $product) {
					$where['product_id'][] = $product->id;
				}
			}
			else if(isset($products[$firstKey]->product_alias))
			{
				$where['product_alias'] = $products[$firstKey]->product_alias;
				$where['product_id'] = array();
				foreach ($products as $product) {
					$where['product_id'][] = $product->product_id;
				}
			}
			if(!empty($where))
			{
				$where['user_type'] = $data['user_type'] = 4;
				if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0 && isset($_SESSION['user']->type))
					$where['user_type'] = $data['user_type'] = $_SESSION['user']->type;
				elseif(isset($_SESSION['option']->new_user_type))
		    		$where['user_type'] = $data['user_type'] = $_SESSION['option']->new_user_type;

				$_products_skip = array();
				if($marketings = $this->db->getAllDataByFieldInArray($this->table('_product'), $where))
					foreach ($products as $product) {
						foreach ($marketings as $marketing) {
							if($marketing->product_id == $product->id)
							{
								$_products_skip[] = $product->id;
								$product->marketing = $marketing;
								if($marketing->change_price == '+')
								{
									if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && $marketing->currency)
									{
										if($marketing->currency == $product->currency)
											$product->price += $marketing->price;
										elseif(isset($_SESSION['currency'][$marketing->currency]) && isset($_SESSION['currency'][$product->currency]))
											$product->price += $marketing->price * $_SESSION['currency'][$marketing->currency] / $_SESSION['currency'][$product->currency];
										else
											$product->price += $marketing->price;
									}
									else
										$product->price += $marketing->price;
								}
								if($marketing->change_price == '*')
									$product->price *= $marketing->price;
								if($marketing->change_price == '=')
								{
									if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && $marketing->currency && isset($_SESSION['currency'][$marketing->currency]))
										$product->price = $marketing->price * $_SESSION['currency'][$marketing->currency] / $_SESSION['currency'][$product->currency];
									else
										$product->price = $marketing->price;
								}
								break;
							}
						}
					}

				if(count($_products_skip) != count($products))
				{
					$where = array();
					if(isset($products[$firstKey]->wl_alias))
						$where['shop_alias'] = $products[$firstKey]->wl_alias;
					else if(isset($products[$firstKey]->product_alias))
						$where['shop_alias'] = $products[$firstKey]->product_alias;
					if(!empty($where))
					{
						$where['user_type'] = $data['user_type'] = 4;
						if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0 && isset($_SESSION['user']->type))
							$where['user_type'] = $data['user_type'] = $_SESSION['user']->type;
						elseif(isset($_SESSION['option']->new_user_type))
				    		$where['user_type'] = $data['user_type'] = $_SESSION['option']->new_user_type;
					}
					if($marketings = $this->db->getAllDataByFieldInArray($this->table(), $where))
						foreach ($products as $product) {
							if(!in_array($product->id, $_products_skip))
								foreach ($marketings as $marketing) {
									$product->marketing = $marketing;
									if($marketing->change_price == '+')
									{
										if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && $marketing->currency)
										{
											if($marketing->currency == $product->currency)
												$product->price += $marketing->price;
											elseif(isset($_SESSION['currency'][$marketing->currency]) && isset($_SESSION['currency'][$product->currency]))
												$product->price += $marketing->price * $_SESSION['currency'][$marketing->currency] / $_SESSION['currency'][$product->currency];
											else
												$product->price += $marketing->price;
										}
										else
											$product->price += $marketing->price;
									}
									if($marketing->change_price == '*')
										$product->price *= $marketing->price;
									break;
								}
						}
				}
			}
		}
		return $products;
	}

	public function saveForShop()
	{
		$data = $update = array();
		$data['shop_alias'] = $this->data->post('shop_id');
		$data['user_type'] = $this->data->post('type_id');
		$update['change_price'] = $this->data->post('change_price');
		$update['price'] = $this->data->post('price');
		$update['currency'] = $this->data->post('currency');
		if($row = $this->db->getAllDataById($this->table(), $data))
			$this->db->updateRow($this->table(), $update, $row->id);
		else
			$this->db->insertRow($this->table(), array_merge($data, $update));
		return true;
	}

	public function saveForProduct()
	{
		$data = $update = array();
		$data['product_alias'] = $this->data->post('shop_id');
		$data['product_id'] = $this->data->post('product_id');
		$data['user_type'] = $this->data->post('type_id');
		$update['change_price'] = $this->data->post('change_price');
		$update['price'] = $this->data->post('price');
		$update['currency'] = $this->data->post('currency');
		if($row = $this->db->getAllDataById($this->table('_product'), $data))
			$this->db->updateRow($this->table('_product'), $update, $row->id);
		else
			$this->db->insertRow($this->table('_product'), array_merge($data, $update));
		return true;
	}

	public function deleteForProduct()
	{
		$data = array();
		$data['product_alias'] = $this->data->post('shop_id');
		$data['product_id'] = $this->data->post('product_id');
		$data['user_type'] = $this->data->post('type_id');
		$this->db->deleteRow($this->table('_product'), $data);
		return true;
	}

	public function delete($id)
	{
		if($this->db->deleteRow($this->table(), $id))
			return true;
		return false;
	}

}

?>