<?php

class install
{
	public $service = null;

	public $name = "newsletter";
	public $title = "Розсилка";
	public $description = "";
	public $group = "";
	public $table_service = "s_newsletter";
	public $multi_alias = 0;
	public $order_alias = 10;
	public $multi_page = 0;
	public $admin_ico = 'fa-envelope-o';
	public $version = "1.0";

	public $options = array('sent_per_part' => 20);
	public $options_type = array('sent_per_part' => 'number');
	public $options_title = array('sent_per_part' => 'email-ів надсилати за один захід розсилки');
	public $options_admin = array();
	public $sub_menu = array();

	public $cooperation_index = array();
	public $cooperation_types = array();
	public $cooperation_service = array();


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
		$this->db->executeQuery("ALTER TABLE `wl_users` ADD `{$this->table_service}` TINYINT(1) NOT NULL DEFAULT '1' AFTER `reset_expires`;");
		$this->db->executeQuery("ALTER TABLE `wl_users` ADD INDEX(` {$this->table_service} `);");

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_templates` (
				  	`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` text NOT NULL,
					`theme` text NULL,
					`text` text NULL,
					`from` text NULL,
					`to_user_types` text NULL,
					`date_add` int(11) NOT NULL,
					`date_edit` int(11) NOT NULL,
					`last_do` int(11) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_log` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `template` int(11) NOT NULL,
				  `to_user_types` text NULL,
				  `all_users` tinyint(1) NULL,
				  `emails_count` int(11) NOT NULL,
				  `emails_sent` int(11) NULL,
				  `from` text NOT NULL,
				  `date` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$this->db->executeQuery($query);

		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_files` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `template` int(11) NOT NULL,
				  `name` text NULL,
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