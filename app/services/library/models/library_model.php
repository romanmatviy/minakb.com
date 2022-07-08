<?php

class library_model {

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function routeURL($url = array(), &$type = null, $admin = false)
	{		
		$i = 1;
		$last_i = count($url) - 1;
		$type = false;

		if($_SESSION['option']->useGroups)
		{
			$parent = 0;
			$group = false;
			
			for (; $i <= $last_i ; $i++) {
				$all_info = ($url[$i] == $url[$last_i]) ? true : false;
				$group = $this->getGroupByAlias($url[$i], $parent, $all_info);
				if($group)
				{
					$parent = $group->id;
					$type = 'group';
					if($i == $last_i) return $group;
				}
				else
				{
					$type = false;
					if($i != $last_i) return false;
					break;
				}
			}

			if($i == ++$last_i) return $group;
		}

		if($article = $this->getArticle(end($url)))
		{
			$url = implode('/', $url);
			if($url != $article->link)
			{
				$link = SITE_URL;
				if($admin) $link .= 'admin/';
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: '. $link. $article->link);
				exit();
			}

			$type = 'article';
			return $article;
		}

		return false;
	}
	
	public function getArticles($Group = -1, $noInclude = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($active)
			$where['active'] = 1;

		if($_SESSION['option']->useGroups > 0 && $Group >= 0)
		{
			if(is_array($Group) && !empty($Group))
			{
				$where['id'] = array();
				foreach ($Group as $g) {
					$articles = $this->db->getAllDataByFieldInArray($this->table('_article_group'), $g->id, 'group');
					if($articles)
						foreach ($articles as $article) if($article->article != $noInclude) {
							array_push($where['id'], $article->article);
						}
				}
			}
			elseif($Group >= 0)
			{
				if($_SESSION['option']->articleMultiGroup == 0 || $Group == 0)
					$where['group'] = $Group;
				else
				{
					$articles = $this->db->getAllDataByFieldInArray($this->table('_article_group'), $Group, 'group');
					if($articles)
					{
						$where['id'] = array();
						foreach ($articles as $article) if($article->article != $noInclude) {
							array_push($where['id'], $article->article);
						}
					}
					else
						return null;
				}
			}
			elseif($noInclude > 0)
				$where['id'] = '!'.$noInclude;
		}
		elseif($noInclude > 0)
			$where['id'] = '!'.$noInclude;
		
		if(isset($_GET['name']) && $_GET['name'] != '')
		{
			$articles = $this->db->getAllDataByFieldInArray('wl_ntkd', array('alias' => $_SESSION['alias']->id, 'content' => '>0', 'name' => '%'.$this->data->get('name')));
			if(!empty($articles))
			{
				if(!isset($where['id']))
				{
					$where['id'] = array();
					foreach ($articles as $p) {
						array_push($where['id'], $p->content);
					}
				}
				else
				{
					$ids = clone $where['id'];
					$where['id'] = array();
					foreach ($articles as $p) {
						if(in_array($p->content, $ids))
							array_push($where['id'], $p->content);
					}
				}
			}
			else
				return false;
		}
		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->articleMultiGroup == 0)
			$where['#g.active'] = 1;
		
		$this->db->select($this->table('_articles').' as a', '*', $where);
		
		$this->db->join('wl_users as aa', 'name as author_add_name', '#a.author_add');
			$this->db->join('wl_users as e', 'name as author_edit_name', '#a.author_edit');

		if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->articleMultiGroup == 0)
		{
			$where_gn['alias'] = $_SESSION['alias']->id;
			$where_gn['content'] = "#-a.group";
			if($_SESSION['language'])
				$where_gn['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd as gn', 'name as group_name', $where_gn);
			$this->db->join($this->table('_groups').' as g', 'active as group_active', '#a.group');
		}

		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = "#a.id";
		if($_SESSION['language'])
			$where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd as n', 'name, text, list', $where_ntkd);
		$this->db->order($_SESSION['option']->articleOrder);

		if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
		{
			$start = 0;
			if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}

		$articles = $this->db->get('array', false);
        if($articles)
        {
			$_SESSION['option']->paginator_total = $this->db->get('count');

			$list = array();
        	if($_SESSION['option']->useGroups > 0)
        	{
        		if(empty($this->allGroups))
        		{
        			$where = array();
        			$where['wl_alias'] = $_SESSION['alias']->id;
					$where['active'] = 1;
					$this->db->select($this->table('_groups') .' as g', '*', $where);

					$where_ntkd['alias'] = $_SESSION['alias']->id;
					$where_ntkd['content'] = "#-g.id";
					if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
					$this->db->join('wl_ntkd', "name", $where_ntkd);
					$this->allGroups = $this->db->get('array');
        		}
	            if($this->allGroups)
	            	foreach ($this->allGroups as $g) {
		            	$list[$g->id] = clone $g;
		            }
	        }

			$sizes = $this->db->getAliasImageSizes();

			$articles_ids = $articles_photos = array();
            foreach ($articles as $article)
            	$articles_ids[] = $article->id;
            if($photos = $this->getArticlePhoto($articles_ids))
            {
	            foreach ($photos as $photo) {
	            	$articles_photos[$photo->content] = clone $photo;
	            }
	            unset($photos);
	        }

	        $sizes = $this->db->getAliasImageSizes();

            foreach ($articles as $article)
            {
            	$article->link = $_SESSION['alias']->alias.'/'.$article->alias;
            	// $article->video = $this->db->getAllDataByFieldInArray('wl_video', array('alias' => $_SESSION['alias']->id, 'content' => $article->id));

            	$article->photo = null;
            	if(isset($articles_photos[$article->id]))
            	{
            		$photo = $articles_photos[$article->id];
					if($sizes)
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix.'_photo';
							$article->$resize_name = $_SESSION['option']->folder.'/'.$article->id.'/'.$resize->prefix.'_'.$photo->file_name;
						}
					$article->photo = $_SESSION['option']->folder.'/'.$article->id.'/'.$photo->file_name;
            	}

            	if($_SESSION['option']->articleUseOptions > 0)
            		$article->options = $this->getOptions($article);

				$article->parents = array();
				if($_SESSION['option']->useGroups > 0)
				{
					if($_SESSION['option']->articleMultiGroup == 0 && $article->group > 0)
					{
						$article->parents = $this->makeParents($list, $article->group, $article->parents);
						$link = '/';
						foreach ($article->parents as $parent) {
							$link .= $parent->alias .'/';
						}
						$article->group_link = $_SESSION['alias']->alias . $link;
						$article->link = $_SESSION['alias']->alias . $link . $article->alias;
					}
					elseif($_SESSION['option']->articleMultiGroup == 1)
					{
						$article->group = array();

						$this->db->select($this->table('_article_group') .' as pg', '', $article->id, 'article');
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
						$where_ntkd['content'] = "#-pg.group";
            			$this->db->join('wl_ntkd', 'name', $where_ntkd);
						$article->group = $this->db->get('array');
						if($article->group)
				            foreach ($article->group as $g) {
				            	if($g->parent > 0) {
				            		$g->link = $_SESSION['alias']->alias . '/' . $this->makeLink($list, $g->parent, $g->alias);
				            	}
				            }
					}
				}
            }

			return $articles;
		}
		$this->db->clear();
		return null;
	}
	
	function getArticle($alias, $key = 'alias', $all_info = true)
	{
		$this->db->select($this->table('_articles').' as p', '*', array('wl_alias' => $_SESSION['alias']->id, $key => $alias));

		if($all_info)
		{
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
			$this->db->join('wl_ntkd as n', 'name', $where_ntkd);
		}

		$article = $this->db->get('single');
        if($article)
        {
        	if(isset($_SESSION['alias']->breadcrumbs))
        	{
        		$where_ntkd['content'] = 0;
        		$alias_ntkd = $this->db->select('wl_ntkd', 'name', $where_ntkd)->get();
        		$_SESSION['alias']->breadcrumbs = array($alias_ntkd->name => $_SESSION['alias']->alias);
        	}

        	$article->link = $_SESSION['alias']->alias.'/'.$article->alias;

        	if($_SESSION['option']->articleUseOptions > 0)
            		$article->options = $this->getOptions($article);

			$article->parents = array();
			if($_SESSION['option']->useGroups > 0)
			{
				$list = array();
				$all_groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
	            if($all_groups) foreach ($all_groups as $g) {
	            	$list[$g->id] = clone $g;
	            }

				if($_SESSION['option']->articleMultiGroup == 0 && $article->group > 0)
				{
					$article->parents = $this->makeParents($list, $article->group, $article->parents);
					$link = $_SESSION['alias']->alias . '/';
					foreach ($article->parents as $parent) {
						$link .= $parent->alias;
						if(isset($_SESSION['alias']->breadcrumbs) && !empty($parent->name)) $_SESSION['alias']->breadcrumbs[$parent->name] = $link;
						$link .= '/';
					}
					$article->group_link = $link;
					$article->link = $link . $article->alias;
				}
				elseif($_SESSION['option']->articleMultiGroup == 1)
				{
					$article->group = array();

					$this->db->select($this->table('_article_group') .' as pg', '', $article->id, 'article');
					$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
					$where_ntkd['content'] = "#-pg.group";
        			$this->db->join('wl_ntkd', 'name', $where_ntkd);
					$article->group = $this->db->get('array');

					if($article->group)
			            foreach ($article->group as $g) {
			            	if($g->parent > 0)
			            		$g->link = $_SESSION['alias']->alias . '/' . $this->makeLink($list, $g->parent, $g->alias);
							else
								$g->link = $_SESSION['alias']->alias . '/' . $g->alias;
			            }
				}
			}
        	if($all_info && isset($_SESSION['alias']->breadcrumbs)) $_SESSION['alias']->breadcrumbs[$article->name] = '';

            return $article;
		}
		return null;
	}

	public function getGroups($parent = 0, $use__per_page = true)
	{
		$where['wl_alias'] = $_SESSION['alias']->id;
		$where['active'] = 1;
		if($parent >= 0 && is_numeric($parent)) $where['parent'] = $parent;
		$this->db->select($this->table('_groups') .' as g', '*', $where);
		$this->db->join('wl_users', 'name as user_name', '#g.author_edit');

		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = "#-g.id";
		if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', "name, text, list", $where_ntkd);

		$this->db->order($_SESSION['option']->groupOrder);
		
		if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0 && $parent >= 0 && $use__per_page)
		{
			$start = 0;
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1){
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			}
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}
		
		if($categories = $this->db->get('array', false))
		{
			if($use__per_page)
				$_SESSION['option']->paginator_total = $this->db->get('count');
			else
				$this->db->clear();

            $list = array();
            $sizes = $this->db->getAliasImageSizes();
            $groups = $this->db->getAllDataByFieldInArray($this->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
            foreach ($groups as $Group) {
            	$list[$Group->id] = clone $Group;
            }

            foreach ($categories as $Group) {
            	$Group->link = $_SESSION['alias']->alias.'/'.$Group->alias;
            	if($Group->parent > 0) {
            		$Group->link = $_SESSION['alias']->alias.'/'.$this->makeLink($list, $Group->parent, $Group->alias);
            	}

            	if($photo = $this->getArticlePhoto(-$Group->id))
            	{
					if($sizes)
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix.'_photo';
							$Group->$resize_name = $_SESSION['option']->folder.'/-'.$Group->id.'/'.$resize->prefix.'_'.$photo->file_name;
						}
					$Group->photo = $_SESSION['option']->folder.'/-'.$Group->id.'/'.$photo->file_name;
            	}
            }

            return $categories;
		}
		else
			$this->db->clear();
		return null;
	}

	public function makeParents($all, $parent, $parents)
	{
		$group = clone $all[$parent];
    	array_unshift ($parents, $group);
		if($all[$parent]->parent > 0) $parents = $this->makeParents ($all, $all[$parent]->parent, $parents);
		return $parents;
	}

	public function getGroupByAlias($alias, $parent = 0, $all_info = true)
	{
		$where['wl_alias'] = $_SESSION['alias']->id;
		$where['alias'] = $alias;
		$where['parent'] = $parent;
		$this->db->select($this->table('_groups') .' as c', '*', $where);
		if($all_info) $this->db->join('wl_users', 'name as user_name', '#c.author_edit');
		$group = $this->db->get('single');
		if($group)
			if($photo = $this->getArticlePhoto(-$group->id))
        	{
				if($sizes = $this->db->getAliasImageSizes())
					foreach ($sizes as $resize) {
						$resize_name = $resize->prefix.'_photo';
						$group->$resize_name = $_SESSION['option']->folder.'/-'.$group->id.'/'.$resize->prefix.'_'.$photo->file_name;
					}
				$group->photo = $_SESSION['option']->folder.'/-'.$group->id.'/'.$photo->file_name;
        	}
		return $group;
	}

	private function makeLink($all, $parent, $link)
	{
		$link = $all[$parent]->alias .'/'.$link;
		if($all[$parent]->parent > 0) $link = $this->makeLink ($all, $all[$parent]->parent, $link);
		return $link;
	}

	public function getArticlePhoto($article, $all = false)
	{
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = $article;
		if(is_array($article) || $article == '<0')
			$where['position'] = 1;
		$this->db->select('wl_images', '*', $where);
		if($all)
			$this->db->join('wl_users', 'name as user_name', '#author');
		elseif(is_numeric($article))
		{
			$this->db->order('position ASC');
			$this->db->limit(1);
		}
		if(is_array($article) || $all)
			return $this->db->get('array');
		else
			return $this->db->get();
	}

	private function getOptions($article)
	{
		$article_options = array();
		$where_language = '';
        if($_SESSION['language']) $where_language = "AND (po.language = '{$_SESSION['language']}' OR po.language = '')";
		$this->db->executeQuery("SELECT go.id, go.alias, go.filter, po.value, it.name as type_name, it.options FROM `{$this->table('_article_options')}` as po LEFT JOIN `{$this->table('_options')}` as go ON go.id = po.option LEFT JOIN `wl_input_types` as it ON it.id = go.type WHERE go.active = 1 AND po.article = '{$article->id}' {$where_language} ORDER BY go.position");
		if($this->db->numRows() > 0)
		{
			$options = $this->db->getRows('array');
			foreach ($options as $option) if($option->value != '') {
				$article_options[$option->id] = new stdClass();
				$article_options[$option->id]->id = $option->id;
				$article_options[$option->id]->alias = $option->alias;
				$article_options[$option->id]->filter = $option->filter;
				$article_options[$option->id]->value = $option->value;
				$where = array();
				$where['option'] = $option->id;
				if($_SESSION['language']) $where['language'] = $_SESSION['language'];
				$name = $this->db->getAllDataById($this->table('_options_name'), $where);

				if($name){
					$article_options[$option->id]->name = $name->name;
					$article_options[$option->id]->sufix = $name->sufix;
				}
				if($option->options == 1){
					if($option->type_name == 'checkbox'){
						$option->value = explode(',', $option->value);
						$article_options[$option->id]->value = array();
						foreach ($option->value as $value) {
							$where = array();
							$where['option'] = $value;
							if($_SESSION['language']) $where['language'] = $_SESSION['language'];
							$value = $this->db->getAllDataById($this->table('_options_name'), $where);
							if($value){
								$article_options[$option->id]->value[] = $value->name;
							}
						}
					} else {
						$where = array();
						$where['option'] = $option->value;
						if($_SESSION['language']) $where['language'] = $_SESSION['language'];
						$value = $this->db->getAllDataById($this->table('_options_name'), $where);
						if($value){
							$article_options[$option->id]->value = $value->name;
						}
					}
				} else {
					$article_options[$option->id]->value = $option->value;
				}
			}
		}
		return $article_options;
	}
	
}

?>