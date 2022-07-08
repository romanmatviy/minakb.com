<?php

class compare_model
{

	public function table($sufix = '', $useAliasTable = false)
	{
		if($useAliasTable) return $_SESSION['service']->table.$sufix.$_SESSION['alias']->table;
		return $_SESSION['service']->table.$sufix;
	}

	public function getUserId()
	{
		if(!empty($_SESSION['user']->id))
			return $_SESSION['user']->id;

		if(!empty($_SESSION['compare_user_id']))
			return $_SESSION['compare_user_id'];

		if(!empty($_SESSION['cart']->user))
			return $_SESSION['cart']->user;

		$cookie_key = 'compare_id';
		$table = $this->table('_users');
		if($_SESSION['option']->useCartUsers)
		{
			$cookie_key = 'cart_id';
			$table = 's_cart_users';
		}

		if(!empty($_COOKIE[$cookie_key]))
		{
			if($user = $this->db->getAllDataById($table, trim($_COOKIE[$cookie_key]), 'cookie'))
			{
				if(!empty($_SESSION['user']->id) && $user->user != $_SESSION['user']->id)
				{
					$this->db->updateRow($table, ['user' => $_SESSION['user']->id], $user->id);
					$this->db->updateRow($this->table(), array('user' => $_SESSION['user']->id), array('cart' => 0, 'user' => -$user->id));
					$user->user = $_SESSION['user']->id;
				}
				if($user->user)
				{
					$_SESSION['compare_user_id'] = $user->user;
					if($_SESSION['option']->useCartUsers)
						$_SESSION['cart']->user = $user->user;
					return $user->user;
				}
				$_SESSION['compare_user_id'] = -$user->id;
				if($_SESSION['option']->useCartUsers)
					$_SESSION['cart']->user = -$user->id;
				return -$user->id;
			}
		}

		$cookie = md5('compare-user-'.time());
		$user = array('cookie' => $cookie, 'date_add' => time());
		if($user_id = $this->db->insertRow($table, $user))
		{
			$_SESSION['compare_user_id'] = -$user_id;
			if($_SESSION['option']->useCartUsers)
				$_SESSION['cart']->user = -$user_id;
			setcookie($cookie_key, $cookie, time() + 3600*24*31, '/');
			return -$user_id;
		}
	}

	public function add($compareGroup = 0)
	{
		$id = 0;
		$like = array();
		$like['user'] = $this->getUserId();
		$like['alias'] = $_POST['alias'];
		$like['content'] = $_POST['content'];
		$like['group'] = $compareGroup;
		if($row = $this->db->getAllDataById($this->table(), $like))
		{
			$id = $row->id;
			if($row->status == 0)
            	$this->db->updateRow($this->table(), array('status' => '1'), $row->id);
		}
		else
		{
			$like['status'] = 1;
			$like['date_add'] = time();
			$id = $this->db->insertRow($this->table(), $like);
		}
		return ['id' => $id, 'count' => $this->getLikesCount()];
	}

	public function cancel()
	{
		if($row = $this->db->getAllDataById($this->table(), $this->data->post('id')))
		{
			if($row->user != $this->getUserId())
				return false;
			if($_SESSION['option']->saveToHistory)
			{
				if($row->status == 1)
					$this->db->updateRow($this->table(), array('status' => '0'), $row->id);
			}
			else
				$this->db->deleteRow($this->table(), $row->id);
		}
		return ['result' => true, 'count' => $this->getLikesCount()];
	}

	public function getItems($where = array())
	{
		if(empty($where))
			return false;

		$res = ['items' => [], 'count' => 0];
		if($res['items'] = $this->db->select($this->table(), 'id, alias, content', $where)->get('array'))
			$res['count'] = count($res['items']);
		return $res;
	}

	public function getLikesCount($user = 0)
	{
		if(empty($user))
			$user = $this->getUserId();
		return (int) $this->db->getCount($this->table(), ['user' => $user, 'status' => 1]);
	}

	public function getLikesWithData($where = array())
	{
		$_SESSION['option']->paginator_total = $this->db->getCount($this->table(), $where);
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
						->join('wl_aliases as a', 'alias as alias_uri', '#l.alias')
						->join('wl_ntkd as n1', 'name as alias_name', $where_alias)
						->join('wl_ntkd as n2', 'name as page_name', $where_name)
						->join('wl_users', 'email as user_email, name as user_name', '#l.user')
						->order('date_add DESC')
						->limit($start, $_SESSION['option']->paginator_per_page)
						->get('array');
	}

	public function getGroupsWithData($user = 0)
	{
		if(empty($user))
			$user = $this->getUserId();
		$language = '';
		if($_SESSION['language'])
			$language = " AND n.language = '{$_SESSION['language']}'";
		return $this->db->getQuery("SELECT c.group as id, a.alias as alias_uri, c.alias as alias_id, n.name, i.file_name 
									FROM `s_compare` as c
									LEFT JOIN wl_aliases as a ON a.id = c.alias
									LEFT JOIN wl_ntkd as n ON n.alias = c.alias AND n.content = -c.group {$language}
									LEFT JOIN wl_images as i ON i.alias = c.alias AND i.content = -c.group AND i.position = 1
									WHERE c.user = {$user} AND c.status = 1
									GROUP BY `group`", 'array');
	}

}

?>