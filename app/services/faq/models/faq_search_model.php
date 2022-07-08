<?php

class faq_search_model
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
			$this->db->select($this->table('_questions'), '*', $content);
			$this->db->join('wl_users', 'name as author_name', '#author_edit');
			$faq = $this->db->get('single');
			if($faq && ($faq->active || $admin))
			{
				$search = new stdClass();
				$search->id = $faq->id;
				$search->link = $_SESSION['alias']->alias.'/'.$faq->alias;
				$search->image = false;
				$search->date = $faq->date_edit;
				$search->author = $faq->author_edit;
				$search->author_name = $faq->author_name;
				$search->additional = false;

				if($_SESSION['option']->useGroups && $faq->group > 0)
				{
					$this->db->select($this->table('_groups') .' as g', 'alias', $faq->group);
					$where_join['alias'] = $_SESSION['alias']->id;
					$where_join['content'] = '#-g.id';
					if($_SESSION['language']) $where_join['language'] = $_SESSION['language'];
					$this->db->join('wl_ntkd', 'name', $where_join);
					$group = $this->db->get('single');

					$search->link = $_SESSION['alias']->alias.'/'.$group->alias.'/'.$faq->alias;
					$link = $_SESSION['alias']->alias.'/'.$group->alias;
					$search->additional = array($link => $group->name);
				}
			}
		}
		else
		{
			$content *= -1;
			$this->db->select($this->table('_groups'), '*', $content);
			$this->db->join('wl_users', 'name as author_name', '#author_edit');
			$faq = $this->db->get('single');
			if($faq && ($faq->active || $admin))
			{
				$search = new stdClass();
				$search->id = $faq->id;
				$search->link = $_SESSION['alias']->alias.'/'.$faq->alias;
				$search->image = false;
				$search->date = $faq->date_edit;
				$search->author = $faq->author_edit;
				$search->author_name = $faq->author_name;
				$search->additional = false;
			}
		}
		
		return $search;
	}

}

?>