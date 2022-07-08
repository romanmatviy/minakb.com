<?php

class static_pages extends Controller {
				
    function _remap($method, $data = array())
    {
        if (method_exists($this, $method))
        {
            if(empty($data))
                $data = null;
            return $this->$method($data);
        }
        else
            $this->index($method);
    }

    public function index()
    {
        $this->wl_alias_model->setContent();
        $this->load->library('video');
        $this->video->makeVideosInText();
        
        $this->load->smodel('static_page_model');
        $page = $this->static_page_model->get();
        
        $this->load->page_view('index_view', array('page' => $page));
    }

    public function __get_SiteMap_Links()
    {
        $row = array();
        $row['link'] = $_SESSION['alias']->alias;
        $row['alias'] = $_SESSION['alias']->id;
        $row['content'] = 0;
        return array($row);
    }

    public function __get_Search($content)
    {
        $this->load->smodel('static_pages_search_model');
        return $this->static_pages_search_model->getByContent($content);
    }

    public function __get_PageData()
    {
        $this->wl_alias_model->setContent();
        $this->load->library('video');
        $this->video->makeVideosInText();

        return clone $_SESSION['alias'];
    }
	
}