<?php if (!defined('SYS_PATH')) exit('Access denied');

class comments extends Controller
{

	public $showAddForm = true;
	public $image_name = false;
	
	public function show($content = -1, $alias = -1)
	{
		$this->load->model("wl_comments_model");

		$where = array('status' => '<3', 'parent' => 0);
		if($alias == -1)
			$where['alias'] = $alias = $_SESSION['alias']->id;
		elseif($alias > 0 && is_numeric($alias))
			$where['alias'] = $alias;

		if(isset($where['alias']))
		{
			if($content == -1)
				$where['content'] = $content = $_SESSION['alias']->content;
			elseif($content >= 0 && is_numeric($content))
				$where['content'] = $content;
		}
		$this->wl_comments_model->get_wl_sitemap = false;
		$comments = $this->wl_comments_model->get($where);

		$showAddForm = $this->showAddForm;
		$image_name = $this->image_name;
		include APP_PATH."views/@wl_comments/index_view.php";
	}

}

 ?>