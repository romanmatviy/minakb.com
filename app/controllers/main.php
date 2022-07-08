<?php

class Main extends Controller {

    public function index()
    {
        $this->wl_alias_model->setContent();
        $this->load->page_view('index_view');
    }

    public function __get_Search($content = 0)
    {
    	$search = new stdClass();
		$search->id = $_SESSION['alias']->id;
		$search->link = '';
		$search->date = 0;
		$search->author = 1;
		$search->author_name = '';
		$search->additional = false;
		$search->folder = false;
		if(isset($_SESSION['option']->folder))
			$search->folder = $_SESSION['option']->folder;
		return $search;
    }

    public function __get_SiteMap_Links()
    {
        $row = array();
        $row['link'] = '';
        $row['alias'] = $_SESSION['alias']->id;
        $row['content'] = 0;
        return array($row);
    }

}

?>