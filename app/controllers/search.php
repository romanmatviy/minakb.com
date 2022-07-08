<?php 

class Search extends Controller {

	public function index()
	{
		$this->wl_alias_model->setContent();
		$_SESSION['alias']->breadcrumbs = '';
		$data = array();
		$current = 0;
		$this->load->library('validator');
		if($this->data->get('by'))
			$this->validator->setRules($this->text('search text'), $this->data->get('by'), '3..100');
		if($this->data->get('keyword'))
			$this->validator->setRules($this->text('search keyword'), $this->data->get('keyword'), '3..100');
		if($this->validator->run())
		{
			$this->load->model('wl_search_model');
			if($search_data = $this->wl_search_model->get($this->data->get('by')))
			{
				$per_page = 0;
				$start = 1;
				if($this->data->get('page') > 1 && isset($_SESSION['option']->paginator_per_page))
					$start = ($this->data->get('page') - 1) * $_SESSION['option']->paginator_per_page + 1;

				foreach ($search_data as $search)
				{
					if($result = $this->load->function_in_alias($search->alias_id, '__get_Search', $search->content))
					{
						$current++;
						if(empty($result->name))
							$result->name = $search->name;
						$result->list = $search->list;
						$result->text = $search->text;
						$result->image = false;
						if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
						{
							if($current >= $start && $current < ($start + $_SESSION['option']->paginator_per_page))
							{
								if(isset($result->folder) && $result->folder)
									$result->image = $this->wl_search_model->getImage($search->alias_id, $search->content, $result->folder, 'thumb_');
								array_push($data, $result);
							}
						} else {
							if($result->folder)
								$result->image = $this->wl_search_model->getImage($search->alias_id, $search->content, $result->folder, 'thumb_');
							array_push($data, $result);
						}
					}
				}
			}
		}
		else
		{
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->errors = $this->validator->getErrors();
		}
		@$_SESSION['option']->paginator_total = $current;

		if(count($data) == 1)
			header("Location:".SITE_URL.$data[0]->link);

		$this->load->page_view('search_view', array('data' => $data));
	}

	public function __get_Keywords($data = 0)
	{
		$alias = $content = 0;
		if(is_array($data))
		{
			if(isset($data['alias']))
				$alias = $data['alias'];
			if(isset($data['content']))
				$content = $data['content'];
		}
		else
			$alias = $data;

		$add = '';		
		$where = array('keywords' => '!');
		if(is_numeric($alias) && $alias > 0)
		{
			if($alias = $this->db->getAllDataById('wl_aliases', $alias))
			{
				$where['alias'] = $alias->id;
				$add = '&alias='.$alias->alias;
			}
		}
		elseif(!is_numeric($alias) && $alias != '')
			if($alias = $this->db->getAllDataById('wl_aliases', $alias, 'alias'))
			{
				$where['alias'] = $alias->id;
				$add = '&alias='.$alias->alias;
			}
		if($content > 0)
			$where['content'] = '>0';
		elseif($content < 0)
			$where['content'] = '<0';
		if($_SESSION['language'])
			$where['language'] = $_SESSION['language'];

		if($keywords = $this->db->getAllDataByFieldInArray('wl_ntkd', $where))
		{
			$list = array();
			foreach ($keywords as $keyword) {
				$keyword->keywords = explode(',', $keyword->keywords);
				foreach ($keyword->keywords as $word) {
					if(!isset($list[$word]))
						$list[$word] = '<a href="'.SITE_URL.'search?keyword='.$word.$add.'">'.$word.'</a>';
				}
			}
			if(!empty($list))
				return $list;
		}
		return false;
	}

	public function __get_Search($content = 0)
	{
		return false;
	}

}

?>