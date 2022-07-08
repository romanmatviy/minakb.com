<?php

class install
{
	public $service = null;
	
	public $name = "library";
	public $title = "Бібліотека статей (Блог)";
	public $description = "Бібліотека статей із підтримкою категорій. Мультимовна.";
	public $group = "page";
	public $table_service = "s_library";
	public $table_alias = "";
	public $multi_alias = 1;
	public $order_alias = 60;
	public $admin_ico = 'fa-book';
	public $version = "2.7.1";

	public $options = array('useGroups' => 1, 'articleMultiGroup' => 0, 'articleUseOptions' => 0, 'folder' => 'library', 'articleOrder' => 'position DESC', 'groupOrder' => 'position ASC');
	public $options_type = array('useGroups' => 'bool', 'articleMultiGroup' => 'bool', 'articleUseOptions' => 'bool', 'folder' => 'text', 'articleOrder' => 'text', 'groupOrder' => 'text');
	public $options_title = array('useGroups' => 'Наявність груп', 'articleMultiGroup' => 'Мультигрупи (1 стаття більше ніж 1 група)', 'articleUseOptions' => 'Наявність властивостей', 'folder' => 'Папка для зображень/аудіо', 'articleOrder' => 'Сортування товарів', 'groupOrder' => 'Сортування груп');
	public $options_admin = array (
					'word:articles_to_all' => 'статтей',
					'word:article_to' => 'До статті',
					'word:article_to_delete' => 'статтю',
					'word:article' => 'стаття',
					'word:articles' => 'статті',
					'word:article_add' => 'Додати статтю',
					'word:groups_to_all' => 'груп',
					'word:groups_to_delete' => 'групу',
					'word:group' => 'група',
					'word:group_add' => 'Додати групу статтей'
				);
	public $sub_menu = array("add" => "Додати статтю", "all" => "До всіх статтей", "groups" => "Групи");
	public $sub_menu_access = array("add" => 2, "all" => 2, "groups" => 2);

	function alias($alias = 0, $table = '')
	{
		if($alias == 0) return false;

		if($this->options['useGroups'] > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_groups` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `wl_alias` int(11) NOT NULL,
						  `alias` text,
						  `parent` int(11) DEFAULT NULL,
						  `position` int(11) DEFAULT NULL,
						  `active` tinyint(1) DEFAULT NULL,
						  `author_add` int(11) NOT NULL,
						  `date_add` int(11) NOT NULL,
						  `author_edit` int(11) NOT NULL,
						  `date_edit` int(11) NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`),
						  KEY `wl_alias` (`wl_alias`),
						  KEY `parent` (`parent`),
						  KEY `position` (`position`),
						  KEY `active` (`active`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			if($this->options['articleMultiGroup'] > 0)
			{
				$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_article_group` (
						  `article` int(11) NOT NULL,
						  `group` int(11) NOT NULL,
						  KEY `article` (`article`, `group`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
				$this->db->executeQuery($query);
			}
		}
		if($this->options['articleUseOptions'] > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_options` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `wl_alias` int(11) NOT NULL,
						  `group` int(11) NULL,
						  `alias` text NULL,
						  `position` int(11) NULL,
						  `type` int(11) NULL,
						  `filter` tinyint(1) NULL,
						  `active` tinyint(1) NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`),
						  KEY `wl_alias` (`wl_alias`),
						  KEY `group` (`group`),
						  KEY `active` (`active`),
						  KEY `position` (`position`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_options_name` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `option` int(11) NOT NULL,
						  `language` varchar(2) NULL,
						  `name` text NULL,
						  `sufix` text NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`),
						  KEY `option` (`option`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_article_options` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `article` int(11) NOT NULL,
				  `option` int(11) NOT NULL,
				  `language` varchar(2) NOT NULL DEFAULT '',
				  `value` text NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `option` (`article`, `option`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);
		}
		return true;
	}

	public function alias_delete($alias = 0, $table = '', $uninstall_service = false)
	{
		if(!$uninstall_service)
		{
			$articles = $this->db->getAllDataByFieldInArray($this->table_service.'_articles', $alias, 'wl_alias');
			if(!empty($articles))
			{
				$this->db->deleteRow($this->table_service.'_articles', $alias, 'wl_alias');
				if($this->options['useGroups'] > 0)
					$this->db->deleteRow($this->table_service.'_groups', $alias, 'wl_alias');
				
				if($this->options['articleMultiGroup'] > 0)
					foreach ($articles as $article) {
						$this->db->deleteRow($this->table_service.'_article_group', $article->id, 'article');
					}

				if($this->options['articleUseOptions'] > 0)
					foreach ($articles as $article) {
						$this->db->deleteRow($this->table_service.'_article_options', $article->id, 'article');
					}
			}
			if($this->options['useGroups'] > 0)
			{
				$groups = $this->db->getAllDataByFieldInArray($this->table_service.'_groups', $alias, 'wl_alias');
				if(!empty($groups))
					$this->db->deleteRow($this->table_service.'_groups', $alias, 'wl_alias');
			}
			if($this->options['articleUseOptions'] > 0)
			{
				$options = $this->db->getAllDataByFieldInArray($this->table_service.'_options', $alias, 'wl_alias');
				if(!empty($options))
				{
					foreach ($options as $option) {
						$this->db->deleteRow($this->table_service.'_options_name', $option->id, 'option');
					}
					$this->db->deleteRow($this->table_service.'_options', $alias, 'wl_alias');
				}
			}
		}
		return true;
	}

	public function setOption($option, $value, $alias, $table = '')
	{
		$this->options[$option] = $value;

		if ($option == 'useGroups' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_groups` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `wl_alias` int(11) NOT NULL,
						  `alias` text,
						  `parent` int(11) DEFAULT NULL,
						  `position` int(11) DEFAULT NULL,
						  `active` tinyint(1) DEFAULT NULL,
						  `author_add` int(11) NOT NULL,
						  `date_add` int(11) NOT NULL,
						  `author_edit` int(11) NOT NULL,
						  `date_edit` int(11) NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`),
						  KEY `wl_alias` (`wl_alias`),
						  KEY `parent` (`parent`),
						  KEY `position` (`position`),
						  KEY `active` (`active`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);
		}
		if($option == 'articleMultiGroup' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_article_group` (
						  `article` int(11) NOT NULL,
						  `group` int(11) NOT NULL,
						  KEY `article` (`article`, `group`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			$articles = $this->db->getAllDataByFieldInArray($this->table_service.'_articles', $alias, 'wl_alias');
			if($articles)
			{
				$list = array();
				foreach ($articles as $article) {
					$list[] = $article->id;
				}

				$count = $this->db->getCount($this->table_service.'_article_group', array('article' => $list));
				if($count > 0)
				{
					foreach ($articles as $article) {
						$this->db->insertRow($this->table_service.'_article_group'.$table, array('article' => $article->id, 'group' => $article->group));
					}
				}
			}
		}
		if($option == 'articleUseOptions' AND $value > 0)
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_options` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `wl_alias` int(11) NOT NULL,
						  `group` int(11) NULL,
						  `alias` text NULL,
						  `position` int(11) NULL,
						  `type` int(11) NULL,
						  `filter` tinyint(1) NULL,
						  `active` tinyint(1) NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`),
						  KEY `wl_alias` (`wl_alias`),
						  KEY `group` (`group`),
						  KEY `active` (`active`),
						  KEY `position` (`position`)
						) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_options_name` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `option` int(11) NOT NULL,
						  `language` varchar(2) NULL,
						  `name` text NULL,
						  `sufix` text NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `id` (`id`),
						  KEY `option` (`option`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);

			$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_article_options` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `article` int(11) NOT NULL,
				  `option` int(11) NOT NULL,
				  `language` varchar(2) NULL,
				  `value` text NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `option` (`article`, `option`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
			$this->db->executeQuery($query);
		}
	}

	public function install_go()
	{
		$query = "CREATE TABLE IF NOT EXISTS `{$this->table_service}_articles` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `wl_alias` int(11) NOT NULL,
					  `alias` text,
					  `group` int(11) DEFAULT NULL,
					  `active` tinyint(1) DEFAULT NULL,
					  `position` int(11) DEFAULT NULL,
					  `author_add` int(11) NOT NULL,
					  `date_add` int(11) NOT NULL,
					  `author_edit` int(11) NOT NULL,
					  `date_edit` int(11) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `id` (`id`),
					  KEY `wl_alias` (`wl_alias`),
					  KEY `group` (`group`),
					  KEY `active` (`active`),
					  KEY `position` (`position`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		$this->db->executeQuery($query);

		return true;
	}

	public function uninstall($service = 0)
	{
		if(isset($_POST['content']) && $_POST['content'] == 1)
		{
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_articles");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_article_group");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_groups");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_options");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_options_name");
			$this->db->executeQuery("DROP TABLE IF EXISTS {$this->table_service}_article_options");
		}
		return true;
	}
	
}

?>