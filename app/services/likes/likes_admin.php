<?php

class likes_admin extends Controller {
				
    function _remap($method, $data = array())
    {
        $this->wl_alias_model->setContent();
        
        if(isset($_SESSION['alias']->name))
            $_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method))
            return $this->$method($data);
        else
            $this->index($method);
    }

    public function index()
    {   
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('likes_model');
        $this->load->admin_view('index_view', array('likes' => $this->likes_model->getLikesWithData()));
    }

    public function __tab_profile($user_id)
    {   
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('likes_model');
        ob_start();
        $this->load->view('admin/__tab_profile', array('likes' => $this->likes_model->getLikesWithData(array('user' => $user_id))));
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = $_SESSION['alias']->name;
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }

    public function __get_Search($content='')
    {
        return false;
    }

}

?>