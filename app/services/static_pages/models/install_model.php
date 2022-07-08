<?php

class install {

	public $service = null;
	
	public $name = "static_pages";
	public $title = "Статичні сторінки";
	public $description = "";
	public $group = "page";
	public $table_service = "s_static_page";
	public $table_alias = "";
	public $multi_alias = 1;
	public $order_alias = 10;
	public $admin_ico = 'fa-newspaper-o';
	public $version = "2.2";

	public $options = array('folder' => 'static_page');
	public $options_type = array('folder' => 'text');
	public $options_title = array('folder' => 'Папка для зображень/аудіо');
	public $options_admin = array();
	public $sub_menu = array();
	public $sub_menu_access = array();

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0) return false;

		$page['id'] = $alias;
		$page['author_add'] = $page['author_edit'] = $_SESSION['user']->id;
		$page['date_add'] = $page['date_edit'] = time();
		$this->db->insertRow($this->table_service, $page);

		return true;
	}

	public function alias_delete($alias = 0, $table = '', $uninstall_service = false)
	{
		if(!$uninstall_service)
			$this->db->deleteRow($this->table_service, $alias);
		return true;
	}

	public function setOption($option, $value, $table = '')
	{
		return true;
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
					  `id` int(11) NOT NULL,
					  `author_add` int(11) NOT NULL,
					  `date_add` int(11) NOT NULL,
					  `author_edit` int(11) NOT NULL,
					  `date_edit` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service_id)
	{
		$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}");

		return true;
	}
	
}

?>