<?php

class static_pages_search_model
{
	
	public function getByContent($content, $admin = false)
	{
		$search = false;

		$this->db->select($_SESSION['service']->table, '*', $_SESSION['alias']->id);
		$this->db->join('wl_users', 'name as author_name', '#author_edit');
		if($article = $this->db->get('single'))
		{
			$search = new stdClass();
			$search->id = $_SESSION['alias']->id;
			$search->link = $_SESSION['alias']->alias;
			$search->date = $article->date_edit;
			$search->author = $article->author_edit;
			$search->author_name = $article->author_name;
			$search->additional = false;
			$search->folder = false;
			if(isset($_SESSION['option']->folder))
				$search->folder = $_SESSION['option']->folder;
			if($admin)
			{
				$search->edit_link = 'admin/'.$_SESSION['alias']->alias;
			}
		}
		return $search;
	}

}

?>