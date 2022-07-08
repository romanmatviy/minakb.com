<?php

class install
{
	public $service = null;
	
	public $name = "price_per_type";
	public $title = "Керування ціною відносно рівня користувача";
	public $description = "";
	public $group = "shop";
	public $table_service = "s_ppt";
	public $multi_alias = 0;
	public $order_alias = 90;
	public $admin_ico = 'fa-qrcode';
	public $version = "1.1";

	public $options = array();
	public $options_type = array();
	public $options_title = array();
	public $options_admin = array();
	public $sub_menu = array();

	public $cooperation_index = array('shopshowcase' => 2);
	public $cooperation_types = array('marketing' => 'Маркетинг');
	public $cooperation_service = array('shopshowcase' => 'marketing');

	public $seo_name = "Керування ціною";
	public $seo_title = "Керування ціною";
	public $seo_description = "";
	public $seo_keywords = "";

	function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;
		return true;
	}

	public function alias_delete($alias = 0, $table = '')
	{
		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		$this->options[$option] = $value;

		return true;
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `shop_alias` int(11) DEFAULT NULL,
					  `user_type` int(11) DEFAULT NULL,
					  `change_price` char(1) DEFAULT NULL,
					  `price` float DEFAULT NULL,
					  `currency` char(3) NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`),
					  KEY `shop_alias` (`shop_alias`,`user_type`) USING BTREE
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_product` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `product_alias` int(11) DEFAULT NULL,
					  `product_id` int(11) DEFAULT NULL,
					  `user_type` int(11) DEFAULT NULL,
					  `change_price` char(1) NOT NULL,
					  `price` float DEFAULT NULL,
					  `currency` char(3) NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`),
					  KEY `product_alias` (`product_alias`,`product_id`) USING BTREE,
					  KEY `user_type` (`user_type`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service = 0)
	{
		if(isset($_POST['content']) && $_POST['content'] == 1)
		{
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_product");
		}
	}
	
}

?>