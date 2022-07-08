<?php

class products_model {

	public function table($sufix = '_products', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getProducts($Group = 0, $active = true)
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
					$products = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $g->id, 'group');
					if($products) {
						foreach ($products as $product) {
							array_push($where['id'], $product->product);
						}
					}
				}
			}
			elseif($Group > 0)
			{
				if($_SESSION['option']->ProductMultiGroup == 0) {
					$where['group'] = $Group;
				} else {
					$products = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $Group, 'group');
					if($products) {
						$where['id'] = array();
						foreach ($products as $product) {
							array_push($where['id'], $product->product);
						}
					} else {
						return null;
					}
				}
			}
		}

		$this->db->select($this->table().' as p', '*', $where);
		
		$this->db->join($this->table('_manufactures'), 'name as manufacturer_name', '#p.manufacturer');
		
		$this->db->join('wl_users as aa', 'name as author_add_name', '#p.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#p.author_edit');

		if($_SESSION['option']->useAvailability > 0)
		{
			$this->db->join($_SESSION['service']->table.'_availability', 'color as availability_color', '#p.availability');
			
			$where_availability_name['availability'] = '#p.availability';
			if($_SESSION['language']) $where_availability_name['language'] = $_SESSION['language'];
			$this->db->join($_SESSION['service']->table.'_availability_name', 'name as availability_name', $where_availability_name);
		}

		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0)
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

		$this->db->order($_SESSION['option']->productOrder);

		$products = $this->db->get('array');
        if($products)
        {
        	$list = array();
        	if($_SESSION['option']->useGroups > 0)
        	{
	            $all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups) foreach ($all_groups as $g) {
	            	$list[$g->id] = clone $g;
	            }
	        }

            foreach ($products as $product)
            {
            	$product->photos = $this->getPhotos($product->id);
            	$product->options = $this->getOptions($product);
            	$product->link = $product->alias;

				$product->parents = array();
				if($_SESSION['option']->useGroups > 0)
				{
					if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0){
						$product->parents = $this->makeParents($list, $product->group, $product->parents);
						$link = '';
						foreach ($product->parents as $parent) {
							$link .= $parent->alias .'/';
						}
						$product->link = $link . $product->alias;
					} elseif($_SESSION['option']->ProductMultiGroup == 1){
						$product->group = array();

						$this->db->select($this->table('_product_group') .' as pg', '', $product->id, 'product');
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
						$where_ntkd['content'] = "#-pg.group";
            			$this->db->join('wl_ntkd', 'name', $where_ntkd);
						$product->group = $this->db->get('array');

			            foreach ($product->group as $g) {
			            	if($g->parent > 0) {
			            		$g->link = $this->makeLink($list, $g->parent, $g->alias);
			            	}
			            }
					}
				}
            }

			return $products;
		}
		return null;
	}
	
	public function getById($id)
	{
		$this->db->select($this->table().' as p', '*', array('wl_alias' => $_SESSION['alias']->id, 'id' => $id));

		$this->db->join('wl_users as aa', 'name as author_add_name', '#p.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#p.author_edit');

		if($_SESSION['option']->useAvailability)
		{
			$this->db->join($_SESSION['service']->table.'_availability', 'color as availability_color', '#p.availability');
			
			$where_availability_name['availability'] = '#p.availability';
			if($_SESSION['language']) $where_availability_name['language'] = $_SESSION['language'];
			$this->db->join($_SESSION['service']->table.'_availability_name', 'name as availability_name', $where_availability_name);
		}

		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0)
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

		$product = $this->db->get('single');
        if($product)
        {
        	if($product->photo != '')
        	{
				$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');
				if($sizes){
					foreach ($sizes as $resize) if($resize->active == 1){
						$resize_name = $resize->prefix.'_photo';
						$product->$resize_name = $_SESSION['option']->folder.'/'.$product->id.'/'.$resize->prefix.'_'.$product->photo;
					}
				}
				$product->photo = $_SESSION['option']->folder.'/'.$product->id.'/'.$product->photo;
        	}
			$product->photos = $this->getPhotos($product->id);
        	$product->options = $this->getOptions($product);
        	$product->link = $_SESSION['alias']->alias.'/'.$product->alias;

			$product->parents = array();
			if($_SESSION['option']->useGroups > 0)
			{
				$list = array();
				$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups) foreach ($all_groups as $g) {
	            	$list[$g->id] = clone $g;
	            }

				if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0){
					$product->parents = $this->makeParents($list, $product->group, $product->parents);
					$link = '';
					foreach ($product->parents as $parent) {
						$link .= $parent->alias .'/';
					}
					$product->link = $link . $product->alias;
				} elseif($_SESSION['option']->ProductMultiGroup == 1){
					$product->group = array();

					$this->db->select($this->table('_product_group') .' as pg', '', $product->id, 'product');
					$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
					$where_ntkd['content'] = "#-pg.group";
        			$this->db->join('wl_ntkd', 'name', $where_ntkd);
					$product->group = $this->db->get('array');

		            foreach ($product->group as $g) {
		            	if($g->parent > 0) {
		            		$g->link = $this->makeLink($list, $g->parent, $g->alias);
		            	}
		            }
				}
			}
            return $product;
		}
		return null;
	}
	
	public function add()
	{
		$manufacturer = $this->db->getAllDataById($this->table('_manufactures'), $this->data->post('manufacturer'));

		$check['wl_alias'] = $_SESSION['alias']->id;
		$check['article'] = trim($this->data->post('article'));
		$check['manufacturer'] = $this->data->post('manufacturer');
		$check = $this->db->getAllDataByFieldInArray($this->table(), $check);
		if($check)
		{
			
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->errors = 'Товар <a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$check[0]->alias.'" target="_blank"><strong>'.$manufacturer->name.' '.$check[0]->article.'</strong> '.$check[0]->name.'</a> вже існує! Перевірте артикул/виробник!';
			$_SESSION['post'] = $_POST;
			return false;
		}

		$data = array();
		$data['wl_alias'] = $_SESSION['alias']->id;
		$data['article'] = trim($this->data->post('article'));
		$data['name'] = trim($this->data->post('name'));
		$data['manufacturer'] = $this->data->post('manufacturer');
		$data['orign'] = $this->data->post('orign');
		$data['alias'] = $this->data->latterUAtoEN($manufacturer->name.'-'.$data['article'].'-'.$data['name']);
		$data['text'] = $data['analogs'] = '';
		$data['active'] = 1;
		$data['price'] = $data['group'] = 0;
		if(isset($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0)
			$data['price'] = $_POST['price'];
		if(isset($_POST['group']) && is_numeric($_POST['group']))
			$data['group'] = $_POST['group'];
		$data['author_add'] = $_SESSION['user']->id;
		$data['date_add'] = time();
		$data['author_edit'] = $_SESSION['user']->id;
		$data['date_edit'] = time();

		if($this->db->insertRow($this->table(), $data))
		{
			$id = $this->db->getLastInsertedId();
			
			if($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup && isset($_POST['group']) && is_array($_POST['group']))
			{
				foreach ($_POST['group'] as $group) {
					$this->db->insertRow($this->table('_product_group'), array('product' => $id, 'group' => $group));
				}
			}

			return $data['alias'];
		}
		return false;
	}

	public function save($id)
	{
		$data = array('active' => 1, 'orign' => 1, 'author_edit' => $_SESSION['user']->id, 'date_edit' => time());

		$data['article'] = trim($this->data->post('article'));
		$data['name'] = trim($this->data->post('name'));
		$data['manufacturer'] = $this->data->post('manufacturer');

		$manufacturer = $this->db->getAllDataById($this->table('_manufactures'), $this->data->post('manufacturer'));
		$data['alias'] = $this->data->latterUAtoEN($manufacturer->name.'-'.$data['article'] .'-'. trim($this->data->post('alias')));

		if(isset($_POST['active']) && $_POST['active'] == 0) $data['active'] = 0;
		if(isset($_POST['orign']) && $_POST['orign'] == 0) $data['orign'] = 0;
		if(isset($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] >= 0) $data['price'] = $_POST['price'];
		if($_SESSION['option']->useGroups)
		{
			if($_SESSION['option']->ProductMultiGroup)
			{
				$use = array();
				$activegroups = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $id, 'product');
				if($activegroups) {
					$temp = array();
					foreach ($activegroups as $ac) {
						$temp[] = $ac->group;
					}
					$activegroups = $temp;
					$temp = null;
				} else $activegroups = array();
				if(isset($_POST['group']) && is_array($_POST['group'])){
					foreach ($_POST['group'] as $group) {
						if(!in_array($group, $activegroups)){
							$this->db->insertRow($this->table('_product_group'), array('product' => $id, 'group' => $group));
						}
						$use[] = $group;
					}
				}
				if($activegroups) {
					foreach ($activegroups as $ac) {
						if(!in_array($ac, $use)){
							$this->db->executeQuery("DELETE FROM {$this->table('_product_group')} WHERE `product` = '{$id}' AND `group` = '{$ac}'");
						}
					}
				}
			} else {
				if(isset($_POST['group']) && is_numeric($_POST['group'])) $data['group'] = $_POST['group'];
			}
		}
		
		$this->db->updateRow($this->table(), $data, $id);
		return $data['alias'];
	}

	public function delete($id)
	{
		$product = $this->getById($id);
		if($product)
		{
			$this->db->deleteRow($this->table(), $product->id);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `id` > '{$product->id}'");
			$this->db->executeQuery("DELETE FROM `wl_ntkd` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '{$product->id}'");
			$this->db->executeQuery("DELETE FROM `{$this->table('_product_photos')}` WHERE `product` = '{$product->id}'");
			
			$path = IMG_PATH.$_SESSION['option']->folder.'/'.$product->id;
			$path = substr($path, strlen(SITE_URL));
			$this->data->removeDirectory($path);

			$link = '';
			if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 0){
				$product->link = explode('/', $product->link);
				array_pop ($product->link);
				$link = '/'.implode('/', $product->link);
			}
			return $link;
		}
	}

	public function saveProductOptios($id)
	{
		$options = array();
		foreach ($_POST as $key => $value) {
			$key = explode('-', $key);
			if($key[0] == 'option' && isset($key[1]) && is_numeric($key[1])){
				if(is_array($value)){
					$options[$key[1]] = implode(',', $value);
				} else {
					if($_SESSION['language'] && isset($key[2]) && in_array($key[2], $_SESSION['all_languages'])){
						$options[$key[1]][$key[2]] = $value;
					} else {
						$options[$key[1]] = $value;
					}
				}
			}
		}
		if(!empty($options)){
			$list_temp = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $id, 'product');
			$list = array();
			if($list_temp) {
				foreach ($list_temp as $option) {
					if($_SESSION['language'] && $option->language != ''){
						$list[$option->option][$option->language] = $option;
					} else {
						$list[$option->option] = $option;
					}
				}
			}
			foreach ($options as $key => $value) {
				if(is_array($value)){
					foreach ($value as $lang => $value2) {
						if(isset($list[$key][$lang])){
							if($list[$key][$lang]->value != $value2){
								$this->db->updateRow($this->table('_product_options'), array('value' => $value2), $list[$key][$lang]->id);
							}
						} else {
							$data['product'] = $id;
							$data['option'] = $key;
							$data['language'] = $lang;
							$data['value'] = $value2;
							$this->db->insertRow($this->table('_product_options'), $data);
						}
					}
				} else {
					if(isset($list[$key])){
						if($list[$key]->value != $value){
							$this->db->updateRow($this->table('_product_options'), array('value' => $value), $list[$key]->id);
						}
					} else {
						$data['product'] = $id;
						$data['option'] = $key;
						$data['value'] = $value;
						$this->db->insertRow($this->table('_product_options'), $data);
					}
				}
			}
		}
		return true;
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

	public function getPhotos($product)
	{
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = $product;
		$this->db->select('wl_images', '*', $where);
		$this->db->join('wl_users', 'name as user_name', '#author');
		return $this->db->get('array');
	}

	private function getOptions($product){
		$product_options = array();
		$where_language = '';
        if($_SESSION['language']) $where_language = "AND (po.language = '{$_SESSION['language']}' OR po.language = '')";
		$this->db->executeQuery("SELECT go.id, go.alias, go.filter, po.value, it.name as type_name, it.options FROM `{$this->table('_product_options')}` as po LEFT JOIN `{$this->table('_options')}` as go ON go.id = po.option LEFT JOIN `wl_input_types` as it ON it.id = go.type WHERE go.active = 1 AND po.product = '{$product->id}' {$where_language} ORDER BY go.position");
		if($this->db->numRows() > 0){
			$options = $this->db->getRows('array');
			foreach ($options as $option) if($option->value != '') {
				@$product_options[$option->id]->id = $option->id;
				$product_options[$option->id]->alias = $option->alias;
				$product_options[$option->id]->filter = $option->filter;
				$where = array();
				$where['option'] = $option->id;
				if($_SESSION['language']) $where['language'] = $_SESSION['language'];
				$name = $this->db->getAllDataById($this->table('_options_name'), $where);

				if($name){
					$product_options[$option->id]->name = $name->name;
					$product_options[$option->id]->sufix = $name->sufix;
				}
				if($option->options == 1){
					if($option->type_name == 'checkbox'){
						$option->value = explode(',', $option->value);
						$product_options[$option->id]->value = array();
						foreach ($option->value as $value) {
							$where = array();
							$where['option'] = $value;
							if($_SESSION['language']) $where['language'] = $_SESSION['language'];
							$value = $this->db->getAllDataById($this->table('_options_name'), $where);
							if($value){
								$product_options[$option->id]->value[] = $value->name;
							}
						}
					} else {
						$where = array();
						$where['option'] = $option->value;
						if($_SESSION['language']) $where['language'] = $_SESSION['language'];
						$value = $this->db->getAllDataById($this->table('_options_name'), $where);
						if($value){
							$product_options[$option->id]->value = $value->name;
						}
					}
				} else {
					$product_options[$option->id]->value = $option->value;
				}
			}
		}
		return $product_options;
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