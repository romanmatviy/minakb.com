<?php

class likes_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function setLike($user)
	{
		$rez = array('setLike' => false, 'cancel' => false, 'count' => 0);
		$like = array();
		$like['user'] = $user;
		$like['alias'] = $_POST['alias'];
		$like['content'] = $_POST['content'];
		if($row = $this->db->getAllDataById($this->table(), $like))
		{
			if($row->status == 1)
                $rez['cancel'] = true;
            else
            {
                $this->db->updateRow($this->table(), array('status' => '1', 'date_update' => time()), $row->id);
                $rez['setLike'] = true;
            }
		}
		else
		{
			$rez['setLike'] = true;
			$like['status'] = 1;
			$like['date_add'] = $like['date_update'] = time();
			$this->db->insertRow($this->table(), $like);
		}
		$rez['count'] = $this->db->getCount($this->table(), array('alias' => $_POST['alias'], 'content' => $_POST['content']));
		return $rez;
	}

	public function cancelLike($user)
	{
		$rez = array('cancelLike' => false, 'count' => 0);
		$like = array();
		$like['user'] = $user;
		$like['alias'] = $_POST['alias'];
		$like['content'] = $_POST['content'];
		if($row = $this->db->getAllDataById($this->table(), $like))
		{
			if($row->status == 1)
			{
				$rez['cancelLike'] = true;
                $this->db->updateRow($this->table(), array('status' => '0', 'date_update' => time()), $row->id);
			}
		}
		$rez['count'] = $this->db->getCount($this->table(), array('alias' => $_POST['alias'], 'content' => $_POST['content']));
		return $rez;
	}

	public function getLikes($where)
	{
		return $this->db->getAllDataByFieldInArray($this->table(), $where);
	}

	public function getLikesWithData($where = array())
	{
		$_SESSION['option']->paginator_total = $this->db->getCount($this->table());
		$start = 0;
		if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
		{
			if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
		}
		$where_alias = array('alias' => '#l.alias', 'content' => '0');
		$where_name = array('alias' => '#l.alias', 'content' => '#l.content');
		if($_SESSION['language'])
		{
			$where_alias['language'] = $_SESSION['language'];
			$where_name['language'] = $_SESSION['language'];
		}
		return $this->db->select($this->table(). ' as l', '*', $where)
						->join('wl_ntkd as n1', 'name as alias_name', $where_alias)
						->join('wl_ntkd as n2', 'name as page_name', $where_name)
						->join('wl_users', 'email as user_email, name as user_name', '#l.user')
						->order('date_update DESC')
						->limit($start, $_SESSION['option']->paginator_per_page)
						->get('array');
	}

}

?>