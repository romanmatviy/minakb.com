<?php

class install
{
	public $service = null;
	
	public $name = "pb_payparts";
	public $title = "Оплата частинами PrivatBank";
	public $description = "Сервіс оплати частинами Visa/Mastercard через PrivatBank";
	public $group = "shop";
	public $table_service = "s_pb_payparts";
	public $multi_alias = 1;
	public $order_alias = 1;
	public $admin_ico = 'fa-cc-visa';
	public $version = "1.0";

	public $options = array('storeId' => '', 'password' => '', 'merchantType' => 'II', 'useMarkUp' => 0, 'markUp' => 0, 'successPayStatusToCart' => 0);
	public $options_type = array('storeId' => 'text', 'password' => 'text', 'merchantType' => 'text', 'useMarkUp' => 'bool', 'markUp' => 'number', 'successPayStatusToCart' => false);
	public $options_title = array('storeId' => 'Ідентифікатор магазину', 'password' => 'Приватний ключ / пароль', 'merchantType' => 'Тип кредиту:<br> <strong>II</strong> - Миттєва розстрочка<br> <strong>PP</strong> - Оплата частинами<br> <strong>PB</strong> - Оплата частинами. Гроші в періоді<br> <strong>IA</strong> - Миттєва розстрочка. Акція 50/50%', 'useMarkUp' => 'Комісію оплачує клієнт (націнено на ціну квитанції - незаконно!)', 'markUp' => 'Націнка у %');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('payment' => 'pb_payparts');
	public $cooperation_service = array('cart' => 'payment');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$where = array('alias' => $alias, 'content' => 0);
        $this->db->updateRow('wl_ntkd', array('list' => '<i class="fab fa-cc-visa"></i> Visa/Mastercard через PrivatBank'), $where);

		return true;
	}

	public function alias_delete($alias = 0, $table = '')
	{
		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		if($option === 'merchantType')
		{
			if(in_array($value, array('II', 'PP', 'PB', 'IA'), false))
				$this->options[$option] = $value;
			else
				exit('MerchantType must be in array(\'II\', \'PP\', \'PB\', \'IA\')');
		}
		else
			$this->options[$option] = $value;

		return true;
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `alias` tinyint(4) NOT NULL,
					  `merchant_type` char(2) NOT NULL,
					  `parts_count` tinyint(2) NOT NULL,
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