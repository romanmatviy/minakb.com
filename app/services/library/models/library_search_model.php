<?php

class library_search_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getByContent($content, $admin = false)
	{
		$search = false;

		if($content > 0)
		{
			$this->db->select($this->table('_articles').' as p', '*', $content);
			$this->db->join('wl_users', 'name as author_name', '#p.author_edit');
			$article = $this->db->get('single');
			if($article && ($article->active || $admin))
			{
				$search = new stdClass();
				$search->id = $article->id;
				$search->link = $_SESSION['alias']->alias.'/'.$article->alias;
				$search->date = $article->date_edit;
				$search->author = $article->author_edit;
				$search->author_name = $article->author_name;
				$search->additional = false;
				$search->folder = false;
				if(isset($_SESSION['option']->folder))
					$search->folder = $_SESSION['option']->folder;

				if($_SESSION['option']->useGroups)
				{
					$search->additional = array();

					$list = array();
					$all_groups = $this->db->getAllData($this->table('_groups'));
		            if($all_groups) foreach ($all_groups as $g) {
		            	$list[$g->id] = clone $g;
		            }

					if($_SESSION['option']->articleMultiGroup == 0 && $article->group > 0)
					{
						$parents = $this->makeParents($list, $article->group, array());
						$link = $_SESSION['alias']->alias .'/';
						foreach ($parents as $parent) {
							$link .= $parent->alias .'/';
							$search->additional[$link] = $parent->name;
						}
						$search->link = $link . $article->alias;
					}
					elseif($_SESSION['option']->articleMultiGroup == 1)
					{
						$this->db->select($this->table('_article_group') .' as pg', '', $article->id, 'article');
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
						$where_ntkd['alias'] = $_SESSION['alias']->id;
						$where_ntkd['content'] = "#-pg.group";
						if($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
	        			$this->db->join('wl_ntkd', 'name', $where_ntkd);
						$groups = $this->db->get('array');

						if($groups)
						{
				            foreach ($groups as $g) {
				            	if($g->parent > 0) {
				            		$link = $_SESSION['alias']->alias .'/';
				            		$link .= $this->makeLink($list, $g->parent, $g->alias);
				            		$search->additional[$link] = $g->name;
				            	}
				            }							
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

					$list = array();
					$all_groups = $this->db->getAllData($this->table('_groups'));
		            if($all_groups) foreach ($all_groups as $g) {
		            	$list[$g->id] = clone $g;
		            }

	            	$parents = $this->makeParents($list, $group->parent, array());
					$link = $_SESSION['alias']->alias .'/';
					foreach ($parents as $parent) {
						$link .= $parent->alias .'/';
						$search->additional[$link] = $parent->name;
					}
					$search->link = $link . $group->alias;
				}
			}
		}
		
		return $search;
	}

	private function makeParents($all, $parent, $parents)
	{
		$group = clone $all[$parent];
		$where = '';
        if($_SESSION['language']) $where = "AND `language` = '{$_SESSION['language']}'";
        $this->db->executeQuery("SELECT `name` FROM `wl_ntkd` WHERE `alias` = '{$_SESSION['alias']->id}' AND `content` = '-{$group->id}' {$where}");
    	if($this->db->numRows() == 1)
    	{
    		$ntkd = $this->db->getRows();
    		$group->name = $ntkd->name;
    	}
    	array_unshift ($parents, $group);
		if($all[$parent]->parent > 0) $parents = $this->makeParents ($all, $all[$parent]->parent, $parents);
		return $parents;
	}

	private function makeLink($all, $parent, $link)
	{
		$link = $all[$parent]->alias .'/'.$link;
		if($all[$parent]->parent > 0) $link = $this->makeLink ($all, $all[$parent]->parent, $link);
		return $link;
	}

}

?>