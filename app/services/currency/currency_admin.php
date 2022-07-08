<?php

/*

 	Service "Currency 2.3"
	for WhiteLion 1.3

*/

class currency_admin extends Controller {
				
    function _remap($method, $data = array())
    {
        $this->wl_alias_model->setContent();
    	$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index($uri)
    {
    	$currents = $this->db->getAllData($_SESSION['service']->table);
	    $this->load->admin_view('index_view', array('currents' => $currents));
    }

    public function add()
    {
        $this->load->admin_view('add_view');
    }

    public function history()
    {
        $this->load->admin_view('history_view');
    }

    public function save()
    {
        $this->load->smodel('currency_model');
        if($this->data->post('id') == 0)
            $this->currency_model->create();
        elseif($this->data->post('id') > 0)
            $this->currency_model->update($this->data->post('id'));
        $this->__clear_cache();
        
        if(!empty($_POST['json']))
            $this->load->json(['success' => true]);
        else
            $this->redirect('admin/'.$_SESSION['alias']->alias);
    }

    public function updatePrivat24()
    {
        $this->load->smodel('currency_model');
        $this->currency_model->updatePrivat24();
        $this->__clear_cache();
        
        $this->redirect('admin/'.$_SESSION['alias']->alias);
    }

    public function delete()
    {
        $this->load->smodel('currency_model');
        if($id = $this->data->post('id'))
            $this->currency_model->delete($id);
        $this->__clear_cache();
        
        if(!empty($_POST['json']))
            $this->load->json(['success' => true]);
        else
            $this->redirect('admin/'.$_SESSION['alias']->alias);
    }

    public function __clear_cache($not_redirect = true)
    {
        if(isset($_SESSION['__page_before_init'][$_SESSION['alias']->id]))
            $_SESSION['__page_before_init'][$_SESSION['alias']->id] = 0;
        $this->db->cache_delete($_SESSION['alias']->alias, 'wl_aliases');
        $this->db->cache_delete_all();

        if(!$not_redirect)
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Кеш очищено';
            $this->redirect();
        }
    }
	
}

?>