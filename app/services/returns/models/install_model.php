<?php

class install
{
	public $service = null;

	public $name = "returns";
	public $title = "Повернення";
	public $description = "Повернення товарів для cart, balance";
	public $group = "cart";
	public $table_service = "s_returns";
	public $multi_alias = 0;
	public $order_alias = 50;
	public $admin_sidebar = 1;
	public $admin_ico = 'fa-reply-all';
	public $version = "1.0";

	public $options = array();
	public $options_type = array();
	public $options_title = array();
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('returns' => 'Корзина');
	public $cooperation_service = array('cart' => 'returns');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$alias1 = -1;
		if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => '<0', 'type' => '__tab_profile'), 'alias1'))
			$alias1 = $actions[0]->alias1 - 1;
		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => $alias1, 'alias2' => $alias, 'type' => '__tab_profile'));
		
		$alias1 = -1;
		if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => '<0', 'type' => '__link_profile'), 'alias1'))
			$alias1 = $actions[0]->alias1 - 1;
		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => $alias1, 'alias2' => $alias, 'type' => '__link_profile'));

		return true;
	}

	public function alias_delete($alias = 0, $table = '')
	{
		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		return true;
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
				    	  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `user_id` int(11) NOT NULL,
						  `cart_id` int(11) NOT NULL,
						  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1-new, 2-good, 3-false',
						  `product_row_id` int(11) NOT NULL,
						  `quantity` tinyint(3) UNSIGNED NOT NULL,
						  `reason` text NOT NULL,
						  `ttn` text DEFAULT NULL,
						  `date_add` int(11) NOT NULL,
						  `date_manage` int(11) DEFAULT NULL,
						  `manager` int(11) DEFAULT NULL,
						  `info` text DEFAULT NULL,
						  `money` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1-balance, 2-cash',
						  `updateStorage` tinyint(1) NOT NULL DEFAULT 0,
						  `date_synchronization` int(11) NOT NULL DEFAULT 0,
						  PRIMARY KEY (`id`),
						  KEY `cart_id` (`cart_id`),
						  KEY `status` (`status`),
						  KEY `date_add` (`date_add`),
						  KEY `user_id` (`user_id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service = 0)
	{
		if(isset($_POST['content']) && $_POST['content'] == 1){
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}");
		}
	}

}

?>