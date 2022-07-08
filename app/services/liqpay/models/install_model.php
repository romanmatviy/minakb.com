<?php

class install
{
	public $service = null;
	
	public $name = "liqpay";
	public $title = "LiqPay";
	public $description = "Сервіс оплати Visa/Mastercard через LiqPay PrivatBank";
	public $group = "shop";
	public $table_service = "s_liqpay";
	public $multi_alias = 1;
	public $order_alias = 1;
	public $admin_ico = 'fa-cc-visa';
	public $version = "1.4";

	public $options = array('public_key' => '', 'private_key' => '', 'useMarkUp' => 0, 'markUp' => 2.75, 'testPay' => 1, 'successPayStatusToCart' => 0);
	public $options_type = array('public_key' => 'text', 'private_key' => 'text', 'useMarkUp' => 'bool', 'markUp' => 'number', 'testPay' => 'bool', 'successPayStatusToCart' => false);
	public $options_title = array('public_key' => 'Публічний ключ', 'private_key' => 'Приватний ключ', 'useMarkUp' => '<strong>Комісію оплачує клієнт</strong> (націнено на суму замовлення - <u>незаконно!</u>) <br> або <br> <strong>Завдаток/Частина від суми замовлення</strong>', 'markUp' => '<strong>Комісія/націнка у *%</strong> (задавати <u>більше нуля</u> ~ 2.75%) <br> або <br> <strong>Завдаток</strong> (задавати <u>менше нуля</u> ~ 30% від суми замовлення)', 'testPay' => 'Тестовий платіж');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('payment' => 'liqpay');
	public $cooperation_service = array('cart' => 'payment');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$where = array('alias' => $alias, 'content' => 0);
        $this->db->updateRow('wl_ntkd', array('list' => '<i class="fab fa-cc-visa"></i> <i class="fab fa-cc-mastercard"></i> Visa/Mastercard через LiqPay PrivatBank'), $where);

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