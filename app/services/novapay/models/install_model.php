<?php

class install
{
	public $service = null;
	
	public $name = "novapay";
	public $title = "NovaPay";
	public $description = "Сервіс оплати Visa/Mastercard через NovaPay";
	public $group = "shop";
	public $table_service = "s_novapay";
	public $multi_alias = 0;
	public $order_alias = 1;
	public $admin_ico = 'fa-cc-visa';
	public $version = "1.0";

	public $options = array('merchant_id' => '', 'mode' => 'payment', 'privatePassphrase' => '', 'testPay' => 1, 'successPayStatusToCart' => 0, 'id_option_weight' => 0, 'id_option_volume_weight' => 0);
	public $options_type = array('merchant_id' => 'text', 'mode' => false, 'privatePassphrase' => 'text', 'testPay' => 'bool', 'successPayStatusToCart' => false, 'id_option_weight' => 'number', 'id_option_volume_weight' => 'number');
	public $options_title = array('merchant_id' => '#мерчант продавця (merchant_id)', 'privatePassphrase' => 'Пароль захисту private rsa key (якщо є)', 'testPay' => 'Тестовий платіж', 'id_option_weight' => 'id властивості "Фактична вага" у shopshowcase', 'id_option_volume_weight' => 'id властивості "Об\'ємна вага" у shopshowcase');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('payment' => 'novapay');
	public $cooperation_service = array('cart' => 'payment');

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0)
			return false;

		$where = array('alias' => $alias, 'content' => 0);
        $this->db->updateRow('wl_ntkd', array('list' => '<i class="fa fa-cc-visa"></i> Visa/Mastercard через NovaPay - Нова пошта'), $where);

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
					  `status` text NOT NULL,
					  `details` text NOT NULL,
					  `novapay_id` CHAR(36) NOT NULL,
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