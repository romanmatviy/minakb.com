<?php

/*

 	Service "FAQ 1.2"
	for WhiteLion 1.0

*/

class faq extends Controller {
				
    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        } 
    }

    public function index($uri)
    {
    	$this->wl_alias_model->setContent();
    	$this->load->smodel('faq_model');
		$faqs = $this->faq_model->getQuestions();
		$groups = ($_SESSION['option']->useGroups) ? $this->faq_model->getGroups() : NULL;
		$this->load->page_view('index_view', array('faqs' => $faqs, 'groups' => $groups));
    }

    public function __get_Search($content)
    {
    	$this->load->smodel('faq_search_model');
    	return $this->faq_search_model->getByContent($content);
    }

    public function __get_SiteMap_Links()
    {
        $data = $row = array();
        $row['link'] = $_SESSION['alias']->alias;
        $row['alias'] = $_SESSION['alias']->id;
        $row['content'] = 0;
        // $row['code'] = 200;
        // $row['data'] = '';
        // $row['time'] = time();
        // $row['changefreq'] = 'daily';
        // $row['priority'] = 5;
        $data[] = $row;

        $this->load->smodel('faq_model');
        if($faqs = $this->faq_model->getQuestions())
        	foreach ($faqs as $faq)
            {
            	$row['link'] = $faq->link;
            	$row['content'] = $faq->id;
            	$data[] = $row;
            }

        return $data;
    }

	public function __get_Questions($data = array())
	{
		$group = (isset($data['group']) && is_numeric($data['group'])) ? $data['group'] : 0;
		if(isset($data['limit']) && is_numeric($data['limit']))
            $_SESSION['option']->PerPage = $data['limit'];

		$this->load->smodel('faq_model');
		return $this->faq_model->getQuestions($group);
	}

	public function __get_Groups()
	{
		$this->load->smodel('faq_model');
		return $this->faq_model->getGroups();
	}
	
}

?>