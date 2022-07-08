<?php

class returns_admin extends Controller {

    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
            if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
            $this->index($method);
        }
    }

    public function index($id = 0)
    {
        $this->wl_alias_model->setContent();
        $_SESSION['alias']->breadcrumb = array('Повернення' => '');
        $this->load->smodel('returns_model');
        $data = [];
        if(is_numeric($id) && $id > 0)
        {
            $_SESSION['alias']->name = 'Заявка на повернення #'.$id;
            $_SESSION['alias']->breadcrumb = array('Повернення товарів' => '');

            if($return = $this->returns_model->get($id))
            {
                if(is_array($return))
                    $return = $return[0];

                $info = $this->load->function_in_alias($return->product_alias, '__get_Product', $return->product_id);
                $return->product_link = $info->link;
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
                $this->load->admin_view('detal_view', array('return' => $return));
            }
            else
                $this->load->notify_view(array('errors' => 'Заявку на повернення #'.$id.' не знайдено'));
        }
        elseif($id == 0)
        {
            if($data['returns'] = $this->returns_model->getList())
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
            $this->load->admin_view('index_view', $data);
        }
        else
            $this->load->page_404(false);
    }

    public function save()
    {
        $_SESSION['notify'] = new stdClass();
        if($this->data->post('code_hidden') == $this->data->post('code_open') && $this->data->post('code_open') && $this->data->post('id'))
        {
            if($status = $this->data->post('status'))
            {
                $this->load->smodel('returns_model');
                if($order = $this->returns_model->get($this->data->post('id')))
                {
                    if(is_array($order))
                        $order = $order[0];

                    if($order->status == 1)
                    {
                        $template_data = ['cart_id' => $order->cart_id, 'info' => $this->data->post('info')];

                        $info = $this->load->function_in_alias($order->product_alias, '__get_Product', $order->product_id);
                        $product_name = $info->name;
                        $product_article = $info->article_show ?? '';
                        $product_manufacturer = '';
                        if(!empty($info->options))
                            foreach ($info->options as $key => $option) {
                                $key = explode('-', $key);
                                if($key[1] == 'manufacturer')
                                {
                                    $product_manufacturer = $option->value;
                                    break;
                                }
                            }

                        switch ($status) {
                            case 1:
                                $this->db->updateRow($_SESSION['service']->table, array('date_manage' => time(), 'manager' => $_SESSION['user']->id, 'info' => $this->data->post('info')), $order->id);

                                $template_data['title'] = $product_article.' - Повернення дозволено';
                                $template_data['text'] = 'Заявка на повернення <strong>'.$product_manufacturer.' '.$product_article.'</strong> '.$product_name.' <strong>'.$order->quantity.' од.</strong> отримала статус: <strong>Повернення дозволено.</strong> </p><p>Після відправки обов\'язково вкажіть <a href="'.SITE_URL.$_SESSION['alias']->alias.'">ТТН відправлення товару</a>';

                                $_SESSION['notify']->success = 'Встановлено статус: <strong>Повернення дозволено</strong>';
                                break;
                            case 2:
                                $accessQuantity = $order->quantity_buy - $order->quantity_returned;
                                $postQuantity = $this->data->post('quantity');
                                if(is_numeric($postQuantity) && $postQuantity > 0 && $postQuantity <= $accessQuantity)
                                {
                                    $amount = $postQuantity * $order->price;
                                    $notificationMoney = '<strong>$'.$amount.'</strong> повернено готівкою';
                                    $_SESSION['notify']->success = 'Встановлено статус: <strong>Підтверджено</strong>';

                                    $returnsUpdate = array('status' => 2, 'date_manage' => time(), 'manager' => $_SESSION['user']->id);
                                    $returnsUpdate['ttn'] = $this->data->post('ttn');
                                    $returnsUpdate['info'] = $this->data->post('info');

                                    $this->db->updateRow('s_cart_products', array('quantity_returned' => ($order->quantity_returned + $postQuantity)), $order->product_row_id);

                                    $s_cart_history = array('cart' => $order->cart_id, 'status' => 0, 'show' => 1, 'user' => $_SESSION['user']->id, 'date' => time());
                                    $s_cart_history['comment'] = 'Повернення згідно заявки #'.$order->id.': <strong>'.$product_manufacturer.' '.$product_article.'</strong> '.$product_name.' <strong>'.$postQuantity.' од.</strong> на суму <strong>$'.$amount.'</strong>';
                                    $this->db->insertRow('s_cart_history', $s_cart_history);

                                    if($order->payment_alias > 0 && $order->payment_id > 0 && !empty($_POST['moneyTo']))
                                    {
                                        if($_POST['moneyTo'] == 'balance')
                                        {
                                            $returnsUpdate['money'] = 1;

                                            $debit = ['user' => $order->user_id];
                                            $debit['debit'] = $amount;
                                            $debit['info'] = 'Повернення <a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$order->id.'">#'.$order->id.' від '.date('d.m.Y H:i', $order->date_add).'</a>';
                                            $debit['action'] = "Повернення {$postQuantity}од. * \${$order->price} = \${$amount} <br> <strong>{$product_article}</strong> {$product_manufacturer} {$product_name}";
                                            $debit['action_alias'] = $_SESSION['alias']->id;
                                            $debit['action_id'] = $order->id;
                                            $this->load->function_in_alias($order->payment_alias, '__debit', $debit, true);

                                            $notificationMoney = 'Баланс поповнено на <strong>$'.$amount.'</strong>';
                                            $_SESSION['notify']->success .= '. Баланс клієнта поповнено на <strong>$'.$amount.'</strong>';
                                        }
                                        else
                                        {
                                            $returnsUpdate['money'] = 2;

                                            $notificationMoney = '<strong>$'.$amount.'</strong> повернено готівкою';
                                            $_SESSION['notify']->success .= '. Повернення коштів готівкою <strong>$'.$amount.'</strong> Баланс клієнта не змінено';
                                        }
                                    }

                                    $template_data['title'] = $product_article.' - Повернення підтверджено';
                                    $template_data['text'] = 'Заявка на повернення <strong>'.$product_manufacturer.' '.$product_article.'</strong> '.$product_name.' <strong>'.$order->quantity.' од.</strong> отримала статус: <strong>Повернення підтверджено.</strong> </p><p>'.$notificationMoney;


                                    if($order->storage_alias && $order->storage_invoice)
                                        if($this->data->post('toStorage') == 1)
                                        {
                                            if($inStorage = $this->db->getAllDataById('s_shopstorage_products', $order->storage_invoice))
                                            {
                                                $this->db->updateRow('s_shopstorage_products', array('amount' => $inStorage->amount + $postQuantity, 'manager_edit' => $_SESSION['user']->id, 'date_edit' => time()), $inStorage->id);
                                            }
                                            else if($inStorage = $this->db->getAllDataByFieldInArray('s_shopstorage_products', array('storage' => $order->storage_alias, 'product' => $order->product_id)))
                                            {
                                                $this->db->updateRow('s_shopstorage_products', array('amount' => $inStorage[0]->amount + $postQuantity, 'manager_edit' => $_SESSION['user']->id, 'date_edit' => time()), $inStorage[0]->id);
                                            }
                                            else
                                            {
                                                $toStorage = array('storage' => $order->storage_alias, 'product' => $order->product_id);
                                                $toStorage['price_in'] = $order->price_in;
                                                if($order->price_in == 0)
                                                    $toStorage['price_in'] = $order->price;
                                                $toStorage['price_out'] = $toStorage['amount_reserved'] = $toStorage['date_in'] = $toStorage['date_out'] = 0;
                                                $toStorage['amount'] = $postQuantity;
                                                $toStorage['manager_add'] = $toStorage['manager_edit'] = $_SESSION['user']->id;
                                                $toStorage['date_add'] = $toStorage['date_edit'] = time();
                                            }
                                            $returnsUpdate['updateStorage'] = $postQuantity;
                                            $_SESSION['notify']->success .= '<br>На склад зараховано <strong>'.$postQuantity.' од.</strong>';
                                        }
                                    
                                    $this->db->updateRow($_SESSION['service']->table, $returnsUpdate, $order->id);
                                }
                                else
                                    $_SESSION['notify']->error = 'Доступна кількість до повернення <strong>'.$quantity.' од.</strong>';
                                break;
                            
                            case 3:
                                $this->db->updateRow($_SESSION['service']->table, array('status' => 3, 'date_manage' => time(), 'manager' => $_SESSION['user']->id, 'info' => $this->data->post('info')), $order->id);

                                $template_data['title'] = $product_article.' - Повернення cкасовано/відмова';
                                $template_data['text'] = 'Заявка на повернення <strong>'.$product_manufacturer.' '.$product_article.'</strong> '.$product_name.' <strong>'.$order->quantity.' од.</strong> отримала статус: <strong>Повернення cкасовано/відмова.</strong>';

                                $_SESSION['notify']->success = 'Встановлено статус замовлення: <strong>Скасовано</strong>';
                                break;
                        }

                        $this->load->library('mail');
                        $this->mail->sendTemplate('notify_user', SITE_EMAIL, $template_data);
                    }
                    else
                    {
                        $_SESSION['notify']->error = 'Поточний статус замовлення: <strong>';
                        if($order->status == 2)
                            $_SESSION['notify']->error .= "Підтверджено";
                        else
                            $_SESSION['notify']->error .= "Скасовано";
                        $_SESSION['notify']->error .= "</strong>. Внести зміни можна лише для замовлень зі статусом <strong>Очікує обробки</strong>.";
                    }
                }
                else
                    $_SESSION['notify']->error = 'Невірний номер замовлення. Зверніться до розробників';
            }
        }
        else
            $_SESSION['notify']->error = 'Невірний код безпеки';
        $this->redirect();
    }

    public function __sidebar($alias)
    {
        $alias->counter = $this->db->getCount($_SESSION['service']->table, ['status' => 1, '&' => "`manager` = 0 || `ttn` != ''"]);
        return $alias;
    }

    public function __tab_profile($user_id)
    {   
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 30;
        $this->load->smodel('returns_model');
        ob_start();
        if($returns = $this->returns_model->getList(['user_id' => $user_id]))
            foreach ($returns as $return) {
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
        $this->load->view('admin/__tab_profile', ['returns' => $returns]);
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = 'Повернення';
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }

}

?>