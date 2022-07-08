<?php

class install
{
	public $service = null;
	
	public $name = "price_per_amount";
	public $title = "Керування ціною від кількості";
	public $description = "";
	public $group = "shop";
	public $table_service = "s_ppa";
	public $multi_alias = 0;
	public $order_alias = 0;
	public $admin_ico = 'fa-qrcode';
	public $version = "1.1";

	public $options = array('markUpByUserTypes' => 0);
	public $options_type = array('markUpByUserTypes' => 'bool');
	public $options_title = array('markUpByUserTypes' => 'Націнка відносно рівня користувача');
	public $options_admin = array ();
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
		if($alias == 0) return false;

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
					  `product_alias` int(11),
					  `product_id` int(11),
					  `price` TEXT NULL,
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
		}
	}
	
}

?>