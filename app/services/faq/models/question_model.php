<?php

class question_model {

	public function table($sufix = '_questions', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}
	
	public function getQuestions($group = 0, $active = true)
	{
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if($group > 0) $where['group'] = $group;
		if($active) $where['active'] = 1;

		$this->db->select($this->table().' as q', '*', $where);

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
				if($_SESSION['option']->useGroups && $faq->group_alias != '')
					$faq->link = $faq->group_alias .'/'. $faq->alias;
				else
					$faq->link = $faq->alias;
			}
		return $faqs;
	}
	
	public function getByAlias($alias)
	{
		$this->db->select($this->table().' as q', '*', ['wl_alias' => $_SESSION['alias']->id, 'alias' => $alias]);
		$this->db->join('wl_users as a', 'name as author_add_name', '#q.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#q.author_edit');
		$where_join['alias'] = $_SESSION['alias']->id;
		$where_join['content'] = '#q.id';
		if($_SESSION['language']) $where_join['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', 'name as question, text as answer', $where_join);
		return $this->db->get('single');
	}
	
	public function add()
	{
		$data = array('wl_alias' => $_SESSION['alias']->id);
		$data['group'] = $group = $this->data->post('group') ?? 0;
		$data['active'] = 1;
		$data['author_add'] = $data['author_edit'] = $_SESSION['user']->id;
		$data['date_add'] = $data['date_edit'] = time();
		$data['position'] = 0;
		if($id = $this->db->insertRow($this->table(), $data))
		{
			$data = array('alias' => '');
			$data['position'] = $this->db->getCount($this->table(), ['wl_alias' => $_SESSION['alias']->id, 'group' => $group]);

			$ntkd['alias'] = $_SESSION['alias']->id;
			$ntkd['content'] = $id;
			if($_SESSION['language'])
			{
				foreach ($_SESSION['all_languages'] as $lang)
				{
					$ntkd['language'] = $lang;
					$ntkd['name'] = $this->data->post('name_'.$lang);
					$ntkd['text'] = $this->data->post('text_'.$lang);
					$ntkd['title'] = $this->data->post('name_'.$lang);
					if($lang == $_SESSION['language'])
						$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
					$this->db->insertRow('wl_ntkd', $ntkd);
				}
			}
			else
			{
				$ntkd['name'] = $this->data->post('name');
				$ntkd['text'] = $this->data->post('text');
				$ntkd['title'] = $this->data->post('name');
				$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
				$this->db->insertRow('wl_ntkd', $ntkd);
			}

			$data['alias'] = $this->makeAlias($data['alias']);
			if($this->db->updateRow($this->table(), $data, $id))
			{
				if($_SESSION['option']->useGroups)
					if($group = $this->db->getAllDataById($this->table('_groups'), $group))
						$data['alias'] = $group->alias .'/'. $data['alias'];
				return $data['alias'];
			}
		}
		return false;
	}

	public function save($id)
	{
		$check = $this->getByAlias($this->data->post('alias'));
		if($check && $check->id != $id)
		{
			$this->errors = 'Питання з адресою "'.$this->data->post('alias').'" вже є! Змініть адресу!';
			return false;
		}
			
		$faq = $this->data->prepare(array('group' => 'number', 'active' => 'number', 'alias'));

		if($_SESSION['option']->useGroups)
		{
			$question = $this->db->getAllDataById($this->table(), $id);
			if($question->group != $faq['group'])
			{
				$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > {$question->position} AND `group` = {$question->group} AND `wl_alias` = {$_SESSION['alias']->id}");
				$faq['position'] = $this->db->getCount($this->table(), $faq['group'], 'group') + 1;
			}
		}

		$faq['author_edit'] = $_SESSION['user']->id;
		$faq['date_edit'] = time();
		if($this->db->updateRow($this->table(), $faq, $id))
		{
			if($_SESSION['option']->useGroups)
				if($group = $this->db->getAllDataById($this->table('_groups'), $faq['group']))
					$faq['alias'] = $group->alias .'/'. $faq['alias'];
			return $faq['alias'];
		}
	}

	public function delete($id)
	{
		if($question = $this->db->getAllDataById($this->table(), $id))
		{
			$this->db->deleteRow($this->table(), $id);
			$this->db->deleteRow($this->table(), ['alias' => $_SESSION['alias']->id, 'content' => $id]);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > {$question->position} AND `group` = {$question->group} AND `wl_alias` = {$_SESSION['alias']->id}");
			if($question->group > 0 && $_SESSION['option']->useGroups)
				if($group = $this->db->getAllDataById($this->table('_groups'), $question->group))
					return '/'.$group->alias;
			return '';
		}
		return false;
	}

	private function makeAlias($link)
	{
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
	
}

?>