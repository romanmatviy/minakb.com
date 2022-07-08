<?php

class install
{
	public $service = null;

	public $name = "novaposhta";
	public $title = "Нова пошта";
	public $description = "";
	public $group = "shop";
	public $table_service = "s_novaposhta";
	public $multi_alias = 0;
	public $order_alias = 0;
	public $multi_page = 0;
	public $admin_ico = 'fa-car';
	public $version = "1.2";

	public $options = array('api_key' => '', 'minTotalMaxWeightAllowed' => 0);
	public $options_type = array('api_key' => 'text', 'minTotalMaxWeightAllowed' => 'number');
	public $options_title = array('api_key' => 'API ключ для синхронізації з сервісами Нової пошти', 'minTotalMaxWeightAllowed' => 'Мінімальна вантажопідйомність відділення (у кг)');
	public $options_admin = array ();
	public $sub_menu = array();

	public $cooperation_index = array('cart' => 2);
	public $cooperation_types = array('shipping' => 'Доставка');
	public $cooperation_service = array('cart' => 'shipping');


	public function alias($alias = 0, $table = '')
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
		return true;
	}

	public function uninstall($service = 0)
	{
		return true;
	}

}

?>