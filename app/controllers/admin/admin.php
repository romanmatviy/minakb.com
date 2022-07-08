<?php

class admin extends Controller {
                
    public function _remap($method, $data = array())
    {
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
        {
            if(empty($data)) $data = null;
            return $this->$method($data);
        }
        else
            $this->index($method);
    }

    public function index()
    {
        $_SESSION['alias']->name = 'Панель керування';
        $_SESSION['alias']->table = $_SESSION['alias']->text = '';
        $_SESSION['alias']->js_load = $_SESSION['alias']->js_init = $_SESSION['alias']->breadcrumb = array();

        $alias = $this->data->uri(1);
        if($alias != '')
        {
            $alias = $this->db->getAllDataById('wl_aliases', $alias, 'alias');
            if(is_object($alias))
            {
                $_SESSION['alias'] = clone $alias;
                $this->load->model('wl_alias_model');
                $this->wl_alias_model->init($alias->alias);
                if($alias->service > 0)
                {
                    $function = 'index';
                    if($this->data->uri(2) != '')
                        $function = $this->data->uri(2);
                    $this->load->function_in_alias($alias->alias, $function, array(), true);
                }
                $_SESSION['alias']->id = 0;
                $_SESSION['alias']->alias = 'admin';
                $_SESSION['alias']->table = '';
                $_SESSION['alias']->service = '';
            }
            else
                $this->load->page_404();
        }
        else
        {
            if($options = $this->db->getAllDataByFieldInArray('wl_options', array('service' => 0, 'alias' => 0)))
                foreach($options as $opt) {
                    $key = $opt->name;
                    @$_SESSION['option']->$key = $opt->value;
                }

            $this->load->admin_view('index_view');
        }
    }

    public function page_403()
    {
        $_SESSION['alias'] = new stdClass();
        $_SESSION['alias']->id = -1;
        $_SESSION['alias']->name = '403 Forbidden';
        $_SESSION['alias']->alias = 'admin403';
        $_SESSION['alias']->service = false;
        $_SESSION['alias']->table = $_SESSION['alias']->text = '';
        $_SESSION['alias']->js_load = $_SESSION['alias']->js_init = $_SESSION['alias']->breadcrumb = array();

        if($options = $this->db->getAllDataByFieldInArray('wl_options', array('service' => 0, 'alias' => 0)))
            foreach($options as $opt) {
                $key = $opt->name;
                @$_SESSION['option']->$key = $opt->value;
            }
        header('HTTP/1.0 403 Forbidden');
        $this->load->admin_view('403_view');
    }
    
}

?>