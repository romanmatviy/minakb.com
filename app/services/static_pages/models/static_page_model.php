<?php

class static_page_model
{
	public function table($sufix = '')
	{
		return $_SESSION['service']->table.$sufix;
	}

	public function get($id = 0)
	{
		if($id == 0)
			$id = $_SESSION['alias']->id;
		$this->db->select($this->table(), '*', $id);
		$this->db->join('wl_users as a', 'name as author_add_name', '#author_add');
		$this->db->join('wl_users as e', 'name as author_edit_name', '#author_edit');
		return $this->db->get('single');
	}

}

?>