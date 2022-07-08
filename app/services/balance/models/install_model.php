<?php

class install
{
	public $service = null;

	public $name = "balance";
	public $title = "Баланс / рахунок клієнтів";
	public $description = "Рахунок (бонусний, поточний баланс) для cart, wl_users";
	public $group = "cart";
	public $table_service = "s_balance";
	public $multi_alias = 0;
	public $order_alias = 150;
	public $admin_sidebar = 1;
	public $admin_ico = 'fa-money';
	public $version = "1.0";

	public $options = array();
	public $options_type = array();
	public $options_title = array();
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('payment' => 'balance');
	public $cooperation_service = array('cart' => 'payment');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$security_block = array();
		$security_block['alias'] = $alias;
		$security_block['content'] = -1;
		$security_block['name'] = 'Баланс користувача. Система безпеки';
		$security_block['language'] = $security_block['title'] = $security_block['description'] = $security_block['keywords'] = $security_block['text'] = $security_block['list'] = $security_block['meta'] = NULL;
		if($_SESSION['language'])
			foreach ($_SESSION['all_languages'] as $language) {
				$security_block['language'] = $language;
				$this->db->insertRow('wl_ntkd', $security_block);
			}
		else
			$this->db->insertRow('wl_ntkd', $security_block);

		$alias1 = -1;
		if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => '<0', 'type' => '__tab_profile'), 'alias1'))
			$alias1 = $actions[0]->alias1 - 1;
		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => $alias1, 'alias2' => $alias, 'type' => '__tab_profile'));
		
		$alias1 = -1;
		if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => '<0', 'type' => '__link_profile'), 'alias1'))
			$alias1 = $actions[0]->alias1 - 1;
		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => $alias1, 'alias2' => $alias, 'type' => '__link_profile'));

		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => '0', 'alias2' => $alias, 'type' => 'login'));

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
					  `user` int(11) NOT NULL,
					  `status` int(2) NOT NULL,
					  `debit` float UNSIGNED NULL,
					  `credit` float UNSIGNED NULL,
					  `balance` float NULL,
					  `action` text NULL,
					  `action_alias` int(11) NULL,
					  `action_id` int(11) NULL,
					  `info` text NULL,
					  `sign` text NULL,
					  `date_add` int(11) NOT NULL,
					  `date_edit` int(11) NOT NULL,
					  `manager` int(11) NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$user = $this->db->getAllDataByFieldInArray('wl_users', ['status' => 1], 'id LIMIT 1');
		if(!isset($user[0]->balance))
		{
			$query = "ALTER TABLE `wl_users` ADD `balance` FLOAT NULL AFTER `status`, ADD `balance_sign` TEXT NULL AFTER `balance`;";
			$this->db->executeQuery($query);
		}
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