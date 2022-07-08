<?php

class groups_model {

	public $allGroups = false;

    public function init()
    {
		if(empty($this->allGroups))
		{
			$where = array();
			$where['wl_alias'] = $_SESSION['alias']->id;
			$this->db->select($this->table('_groups') .' as g', '*', $where);

			$where_ntkd['alias'] = $_SESSION['alias']->id;
			$where_ntkd['content'] = "#-g.id";
			if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd', "name", $where_ntkd);
			$this->db->join('wl_users as ua', 'name as author_add_name', '#g.author_add');
			$this->db->join('wl_users as ue', 'name as user_name', '#g.author_edit');
			$this->db->order($_SESSION['option']->groupOrder);
			if($list = $this->db->get('array'))
			{
				foreach ($list as $g) {
	            	$this->allGroups[$g->id] = clone $g;
	            }
	            unset($list);
	        }
		}
    }

	public function table($sufix = '_groups', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function getGroups($parent = 0, $active = true)
	{
		if(empty($this->allGroups))
    		$this->init();
        if(empty($this->allGroups))
        	return false;

		$categories = array();
		if($parent < 0 && !$active)
			$categories = $this->allGroups;
		else
			foreach ($this->allGroups as $group) {
				if($active && $group->active)
				{
					if($parent < 0)
						$categories[] = clone $group;
					else if($group->parent == $parent)
						$categories[] = clone $group;
				}
				elseif(!$active)
				{
					if($parent < 0)
						$categories[] = clone $group;
					else if($group->parent == $parent)
						$categories[] = clone $group;
				}
			}
		if(empty($categories))
        	return false;
		else
		{
			$link = $_SESSION['alias']->alias.'/';
	        if($parent > 0)
	        	$link .= $this->makeLink($parent, '');

            foreach ($categories as $Group) {
            	$Group->link = $link.$Group->alias;
            	if($parent < 0 && $Group->parent > 0)
            		$Group->link = $link.$this->makeLink($Group->parent, $Group->alias);
            }
            return $categories;
		}
		return false;
	}

	public function getByAlias($alias, $parent = 0)
	{
		$where['wl_alias'] = $_SESSION['alias']->id;
		$where['alias'] = $alias;
		$where['parent'] = $parent;
		$this->db->select($this->table() .' as c', '*', $where);
		$this->db->join('wl_users', 'name as user_name', '#c.author_edit');
		return $this->db->get('single');
	}

	public function getById($id)
	{
		if(empty($this->allGroups))
    		$this->init();
		if(isset($this->allGroups[$id]))
			$group = $this->allGroups[$id];
		else
			return false;

		$group->link = $_SESSION['alias']->alias.'/';
		$group->parents = array();
        if($group->parent > 0)
        {
        	$group->parents = $this->makeParents($group->parent, $group->parents);
        	$group->link .= $this->makeLink($group->parent, '');
        }
        $group->link .= $group->alias; 
        return $group;
	}

	public function add(&$alias = '')
	{
		$data = array();
		$data['wl_alias'] = $_SESSION['alias']->id;
		$parent = $data['parent'] = $data['position'] = 0;
		if(isset($_POST['parent']) && is_numeric($_POST['parent']) && $_POST['parent'] > 0)
			$parent = $data['parent'] = $_POST['parent'];
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
			$data['position'] = $this->db->getCount($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'parent' => $parent));

			$ntkd['alias'] = $_SESSION['alias']->id;
			$ntkd['content'] = $id;
			$ntkd['content'] *= -1;
			if($_SESSION['language'])
			{
				foreach ($_SESSION['all_languages'] as $lang) {
					$ntkd['language'] = $lang;
					$ntkd['name'] = $this->data->post('name_'.$lang);
					if($lang == $_SESSION['language'])
						$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
					$this->db->insertRow('wl_ntkd', $ntkd);
				}
			}
			else
			{
				$ntkd['name'] = $this->data->post('name');
				$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
				$this->db->insertRow('wl_ntkd', $ntkd);
			}

			$data['alias'] = $this->makeLink_v1($data['alias'], $parent);
			$alias = $data['alias'];

			if($parent == 0)
				$this->db->sitemap_add(-$id, $_SESSION['alias']->alias.'/'.$alias, 200, 6);
			else
			{
				$list = array();
	            $groups = $this->db->getAllDataByFieldInArray($this->table(), $_SESSION['alias']->id, 'wl_alias');
	            foreach ($groups as $Group) {
	            	$list[$Group->id] = clone $Group;
	            }
	            $link = $this->getLink($list, $parent, $alias);
	            $this->db->sitemap_add(-$id, $_SESSION['alias']->alias.'/'.$link, 200, 6);
			}

			if($this->db->updateRow($this->table(), $data, $id))
				return $id;
		}
		return false;
	}

	public function save($id)
	{
		$group = $this->db->getAllDataById($this->table(), $id);
		if($group)
		{
			$data = array('active' => 0, 'hide' => 0);
			if(isset($_POST['alias']) && $_POST['alias'] != '')
				$data['alias'] = $this->data->post('alias');
			if(isset($_POST['active']) && $_POST['active'] == 1)
				$data['active'] = 1;
			if(isset($_POST['hide']) && $_POST['hide'] == 1)
				$data['hide'] = 1;
			if(isset($_POST['parent']) && is_numeric($_POST['parent']) && $_POST['parent'] >= 0)
				$data['parent'] = $_POST['parent'];
			if (isset($_SESSION['admin_options']['groups:additional_fields']) && $_SESSION['admin_options']['groups:additional_fields'] != '')
			{
				$fields = explode(',', $_SESSION['admin_options']['groups:additional_fields']);
				foreach ($fields as $field) {
					$data[$field] = $this->data->post($field);
				}
			}
			if($group->parent != $data['parent'])
				$this->changeParent($group->id, $group->parent, $data['parent']);

			if($group->alias != $data['alias'] || $group->parent != $data['parent'])
			{
				$list = array();
	            $groups = $this->db->getAllDataByFieldInArray($this->table(), $_SESSION['alias']->id, 'wl_alias');
	            foreach ($groups as $Group) {
	            	$list[$Group->id] = clone $Group;
	            }
	            $link = $this->getLink($list, $data['parent'], $data['alias']);
	            $this->db->sitemap_update(-$id, 'link', $_SESSION['alias']->alias.'/'.$link);
	            $this->db->html_cache_clear(0);
			}

			$this->db->html_cache_clear(-$id);
			if($this->db->updateRow($this->table(), $data, $id))
			{
				if($group->active != $data['active'] || $group->alias != $data['alias'] || $group->parent != $data['parent'])
				{
					if($_SESSION['option']->ProductMultiGroup)
					{
						$this->db->sitemap_index(-$id, $data['active']);
						if($groups = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $id, 'group'))
							foreach ($groups as $pg) {
								$this->db->html_cache_clear($pg->product);
							}
					}
					else
					{
						if(empty($list) || !is_array($list) || $list[$id]->id != $id)
						{
							$list = array();
				            $groups = $this->db->getAllDataByFieldInArray($this->table(), $_SESSION['alias']->id, 'wl_alias');
				            foreach ($groups as $Group) {
				            	$list[$Group->id] = clone $Group;
				            }
						}
						$up = $id;
						while ($up > 0) {
							if($group->active != $data['active'])
								$this->db->sitemap_index(-$up, $data['active']);
							if($products = $this->db->getAllDataByFieldInArray($this->table('_products'), $up, 'group'))
							{
								foreach ($products as $product) {
									if($group->active != $data['active'])
										$this->db->sitemap_index($product->id, $data['active']);
									if($group->alias != $data['alias'] || $group->parent != $data['parent'])
										$this->db->html_cache_clear($product->id);
								}
							}
							$this->db->html_cache_clear(-$up);
							$up = $list[$up]->parent;
						}
					}
				}
				return true;
			}
		}
		return false;
	}

	public function delete($id)
	{
		$group = $this->db->getAllDataById($this->table(), $id);
		if($group)
		{
			$content = false;
			if(isset($_POST['content']) && $_POST['content'] == 1) $content = true;
			if($content)
			{
				$list = array();
				$childs1 = array();
				$emptyParentsList = array();
	            $groups = $this->db->getAllDataByFieldInArray($this->table(), $_SESSION['alias']->id, 'wl_alias');
	            foreach ($groups as $g) {
	            	$list[$g->id] = clone $g;
	            	$list[$g->id]->childs = array();
					if(isset($emptyParentsList[$g->id])){
						foreach ($emptyParentsList[$g->id] as $c) {
							$list[$g->id]->childs[] = $c;
						}
					}
					if($g->parent > 0) {
						if(isset($list[$g->parent]->childs)) $list[$g->parent]->childs[] = $g->id;
						else {
							if(isset($emptyParentsList[$g->parent])) $emptyParentsList[$g->parent][] = $group->id;
							else $emptyParentsList[$g->parent] = array($g->id);
						}
					}
	            	if($g->parent == $group->id) $childs1[] = $g->id;
	            }
				$childs = $this->getParents($list, $childs1);

				$this->deleteProductsByGroup($group->id);
				if($childs)
				{
					foreach ($childs as $g) {
						$this->deleteProductsByGroup($g);
						$this->db->deleteRow($this->table(), $g);
						$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '-{$g}'");
					}
				}
			}
			else
			{
				$groups = $this->db->getAllDataByFieldInArray($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'parent' => $group->id));
				if($groups){
					$count = $this->db->getCount($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'parent' => $group->parent));
		            foreach ($groups as $g) {
		            	$count++;
		            	$this->db->updateRow($this->table(), array('parent' => $group->parent, 'position' => $count), $g->id);
		            }
				}
				$this->db->executeQuery("UPDATE `{$this->table('_products')}` SET `group` = '{$group->parent}' WHERE `group` = '{$group->id}'");
			}

			$this->db->sitemap_remove(-$group->id);
			$this->db->deleteRow($this->table(), $group->id);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > '{$group->position}'");
			$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '-{$group->id}'");
			$this->db->executeQuery("DELETE FROM wl_audio WHERE alias = '{$_SESSION['alias']->id}' AND content = '-{$group->id}'");
			$this->db->executeQuery("DELETE FROM wl_images WHERE alias = '{$_SESSION['alias']->id}' AND content = '-{$group->id}'");
			$this->db->executeQuery("DELETE FROM wl_video WHERE alias = '{$_SESSION['alias']->id}' AND content = '-{$group->id}'");

			$path = IMG_PATH.$_SESSION['option']->folder.'/-'.$group->id;
			$path = substr($path, strlen(SITE_URL));
			$this->data->removeDirectory($path);

			return true;
		}
		return false;
	}

	private function changeParent($id, $old, $new)
	{
		$groups = $this->db->getAllDataByFieldInArray($this->table(), $_SESSION['alias']->id, 'wl_alias');
		if($groups)
		{
			$level_1 = array();
			$childs = array();
			$list = array();
			$emptyParentsList = array();
			foreach ($groups as $group) {
				$list[$group->id] = $group;
				$list[$group->id]->childs = array();
				if(isset($emptyParentsList[$group->id])){
					foreach ($emptyParentsList[$group->id] as $c) {
						$list[$group->id]->childs[] = $c;
					}
				}
				if($group->parent > 0) {
					if(isset($list[$group->parent]->childs)) $list[$group->parent]->childs[] = $group->id;
					else {
						if(isset($emptyParentsList[$group->parent])) $emptyParentsList[$group->parent][] = $group->id;
						else $emptyParentsList[$group->parent] = array($group->id);
					}
				}
				if($group->parent == $id){
					$level_1[] = $group->id;
					$childs[] = $group->id;
				}
			}
			if(!empty($level_1)){
				foreach ($level_1 as $group) {
					if(!empty($list[$group]->childs)){
						$childs = $this->getParents($list, $list[$group]->childs);
					}
				}
			}
			if(in_array($new, $level_1) || in_array($new, $childs)){
				$position = $list[$id]->position;
				$groups = $this->db->getAllDataByFieldInArray($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'parent' => $old, 'position' => '>'.$list[$id]->position), 'position ASC');
				foreach ($level_1 as $group) {
					$this->db->updateRow($this->table(), array('parent' => $old, 'position' => $position), $group);
					$position++;
				}
				if($groups){
					$step = $position - $groups[0]->position;
					foreach ($groups as $group) {
						$position = $group->position + $step;
						$this->db->updateRow($this->table(), array('position' => $position), $group->id);
					}
				}
			} else {
				$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = `position` - 1 WHERE `wl_alias` = {$_SESSION['alias']->id} AND `parent` = '{$old}' AND `position` > '{$list[$id]->position}'");
			}
			$position = $this->db->getCount($this->table(), $new, 'parent');
            $position++;
            $this->db->updateRow($this->table(), array('position' => $position), $id);
		}
		return true;
	}

	public function makeParents($parent, $parents)
	{
		if(empty($this->allGroups))
    		$this->init();
		if(isset($this->allGroups[$parent]))
		{
			$group = clone $this->allGroups[$parent];
	    	array_unshift ($parents, $group);
			if($this->allGroups[$parent]->parent > 0)
				$parents = $this->makeParents ($this->allGroups[$parent]->parent, $parents);
		}
		return $parents;
	}

	private function makeLink($parent, $link)
	{
		$link = $this->allGroups[$parent]->alias .'/'.$link;
		if($this->allGroups[$parent]->parent > 0)
			$link = $this->makeLink ($this->allGroups[$parent]->parent, $link);
		return $link;
	}

	private function makeLink_v1($link, $parent = 0){
		$Group = $this->getByAlias($link, $parent);
		$end = 0;
		$link2 = $link;
		while ($Group) {
			$end++;
			$link2 = $link.'-'.$end;
		 	$Group = $this->getByAlias($link2, $parent);
		}
		return $link2;
	}

	private function getLink($all, $parent, $link)
	{
		if($parent > 0)
		{
			$link = $all[$parent]->alias .'/'.$link;
			if($all[$parent]->parent > 0) $link = $this->getLink ($all, $all[$parent]->parent, $link);
		}
		return $link;
	}

	private function getParents($all, $list)
	{
		$childs = array();
		foreach ($list as $group) {
			$childs[] = $group;
			if(!empty($all[$group]->childs)) $childs = array_merge($childs, $this->getParents($all, $all[$group]->childs));
		}
		return $childs;
	}

	private function deleteProductsByGroup($group)
	{
		$products = $this->db->getAllDataByFieldInArray($this->table('_products'), $group, 'group');
		if($products)
			foreach ($products as $a) {
				$this->db->sitemap_remove($a->id);
				$this->db->deleteRow($this->table('_products'), $a->id);
				$this->db->deleteRow($this->table('_product_options'), $a->id, 'product');
				if($_SESSION['option']->searchHistory)
					$this->db->deleteRow($this->table('_search_history'), $a->id, 'product_id');
				$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$a->id}'");
				$this->db->executeQuery("DELETE FROM wl_audio WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$a->id}'");
				$this->db->executeQuery("DELETE FROM wl_images WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$a->id}'");
				$this->db->executeQuery("DELETE FROM wl_video WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$a->id}'");

				$path = IMG_PATH.$_SESSION['option']->folder.'/'.$a->id;
				$path = substr($path, strlen(SITE_URL));
				$this->data->removeDirectory($path);
			}
		return true;
	}

}

?>