<?php

class shop_model {

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function routeURL($url = array(), &$type = null, $admin = false)
	{
		if($product = $this->getProduct(end($url)))
		{
			$url = implode('/', $url);
			if($url != $product->link)
			{
				$link = SITE_URL;
				if($admin) $link .= 'admin/';
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: '. $link. $product->link);
				exit();
			}

			$type = 'product';
			return $product;
		}

		if($_SESSION['option']->useGroups)
		{
			$group = false;
			$parent = 0;
			array_shift($url);
			foreach ($url as $uri) {
				$group = $this->getGroupByAlias($uri, $parent);
				if($group){
					$parent = $group->id;
				} else $group = false;
			}

			$type = 'group';
			return $group;
		}

		return false;
	}
	
	public function getProducts($Group = 0, $noInclude = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($active)
			$where['active'] = 1;

		if(is_string($Group) && $Group[0] == '%')
		{
			$where['article'] = $Group;
		}
		elseif(is_string($Group) && $Group[0] == '#')
		{
			$where['article'] = substr($Group, 1);
		}
		elseif($_SESSION['option']->useGroups > 0)
		{
			if(is_array($Group) && !empty($Group))
			{
				$where['id'] = array();
				foreach ($Group as $g) {
					$products = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $g->id, 'group');
					if($products) {
						foreach ($products as $product) if($product->product != $noInclude) {
							array_push($where['id'], $product->product);
						}
					}
				}
			}
			elseif($Group >= 0)
			{
				if($_SESSION['option']->ProductMultiGroup == 0 || $Group == 0) {
					$where['group'] = $Group;
				} else {
					$products = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $Group, 'group');
					if($products) {
						$where['id'] = array();
						foreach ($products as $product) if($product->product != $noInclude) {
							array_push($where['id'], $product->product);
						}
					} else {
						return null;
					}
				}
			}
			elseif($noInclude > 0)
			{
				$where['id'] = '!'.$noInclude;
			}
		}
		elseif($noInclude > 0)
		{
			$where['id'] = '!'.$noInclude;
		}
		
		if(count($_GET) > 1)
		{
			foreach ($_GET as $key => $value) {
				if($key != 'request' && $key != 'page' && is_array($_GET[$key]))
				{
					$option = $this->db->getAllDataById($this->table('_options'), array('wl_alias' => $_SESSION['alias']->id, 'alias' => $key, 'filter' => 1));
					if($option)
					{
						$list_where['option'] = $option->id;
						if(!empty($where['id'])) $list_where['product'] = clone $where['id'];
						$where['id'] = array();
						foreach ($_GET[$key] as $value) if(is_numeric($value)) {
							if($option->type == 8) //checkbox
							{
								$list_where['value'] = '%'.$value;
								$list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $list_where);
								if($list){
									foreach ($list as $p) {
										$p->value = explode(',', $p->value);
										if(in_array($value, $p->value)) array_push($where['id'], $p->product);
									}
								}
							} else {
								$list_where['value'] = $value;
								$list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $list_where);
								if($list){
									foreach ($list as $p) {
										array_push($where['id'], $p->product);
									}
								}
							}
						}

						if(empty($where['id']))
						{
							return false;
						}
					}
				}
				if(isset($_GET['name']) && $_GET['name'] != '')
					$where['name'] = '%'.$this->data->get('name');
			}
		}
		
		$this->db->select($this->table('_products').' as p', '*', $where);
		
		$this->db->join($this->table('_manufactures'), 'name as manufacturer_name', '#p.manufacturer');
		
		$this->db->join('wl_users', 'name as user_name', '#p.author_edit');

		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0)
		{
			$where_gn['alias'] = $_SESSION['alias']->id;
			$where_gn['content'] = "#-p.group";
			if($_SESSION['language']) $where_gn['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd as gn', 'name as group_name', $where_gn);
		}

		if(isset($_GET['sort']))
		{
			switch ($this->data->get('sort')) {
				case 'price_up':
					$this->db->order('price DESC');
					break;
				case 'price_down':
					$this->db->order('price ASC');
					break;
				case 'article':
					$this->db->order('article ASC');
					break;
			}
		}

		if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
		{
			$start = 0;
			if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0) {
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			}
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1) {
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			}
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}

		$products = $this->db->get('array', false);
        if($products)
        {
			$_SESSION['option']->paginator_total = $this->db->get('count');

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
            	$product->link = $_SESSION['alias']->alias.'/'.$product->alias;
            	$product->options = $this->getProductOptions($product);

				$product->parents = array();
				if($_SESSION['option']->useGroups > 0)
				{
					if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0)
					{
						$product->parents = $this->makeParents($list, $product->group, $product->parents);
						$link = '/';
						foreach ($product->parents as $parent) {
							$link .= $parent->alias .'/';
						}
						$product->group_link = $_SESSION['alias']->alias . $link;
						$product->link = $_SESSION['alias']->alias . $link . $product->alias;
					} elseif($_SESSION['option']->ProductMultiGroup == 1)
					{
						$product->group = array();

						$this->db->select($this->table('_product_group') .' as pg', '', $product->id, 'product');
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
						$where_ntkd['content'] = "#-pg.group";
            			$this->db->join('wl_ntkd', 'name', $where_ntkd);
						$product->group = $this->db->get('array');

			            foreach ($product->group as $g) {
			            	if($g->parent > 0) {
			            		$g->link = $_SESSION['alias']->alias . '/' . $this->makeLink($list, $g->parent, $g->alias);
			            	}
			            }
					}
				}
            }

			return $products;
		}
		$this->db->clear();
		return null;
	}
	
	function getProduct($alias, $key = 'alias', $all_info = true)
	{
		$this->db->select($this->table('_products').' as p', '*', array('wl_alias' => $_SESSION['alias']->id, $key => $alias));

		$this->db->join($this->table('_manufactures'), 'name as manufacturer_name', '#p.manufacturer');

		if($all_info)
		{
			$this->db->join('wl_users', 'name as user_name', '#p.author_edit');

			if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0)
			{
				$where_gn['alias'] = $_SESSION['alias']->id;
				$where_gn['content'] = "#-p.group";
				if($_SESSION['language']) $where_gn['language'] = $_SESSION['language'];
				$this->db->join('wl_ntkd as gn', 'name as group_name', $where_gn);
			}
		}

		$product = $this->db->get('single');
        if($product)
        {
        	if(isset($_SESSION['alias']->breadcrumbs))
        	{
        		$_SESSION['alias']->breadcrumbs = array($_SESSION['alias']->name => $_SESSION['alias']->alias);
        	}
        	$product->link = $_SESSION['alias']->alias.'/'.$product->alias;

        	if($all_info)
        		$product->options = $this->getProductOptions($product);

			$product->parents = array();
			if($_SESSION['option']->useGroups > 0)
			{
				$list = array();
				$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups) foreach ($all_groups as $g) {
	            	$list[$g->id] = clone $g;
	            }

				if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0)
				{
					$product->parents = $this->makeParents($list, $product->group, $product->parents);
					$link = $_SESSION['alias']->alias . '/';
					foreach ($product->parents as $parent) {
						$link .= $parent->alias .'/';
						if(isset($_SESSION['alias']->breadcrumbs)) $_SESSION['alias']->breadcrumbs[$parent->name] = $link;
					}
					$product->group_link = $link;
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

		            foreach ($product->group as $g) {
		            	if($g->parent > 0) {
		            		$g->link = $_SESSION['alias']->alias . '/' . $this->makeLink($list, $g->parent, $g->alias);
		            	}
		            }
				}
			}
			if($all_info)
        	{
        		$name = $product->article  .' - '. $product->name;
        		if(isset($_SESSION['alias']->breadcrumbs)) $_SESSION['alias']->breadcrumbs[$name] = '';
        	}
            return $product;
		}
		return null;
	}

	private function getProductOptions($product)
	{
		$product_options = array();
		$where_language = '';
        if($_SESSION['language']) $where_language = "AND (po.language = '{$_SESSION['language']}' OR po.language = '')";
		$this->db->executeQuery("SELECT go.id, go.alias, go.filter, po.value, it.name as type_name, it.options FROM `{$this->table('_product_options')}` as po LEFT JOIN `{$this->table('_options')}` as go ON go.id = po.option LEFT JOIN `wl_input_types` as it ON it.id = go.type WHERE go.active = 1 AND po.product = '{$product->id}' {$where_language} ORDER BY go.position");
		if($this->db->numRows() > 0){
			$options = $this->db->getRows('array');
			foreach ($options as $option) if($option->value != '') {
				@$product_options[$option->alias]->id = $option->id;
				$product_options[$option->alias]->alias = $option->alias;
				$product_options[$option->alias]->filter = $option->filter;
				$where = array();
				$where['option'] = $option->id;
				if($_SESSION['language']) $where['language'] = $_SESSION['language'];
				$name = $this->db->getAllDataById($this->table('_options_name'), $where);

				if($name){
					$product_options[$option->alias]->name = $name->name;
					$product_options[$option->alias]->sufix = $name->sufix;
				}
				if($option->options == 1){
					if($option->type_name == 'checkbox'){
						$option->value = explode(',', $option->value);
						$product_options[$option->alias]->value = array();
						foreach ($option->value as $value) {
							$where = array();
							$where['option'] = $value;
							if($_SESSION['language']) $where['language'] = $_SESSION['language'];
							$value = $this->db->getAllDataById($this->table('_options_name'), $where);
							if($value){
								$product_options[$option->alias]->value[] = $value->name;
							}
						}
					} else {
						$where = array();
						$where['option'] = $option->value;
						if($_SESSION['language']) $where['language'] = $_SESSION['language'];
						$value = $this->db->getAllDataById($this->table('_options_name'), $where);
						if($value){
							$product_options[$option->alias]->value = $value->name;
						}
					}
				} else {
					$product_options[$option->alias]->value = $option->value;
				}
			}
		}
		return $product_options;
	}

	public function getGroups($parent = 0)
	{
		$where['wl_alias'] = $_SESSION['alias']->id;
		$where['active'] = 1;
		if($parent >= 0) $where['parent'] = $parent;
		$this->db->select($this->table('_groups') .' as g', '*', $where);

		$this->db->join('wl_users', 'name as user_name', '#g.author_edit');

		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = "#-g.id";
		if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', "name, text, list", $where_ntkd);

		$this->db->order($_SESSION['option']->groupOrder);
		
		if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0 && $parent >= 0){
			$start = 0;
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1){
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			}
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}
		
		$categories = $this->db->get('array', false);
		if($categories)
		{
			@$_SESSION['option']->count_all_products = $this->db->get('count');

            $list = array();
            $groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
            foreach ($groups as $Group) {
            	$list[$Group->id] = clone $Group;
            }

			$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');

            foreach ($categories as $Group) {
            	$Group->link = $_SESSION['alias']->alias.'/'.$Group->alias;
            	if($Group->parent > 0) {
            		$Group->link = $_SESSION['alias']->alias.'/'.$this->makeLink($list, $Group->parent, $Group->alias);
            	}

            	if($Group->photo != '')
            	{
					if($sizes){
						foreach ($sizes as $resize) if($resize->active == 1){
							$resize_name = $resize->prefix.'_photo';
							$Group->$resize_name = $_SESSION['option']->folder.'/groups/'.$resize->prefix.'_'.$Group->photo;
						}
					}
					$Group->photo = $_SESSION['option']->folder.'/groups/'.$Group->photo;
            	}
            }

            return $categories;
		}
		else
		{
			$this->db->clear();
		}
		return null;
	}

	public function makeParents($all, $parent, $parents)
	{
		$group = clone $all[$parent];
		$where = '';
        if($_SESSION['language']) $where = "AND `language` = '{$_SESSION['language']}'";
        $this->db->executeQuery("SELECT `name` FROM `wl_ntkd` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '-{$group->id}' {$where}");
    	if($this->db->numRows() == 1){
    		$ntkd = $this->db->getRows();
    		$group->name = $ntkd->name;
    	}
    	array_unshift ($parents, $group);
		if($all[$parent]->parent > 0) $parents = $this->makeParents ($all, $all[$parent]->parent, $parents);
		return $parents;
	}

	public function getGroupByAlias($alias, $parent = 0)
	{
		$where['wl_alias'] = $_SESSION['alias']->id;
		$where['alias'] = $alias;
		$where['parent'] = $parent;
		$this->db->select($this->table('_groups') .' as c', '*', $where);
		$this->db->join('wl_users', 'name as user_name', '#c.author_edit');
		$group = $this->db->get('single');
		if($group && $group->photo > 0)
		{
			$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');
			if($sizes) {
				foreach ($sizes as $resize) if($resize->active == 1){
					$resize_name = $resize->prefix.'_photo';
					$group->$resize_name = $_SESSION['option']->folder.'/groups/'.$resize->prefix.'_'.$group->photo;
				}
			}
			$group->photo = $_SESSION['option']->folder.'/groups/'.$group->photo;
		}
		return $group;
	}

	public function getOptionsToGroup($group = 0, $filter = true)
	{
		$products = false;
		if($group === 0)
		{
			$where['group'] = 0;
			$group = new stdClass();
			$group->id = 0;
			$group->parent = 0;
		}
		elseif(is_numeric($group))
		{
			$group = $this->db->getAllDataById($this->table('_groups'), $group);
			if($group == false) return false;
		}

		if($_SESSION['option']->useGroups && $group->id > 0)
		{
			if($_SESSION['option']->ProductMultiGroup)
			{
				$products_id = $this->db->getAllDataByFieldInArray($this->table('_product_group'), $group->id, 'group');
				if($products_id)
				{
					foreach ($products_id as $product) {
						$products[] = $product->product;
					}
				}
			}
			else
			{
				$products_id = $this->db->getAllDataByFieldInArray($this->table('_products'), $group->id, 'group');
				if($products_id)
				{
					foreach ($products_id as $product) {
						$products[] = $product->id;
					}
				}
			}
		}

    	if($filter && ($group->id > 0 && $products || $group->id == 0) || !$filter)
    	{
    		$where['group'] = array(0);
			array_push($where['group'], $group->id);
			if($group->parent > 0)
			{
				array_push($where['group'], $group->id);
				while ($group->parent > 0) {
					$group = $this->db->getAllDataById($this->table('_groups'), $group->parent);
				}
			}
			$where['wl_alias'] = $_SESSION['alias']->id;
			$where['filter'] = 1;
			$where['active'] = 1;
			$this->db->select($this->table('_options').' as o', '*', $where);
			$this->db->join('wl_input_types', 'name as type_name', '#o.type');
			$where = array('option' => '#o.id');
	        if($_SESSION['language']) $where['language'] = $_SESSION['language'];
	        $this->db->join($this->table('_options_name'), 'name, sufix', $where);
	        $this->db->order('position');
			$options = $this->db->get('array');

			if($options)
			{
				$to_delete_options = array();
		        foreach ($options as $i => $option) {
		        	$this->db->select($this->table('_options').' as o', 'id', -$option->id, 'group');
		        	$this->db->join($this->table('_options_name'), 'name', $where);
		        	$option->values = $this->db->get('array');

					if(!empty($option->values))
		    		{
		    			$to_delete_values = array();
		    			$where = array();
		    			if($products) $where['product'] = $products;
		    			foreach ($option->values as $i => $value) {
		    				$where['option'] = $option->id;
		    				if($option->type_name == 'checkbox')
		    				{
		    					$count = 0;
								$where['value'] = '%'.$value->id;
			        			$list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $where);
			        			if($list)
			        			{
			        				foreach ($list as $key) {
			        					$key->value = explode(',', $key->value);
			        					if(in_array($value->id, $key->value)) $count++;
			        				}
			        			}
		    				}
		    				else
		    				{
		    					$where['value'] = $value->id;
		        				$count = $this->db->getCount($this->table('_product_options'), $where);
		    				}
		    				
		        			$value->count = $count;
		        			if(!$count && $filter)
		        			{
		        				$to_delete_values[] = $i;
		        			}
		        		}
		        		if(!empty($to_delete_values) && $filter)
		        		{
		        			rsort($to_delete_values);
		        			foreach ($to_delete_values as $i) {
		        				unset($option->values[$i]);
		        			}
		        		}
		    		}
		    		elseif($filter)
		    		{
		    			$to_delete_options[] = $i;
		    		}
		        }
		        if(!empty($to_delete_options))
        		{
        			rsort($to_delete_options);
        			foreach ($to_delete_options as $i) {
        				unset($options[$i]);
        			}
        		}		
			}

			return $options;
		}
		return false;
	}

	private function makeLink($all, $parent, $link)
	{
		$link = $all[$parent]->alias .'/'.$link;
		if($all[$parent]->parent > 0) $link = $this->makeLink ($all, $all[$parent]->parent, $link);
		return $link;
	}

	public function searchHistory($product_id, $product_article = NULL)
	{
		$data['user'] = $_SESSION['user']->id;
		$data['date'] = strtotime('today');

		if($product_id > 0)
			$data['product_id'] = $product_id;
		else
			$data['product_article'] = $product_article;

		$search = $this->db->getAllDataById($this->table('_search_history'), $data);
		if($search)
		{
			$this->db->updateRow($this->table('_search_history'), array('count_per_day' => $search->count_per_day + 1, 'last_view' => time()), $search->id);
			return true;
		}

		$data['product_id'] = $product_id;
		$data['product_article'] = $product_article;
		$data['last_view'] = time();
		$data['count_per_day'] = 1;
		$this->db->insertRow($this->table('_search_history'), $data);
	}

	public function getInvoices($id, $storages = array(), $user_type = 0)
    {
        if(empty($storages))
        	return false;

        if($user_type == 0)
        {
	        $user_type = (isset($_SESSION['user']->type)) ? $_SESSION['user']->type : 0;
	        if($user_type == 1)
	        	$user_type = 2;
	    }
        $where['storage'] = $storages;
        $where['product'] = $id;
        $this->db->select('s_shopstorage_products as s', '*', $where);
        $this->db->join('wl_ntkd', 'name as storage_name, list as storage_time', array('alias' => '#s.storage', 'content' => '0'));
        $this->db->join('s_shopstorage_markup', 'markup', array('storage' => '#s.storage', 'user_type' => $user_type));
        $this->db->order('price_in ASC');
        $invoises = $this->db->get('array');
        if($invoises)
        {
            $prices_out = array();
            foreach ($invoises as $invoise) {
            	if($user_type >= 0)
            	{
	            	if($invoise->price_out != 0)
	            	{
		                $price_out = unserialize($invoise->price_out);
		                if(isset($price_out[$user_type]))
		                    $invoise->price_out = $price_out[$user_type];
		                else
		                    $invoise->price_out = end($price_out);
		            }
		            else
		            {
		            	$invoise->price_out = $invoise->price_in;
		            	if($invoise->markup > 0)
							$invoise->price_out = round($invoise->price_in * ($invoise->markup + 100) / 100, 2);
		            }
		            $prices_out[] = $invoise->price_out;
		        }
		        else
		        	$prices_out[] = $invoise->price_in;
                $invoise->amount_free = $invoise->amount - $invoise->amount_reserved;
            }
            array_multisort($prices_out, $invoises);
        }
        return $invoises;
    }

    public function getManufactures($all_info = true)
    {
    	$where = array('wl_alias' => $_SESSION['alias']->id, 'main_id' => 0);
		$manufacturers = $this->db->getAllDataByFieldInArray($this->table('_manufactures'), $where);
		if($manufacturers && $all_info)
		{
			$where['main_id'] = '>0';
			if($childs = $this->db->getAllDataByFieldInArray($this->table('_manufactures'), $where, 'main_id'))
			{
				$id = 0;
				$add = array();
				foreach ($childs as $child) {
					if($id != $child->main_id && $id > 0)
					{
						foreach ($manufacturers as $manufacturer) {
							if($manufacturer->id == $id)
							{
								$manufacturer->name .= ' ('.implode(', ', $add).')';
								$add = array();
								break;
							}
						}
						$id = $child->main_id;
						$add[] = $child->name;
					}
					else
					{
						$id = $child->main_id;
						$add[] = $child->name;
					}
				}
			}
		}
		return $manufacturers;
    }

    public function getProductAnalogs($article='')
    {
    	$products = $this->db->getAllDataByFieldInArray($this->table('_products'), array('analogs' => '%'.$article));
    	if($products)
    	{
    		$analogs = array();
    		foreach ($products as $product) {
    			$analogs[] = $this->getProduct($product->id, 'id', false);
    		}
    		return $analogs;
    	}
    	return false;
    }

    public function makeArticle($article)
	{
		$article = (string) $article;
		$article = trim($article);
		$article = strtoupper($article);
		$article = str_replace('-', '', $article);
		return str_replace(' ', '', $article);
	}
	
}

?>