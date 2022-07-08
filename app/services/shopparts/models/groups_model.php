<?php

class groups_model {

	public function table($sufix = '_groups', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function getGroups($parent = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($active) $where['active'] = 1;
		if($parent >= 0) $where['parent'] = $parent;
		$this->db->select($this->table() .' as c', '*', $where);
		$this->db->join('wl_users', 'name as user_name', '#c.author_edit');
		$this->db->order($_SESSION['option']->groupOrder);
		
		$categories = $this->db->get('array');
		if($categories)
		{
            $list = array();
            $groups = $this->db->getAllDataByFieldInArray($this->table(), $_SESSION['alias']->id, 'wl_alias');
            foreach ($groups as $Group) {
            	$list[$Group->id] = clone $Group;
            }

            $where = array();
            if($_SESSION['language']) $where['language'] = $_SESSION['language'];
            $where['alias'] = $_SESSION['alias']->id;

            foreach ($categories as $Group) {
            	$Group->link = $Group->alias;
            	if($Group->parent > 0) {
            		$Group->link = $this->getLink($list, $Group->parent, $Group->alias);
            	}
            	$where['content'] = $Group->id * -1;
            	$this->db->select('wl_ntkd', "name, text, list", $where);
            	$ntkd = $this->db->get('single');
            	if($ntkd){
            		$Group->name = $ntkd->name;
            		$Group->list = $ntkd->list;
            		$Group->text = $ntkd->text;
            	}
            }
		}
		return $categories;
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
		$this->db->select($this->table() .' as g', '*', array('wl_alias' => $_SESSION['alias']->id, 'id' => $id));
		$this->db->join('wl_users', 'name as user_name', '#g.author_edit');
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = '#-g.id';
		if($_SESSION['language']) {
			$where['language'] = $_SESSION['language'];
		}
		$this->db->join('wl_ntkd', 'name', $where);
		return $this->db->get('single');
	}

	public function add(&$alias)
	{
		$data = array();
		$data['wl_alias'] = $_SESSION['alias']->id;
		$data['parent'] = 0;
		if(isset($_POST['parent']) && is_numeric($_POST['parent']) && $_POST['parent'] > 0) $data['parent'] = $_POST['parent'];
		$data['active'] = 1;
		$data['author_add'] = $_SESSION['user']->id;
		$data['date_add'] = time();
		$data['author_edit'] = $_SESSION['user']->id;
		$data['date_edit'] = time();
		if($this->db->insertRow($this->table(), $data)){
			$id = $this->db->getLastInsertedId();

			$position = $this->db->getCount($this->table(), array('wl_alias' => $_SESSION['alias']->id, 'parent' => $data['parent']));

			$data = array();
			$data['alias'] = '';

			$ntkd['alias'] = $_SESSION['alias']->id;
			$ntkd['content'] = $id;
			$ntkd['content'] *= -1;
			if($_SESSION['language']){
				foreach ($_SESSION['all_languages'] as $lang) {
					$ntkd['language'] = $lang;
					$ntkd['name'] = $this->data->post('name_'.$lang);
					if($lang == $_SESSION['language']){
						$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
					}
					$this->db->insertRow('wl_ntkd', $ntkd);
				}
			} else {
				$ntkd['name'] = $this->data->post('name');
				$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
				$this->db->insertRow('wl_ntkd', $ntkd);
			}

			$data['alias'] = $this->makeLink($data['alias']);
			$alias = $data['alias'];
			if($position == 0)	$data['position'] = $this->db->getCount($this->table(), $_SESSION['alias']->id, 'wl_alias');
			else $data['position'] = $position;
			
			if($this->db->updateRow($this->table(), $data, $id)) return $id;
		}
		return false;
	}

	public function save($id, &$alias)
	{
		$group = $this->db->getAllDataById($this->table(), $id);
		if($group)
		{
			$data = array('active' => 1);
			if(isset($_POST['alias']) && $_POST['alias'] != '') {
				$data['alias'] = $this->data->post('alias');
			}
			if(isset($_POST['active']) && $_POST['active'] == 0) {
				$data['active'] = 0;
			}
			if(isset($_POST['parent']) && is_numeric($_POST['parent']) && $_POST['parent'] >= 0) {
				$data['parent'] = $_POST['parent'];
			}
			if (isset($_SESSION['admin_options']['groups:additional_fields']) && $_SESSION['admin_options']['groups:additional_fields'] != '')
			{
				$fields = explode(',', $_SESSION['admin_options']['groups:additional_fields']);
				foreach ($fields as $field) {
					$data[$field] = $this->data->post($field);
				}
			}
			if($group->parent != $data['parent']) {
				$this->changeParent($group->id, $group->parent, $data['parent']);
			}
			if($this->db->updateRow($this->table(), $data, $id))
			{
				$alias = $data['alias'];
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
						if($list[$g]->photo > 0){
							$path = IMG_PATH.$_SESSION['option']->folder.'/groups/';
							$path = substr($path, strlen(SITE_URL));

							$prefix = array('');
							$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');
							if($sizes){
								foreach ($sizes as $resize) if($resize->active == 1 && $resize->prefix != ''){
									$prefix[] = $resize->prefix .'_';
								}
							}
							
							foreach ($prefix as $p) {
								$filename = $path.$p.$list[$g]->photo.'.jpg';
								@unlink ($filename);
							}
						}
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

			$this->db->deleteRow($this->table(), $group->id);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > '{$group->position}'");
			$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '-{$group->id}'");
			if($group->photo > 0)
			{
				$path = IMG_PATH.$_SESSION['option']->folder.'/groups/';
				$path = substr($path, strlen(SITE_URL));

				$prefix = array('');
				$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');
				if($sizes){
					foreach ($sizes as $resize) if($resize->active == 1 && $resize->prefix != ''){
						$prefix[] = $resize->prefix .'_';
					}
				}
				
				foreach ($prefix as $p) {
					$filename = $path.$p.$group->photo.'.jpg';
					@unlink ($filename);
				}
			}

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

	public function makeParents($all, $parent, $parents)
	{
		$group = clone $all[$parent];
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = "-{$group->id}";
        if($_SESSION['language']) {
        	$where['language'] = $_SESSION['language'];
        }
        $this->db->select("wl_ntkd", 'name', $where);
        $ntkd = $this->db->get('single');
    	if($ntkd){
    		$group->name = $ntkd->name;
    	}
    	array_unshift ($parents, $group);
		if($all[$parent]->parent > 0) $parents = $this->makeParents ($all, $all[$parent]->parent, $parents);
		return $parents;
	}

	private function makeLink($link){
		$Group = $this->getByAlias($link);
		$end = 0;
		$link2 = $link;
		while ($Group) {
			$end++;
			$link2 = $link.'-'.$end;
		 	$Group = $this->getByAlias($link2);
		}
		return $link2;
	}

	private function getLink($all, $parent, $link)
	{
		$link = $all[$parent]->alias .'/'.$link;
		if($all[$parent]->parent > 0) $link = $this->getLink ($all, $all[$parent]->parent, $link);
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
		if($products) foreach ($products as $a) {
			$this->db->deleteRow($this->table('_products'), $a->id);
			$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$a->id}'");
			if($a->photo > 0){
				$path = IMG_PATH.$_SESSION['option']->folder.'/';
				if(strlen(IMG_PATH) > strlen(SITE_URL)) $path = substr($path, strlen(SITE_URL));

				$prefix = array('');
				$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');
				if($sizes){
					foreach ($sizes as $resize) if($resize->active == 1 && $resize->prefix != ''){
						$prefix[] = $resize->prefix .'_';
					}
				}
				
				foreach ($prefix as $p) {
					$filename = $path.$p.$a->photo.'.jpg';
					@unlink ($filename);
				}
			}
		}
		return true;
	}
	
}

?>