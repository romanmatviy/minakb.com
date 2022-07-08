<?php

class install
{
	public $service = null;
	
	public $name = "privat24";
	public $title = "Privat24";
	public $description = "Сервіс оплати через Privat24";
	public $group = "shop";
	public $table_service = "s_privat24";
	public $multi_alias = 0;
	public $order_alias = 1;
	public $admin_ico = 'fa-paypal';
	public $version = "1.1";

	public $options = array('merchant' => '', 'password' => '', 'useMarkUp' => 0, 'markUp' => 0);
	public $options_type = array('merchant' => 'text', 'password' => 'text', 'useMarkUp' => 'bool', 'markUp' => 'number');
	public $options_title = array('merchant' => 'Merchant id', 'password' => 'Пароль мерчанта', 'useMarkUp' => 'Комісію оплачує клієнт (націнено на ціну квитанції - незаконно!)', 'markUp' => 'Націнка у %');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('payment' => 'privat24');
	public $cooperation_service = array('cart' => 'payment');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$where = array('alias' => $alias, 'content' => 0);
        $this->db->updateRow('wl_ntkd', array('list' => '<img src="/style/privat24/privat24.png" alt="Privat24" title="Privat24">'), $where);

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
					  `alias` tinyint(4) NOT NULL,
					  `cart_alias` tinyint(4) NOT NULL,
					  `cart_id` int(11) NOT NULL,
					  `amount` float NOT NULL,
					  `murkup` float NOT NULL,
					  `currency` int(11) NOT NULL,
					  `status` text NOT NULL,
					  `details` text NOT NULL,
					  `comment` text NOT NULL,
					  `date_add` int(11) NOT NULL,
					  `date_edit` int(11) NOT NULL,
					  `signature` text NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `method` (`alias`,`cart_id`,`cart_alias`)
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