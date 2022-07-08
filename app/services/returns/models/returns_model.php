<?php 

class returns_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable)
			return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getList($where = [])
	{
		$this->db->select($this->table().' as r', '*', $where)
	        			->join('s_cart_products as с', 'product_alias, product_id', '#r.product_row_id')
	        			->join('wl_users as u', 'name as user_name, email as user_email', '#r.user_id')
	        			->order('date_add DESC');
	    if($_SESSION['option']->paginator_per_page > 0)
        {
            $start = 0;
            if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
                $_SESSION['option']->paginator_per_page = $_GET['per_page'];
            if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
                $start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
            $this->db->limit($start, $_SESSION['option']->paginator_per_page);
        }
        $_SESSION['option']->paginator_total = $this->db->get('count', false);
        return $this->db->get('array');
	}

	public function get($where = [])
	{
		return $this->db->select($this->table().' as r', '*', $where)
	        			->join('s_cart as c', 'payment_alias, payment_id', '#r.cart_id')
	        			->join('s_cart_products as cp', 'product_alias, product_id, storage_alias, storage_invoice, price, price_in, quantity as quantity_buy, quantity_returned', '#r.product_row_id')
	        			->join('wl_ntkd', 'name as storage_name', ['alias' => '#cp.storage_alias', 'content' => 0])
	        			->join('wl_users as u', 'name as user_name, email as user_email', '#r.user_id')
	        			->join('wl_user_info', 'value as user_phone', ['user' => '#u.id', 'field' => 'phone'])
            			->join('wl_users as m', 'name as manager_name, email as manager_email', '#r.manager')
         				->get();
	}

	

}

?>