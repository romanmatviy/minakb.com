<?php

class install
{
	public $service = null;
	
	public $name = "shopstorage";
	public $title = "Склад";
	public $description = "Наявність товару на складі (Ціна - кількість). Ціна може залежати від типу користувача";
	public $group = "shop";
	public $table_service = "s_shopstorage";
	public $multi_alias = 1;
	public $order_alias = 150;
	public $admin_ico = 'fa-qrcode';
	public $version = "1.2";

	public $options = array('productUseArticle' => 1, 'deleteIfZero' => 0, 'markUpByUserTypes' => 0);
	public $options_type = array('productUseArticle' => 'bool', 'deleteIfZero' => 'bool', 'markUpByUserTypes' => 'bool');
	public $options_title = array('productUseArticle' => 'Використання товарами зовнішнього артикулу', 'deleteIfZero' => 'Видаляти прихідні квитанції по закінченню залишків', 'markUpByUserTypes' => 'Націнка відносно рівня користувача');
	public $options_admin = array ();
	public $sub_menu = array("add" => "Прихід товару", "options" => "Властивості");

	public $cooperation_index = array('shopshowcase' => 2);
	public $cooperation_types = array('storage' => 'Склад');
	public $cooperation_service = array('shopshowcase' => 'storage');

	public $seo_name = "Склад";
	public $seo_title = "Склад";
	public $seo_description = "";
	public $seo_keywords = "";

	function alias($alias = 0, $table = '')
	{
		if($alias == 0) return false;

		$storage['id'] = $alias;
		$storage['date_add'] = time();
		$storage['user_add'] = $_SESSION['user']->id;
		$storage['active'] = 1;
		$this->db->insertRow($this->table_service, $storage);

		if($this->options['markUpByUserTypes'] > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_markup` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `storage` int(11) NOT NULL,
					  `user_type` tinyint(2) NOT NULL,
					  `markup` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);
		}

		return true;
	}

	public function alias_delete($alias = 0, $table = '')
	{
		$this->db->deleteRow($this->table_service.'_products', $alias, 'storage');
		$this->db->deleteRow($this->table_service.'_markup', $alias, 'storage');
		$this->db->deleteRow($this->table_service, $alias);

		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		$this->options[$option] = $value;

		if ($option == 'markUpByUserTypes' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_markup` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `storage` int(11) NOT NULL,
					  `user_type` tinyint(2) NOT NULL,
					  `markup` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);
		}

		return true;
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` text NULL,
					  `currency` varchar(3) NOT NULL DEFAULT 'USD',
					  `updateRows` TEXT NULL,
					  `updateCols` TEXT NULL,
					  `markup` int(11) NULL,
					  `date_add` int(11) NOT NULL,
					  `user_add` int(11) NOT NULL,
					  `active` tinyint(1) NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_products` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `storage` int(11) NOT NULL,
					  `product` int(11) NOT NULL,
					  `price_in` float UNSIGNED NOT NULL,
					  `price_out` text NULL,
					  `amount` int(11) NOT NULL,
					  `amount_reserved` int(11) NOT NULL,
					  `date_in` int(11) NOT NULL,
					  `date_out` int(11) NOT NULL,
					  `manager_add` int(11) NOT NULL,
					  `date_add` int(11) NOT NULL,
					  `manager_edit` int(11) NOT NULL,
					  `date_edit` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_updates` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `storage` int(11) NOT NULL,
					  `file` int(11) NOT NULL,
					  `price_for_1` float UNSIGNED NOT NULL,
					  `currency` varchar(3) NOT NULL,
					  `inserted` int(11) NOT NULL,
					  `updated` int(11) NOT NULL,
					  `deleted` int(11) NOT NULL,
					  `manager` int(11) NOT NULL,
					  `date` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service = 0)
	{
		if(isset($_POST['content']) && $_POST['content'] == 1)
		{
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_products");
		}
	}
	
}

?>