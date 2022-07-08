<?php 

class search_admin extends Controller {

	public function index()
	{
		$_SESSION['alias']->name = 'Пошук';
        $_SESSION['alias']->breadcrumb = array('Пошук' => '');

		$data = array();
		$current = 0;
		$this->load->library('validator');
		$this->validator->setRules($this->text('search text'), $this->data->get('by'), 'required|3..100');
		if($this->validator->run())
		{
			$this->load->model('wl_search_model');
			$search_data = $this->wl_search_model->get($this->data->get('by'), true);
			if($search_data)
			{
				$per_page = 0;
				$start = 1;
				if($this->data->get('page') > 1 && isset($_SESSION['option']->paginator_per_page)) {
					$start = $this->data->get('page') * $_SESSION['option']->paginator_per_page;
				}

				$find = array();
				foreach ($search_data as $search)
				{
					if(isset($find[$search->alias_id]))
					{
						if(in_array($search->content, $find[$search->alias_id]))
							continue;
						else
							$find[$search->alias_id][] = $search->content;
					}
					else
					{
						$find[$search->alias_id] = array();
						$find[$search->alias_id][] = $search->content;
					}
					$result = $this->load->function_in_alias($search->alias_id, '__get_Search', $search->content, true);
					if($result)
					{
						$current++;
						$result->name = $search->name;
						$result->list = $search->list;
						$result->text = $search->text;
						$result->image = false;
						if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
						{
							if($current >= $start && $current < ($start + $_SESSION['option']->paginator_per_page))
							{
								if(isset($result->folder) && $result->folder)
									$result->image = $this->wl_search_model->getImage($search->alias_id, $search->content, $result->folder);
								array_push($data, $result);
							}
						}
						else
						{
							if($result->folder)
								$result->image = $this->wl_search_model->getImage($search->alias_id, $search->content, $result->folder);
							array_push($data, $result);
						}
					}
				}
			}
		} else {
			@$_SESSION['notify']->errors = $this->validator->getErrors();
		}
		@$_SESSION['option']->paginator_total = $current;
		$this->load->admin_view('search_view', array('data' => $data));
	}

}

?>