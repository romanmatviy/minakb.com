<?php

class shop_search_model
{

	public $allGroups = false;
	public $disabledGroups = [];

    public function init()
    {
		if($_SESSION['option']->useGroups && empty($this->allGroups))
		{
			$where = array();
			$where['wl_alias'] = $_SESSION['alias']->id;
			$this->db->select($this->table('_groups') .' as g', 'id, alias, parent, active', $where)
						->order('parent');
			if($list = $this->db->get('array'))
			{
				foreach ($list as $g) {
					if(!$g->active)
						$this->disabledGroups[] = $g->id;
					if(in_array($g->parent, $this->disabledGroups))
					{
						$this->disabledGroups[] = $g->id;
						$g->active = false;
					}
	            	$this->allGroups[$g->id] = clone $g;
	            }
	            unset($list);
	        }
		}
    }

	public function table($sufix = '')
	{
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getByContent($content, $admin = false)
	{
		$search = false;

		if($content > 0)
		{
			$this->db->select($this->table('_products').' as p', '*', $content);
			$this->db->join('wl_users', 'name as author_name', '#p.author_edit');
			$where = ['alias' => '#p.wl_alias', 'content' => '#p.id'];
			if($_SESSION['language'])
				$where['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd', 'name', $where);
			if($_SESSION['option']->useMarkUp > 0){
				$this->db->join($this->table('_markup'), 'value as markup', array('from' => '<p.price', 'to' => '>=p.price'));
			}
			$product = $this->db->get('single');
			if($product && ($product->active || $admin || $_SESSION['option']->ProductMultiGroup))
			{
				if($_SESSION['option']->ProductUseArticle && mb_strlen($product->name) > mb_strlen($product->article))
				{
					$name = explode(' ', $product->name);
					$last_name = array_pop($name);
					if($last_name == $product->article || $last_name == $product->article_show)
						$product->name = implode(' ', $name);
				}
				
				if($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup)
				{
					$where_product_group = array('product' => $product->id);
					if(!$admin)
						$where_product_group['active'] = 1;
					$this->db->select($this->table('_product_group') .' as pg', '', $where_product_group);
					$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
					$where_ntkd['alias'] = $_SESSION['alias']->id;
					$where_ntkd['content'] = "#-pg.group";
					if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
        			$this->db->join('wl_ntkd', 'name', $where_ntkd);
					$groups = $this->db->get('array');
					if(!$admin && empty($groups))
						return false;
				}
				else if(!empty($product->group) && !$admin)
					if(in_array($product->group, $this->disabledGroups))
						return false;

				if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && isset($_SESSION['currency'][$product->currency]))
				{
					$product->price *= $_SESSION['currency'][$product->currency];
					$product->old_price *= $_SESSION['currency'][$product->currency];
				}

				$search = new stdClass();
				$search->id = $product->id;
				$search->article = $product->article;
				$search->link = $_SESSION['alias']->alias.'/'.$product->alias;
				$search->date = $product->date_edit;
				$search->author = $product->author_edit;
				$search->author_name = $product->author_name;
				$search->additional = false;
				$search->price = $product->price;
				$search->old_price = $product->old_price;

				if($_SESSION['option']->useMarkUp > 0 && $product->markup){
	        		$search->price = $product->price * $product->markup;
	        		$search->old_price = $product->old_price * $product->markup;
	        	}

	        	$search->old_price = $search->price != $search->old_price ? ceil($search->old_price) : 0;
		        $search->price = ceil($search->price);

				$search->folder = false;
				if(isset($_SESSION['option']->folder))
					$search->folder = $_SESSION['option']->folder;

				if($_SESSION['option']->useGroups)
				{
					$search->additional = array();

					if($_SESSION['option']->useGroups && empty($this->allGroups))
        				$this->init();

					if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0)
					{
						$link = $_SESSION['alias']->alias .'/';
						if($parents = $this->makeParents($product->group, array()))
						{
							$where_ntkd['alias'] = $_SESSION['alias']->id;
							$where_ntkd['content'] = array();
							foreach ($parents as $parent)
								$where_ntkd['content'][] = -$parent->id;
							if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
							$this->db->select('wl_ntkd', 'name, content', $where_ntkd);
							$names = $this->db->get('array');
						
							foreach ($parents as $parent) {
								foreach ($names as $name) {
									if($name->content == -$parent->id)
									{
										$link .= $parent->alias .'/';
										$search->additional[$link] = $name->name;
										break;
									}
								}
							}
						}
						$search->link = $link . $product->alias;
					}
					elseif($_SESSION['option']->ProductMultiGroup == 1)
					{
						if($groups)
				            foreach ($groups as $g) {
			            		$link = $_SESSION['alias']->alias .'/';
			            		if($g->parent > 0)
			            			$link .= $this->makeLink($g->parent, $g->alias);
			            		else
			            			$link .= $g->alias;
			            		$search->additional[$link] = $g->name;
			            	}
					}
				}
				if($admin)
				{
					$search->edit_link = 'admin/'.$search->link;
				}
			}
		}
		elseif($content == 0)
		{
			$search = new stdClass();
			$search->id = $_SESSION['alias']->id;
			$search->link = $_SESSION['alias']->alias;
			$search->date = 0;
			$search->author = 1;
			$search->author_name = '';
			$search->additional = false;
			$search->folder = false;
			if(isset($_SESSION['option']->folder))
				$search->folder = $_SESSION['option']->folder;
			return $search;
		}
		else
		{
			$content *= -1;
			$this->db->select($this->table('_groups'), '*', $content);
			$this->db->join('wl_users', 'name as author_name', '#author_edit');
			$group = $this->db->get('single');
			if($group && ($group->active || $admin))
			{
				$search = new stdClass();
				$search->id = $group->id;
				$search->link = $_SESSION['alias']->alias.'/'.$group->alias;
				$search->date = $group->date_edit;
				$search->author = $group->author_edit;
				$search->author_name = $group->author_name;
				$search->additional = false;
				$search->folder = false;
				if(isset($_SESSION['option']->folder))
					$search->folder = $_SESSION['option']->folder;
				if($admin)
				{
					$search->edit_link = 'admin/'.$_SESSION['alias']->alias.'/groups/'.$group->id;
				}

				if($group->parent > 0)
				{
					$search->additional = array();

					if($_SESSION['option']->useGroups && empty($this->allGroups))
        				$this->init();

        			if(!$admin)
	        			if(in_array($group->parent, $this->disabledGroups))
							return false;

        			$link = $_SESSION['alias']->alias .'/';
					if($parents = $this->makeParents($group->parent, array()))
					{
						$where_ntkd['alias'] = $_SESSION['alias']->id;
						$where_ntkd['content'] = array();
						foreach ($parents as $parent)
							$where_ntkd['content'][] = -$parent->id;
						if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
						$this->db->select('wl_ntkd', 'name, content', $where_ntkd);
						$names = $this->db->get('array');
					
						foreach ($parents as $parent) {
							foreach ($names as $name) {
								if($name->content == -$parent->id)
								{
									$link .= $parent->alias .'/';
									$search->additional[$link] = $name->name;
									break;
								}
							}
						}
					}
					$search->link = $link . $group->alias;
				}
			}
		}

		return $search;
	}

	private function makeParents($parent, $parents)
	{
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

	public function getProducts_SiteMap()
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);

		if($_SESSION['option']->useGroups == 1)
		{
			if($_SESSION['option']->ProductMultiGroup == 0)
				$where['active'] = 1;
			else
			{

			}
		}
		else
			$where['active'] = 1;

		$this->db->select($this->table('_products'), 'id, alias, `group`', $where);
		if($products = $this->db->get('array'))
        {
        	if($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup == 0 && empty($this->allGroups))
        		$this->init();

            foreach ($products as $product)
            {
            	$product->skip = false;
            	$link = $_SESSION['alias']->alias.'/';
            	if($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0)
            	{
            		if(in_array($product->group, $this->disabledGroups))
            			$product->skip = true;
            		else
						foreach ($this->makeParents($product->group, array()) as $parent) {
							$link .= $parent->alias .'/';
						}
				}
            	$product->link = $link.$product->alias;
            }
		}
		return $products;
	}

	public function getGroups_SiteMap()
	{
		if($_SESSION['option']->useGroups && empty($this->allGroups))
    		$this->init();
        if(empty($this->allGroups))
        	return false;

		$categories = array();
		foreach ($this->allGroups as $group) {
			if($group->active)
				$categories[] = clone $group;
		}
		if(empty($categories))
        	return false;

		if(!empty($categories))
		{
			$link = $_SESSION['alias']->alias.'/';
            foreach ($categories as $Group) {
            	$Group->link = $link.$Group->alias;
            	if($Group->parent > 0)
            		$Group->link = $link.$this->makeLink($Group->parent, $Group->alias);
            }
            return $categories;
		}
		return false;
	}

}

?>