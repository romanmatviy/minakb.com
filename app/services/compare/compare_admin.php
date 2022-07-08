<?php

class compare_admin extends Controller {
				
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
        $this->load->smodel('compare_model');
        $where = isset($_GET['all']) ? [] : ['status' => 1];
        $this->load->admin_view('index_view', array('likes' => $this->compare_model->getLikesWithData($where)));
    }

    public function save_group_by()
    {
        if($_SESSION['user']->type == 1 && $this->data->post('service'))
        {
            $value = $this->data->post('groupBy');
            $where = array('alias' => $_SESSION['alias']->id, 'name' => 'groupBy');
            $where['service'] = $this->data->post('service');
            if($option = $this->db->getAllDataById('wl_options', $where))
            {
                if($option->value != $value)
                    $this->db->updateRow('wl_options', array('value' => $value), $option->id);
            }
            else
            {
                $where['value'] = $value;
                $this->db->insertRow('wl_options', $where);
            }

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Групування товарів при порівнянні оновлено';
            unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);
            $this->db->cache_delete($_SESSION['alias']->alias, 'wl_aliases');
        }
        $this->redirect();
    }

    public function __tab_profile($user_id)
    {   
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('compare_model');
        ob_start();
        $this->load->view('admin/__tab_profile', array('likes' => $this->compare_model->getLikesWithData(array('user' => $user_id))));
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