<?php

/**
 * shop_promo_model for shopshowcase 2.9.6+
 */
class shop_promo_model
{

	public function table($sufix = '_promo', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function get($where = [])
	{
		if(is_numeric($where) && $where > 0)
			return $this->db->getAllDataById($this->table(), $where);
		else if(!empty($where))
			return $this->db->getAllDataByFieldInArray($this->table(), $where, 'id DESC');
		else
			return $this->db->getAllData($this->table(), 'id DESC');
	}

	public function save()
	{
		$from = $this->data->post('from_date').' '.$this->data->post('from_time');
		$to = $this->data->post('to_date').' '.$this->data->post('to_time');
		$from = strtotime($from);
		$to = strtotime($to);
		if(!is_numeric($from) || !is_numeric($to) || $from >= $to)
		{
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->errors = 'Дата закінчення акції не може бути меншої ніж дата початку';
			return false;
		}

		$data = ['from' => $from, 'to' => $to];
		$data['percent'] = $this->data->post('percent');
		$data['info'] = $this->data->post('info');
		$data['date_edit'] = time();
		$data['manager_edit'] = $_SESSION['user']->id;

		if($id = $this->data->post('id'))
		{
			$data['status'] = $this->data->post('status');
			$this->db->updateRow($this->table(), $data, $id);
			return $id;
		}
		else
		{
			$data['status'] = 0;
			$data['date_add'] = $data['date_edit'];
			return $this->db->insertRow($this->table(), $data);
		}
	}

	public function getProducts($promoId, $type = 'products')
	{
		if($type == 'count')
			return $this->db->getCount($this->table('_products'), ['promo' => $promoId]);

		$where_language = ['alias' => '#p.wl_alias', 'content' => '#p.id'];
		if($_SESSION['language'])
			$where_language['language'] = $_SESSION['language'];
		return $this->db->select($this->table('_products').' as p', 'id, article_show, price, old_price, currency', ['promo' => $promoId])
										->join('wl_ntkd', 'name', $where_language)
										->get('array');
	}

	public function saveProduct($productId, $promoId, $active)
	{
		$this->db->updateRow($this->table(), ['date_edit' => time(), 'manager_edit' => $_SESSION['user']->id], $promoId);
		return $this->db->updateRow($this->table('_products'), ['promo' => $active], $productId);
	}
}

?>