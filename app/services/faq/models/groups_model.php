<?php

class groups_model {

	public $errors = false;

	public function table($sufix = '_groups', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function getGroups($active = true)
	{
		$where_groups = array('wl_alias' => $_SESSION['alias']->id);
		if($active)
			$where_groups['active'] = 1;
		$this->db->select($this->table().' as g', '*', $where_groups);
		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = '#-g.id';
		if($_SESSION['language'])
			$where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', 'name', $where_ntkd);
		$this->db->order('position');
		return $this->db->get('array');
	}

	public function getByAlias($alias)
	{
		$this->db->select($this->table().' as g', '*', ['wl_alias' => $_SESSION['alias']->id, 'alias' => $alias]);
		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = '#-g.id';
		if($_SESSION['language'])
			$where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd', 'name', $where_ntkd);
		$this->db->join('wl_users as a', 'name as author_add_name', '#g.author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#g.author_edit');
		return $this->db->get('single');
	}

	public function add()
	{
		$data = array('wl_alias' => $_SESSION['alias']->id);
		$data['active'] = 1;
		$data['author_add'] = $data['author_edit'] = $_SESSION['user']->id;
		$data['date_add'] = $data['date_edit'] = time();
		$data['position'] = 0;
		if($id = $this->db->insertRow($this->table(), $data))
		{
			$data = array('alias' => '');
			$data['position'] = $this->db->getCount($this->table(), ['wl_alias' => $_SESSION['alias']->id]);

			$ntkd['alias'] = $_SESSION['alias']->id;
			$ntkd['content'] = -$id;
			if($_SESSION['language'])
			{
				foreach ($_SESSION['all_languages'] as $lang)
				{
					$ntkd['language'] = $lang;
					$ntkd['name'] = $this->data->post('name_'.$lang);
					$ntkd['title'] = $this->data->post('name_'.$lang);
					if($lang == $_SESSION['language'])
						$data['alias'] = $this->data->latterUAtoEN($ntkd['name']);
					$this->db->insertRow('wl_ntkd', $ntkd);
				}
			}
			else
			{
				$ntkd['name'] = $this->data->post('name');
				$ntkd['title'] = $this->data->post('name');
				$data['alias'] = $this->db->latterUAtoEN($ntkd['name']);
				$this->db->insertRow('wl_ntkd', $ntkd);
			}

			$data['alias'] = $this->makeAlias($data['alias']);
			if($this->db->updateRow($this->table(), $data, $id))
				return $data['alias'];
		}
		return false;
	}

	public function save($id)
	{
		$check = $this->getByAlias($this->data->post('alias'));
		if($check && $check->id != $id)
		{
			$this->errors = 'Група з адресою "'.$this->data->post('alias').'" вже є! Змініть адресу!';
			return false;
		}
		$faq = $this->data->prepare(array('active' => 'number', 'alias'));
		$faq['author_edit'] = $_SESSION['user']->id;
		$faq['date_edit'] = time();
		if($this->db->updateRow($this->table(), $faq, $id))
			return true;
	}

	public function delete($id)
	{
		if($group = $this->db->getAllDataById($this->table(), $id))
		{
			if($_POST['content'] == 1)
			{
				if($questions = $this->db->getAllDataByFieldInArray($this->table('_questions'), $group->id, 'group'))
				{
					foreach ($questions as $q) {
						$this->db->executeQuery("DELETE FROM wl_ntkd WHERE alias = '{$_SESSION['alias']->id}' AND content = '{$q->id}'");
					}
					$this->db->deleteRow($this->table('_questions'), $group->id, 'group');
				}
			}
			else
		        $this->db->updateRow($this->table('_questions'), array('active' => 0, 'group' => 0), $group->id, 'group');

			$this->db->deleteRow($this->table(), $group->id);
			$this->db->deleteRow($this->table(), ['alias' => $_SESSION['alias']->id, 'content' => -$group->id]);
			$this->db->executeQuery("UPDATE `{$this->table()}` SET `position` = position - 1 WHERE `position` > '{$group->position}'");

			return true;
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