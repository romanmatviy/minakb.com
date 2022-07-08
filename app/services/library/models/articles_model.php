<?php

class articles_model {

	public function table($sufix = '_articles', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getArticles($Group = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($active) {
			$where['active'] = 1;
		}

		if($_SESSION['option']->useGroups > 0)
		{
			if(is_array($Group) && !empty($Group))
			{
				$where['id'] = array();
				foreach ($Group as $g) {
					$articles = $this->db->getAllDataByFieldInArray($this->table('_article_group'), $g->id, 'group');
					if($articles) {
						foreach ($articles as $article) {
							array_push($where['id'], $article->article);
						}
					}
				}
			}
			elseif($Group > 0)
			{
				if($_SESSION['option']->articleMultiGroup == 0) {
					$where['group'] = $Group;
				} else {
					$articles = $this->db->getAllDataByFieldInArray($this->table('_article_group'), $Group, 'group');
					if($articles) {
						$where['id'] = array();
						foreach ($articles as $article) {
							array_push($where['id'], $article->article);
						}
					} else {
						return null;
					}
				}
			}
		}

		$this->db->select($this->table().' as p', '*', $where);
		
		$this->db->join('wl_users as a', 'name as author_add_name', '#p.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#p.author_edit');

		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->articleMultiGroup == 0)
		{
			$where_gn['alias'] = $_SESSION['alias']->id;
			$where_gn['content'] = "#-p.group";
			if($_SESSION['language']) $where_gn['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd as gn', 'name as group_name', $where_gn);
		}

		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = "#p.id";
		if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd as n', 'name, text, list', $where_ntkd);

		$this->db->order($_SESSION['option']->articleOrder);

		$articles = $this->db->get('array');
        if($articles)
        {
        	$list = array();
        	if($_SESSION['option']->useGroups > 0)
        	{
	            $all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups) foreach ($all_groups as $g) {
	            	$list[$g->id] = clone $g;
	            }
	        }

            foreach ($articles as $article)
            {
            	$article->link = $article->alias;

				$article->parents = array();
				if($_SESSION['option']->useGroups > 0)
				{
					if($_SESSION['option']->articleMultiGroup == 0 && $article->group > 0){
						$article->parents = $this->makeParents($list, $article->group, $article->parents);
						$link = '';
						foreach ($article->parents as $parent) {
							$link .= $parent->alias .'/';
						}
						$article->link = $link . $article->alias;
					} elseif($_SESSION['option']->articleMultiGroup == 1){
						$article->group = array();

						$this->db->select($this->table('_article_group') .' as pg', '', $article->id, 'article');
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
						$where_ntkd['content'] = "#-pg.group";
            			$this->db->join('wl_ntkd', 'name', $where_ntkd);
						$article->group = $this->db->get('array');

						if($article->group)
				            foreach ($article->group as $g) {
				            	if($g->parent > 0) {
				            		$g->link = $this->makeLink($list, $g->parent, $g->alias);
				            	}
				            }
					}
				}
            }

			return $articles;
		}
		return null;
	}
	
	public function getById($id)
	{
		$this->db->select($this->table().' as p', '*', array('wl_alias' => $_SESSION['alias']->id, 'id' => $id));

		$this->db->join('wl_users as a', 'name as author_add_name', '#p.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#p.author_edit');

		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->articleMultiGroup == 0)
		{
			$where_gn['alias'] = $_SESSION['alias']->id;
			$where_gn['content'] = "#-p.group";
			if($_SESSION['language']) $where_gn['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd as gn', 'name as group_name', $where_gn);
		}

		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = "#p.id";
		if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd as n', 'name, text, list', $where_ntkd);

		$article = $this->db->get('single');
        if($article)
        {
        	$article->link = $_SESSION['alias']->alias.'/'.$article->alias;

			$article->parents = array();
			if($_SESSION['option']->useGroups > 0)
			{
				$list = array();
				$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups) foreach ($all_groups as $g) {
	            	$list[$g->id] = clone $g;
	            }

				if($_SESSION['option']->articleMultiGroup == 0 && $article->group > 0){
					$article->parents = $this->makeParents($list, $article->group, $article->parents);
					$link = '';
					foreach ($article->parents as $parent) {
						$link .= $parent->alias .'/';
					}
					$article->link = $link . $article->alias;
				} elseif($_SESSION['option']->articleMultiGroup == 1){
					$article->group = array();

					$this->db->select($this->table('_article_group') .' as pg', '', $article->id, 'article');
					$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
					$where_ntkd['content'] = "#-pg.group";
        			$this->db->join('wl_ntkd', 'name', $where_ntkd);
					$article->group = $this->db->get('array');

		            foreach ($article->group as $g) {
		            	if($g->parent > 0) {
		            		$g->link = $this->makeLink($list, $g->parent, $g->alias);
		            	}
		            }
				}
			}
            return $article;
		}
		return null;
	}
	
	public function add(&$link = '', &$name = '')
	{
		$data = array();
		$data['wl_alias'] = $_SESSION['alias']->id;
		$data['active'] = 1;
		$data['author_add'] = $_SESSION['user']->id;
		$data['date_add'] = time();
		$data['author_edit'] = $_SESSION['user']->id;
		$data['date_edit'] = time();

		if($this->db->insertRow($this->table(), $data))
		{
			$id = $this->db->getLastInsertedId();
			$data = array();
			$data['alias'] = '';

			$ntkd['alias'] = $_SESSION['alias']->id;
			$ntkd['content'] = $id;
			if($_SESSION['language'])
			{
				foreach ($_SESSION['all_languages'] as $lang) {
					$ntkd['language'] = $lang;
					$name = trim($this->data->post('name_'.$lang));
					$ntkd['name'] = $name;
					if($lang == $_SESSION['language'])
						$data['alias'] = $this->data->latterUAtoEN($name);
					$this->db->insertRow('wl_ntkd', $ntkd);
				}
			}
			else
			{
				$name = trim($this->data->post('name'));
				$ntkd['name'] = $name;
				$data['alias'] = $this->data->latterUAtoEN($name);
				$this->db->insertRow('wl_ntkd', $ntkd);
			}
			
			$link = $data['alias'] = $this->ckeckAlias($data['alias']);
			
			if($_SESSION['option']->useGroups)
			{
				if($_SESSION['option']->articleMultiGroup && isset($_POST['group']) && is_array($_POST['group']))
				{
					foreach ($_POST['group'] as $group) {
						$this->db->insertRow($this->table('_article_group'), array('article' => $id, 'group' => $group));
					}
				}
				elseif(isset($_POST['group']) && is_numeric($_POST['group']))
				{
					$data['group'] = $_POST['group'];
					$data['position'] = 1 + $this->db->getCount($this->table('_articles'), array('wl_alias' => $_SESSION['alias']->id, 'group' => $data['group']));

					if($data['group'] > 0)
					{
						$groups = array();
						$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
			            if($all_groups) foreach ($all_groups as $g) {
			            	$groups[$g->id] = clone $g;
			            }
						$link = $this->makeLink($groups, $_POST['group'], $link);
					}
				}
			}
			else
				$data['position'] = $this->db->getCount($this->table('_articles'), $_SESSION['alias']->id, 'wl_alias');
			
			$this->db->sitemap_add($id, $_SESSION['alias']->alias.'/'.$link);
			if($this->db->updateRow($this->table('_articles'), $data, $id)) return $id;
		}
		return false;
	}

	public function save($id)
	{
		$data = array('active' => 0, 'author_edit' => $_SESSION['user']->id, 'date_edit' => time());

		$data['alias'] = trim($this->data->post('alias'));
		$check_article = $this->db->getAllDataById($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'alias' => $data['alias']));
		if($check_article && $check_article->id != $id)
		{
			unset($data['alias']);
			$_SESSION['notify']->errors = 'Дана адреса зайнята! Інші дані збережено успішно.';
		}
		if(!$check_article || $check_article->id != $id) $check_article = $this->db->getAllDataById($this->table(), $id);
		$check_article->link = $check_article->alias;
		if(isset($data['alias'])) $check_article->link = $data['alias'];

		if(isset($_POST['active']) && $_POST['active'] == 1) $data['active'] = 1;
		if($_SESSION['option']->useGroups)
		{
			if($_SESSION['option']->articleMultiGroup)
			{
				$use = array();
				$activegroups = $this->db->getAllDataByFieldInArray($this->table('_article_group'), $id, 'article');
				if($activegroups) {
					$temp = array();
					foreach ($activegroups as $ac) {
						$temp[] = $ac->group;
					}
					$activegroups = $temp;
					$temp = null;
				}
				else
					$activegroups = array();
				if(isset($_POST['group']) && is_array($_POST['group']))
				{
					foreach ($_POST['group'] as $group) {
						if(!in_array($group, $activegroups))
							$this->db->insertRow($this->table('_article_group'), array('article' => $id, 'group' => $group));
						$use[] = $group;
					}
				}
				if($activegroups)
				{
					foreach ($activegroups as $ac) {
						if(!in_array($ac, $use))
							$this->db->executeQuery("DELETE FROM {$this->table('_article_group')} WHERE `article` = '{$id}' AND `group` = '{$ac}'");
					}
				}
			}
			else
			{
				if(isset($_POST['group']) && is_numeric($_POST['group']) && $_POST['group'] != $_POST['group_old'])
				{
					$data['group'] = $_POST['group'];
					$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > '{$_POST['position_old']}' AND `group` = '{$_POST['group_old']}'");
					$data['position'] = 1 + $this->db->getCount($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'group' => $data['group']));
					if($data['group'] > 0)
					{
						$groups = array();
						$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
			            if($all_groups) 
			            	foreach ($all_groups as $g) {
				            	$groups[$g->id] = clone $g;
				            }
						$check_article->link = $this->makeLink($groups, $_POST['group'], $check_article->link);
					}
				}
			}
		}
		$this->db->sitemap_update($id, 'link', $_SESSION['alias']->alias.'/'.$check_article->link);
		$this->db->sitemap_index($id, $data['active']);
		$this->db->updateRow($this->table(), $data, $id);
		$this->db->html_cache_clear(0);
		return $check_article->link;
	}

	public function saveArticleOptios($id)
	{
		$options = array();
		foreach ($_POST as $key => $value) {
			$is_array = (is_array($_POST[$key])) ? true : false;
			$key = explode('-', $key);
			if($key[0] == 'option' && isset($key[1]) && is_numeric($key[1]))
			{
				if($is_array)
					$options[$key[1]] = implode(',', $value);
				else
				{
					if($_SESSION['language'] && isset($key[2]) && in_array($key[2], $_SESSION['all_languages']))
						$options[$key[1]][$key[2]] = $value;
					else
						$options[$key[1]] = $value;
				}
			}
		}
		$list_temp = $this->db->getAllDataByFieldInArray($this->table('_article_options'), $id, 'article');
		$list = array();
		if($list_temp)
			foreach ($list_temp as $option) {
				if($_SESSION['language'] && $option->language != ''){
					$list[$option->option][$option->language] = $option;
				} else {
					$list[$option->option] = $option;
				}
			}
		if(!empty($options))
		{
			foreach ($options as $key => $value) {
				if(is_array($value))
				{
					foreach ($value as $lang => $value2) {

						if($_SESSION['language'] && isset($list[$key][$lang]))
						{
							if($list[$key][$lang]->value != $value2)
								$this->db->updateRow($this->table('_article_options'), array('value' => $value2), $list[$key][$lang]->id);
							unset($list[$key]);
						}
						elseif(isset($list[$key]))
						{
							if($list[$key]->value != $value2)
								$this->db->updateRow($this->table('_article_options'), array('value' => $value2), $list[$key]->id);
							unset($list[$key]);
						}
						else
						{
							$data['article'] = $id;
							$data['option'] = $key;
							$data['language'] = $lang;
							$data['value'] = $value2;
							$this->db->insertRow($this->table('_article_options'), $data);
						}
					}
				}
				else
				{
					if(isset($list[$key]))
					{
						if($list[$key]->value != $value)
							$this->db->updateRow($this->table('_article_options'), array('value' => $value), $list[$key]->id);
					}
					else
					{
						$data['article'] = $id;
						$data['option'] = $key;
						$data['value'] = $value;
						$data['language'] = '';
						$this->db->insertRow($this->table('_article_options'), $data);
					}
					unset($list[$key]);
				}
			}
		}
		if(!empty($list))
		{
			foreach ($list as $option) {
				if(is_array($option))
					foreach ($option as $el) {
						$this->db->deleteRow($this->table('_article_options'), $el->id);
					}
				else
					$this->db->deleteRow($this->table('_article_options'), $option->id);
			}
		}
		return true;
	}

	public function delete($id)
	{
		$article = $this->getById($id);
		if($article)
		{
			$this->db->sitemap_remove($article->id);
			$this->db->deleteRow($this->table(), $article->id);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `id` > '{$article->id}'");
			$this->db->executeQuery("DELETE FROM `wl_ntkd` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '{$article->id}'");
			$this->db->executeQuery("DELETE FROM `wl_audio` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '{$article->id}'");
			$this->db->executeQuery("DELETE FROM `wl_images` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '{$article->id}'");
			$this->db->executeQuery("DELETE FROM `wl_video` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '{$article->id}'");
			
			$path = IMG_PATH.$_SESSION['option']->folder.'/'.$article->id;
			$path = substr($path, strlen(SITE_URL));
			$this->data->removeDirectory($path);

			$link = '';
			if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->articleMultiGroup == 0){
				$article->link = explode('/', $article->link);
				array_pop ($article->link);
				$link = implode('/', $article->link);
			}
			return $link;
		}
	}

	private function makeLink($all, $parent, $link)
	{
		$link = $all[$parent]->alias .'/'.$link;
		if($all[$parent]->parent > 0) $link = $this->makeLink ($all, $all[$parent]->parent, $link);
		return $link;
	}

	public function makeParents($all, $parent, $parents)
	{
		$group = clone $all[$parent];
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = "-{$group->id}";
        if($_SESSION['language']) $where['language'] = $_SESSION['language'];
        $this->db->select("wl_ntkd", 'name', $where);
        $ntkd = $this->db->get('single');
    	if($ntkd) {
    		$group->name = $ntkd->name;
    	}
    	array_unshift ($parents, $group);
		if($all[$parent]->parent > 0) $parents = $this->makeParents ($all, $all[$parent]->parent, $parents);
		return $parents;
	}

	private function ckeckAlias($link){
		$Group = $this->db->getAllDataById($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'alias' => $link));
		$end = 0;
		$link2 = $link;
		while ($Group) {
			$end++;
			$link2 = $link.'-'.$end;
		 	$Group = $this->db->getAllDataById($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'alias' => $link2));
		}
		return $link2;
	}
	
}

?>