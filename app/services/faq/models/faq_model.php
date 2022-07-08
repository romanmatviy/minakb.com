<?php

class faq_model {

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getQuestions($group = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($group > 0) $where['group'] = $group;
		if($active) $where['active'] = 1;

		$this->db->select($this->table('_questions').' as q', '*', $where);

		$this->db->join('wl_users as a', 'name as author_add_name', '#q.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#q.author_edit');

		$where_join['alias'] = $_SESSION['alias']->id;
		$where_join['content'] = '#q.id';
		if($_SESSION['language']) $where_join['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd as qn', 'name as question, text as answer', $where_join);

		if($_SESSION['option']->useGroups)
		{
			$where_join['content'] = '#-q.group';
			$this->db->join('wl_ntkd as gn', 'name as group_name', $where_join);
			$this->db->join($this->table('_groups').' as g', 'alias as group_alias', '#q.group');
		}

		$this->db->order('position');
		$faqs = $this->db->get('array');
		if($faqs)
			foreach ($faqs as $faq) {
				$answer = html_entity_decode($faq->answer);
				if(substr($answer, 0, 3) == '<p>')
					$faq->answer = $answer;
				else
					$faq->answer = nl2br($faq->answer);
				if($_SESSION['option']->useGroups && $faq->group_alias != '')
				{
					$faq->link = $faq->group_alias .'/'. $faq->alias;
				} else {
					$faq->link = $faq->alias;
				}
			}
		return $faqs;
	}
	
	public function getQuestionsByAlias($alias)
	{
		$this->db->select($this->table('_questions').' as q', '*', ['wl_alias' => $_SESSION['alias']->id, 'alias' => $alias]);
		$this->db->join('wl_users as a', 'name as author_add_name', '#q.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#q.author_edit');
		$where_join['alias'] = $_SESSION['alias']->id;
		$where_join['content'] = '#q.id';
		if($_SESSION['language']) $where_join['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', 'name as question, text as answer', $where_join);
		return $this->db->get('single');
	}

	public function getGroups($active = true)
	{
		$where_groups = array('wl_alias' => $_SESSION['alias']->id);
		if($active)
			$where_groups['active'] = 1;
		$this->db->select($this->table('_groups').' as g', '*', $where_groups);
		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = '#-g.id';
		if($_SESSION['language'])
			$where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', 'name', $where_ntkd);
		$this->db->order('position');
		return $this->db->get('array');
	}

	public function getGroupByAlias($alias)
	{
		$this->db->select($this->table('_groups').' as g', '*', ['wl_alias' => $_SESSION['alias']->id, 'alias' => $alias]);
		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = '#-g.id';
		if($_SESSION['language'])
			$where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', 'name', $where_ntkd);
		$this->db->join('wl_users as a', 'name as author_add_name', '#g.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#g.author_edit');
		return $this->db->get('single');
	}
	
}

?>