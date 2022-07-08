<?php

class install
{
	public $service = null;
	
	public $name = "shopparts";
	public $title = "Магазин БД запчастин";
	public $seo_name = "Запчастини";
	public $description = "Перелік товарів запчастин до автомобіля з підтримкою властифостей та фотогалереї БЕЗ можливості їх замовити та оплатити. Одномовна.";
	public $group = "shop";
	public $table_service = "s_shopparts";
	public $multi_alias = 0;
	public $order_alias = 100;
	public $admin_ico = 'fa-qrcode';
	public $version = "1.0";

	public $options = array('useGroups' => 1, 'ProductMultiGroup' => 0, 'searchHistory' => 1, 'groupOrder' => 'position ASC');
	public $options_type = array('useGroups' => 'bool', 'ProductMultiGroup' => 'bool', 'searchHistory' => 'bool', 'groupOrder' => 'text');
	public $options_title = array('useGroups' => 'Наявність груп', 'ProductMultiGroup' => 'Мультигрупи (1 товар більше ніж 1 група)', 'searchHistory' => 'Зберігати історію пошуку користувачів', 'groupOrder' => 'Сортування груп');
	public $options_admin = array (
					'word:products_to_all' => 'товарів',
					'word:product_to' => 'До товару',
					'word:product_to_delete' => 'товару',
					'word:product' => 'товар',
					'word:products' => 'товари',
					'word:product_add' => 'Додати товар',
					'word:groups_to_all' => 'груп',
					'word:groups_to_delete' => 'групу',
					'word:group' => 'група',
					'word:group_add' => 'Додати групу товарів',
					'word:options_to_all' => 'параметрів',
					'word:option' => 'параметр товару',
					'word:option_add' => 'Додати параметр товару'
				);
	public $sub_menu = array("add" => "Додати товар", "all" => "До всіх товарів", "groups" => "Групи", "options" => "Властивості", "manufactures" => "Виробники");

	function alias($alias = 0, $table = '')
	{
		if($alias == 0) return false;

		if($this->options['useGroups'] > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_groups` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `wl_alias` int(11) NOT NULL,
						  `alias` text NOT NULL,
						  `parent` int(11),
						  `position` int(11) NOT NULL,
						  `photo` text NOT NULL,
						  `active` tinyint(1) NOT NULL,
						  `author_add` int(11) NOT NULL,
						  `date_add` int(11) NOT NULL,
						  `author_edit` int(11) NOT NULL,
						  `date_edit` int(11) NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			if($this->options['ProductMultiGroup'] > 0)
			{
				$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_product_group` (
						  `product` int(11) NOT NULL,
						  `group` int(11) NOT NULL
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
				$this->db->executeQuery($query);
			}
		}

		if($this->options['searchHistory'] > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_search_history` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `product_id` int(11) NOT NULL,
						  `product_article` text NOT NULL,
						  `user` int(11) NOT NULL,
						  `date` int(11) NOT NULL,
						  `last_view` int(11) NOT NULL,
						  `count_per_day` int(11) NOT NULL,
						  PRIMARY KEY (`id`),
						  KEY `product` (`product_id`,`user`,`date`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			$this->db->executeQuery($query);
		}

		return true;
	}

	public function alias_delete($alias = 0, $table = '')
	{
		if($alias > 0) {
			$products = $this->db->getAllDataByFieldInArray($this->table_service.'_products', $alias, 'wl_alias');
			if(!empty($products))
			{
				$this->db->deleteRow($this->table_service.'_products', $alias, 'wl_alias');
				$this->db->deleteRow($this->table_service.'_groups', $alias, 'wl_alias');

				foreach ($products as $product) {
					$this->db->deleteRow($this->table_service.'_product_group', $product->id, 'product');
				}

				$options = $this->db->getAllDataByFieldInArray($this->table_service.'_options', $alias, 'wl_alias');
				if(!empty($options))
				{
					$this->db->deleteRow($this->table_service.'_options', $alias, 'wl_alias');

					foreach ($options as $option) {
						$this->db->deleteRow($this->table_service.'_product_options', $option->id, 'option');
						$this->db->deleteRow($this->table_service.'_options_name', $option->id, 'option');
					}
				}
			}

			$groups = $this->db->getAllDataByFieldInArray($this->table_service.'_groups', $alias, 'wl_alias');
			if(!empty($groups))
				$this->db->deleteRow($this->table_service.'_groups', $alias, 'wl_alias');
			
			$this->db->deleteRow($this->table_service.'_product_options', $alias, 'shop');
		}

		$path = IMG_PATH.$this->options['folder'];
		if(strlen(IMG_PATH) > strlen(SITE_URL)) $path = substr($path, strlen(SITE_URL));
		if(is_dir($path)) $this->removeDirectory($path);

		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		$this->options[$option] = $value;

		if ($option == 'useGroups' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_groups` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `wl_alias` int(11) NOT NULL,
						  `alias` text NOT NULL,
						  `parent` int(11),
						  `position` int(11) NOT NULL,
						  `photo` int(11) NOT NULL,
						  `active` tinyint(1) NOT NULL,
						  `author_add` int(11) NOT NULL,
						  `date_add` int(11) NOT NULL,
						  `author_edit` int(11) NOT NULL,
						  `date_edit` int(11) NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);
		}
		if($option == 'ProductMultiGroup' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_product_group` (
						  `product` int(11) NOT NULL,
						  `group` int(11) NOT NULL
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			$products = $this->db->getAllDataByFieldInArray($this->table_service.'_products', $alias, 'wl_alias');
			if($products)
			{
				$list = array();
				foreach ($products as $product) {
					$list[] = $product->id;
				}

				$count = $this->db->getCount($this->table_service.'_product_group', array('product' => $list));
				if($count > 0)
				{
					foreach ($products as $product) {
						$this->db->insertRow($this->table_service.'_product_group'.$table, array('product' => $product->id, 'group' => $product->group));
					}
				}
			}
		}
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_products` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `wl_alias` int(11) NOT NULL,
					  `article` text NOT NULL,
					  `alias` text NOT NULL,
					  `manufacturer` int(11) NULL,
					  `name` text NULL,
					  `text` text NULL,
					  `group` int(11) NULL,
					  `price` float unsigned NULL,
					  `currency` tinyint(2) NULL,
					  `orign` tinyint(1) NULL,
					  `analogs` text NULL,
					  `active` tinyint(1) NOT NULL,
					  `author_add` int(11) NOT NULL,
					  `date_add` int(11) NOT NULL,
					  `author_edit` int(11) NOT NULL,
					  `date_edit` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_manufactures` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `wl_alias` int(11) NOT NULL,
					  `alias` text NULL,
					  `main_id` int(11) NULL,
					  `name` text NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_options` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `wl_alias` int(11) NOT NULL,
					  `group` int(11) NOT NULL,
					  `alias` text NOT NULL,
					  `position` int(11) NOT NULL,
					  `type` int(11) NOT NULL,
					  `filter` tinyint(1) NOT NULL,
					  `active` tinyint(1) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_options_name` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `option` int(11) NOT NULL,
					  `language` varchar(2) NOT NULL,
					  `name` text NOT NULL,
					  `sufix` text NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_product_options` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `product` int(11) NOT NULL,
			  `option` int(11) NOT NULL,
			  `language` varchar(2) NOT NULL,
			  `value` text NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service = 0)
	{
		if(isset($_POST['content']) && $_POST['content'] == 1){
			$path = IMG_PATH.$this->options['folder'];
			if(strlen(IMG_PATH) > strlen(SITE_URL)) $path = substr($path, strlen(SITE_URL));
			if(is_dir($path)) $this->removeDirectory($path);

			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_products");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_manufactures");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_options");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_options_name");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_product_options");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_product_group");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_services");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_groups");
		}
	}

	private function removeDirectory($dir) {
	    if ($objs = glob($dir."/*")) {
	       foreach($objs as $obj) {
	         is_dir($obj) ? $this->removeDirectory($obj) : unlink($obj);
	       }
	    }
	    rmdir($dir);
	}
	
}

?>