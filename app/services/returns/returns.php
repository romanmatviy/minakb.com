<?php

class Returns extends Controller {

    private $daysToReturn = 14;
    private $accessStorages = 0; //array(4); // 0 - all storages

    function _remap($method)
    {
        if($this->userCan())
            $this->accessStorages = 0;
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index($uri = false)
    {
        if($this->userIs())
        {
            $this->wl_alias_model->setContent();
            $data = [];
            if($cart_id = $this->data->get('cart'))
                if(is_numeric($cart_id) && $cart_id > 0)
                {
                    $_POST['order'] = $cart_id;
                    $data['cart_id'] = $cart_id;
                    $data['res'] = $this->check(true);
                }
            $this->load->smodel('returns_model');
            if($data['returns'] = $this->returns_model->getList(['user_id' => $_SESSION['user']->id]))
                foreach ($data['returns'] as $return) {
                    $info = $this->load->function_in_alias($return->product_alias, '__get_Product', $return->product_id);
                    $return->product_name = $info->name;
                    $return->product_article = $info->article_show ?? '';
                    $return->product_manufacturer = '';
                    if(!empty($info->options))
                        foreach ($info->options as $key => $option) {
                            $key = explode('-', $key);
                            if($key[1] == 'manufacturer')
                            {
                                $return->product_manufacturer = $option->value;
                                break;
                            }
                        }
                }
            $this->load->profile_view('request_view', $data);
        }
        else
            $this->redirect('login');
    }

    public function request()
    {
        $_SESSION['notify'] = new stdClass();
        if($this->userIs() && $this->data->post('order') && is_numeric($_POST['order']) && $this->data->post('reason'))
        {
            if($order = $this->db->getAllDataById('s_cart', $this->data->post('order')))
            {
                if(($order->user == $_SESSION['user']->id || $this->userCan()) && $order->status == 6)
                {
                    $lastday = time() - 86400*$this->daysToReturn;
                    if($order->date_edit > $lastday)
                    {
                        $data = array();
                        $data['user_id'] = $order->user;
                        $data['cart_id'] = $order->id;
                        $data['status'] = 1;
                        $data['reason'] = $this->data->post('reason');
                        $data['date_add'] = time();
                        $data['date_manage'] = $data['manager'] = $data['updateStorage'] = $data['money'] = $data['date_synchronization'] = 0;
                        $data['ttn'] = $data['info'] = '';

                        if($products = $this->db->getAllDataByFieldInArray('s_cart_products', $order->id, 'cart'))
                        {
                            $this->load->library('mail');
                            foreach ($products as $product) {
                                if((is_array($this->accessStorages) && in_array($product->storage_alias, $this->accessStorages) || $this->accessStorages == 0))
                                {
                                    if($quantity = $this->data->post('product-'.$product->id))
                                    {
                                        $data['product_row_id'] = $product->id;
                                        $data['quantity'] = $quantity;
                                        $template_data = $data;
                                        $template_data['id'] = $this->db->insertRow($_SESSION['service']->table, $data);

                                        $info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                                        $template_data['product_name'] = $info->name;
                                        $template_data['product_article'] = $info->article_show ?? '';
                                        $template_data['product_manufacturer'] = '';
                                        if(!empty($info->options))
                                            foreach ($info->options as $key => $option) {
                                                $key = explode('-', $key);
                                                if($key[1] == 'manufacturer')
                                                {
                                                    $template_data['product_manufacturer'] = $option->value;
                                                    break;
                                                }
                                            }

                                        $this->mail->sendTemplate('notify_manager', SITE_EMAIL, $template_data);
                                    }
                                }
                            }
                            $_SESSION['notify']->success = 'Вашу заявку відправлено адміністрації на перевірку. <br> Якщо заявку буде погоджено, відправте товар та вкажіть ТТН відправлення.';
                            $this->redirect($_SESSION['alias']->alias);
                        }
                    }
                }
            }
        }
        $_SESSION['notify']->errors = 'Повторіть спробу.';
        $this->redirect();
    }

    public function check($return = false)
    {
        $res = array('result' => 'access');
        if($this->userIs() && $this->data->post('order') && is_numeric($_POST['order']))
        {
            if($order = $this->db->getAllDataById('s_cart', $this->data->post('order')))
            {
                if($order->user == $_SESSION['user']->id || $this->userCan())
                {
                    if($order->status == 6)
                    {
                        $lastday = time() - 86400*$this->daysToReturn;
                        if($order->date_edit > $lastday || $this->userCan())
                        {
                            $res['result'] = true;
                            $res['order'] = $order->id;
                            $res['products'] = array();
                            if($products = $this->db->getAllDataByFieldInArray('s_cart_products', $order->id, 'cart'))
                            {
                                foreach ($products as $product) {
                                    $openProduct = new stdClass();
                                    $openProduct->can_return = true;
                                    if((is_array($this->accessStorages) && !in_array($product->storage_alias, $this->accessStorages)) || (is_numeric($this->accessStorages) && $this->accessStorages != 0))
                                        $openProduct->can_return = false;
                                    $accessQuantity = $product->quantity - $product->quantity_returned;
                                    if($accessQuantity == 0)
                                        $openProduct->can_return = false;

                                    $openProduct->id = $product->id;
                                    $openProduct->price = $product->price;
                                    $openProduct->quantity = $product->quantity - $product->quantity_returned;

                                    $info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                                    $openProduct->name = $info->name;
                                    $openProduct->article_show = $info->article_show ?? '';
                                    $openProduct->manufacturer_name = '';
                                    if(!empty($info->options))
                                        foreach ($info->options as $key => $option) {
                                            $key = explode('-', $key);
                                            if($key[1] == 'manufacturer')
                                            {
                                                $openProduct->manufacturer_name = $option->value;
                                                break;
                                            }
                                        }

                                    $res['products'][] = clone $openProduct;
                                }
                            }
                        }
                        else
                            $res['result'] = 'time';
                    }
                    else
                        $res['result'] = 'status';
                }
            }
        }
        if($return)
            return $res;
        $this->load->json($res);
    }

    public function save_ttn()
    {
        $res = array('result' => false);
        if($this->userIs() && $this->data->post('return_id') && is_numeric($_POST['return_id']))
            if($return = $this->db->getAllDataById($_SESSION['service']->table, $this->data->post('return_id')))
                if($return->user_id == $_SESSION['user']->id || $this->userCan())
                    if($return->status == 1 && $return->manager > 0)
                    {
                        $this->db->updateRow($_SESSION['service']->table, ['ttn' => $this->data->post('ttn')], $return->id);
                        $res['result'] = true;
                    }
        $this->load->json($res);
    }
}

?>