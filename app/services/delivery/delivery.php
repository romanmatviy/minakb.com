<?php

/*

 	Service "Delivery 1.1"
	for WhiteLion 1.0

*/

class delivery extends Controller {

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
        if($this->userIs())
        {
            $this->wl_alias_model->setContent();
            $methods = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_methods', 1, 'active');
            $delivery = $this->db->getAllDataById($_SESSION['service']->table.'_users', $_SESSION['user']->id, 'user');
            if(!$delivery)
            {
                $delivery = new stdClass();
                $delivery->id = 0;
                $delivery->method = 0;
                $delivery->address = '';
            }
            $this->load->page_view('user_view', array('delivery' => $delivery, 'methods' => $methods));
        }
    	else
            $this->redirect('login');
    }

    public function __get_Search($content)
    {
    	return false;
    }

    public function __show_user_form()
    {
        $methods = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_methods', 1, 'active');
        $delivery = $this->db->getAllDataById($_SESSION['service']->table.'_users', $_SESSION['user']->id, 'user');
        if(!$delivery)
        {
            $delivery = new stdClass();
            $delivery->id = 0;
            $delivery->method = 0;
            $delivery->address = '';
        }
        $this->load->view('user_view', array('delivery' => $delivery, 'methods' => $methods));
    }

    public function __get_delivery_info($id)
    {
        return  $this->db->select($_SESSION['service']->table.'_carts as d', '*', $id)
                            ->join($_SESSION['service']->table.'_methods', 'name as method_name, site as method_site, department', '#d.method')
                            ->get('single');
    }

    public function __get_Shipping_to_cart()
    {
        if(!empty($_SESSION['user']->id))
        {
            $delivery = $this->db->getAllDataById($_SESSION['service']->table.'_users', $_SESSION['user']->id, 'user');
            if(!$delivery)
            {
                $delivery = new stdClass();
                $delivery->id = 0;
                $delivery->method = 0;
                $delivery->address = '';
                $delivery->receiver = $_SESSION['user']->name;
                $delivery->email = $_SESSION['user']->email;
                if($phones = $this->db->getAllDataByFieldInArray('wl_user_info', array('user' => $_SESSION['user']->id, 'field' => 'phone')))
                    $delivery->phone = $phones[0]->value;
            }
        }
        else
        {
            $delivery = new stdClass();
            $delivery->id = 0;
            $delivery->method = 0;
            $delivery->address = '';
        }

        $methods = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_methods', 1, 'active');
        $warehouselist = file_get_contents (APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'np.json');
        $warehouselist = json_decode ($warehouselist, true);

        $warehouse_by_city = $cities = array();
        foreach($warehouselist['response'] as $warehouse) {
            $cities[] = $warehouse['city'];
            $warehouse_by_city[$warehouse['city']][] = array(
                'city' => $warehouse['city'],  //назва міста
                'address' => preg_replace('/\([^)]+\)/', '', $warehouse['address']), //адрес відділення
                'number' => $warehouse['number'] //номер відділення
            );
        }
        ksort($warehouse_by_city);

        $cities = '"'. implode('","', array_keys($warehouse_by_city)) . '"';

        $this->load->view('__cart_view', array('delivery' => $delivery, 'methods' => $methods, 'warehouse_by_city' => json_encode($warehouse_by_city), 'cities' => $cities));
    }

    public function __set_Shipping_from_cart()
    {
        $data = array();
        $data['user'] = $_SESSION['user']->id;
        $data['method'] = $this->data->post('shipping-method');
        $data['address'] = $this->data->post('shipping-city');
        if($department = $this->data->post('shipping-department'))
            $data['address'] .= ': '.$department;
        if($department = $this->data->post('shipping-department-other'))
            $data['address'] .= ': '.$department;
        if($address = $this->data->post('shipping-address'))
            $data['address'] .= '. '.$address;
        $data['receiver'] = $this->data->post('name');
        $data['phone'] = $this->data->post('phone');

        if($this->data->post('shipping-default') == 1)
        {
            if($default = $this->db->getAllDataById($_SESSION['service']->table.'_users', $_SESSION['user']->id, 'user'))
                $this->db->updateRow($_SESSION['service']->table.'_users', $data, $default->id);
            else
                $this->db->insertRow($_SESSION['service']->table.'_users', $data);
        }

        $data['comment'] = NULL;

        $delivery = array('shipping_alias' => $_SESSION['alias']->id);
        $delivery['shipping_id'] = $this->db->insertRow($_SESSION['service']->table.'_carts', $data);
        $delivery['info'] = $this->__get_delivery_info($delivery['shipping_id']);
        return $delivery;
    }

    public function save()
    {
        if($this->userIs())
        {
            $delivery['method'] = $this->data->post('method');
            $delivery['address'] = $this->data->post('address');
            if($this->data->post('id') == 0)
            {
                $delivery['user'] = $_SESSION['user']->id;
                $this->db->insertRow($_SESSION['service']->table.'_users', $delivery);
            }
            else
            {
                $check = $this->db->getAllDataById($_SESSION['service']->table.'_users', $this->data->post('id'));
                if($check && $check->user == $_SESSION['user']->id) $this->db->updateRow($_SESSION['service']->table.'_users', $delivery, $this->data->post('id'));
            }
            $this->redirect('profile/delivery');
        }
        $this->redirect('login');
    }

}

?>