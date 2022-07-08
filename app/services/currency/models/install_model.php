<?php

class install
{
	public $service = null;
	
	public $name = "currency";
	public $title = "Курс валют";
	public $description = "";
	public $group = "currency";
	public $table_service = "s_currency";
	public $multi_alias = 0;
	public $order_alias = 10;
	public $admin_ico = 'fa-line-chart';
	public $version = "2.3";

	public $options = array('autoUpdate' => 1, 'saveToHistory' => 1);
	public $options_type = array('autoUpdate' => 'bool', 'saveToHistory' => 'bool');
	public $options_title = array('autoUpdate' => 'Автоматично оновлювати через privat24', 'saveToHistory' => 'Зберігати історію');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array();
	public $cooperation_types = array();
	public $cooperation_service = array();

	function alias($alias = 0, $table = '')
	{
		if($alias == 0) return false;

		if($this->options['saveToHistory'] > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_history` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `currency` tinyint(4) NOT NULL,
					  `day` int(11) NOT NULL,
					  `value` float NOT NULL,
					  `from` text NOT NULL,
					  `update` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
			$this->db->executeQuery($query);
		}

		$data = array('alias1' => 0, 'alias2' => $alias, 'type' => '__page_before_init');
		$this->db->insertRow('wl_aliases_cooperation', $data);
		$this->db->cache_delete('__page_before_init', 'wl_aliases');

		return true;
	}

	public function alias_delete($alias = 0, $table = '')
	{
		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		$this->options[$option] = $value;

		if($option == 'saveToHistory' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_history` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `currency` tinyint(4) NOT NULL,
					  `day` int(11) NOT NULL,
					  `value` float NOT NULL,
					  `from` text NOT NULL,
					  `update` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
			$this->db->executeQuery($query);
		}

		return true;
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
					  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
					  `code` varchar(3) NOT NULL,
					  `currency` float NOT NULL,
					  `day` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service = 0)
	{
		return true;
	}
	
}

?>