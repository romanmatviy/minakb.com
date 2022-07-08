<?php

class static_pages_admin extends Controller {
				
    function _remap($method, $data = array())
    {
        if(isset($_SESSION['alias']->name))
            $_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method))
            return $this->$method($data);
        else
            $this->index($method);
    }

    public function index()
    {
        $this->wl_alias_model->setContent();
    	$this->load->smodel('static_page_model');
        $this->load->admin_view('index_view', array('page' => $this->static_page_model->get()));
    }

    public function __get_Search($content)
    {
        $this->load->smodel('static_pages_search_model');
        return $this->static_pages_search_model->getByContent($content, true);
    }
	
}

?>