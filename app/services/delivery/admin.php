<?php

/*

 	Service "Delivery 1.1"
	for WhiteLion 1.0

*/

class delivery extends Controller {
				
    function _remap($method, $data = array())
    {
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
        if(is_numeric($uri))
        {
            $delivery = $this->db->getAllDataById($_SESSION['service']->table.'_methods', $uri);
            if($delivery)
                $this->load->admin_view('edit_view', array('delivery' => $delivery));
            else
                $this->load->page_404();
        }
        else
        {
            $delivery = $this->db->getAllData($_SESSION['service']->table.'_methods');
            $this->load->admin_view('index_view', array('delivery' => $delivery));
        }
    }

    public function add()
    {
        $this->load->admin_view('add_view');
    }

    public function save()
    {
        $this->load->smodel('delivery_model');
        if($this->data->post('id') == 0)
            $this->delivery_model->method_add();
        elseif($this->data->post('id') > 0)
            $this->delivery_model->method_update($this->data->post('id'));
        $this->redirect('admin/'.$_SESSION['alias']->alias);
    }
	
}

?>