<?php

class install
{
	public $service = null;
	
	public $name = "paypal";
	public $title = "PayPal";
	public $description = "Сервіс оплати PayPal";
	public $group = "shop";
	public $table_service = "s_paypal";
	public $multi_alias = 0;
	public $order_alias = 1;
	public $admin_ico = 'fa-paypal';
	public $version = "1.0";

	public $options = array('receiverEmail' => '', 'currency_code' => 'EUR', 'testPay' => 1);
	public $options_type = array('receiverEmail' => 'text', 'currency_code' => 'text', 'testPay' => 'bool');
	public $options_title = array('receiverEmail' => 'email отримувача платежу (на нього зареєстровано paypal акаунт)', 'currency_code' => 'Код валюти (3 символи)', 'testPay' => 'Тестовий платіж');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('payment' => 'paypal');
	public $cooperation_service = array('cart' => 'payment');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$where = array('alias' => $alias, 'content' => 0);
        $this->db->updateRow('wl_ntkd', array('list' => '<i class="fa fa-paypal"></i> Visa/Mastercard via PayPal'), $where);

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
					  `currency_code` varchar(3) NOT NULL,
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

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_history` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `pay_id` int(11) NOT NULL,
					  `status` text NOT NULL,
					  `details` text NOT NULL,
					  `date` int(11) NOT NULL,
					  `signature` text NOT NULL,
					  PRIMARY KEY (`id`),
					  KEY `method` (`pay_id`)
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