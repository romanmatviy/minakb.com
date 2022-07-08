<?php

class products_model {

	public $multigroup_new_position = array();

	public function table($sufix = '_products', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getProducts($Group = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($active)
			$where['active'] = 1;

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
				if($_SESSION['option']->ProductMultiGroup == 0)
					$where['group'] = $Group;
				else
				{
					$products = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $Group, 'group');
					if($products)
					{
						$where['id'] = array();
						foreach ($products as $product) {
							array_push($where['id'], $product->product);
						}
					}
					else
						return null;
				}
			}
		}

		$this->db->select($this->table().' as p', '*', $where);
		
		$this->db->join('wl_users as aa', 'name as author_add_name', '#p.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#p.author_edit');

		if($_SESSION['option']->useAvailability == 0)
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

						$this->db->select($this->table('_product_group') .' as pg', 'active', $product->id, 'product');
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
						$where_ntkd['content'] = "#-pg.group";
            			$this->db->join('wl_ntkd', 'name', $where_ntkd);
						$product->group = $this->db->get('array');

						if($product->group)
				            foreach ($product->group as $g) {
				            	if($g->parent > 0)
				            		$g->link = $this->makeLink($list, $g->parent, $g->alias);
				            	else
				            		$g->link = $g->alias;
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

		if($_SESSION['option']->useAvailability == 0)
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

        if($product = $this->db->get('single'))
        {
        	$product->options = $this->getOptions($product);
        	$product->link = $_SESSION['alias']->alias.'/'.$product->alias;

			$product->parents = array();
			if($_SESSION['option']->useGroups > 0)
			{
				$list = array();
				$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups)
	            	foreach ($all_groups as $g) {
		            	$list[$g->id] = clone $g;
		            }

				if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0)
				{
					$product->parents = $this->makeParents($list, $product->group, $product->parents);
					$link = $_SESSION['alias']->alias.'/';
					foreach ($product->parents as $parent) {
						$link .= $parent->alias .'/';
					}
					$product->link = $link . $product->alias;
				}
				elseif($_SESSION['option']->ProductMultiGroup == 1)
				{
					$product->group = array();

					$this->db->select($this->table('_product_group') .' as pg', '', $product->id, 'product');
					$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
					$where_ntkd['content'] = "#-pg.group";
        			$this->db->join('wl_ntkd', 'name', $where_ntkd);
					$product->group = $this->db->get('array');
					if($product->group)
			            foreach ($product->group as $g) {
			            	if($g->parent > 0)
			            		$g->link = $this->makeLink($list, $g->parent, $g->alias);
			            	else
			            		$g->link = $g->alias;
			            }
				}
			}
            return $product;
		}
		return null;
	}
	
	public function add(&$link = '')
	{
		$article = false;
		if($_SESSION['option']->ProductUseArticle && $_POST['article'] != '')
		{
			$check['wl_alias'] = $_SESSION['alias']->id;
			$check['article'] = $article = $this->prepareArticleKey($this->data->post('article'));
			$check = $this->db->getAllDataByFieldInArray($this->table(), $check);
			if($check)
			{
				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->errors = 'Артикул '.$_POST['article'].' вже використовується! <a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$check[0]->alias.'" target="_blank">Перевірте артикул!</a>';
				return false;
			}
		}

		$data = array();
		$data['wl_alias'] = $_SESSION['alias']->id;
		if(isset($_POST['article']))
		{
			$data['article'] = $article;
			$data['article_show'] = $this->data->post('article');
		}
		$data['active'] = $data['availability'] = 1;
		$data['price'] = $data['group'] = $data['position'] = 0;
		if(isset($_POST['active']) && is_numeric($_POST['active']))
			$data['active'] = $_POST['active'];
		if($availability = $this->data->post('availability'))
			$data['availability'] = $availability;
		if(isset($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0)
		{
			$data['price'] = $_POST['price'];
			if(!empty($_POST['currency']))
				$data['currency'] = trim($this->data->post('currency'));
		}
		$data['author_add'] = $data['author_edit'] = $_SESSION['user']->id;
		$data['date_add'] = $data['date_edit'] = time();

		if($id = $this->db->insertRow($this->table(), $data))
		{
			$data = array('alias' => '');

			$ntkd['alias'] = $_SESSION['alias']->id;
			$ntkd['content'] = $id;
			if($_SESSION['language'])
			{
				foreach ($_SESSION['all_languages'] as $lang) {
					$ntkd['language'] = $lang;
					$name = trim($this->data->post('name_'.$lang));
					$ntkd['name'] = $name;
					if($_SESSION['option']->ProductUseArticle > 0 && $this->data->post('article') != '')
						$ntkd['name'] =  $name.' '.trim($this->data->post('article'));
					if($lang == $_SESSION['language'])
						$data['alias'] = $this->data->latterUAtoEN($name);
					$this->db->insertRow('wl_ntkd', $ntkd);
				}
			}
			else
			{
				$name = trim($this->data->post('name'));
				$ntkd['name'] = $name;
				if($_SESSION['option']->ProductUseArticle > 0 && $this->data->post('article') != '')
					$ntkd['name'] = $name.' '.trim($this->data->post('article'));
				$data['alias'] = $this->data->latterUAtoEN($name);
				$this->db->insertRow('wl_ntkd', $ntkd);
			}
			
			if($_SESSION['option']->ProductUseArticle > 0 && $article)
				$data['alias'] = $this->ckeckAlias($article . '-' . $data['alias']);
			else
				$data['alias'] = $id . '-' . $data['alias'];
			$link = $data['alias'];
			$data['position'] = 0;
			
			if($_SESSION['option']->useGroups)
			{
				if($_SESSION['option']->ProductMultiGroup && !empty($_POST['product_groups']))
				{
					$product_groups = explode(',', $_POST['product_groups']);
					foreach ($product_groups as $group) {
						$all = 1 + $this->db->getCount($this->table('_product_group'), $group, 'group');
						$this->db->insertRow($this->table('_product_group'), array('product' => $id, 'group' => $group, 'position' => $all, 'active' => 1));
					}
				}
				else
				{
					if(isset($_POST['group']) && is_numeric($_POST['group']))
					{
						$data['group'] = $_POST['group'];
						$data['position'] = $this->db->getCount($this->table('_products'), array('wl_alias' => $_SESSION['alias']->id, 'group' => $data['group'])) + 1;

						if($data['group'] != 0)
						{
							$list = array();
				            $groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
				            foreach ($groups as $Group) {
				            	$list[$Group->id] = clone $Group;
				            }
				            $link = $this->makeLink($list, $data['group'], $data['alias']);
						}
					}
					else
						$data['position'] = $this->db->getCount($this->table('_products'), $_SESSION['alias']->id, 'wl_alias') + 1;
				}
			}
			else
				$data['position'] = $this->db->getCount($this->table('_products'), $_SESSION['alias']->id, 'wl_alias') + 1;

			$this->db->sitemap_add($id, $_SESSION['alias']->alias.'/'.$link);
			$this->db->updateRow($this->table('_products'), $data, $id);


			$options = array();
			foreach ($_POST as $key => $value) {
				if(empty($value))
					continue;
				$option = [];
				if(is_array($_POST[$key]))
					$option['value'] = implode(',', $value);
				else
					$option['value'] = $this->data->post($key);
				$key = explode('-', $key);
				if($key[0] == 'option' && isset($key[1]) && is_numeric($key[1]))
				{
					$option['option'] = $key[1];
					if($_SESSION['language'] && isset($key[2]) && in_array($key[2], $_SESSION['all_languages']))
						$option['language'] = $key[2];
					$options[] = $option;
				}
			}
			if(!empty($options))
				$this->db->insertRows($this->table('_product_options'), ['product' => $id, 'option', 'language' => '', 'value'], $options);

			return $id;
		}
		return false;
	}

	public function save($id)
	{
		$data = array('author_edit' => $_SESSION['user']->id, 'date_edit' => time());
		if(isset($_POST['id_1c']))
			$data['id_1c'] = $this->data->post('id_1c');
		if($_SESSION['option']->ProductUseArticle)
		{
			$data['alias'] = $id;
			$data['article_show'] = $this->data->post('article');
			$data['article'] = $this->prepareArticleKey($data['article_show']);

			$alias = trim($this->data->post('alias'));
			if(empty($data['article']) && !empty($alias))
				$data['alias'] = $id .'-'. $this->data->latterUAtoEN($alias);
			else
			{
				if(empty($_POST['alias']))
					$data['alias'] = $data['article'];
				else
					$data['alias'] = $data['article'] .'-'. $this->data->latterUAtoEN($alias);
			}
			if($data['article'] != $this->data->post('article_old'))
			{
				if($names = $this->db->getAllDataByFieldInArray('wl_ntkd', array('alias' => $_SESSION['alias']->id, 'content' => $id)))
				{
					foreach ($names as $row) {
						$name = mb_substr($row->name, 0, (mb_strlen($this->data->post('article_old'), 'utf-8') + 1) * -1, 'utf-8');
						$article_old = mb_substr($row->name, mb_strlen($this->data->post('article_old'), 'utf-8') * -1, NULL, 'utf-8');
						if($article_old == $this->data->post('article_old'))
						{
							$name .= ' '.$data['article'];
							$this->db->updateRow('wl_ntkd', array('name' => $name), $row->id);
						}
					}
				}
			}
		}
		else
		{
			if(empty($_POST['alias']))
				$data['alias'] = $id;
			else
				$data['alias'] = $id .'-'. $this->data->latterUAtoEN(trim($this->data->post('alias')));
		}
		$link = $data['alias'];
		if(isset($_POST['active']) && is_numeric($_POST['active']))
			$data['active'] = $_POST['active'];
		if(isset($_POST['availability']) && is_numeric($_POST['availability']))
			$data['availability'] = $_POST['availability'];
		if(!empty($_POST['currency']))
			$data['currency'] = trim($this->data->post('currency'));
		if(isset($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] >= 0)
			$data['price'] = $_POST['price'];
		if(isset($_POST['old_price']) && is_numeric($_POST['old_price']) && $_POST['old_price'] >= 0)
			$data['old_price'] = $_POST['old_price'];
		if($_SESSION['option']->useGroups)
		{
			if($_SESSION['option']->ProductMultiGroup)
			{
				$this->db->sitemap_update($id, 'link', $_SESSION['alias']->alias.'/'.$data['alias']);
				$use = $activegroups_position = array();
				$activegroups = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $id, 'product');
				if($activegroups)
				{
					$temp = array();
					foreach ($activegroups as $ac) {
						$temp[] = $ac->group;
						$activegroups_position[$ac->group] = new stdClass();
						$activegroups_position[$ac->group]->position = $ac->position;
						$activegroups_position[$ac->group]->active = $ac->active;
						$activegroups_position[$ac->group]->id = $ac->id;
						$this->db->html_cache_clear(-$ac->group);
					}
					$activegroups = $temp;
					$temp = null;
				}
				else
					$activegroups = array();
				$product_groups_new = explode(',', $_POST['product_groups']);
				if(!empty($product_groups_new))
				{
					foreach ($product_groups_new as $group) {
						if(!in_array($group, $activegroups))
						{
							$all = 1 + $this->db->getCount($this->table('_product_group'), $group, 'group');
							$this->db->insertRow($this->table('_product_group'), array('product' => $id, 'group' => $group, 'position' => $all, 'active' => 1));
						}
						else
						{
							if(isset($_POST['position-group-'.$group]) && isset($activegroups_position[$group]) && $activegroups_position[$group]->position != $_POST['position-group-'.$group] && $_POST['position-group-'.$group] > 0)
							{
								$pg_new = new stdClass();
								$pg_new->id = $activegroups_position[$group]->id;
								$pg_new->group = $group;
								$pg_new->position = $this->data->post('position-group-'.$group);
								$this->multigroup_new_position[] = $pg_new;
							}
							$active = 0;
							if(isset($_POST['active-group-'.$group]) && $_POST['active-group-'.$group] == 1)
								$active = 1;
							if(isset($activegroups_position[$group]) && $activegroups_position[$group]->active != $active)
								$this->db->updateRow($this->table('_product_group'), array('active' => $active), $activegroups_position[$group]->id);
						}
						$use[] = $group;
					}
				}
				if($activegroups)
					foreach ($activegroups as $ac) {
						if(!in_array($ac, $use))
						{
							$this->db->executeQuery("UPDATE `{$this->table('_product_group')}` SET `position` = `position` - 1 WHERE `position` > '{$activegroups_position[$ac]->position}' AND `group` = '{$ac}'");
							$this->db->executeQuery("DELETE FROM {$this->table('_product_group')} WHERE `product` = '{$id}' AND `group` = '{$ac}'");
						}
					}
			}
			elseif(isset($_POST['group']) && is_numeric($_POST['group']) && isset($_POST['group_old']))
			{
				if($_POST['group'] > 0)
				{
					$list = array();
		            $groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
		            foreach ($groups as $Group) {
		            	$list[$Group->id] = clone $Group;
		            }
		            $link = $this->makeLink($list, $_POST['group'], $data['alias']);
		            $up = $Group->id;
		            while ($up > 0) {
		            	$this->db->html_cache_clear(-$up);
		            	$up = $list[$up]->parent;
		            }
		        }

				if($_POST['group'] != $_POST['group_old'])
				{
					$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > '{$_POST['position_old']}' AND `group` = '{$_POST['group_old']}'");
					$data['group'] = $_POST['group'];
					$data['position'] = 1 + $this->db->getCount($this->table('_products'), array('wl_alias' => $_SESSION['alias']->id, 'group' => $data['group']));
					$this->db->sitemap_update($id, 'link', $_SESSION['alias']->alias.'/'.$link);
					$this->db->html_cache_clear(0);
				}
			}
		}
		else
			$this->db->sitemap_update($id, 'link', $_SESSION['alias']->alias.'/'.$link);

		if(empty($data['active']))
			$data['active'] = 1;
		$this->db->sitemap_index($id, $data['active']);
		$this->db->html_cache_clear($id);
		$this->db->updateRow($this->table(), $data, $id);

		if(!empty($_POST['name']))
		{
			$whereLang = ['alias' => $_SESSION['alias']->id, 'content' => $id];
			if($_SESSION['language'])
				$whereLang['language'] = $_SESSION['language'];
			$nlt = $this->data->prepare(['name', 'list', 'text']);
			if($_SESSION['option']->ProductUseArticle && !empty($data['article']))
				$nlt['name'] .= ' '.$data['article'];
			$this->db->updateRow('wl_ntkd', $nlt, $whereLang);
		}
		return $link;
	}

	public function delete($id)
	{
		if($product = $this->getById($id))
		{
			$this->db->sitemap_remove($product->id);
			$this->db->deleteRow($this->table(), $product->id);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > '{$product->position}' AND `group` = '{$product->group}'");
			$this->db->deleteRow($this->table('_product_options'), $product->id, 'product');
			$this->db->deleteRow($this->table('_products_similar'), $product->id, 'product');
			if($_SESSION['option']->searchHistory)
				$this->db->deleteRow($this->table('_search_history'), $product->id, 'product_id');
			$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$product->id}'");
			$this->db->executeQuery("DELETE FROM wl_audio WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$product->id}'");
			$this->db->executeQuery("DELETE FROM wl_images WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$product->id}'");
			$this->db->executeQuery("DELETE FROM wl_video WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$product->id}'");
			
			$path = IMG_PATH.$_SESSION['option']->folder.'/'.$product->id;
			$path = substr($path, strlen(SITE_URL));
			$this->data->removeDirectory($path);

			$link = '';
			if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 0)
			{
				$product->link = explode('/', $product->link);
				array_pop ($product->link);
				$link = '/'.implode('/', $product->link);
			}
			return $link;
		}
	}

	public function saveProductOptios($id, $chekAll = true)
	{
		$options = array();
		foreach ($_POST as $key => $value) {
			if(empty($value))
				continue;
			$is_array = is_array($_POST[$key]) ? true : false;
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
		$list_temp = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $id, 'product');
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
								$this->db->updateRow($this->table('_product_options'), array('value' => $value2), $list[$key][$lang]->id);
							unset($list[$key]);
						}
						elseif(isset($list[$key]) && is_object($list[$key]))
						{
							if($list[$key]->value != $value2)
								$this->db->updateRow($this->table('_product_options'), array('value' => $value2), $list[$key]->id);
							unset($list[$key]);
						}
						else
						{
							$data['product'] = $id;
							$data['option'] = $key;
							$data['language'] = $lang;
							$data['value'] = $value2;
							$this->db->insertRow($this->table('_product_options'), $data);
						}
					}
				}
				else
				{
					if(isset($list[$key]) && is_object($list[$key]))
					{
						if($list[$key]->value != $value)
							$this->db->updateRow($this->table('_product_options'), array('value' => $value), $list[$key]->id);
					}
					else
					{
						$data['product'] = $id;
						$data['option'] = $key;
						$data['value'] = $value;
						$this->db->insertRow($this->table('_product_options'), $data);
					}
					unset($list[$key]);
				}
			}
		}
		if(!empty($list) && $chekAll)
		{
			foreach ($list as $option) {
				if(is_object($option))
					$this->db->deleteRow($this->table('_product_options'), $option->id);
			}
		}
		return true;
	}

	public function saveChangePrice($id)
	{
		$data = $options = array();
		$data['price'] = (float) $this->data->post('price');
		$data['old_price'] = (float) $this->data->post('old_price');
		$data['currency'] = trim($this->data->post('currency'));
		$this->db->updateRow($this->table(), $data, $id);

		foreach ($_POST as $key => $action) {
			$key = explode('-', $key);
			if($key[0] == 'changePrice' && $key[1] == 'action' && is_numeric($key[2]))
			{
				$key = $key[2];
				if($option = $this->data->post('changePrice-option-'.$key))
				{
					if(!isset($options[$option]))
						$options[$option] = array();
					if($action == '1')
					{
						if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']))
						{
							$changePrice = array();
							$changePrice['action'] = $action;
							$changePrice['value'] = $this->data->post('changePrice-value-'.$key);
							$changePrice['currency'] = $this->data->post('changePrice-currency-'.$key);
							$options[$option][$key] = $changePrice;
						}
						else
		    				$options[$option][$key] = $this->data->post('changePrice-value-'.$key);
					}
		    		else if (is_numeric($action)) {
		    			$options[$option][$key] = $action;
		    		}
					else
					{
						$changePrice = array();
						$changePrice['action'] = $action;
						$changePrice['value'] = $this->data->post('changePrice-value-'.$key);
						$changePrice['currency'] = $this->data->post('changePrice-currency-'.$key);
						$options[$option][$key] = $changePrice;
					}
				}
			}
		}
		if(!empty($options))
			foreach ($options as $key => $value) {
				$changePrice = serialize($value);
				if($option = $this->db->getAllDataById($this->table('_product_options'), array('product' => $id, 'option' => $key)))
				{
					if($option->changePrice != $changePrice)
						$this->db->updateRow($this->table('_product_options'), array('changePrice' => $changePrice), $option->id);
				}
				else
					$this->db->insertRow($this->table('_product_options'), array('product' => $id, 'option' => $key, 'changePrice' => $changePrice));
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
    	if($ntkd)
    		$group->name = $ntkd->name;
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

	private function getOptions($product)
	{
		$product_options = array();
		$where_language = '';
        if($_SESSION['language']) $where_language = "AND (po.language = '{$_SESSION['language']}' OR po.language = '')";
		$this->db->executeQuery("SELECT go.id, go.alias, go.filter, po.value, it.name as type_name, it.options FROM `{$this->table('_product_options')}` as po LEFT JOIN `{$this->table('_options')}` as go ON go.id = po.option LEFT JOIN `wl_input_types` as it ON it.id = go.type WHERE go.active = 1 AND po.product = '{$product->id}' {$where_language} ORDER BY go.position");
		if($this->db->numRows() > 0)
		{
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

	private function ckeckAlias($link)
	{
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

	public function prepareArticleKey($text)
	{
		$text = (string) $text;
		$text = trim($text);
		$text = mb_strtolower($text, "utf-8");
        $ua = array('-', '_', ' ', '`', '~', '!', '@', '#', '$', '%', '^', '&', '"', ',', '\.', '\?', '/', ';', ':', '\'', '[+]', '“', '”');
        $en = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        for ($i = 0; $i < count($ua); $i++) {
            $text = mb_eregi_replace($ua[$i], $en[$i], $text);
        }
        $text = mb_eregi_replace("[-]{2,}", '-', $text);
        return $text;
	}
	
}

?>