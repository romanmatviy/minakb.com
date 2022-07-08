<?php

class install {

	public $service = null;
	
	public $name = "likes";
	public $title = "Вподобайки";
	public $description = "";
	public $group = "shop";
	public $table_service = "s_likes";
	public $table_alias = "";
	public $multi_alias = 0;
	public $order_alias = 5;
	public $admin_ico = 'fa-heart';
	public $version = "1.0";

	public $options = array();
	public $options_type = array();
	public $options_title = array();
	public $options_admin = array();
	public $sub_menu = array();
	public $sub_menu_access = array();

	public function alias($alias = 0, $table = '')
	{
		if($alias == 0) return false;

		$alias1 = -1;
		if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => '<0', 'type' => '__tab_profile'), 'alias1'))
			$alias1 = $actions[0]->alias1 - 1;
		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => $alias1, 'alias2' => $alias, 'type' => '__tab_profile'));

		$alias1 = -1;
		if($actions = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => '<0', 'type' => '__link_profile'), 'alias1'))
			$alias1 = $actions[0]->alias1 - 1;
		$this->db->insertRow('wl_aliases_cooperation', array('alias1' => $alias1, 'alias2' => $alias, 'type' => '__link_profile'));

		return true;
	}

	public function alias_delete($alias = 0, $table = '', $uninstall_service = false)
	{
		$this->db->executeQuery("TRUNCATE TABLE IF EXISTS `{$this->table_service}`");
		return true;
	}

	public function setOption($option, $value, $table = '')
	{
		return true;
	}

    public function install_go()
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}` (
                      `id` int(11) NOT NULL  AUTO_INCREMENT,
                      `user` int(11) NOT NULL,
                      `alias` text NULL,
                      `content` int(11) NOT NULL,
                      `status` tinyint(1) NOT NULL,
                      `date_add` int(11) NOT NULL,
                      `date_update` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
        $this->db->executeQuery($query);

        return true;
    }

    public function uninstall()
    {
        $this->db->executeQuery("DROP TABLE IF EXISTS `{$this->table_service}`");
        return true;
    }	
}
?>