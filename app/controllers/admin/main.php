<?php

class main_admin extends Controller {
				
    function _remap($method, $data = array())
    {
        $_SESSION['alias']->breadcrumb = array('Головна сторінка' => '');
        if (method_exists($this, $method))
            return $this->$method($data);
        else
            $this->index($method);
    }

    public function index()
    {
        $this->wl_alias_model->setContent();
        $this->load->admin_view('main_view');
    }

    public function __get_Search($content)
    {
        $search = new stdClass();
        $search->id = $_SESSION['alias']->id;
        $search->link = '';
        $admin = $this->db->getAllData('wl_users', 'id ASC LIMIT 1');
        if($admin)
        {
            $search->date = $admin[0]->registered;
            $search->author = $admin[0]->id;
            $search->author_name = $admin[0]->name;
        }
        else
        {
            $search->date = time();
            $search->author = $_SESSION['user']->id;
            $search->author_name = $_SESSION['user']->name;
        }
        $search->additional = false;
        $search->folder = false;
        if(isset($_SESSION['option']->folder))
            $search->folder = $_SESSION['option']->folder;
        $search->edit_link = 'admin/'.$_SESSION['alias']->alias;
        return $search;
    }
	
}

?>