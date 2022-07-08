<?php

class cart_admin extends Controller {

    private $notify_client_sms_on_delivered = false;

    function _remap($method, $data = array())
    {
        $_SESSION['alias']->breadcrumb = array('Корзина' => '');
        $_SESSION['alias']->title = $_SESSION['alias']->name = 'Корзина';
                
        if(isset($_SESSION['alias']->name))
            $_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method))
        {
            if(empty($data)) $data = null;
            return $this->$method($data);
        }
        else
            $this->index($method);
    }

    public function index($id)
    {
        $this->load->smodel('cart_model');

        if(is_numeric($id))
        {
            if($cart = $this->cart_model->getById($id))
            {
                $_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Замовлення #'.$id => '');
                $_SESSION['alias']->name .= '. Замовлення #'.$id.' від '.date('d.m.Y H:i', $cart->date_add);
                $cart->totalFormat = $cart->total;
                $cart->subTotal = $cart->subTotalFormat = $cart->shippingPrice = $cart->shippingPriceFormat = 0;
                $cart->shipping = $cart->payment = $cart->paymentsMethod = false;

                if($cart->shipping_id && !empty($cart->shipping_info))
                {
                    $shipping_info = @unserialize($cart->shipping_info);
                    if(is_array($shipping_info))
                        $cart->shipping_info = $shipping_info;
                    if($cart->shipping = $this->cart_model->getShippings(array('id' => $cart->shipping_id)))
                    {
                        $cart->shipping->text = '';
                        if($cart->shipping->wl_alias && is_array($shipping_info))
                        {
                            $cart->shipping->text = $this->load->function_in_alias($cart->shipping->wl_alias, '__get_info', $cart->shipping_info);
                        }
                        elseif(!empty($cart->shipping_info))
                        {
                            foreach(['country' => 'Країна', 'city' => 'Місто', 'department' => 'Відділення', 'address' => 'Адреса'] as $field_key => $field_name)
                            {
                                if(!empty($cart->shipping_info[$field_key]))
                                {
                                    $cart->shipping->text .= "{$field_name}: <strong>{$cart->shipping_info[$field_key]}</strong><br>";
                                }
                            }
                        }
                    }
                    if(!empty($cart->shipping_info['price']))
                        $cart->shippingPrice = $cart->shipping_info['price'];
                }

                if($cart->products)
                {
                    $shop_alias = $cart->products[0]->product_alias;
                    if(empty($shop_alias))
                    {
                        if($row = $this->db->select('s_cart_products', 'product_alias', ['product_alias' => '>0'])->limit(1)->get())
                            $shop_alias = $row->product_alias;
                    }
                    foreach ($cart->products as $product) {
                        if($product->product_alias)
                        {
                            $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                            if($product->storage_invoice)
                                $product->storage = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', array('id' => $product->storage_invoice, 'user_type' => $cart->user_type));
                            $product->price_format =  $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                            $cart->subTotal += $product->price * $product->quantity + $product->discount;
                            $product->sum_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity);
                            if($product->discount)
                            {
                                $product->sumBefore_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity + $product->discount);
                                $product->discountFormat = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->discount);
                            }
                        }
                        else if(!empty($product->product_options))
                        {
                            $options = unserialize($product->product_options);
                            $product->info = new stdClass();
                            $product->info->id = $product->id;
                            $product->info->photo = $options['photo'];
                            $product->info->cart_photo = $product->info->admin_photo = $options['cart_photo'] ?? '';
                            $product->info->article = $product->info->article_show = $options['article'] ?? '';
                            $product->info->name = $options['name'];
                            $product->info->link = $options['photo'] ?? '';
                            $cart->subTotal += $product->price * $product->quantity + $product->discount;
                            if($shop_alias)
                            {
                                $product->price_format = $this->load->function_in_alias($shop_alias, '__formatPrice', $product->price);
                                $product->sum_format = $this->load->function_in_alias($shop_alias, '__formatPrice', $product->price * $product->quantity);
                            }
                            else
                            {
                                $product->price_format = $product->price;
                                $product->sum_format = $product->price * $product->quantity;
                            }
                            $product->product_options = false;
                        }
                    }

                    $cart->subTotalFormat = $cart->subTotal;
                    $cart->discountFormat = $cart->discount;
                    $cart->shippingPriceFormat = $cart->shippingPrice;
                    $cart->totalFormat = $cart->total;
                    $cart->payedFormat = $cart->payed;
                    if($shop_alias)
                    {
                        $cart->subTotalFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->subTotal);
                        
                        if($cart->discount)
                            $cart->discountFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->discount);
                        
                        if($cart->shippingPrice)
                            $cart->shippingPriceFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->shippingPrice);

                        $cart->totalFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->total);

                        if($cart->payed > 0)
                            $cart->payedFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->payed);
                    }
                }
                
                if($cart->payment_alias && $cart->payment_id)
                    $cart->payment = $this->load->function_in_alias($cart->payment_alias, '__get_info', $cart->payment_id);
                else if($cart->payment_id)
                    $cart->payment = $this->cart_model->getPayments(array('id' => $cart->payment_id))[0];

                $_SESSION['alias']->title = $_SESSION['alias']->name;
                if($this->data->uri(3) == 'print')
                {
                    if(isset($_GET['go']))
                        $this->load->view('print_view', array('cart' => $cart));
                    else
                        $this->load->admin_view('print_view', array('cart' => $cart));
                }
                else
                {
                    if($cart->payed < $cart->total)
                        $cart->paymentsMethod = $this->cart_model->getPayments(array('active' => 1));
                    $cartStatuses = false;
                    if($cart->status_weight < 90)
                        $cartStatuses = $this->db->getQuery("SELECT * FROM `s_cart_status` WHERE `active` = 1 AND `weight` > (SELECT weight FROM `s_cart_status` WHERE id = $cart->status ) ORDER BY weight", 'array');

                    $this->load->admin_view('detal_view', array('cart' => $cart, 'cartStatuses' => $cartStatuses));
                }
            }
            else
                $this->load->page_404(false);
        }
        else
        {
            $_SESSION['option']->paginator_per_page = 25;

            $carts = false;
            if(!empty($_GET['id']))
            {
                if($cart = $this->db->getAllDataById($this->cart_model->table(), $this->data->get('id')))
                    $this->load->redirect('admin/'.$_SESSION['alias']->alias.'/'.$cart->id);
            }
            elseif(!empty($_GET['article']))
            {
                $ids_search = $list = [];
                $where = ['alias2' => $_SESSION['alias']->id, 'type' => 'cart'];
                if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $where))
                    foreach ($cooperation as $shop)
                        if($products = $this->load->function_in_alias($shop->alias1, '__get_Products', array('article' => '%'.$this->data->get('article'))))
                            foreach ($products as $product)
                            {
                                $ids_search[] = $product->id;
                                $list[$product->id] = clone $product;
                            }
                if(!empty($ids_search))
                {
                    $where = [];
                    if(isset($_GET['status']) && is_numeric($_GET['status']) && $_GET['status'] > 0)
                        $where = array('status' => $_GET['status']);
                    if($carts = $this->cart_model->getCartsByProducts($ids_search, $where))
                        foreach ($carts as $cart) {
                            if($cart->products)
                                foreach ($cart->products as $product) {
                                    $product->info = $list[$product->product_id] ?? false;
                                }
                        }
                }
                $this->load->admin_view('index_view', array('carts' => $carts));
            }
            else
            {
                $where = [];
                if(isset($_GET['user']) && is_numeric($_GET['user']) && $_GET['user'] > 0)
                    $where['user'] = $_GET['user'];
                if(isset($_GET['manager']) && is_numeric($_GET['manager']) && $_GET['manager'] >= 0)
                    $where['manager'] = $_GET['manager'];
                if(isset($_GET['status']) && is_numeric($_GET['status']) && $_GET['status'] > 0)
                    $where['status'] = $_GET['status'];
                if(isset($_GET['pay']) && $_GET['pay'] == 'part')
                {
                    $where['payed'] = '<total';
                    $where['+payed'] = '>0';
                }
                if(isset($_GET['pay']) && $_GET['pay'] == 'full')
                    $where['payed'] = '#total';
                if(isset($_GET['pay']) && $_GET['pay'] == 'null')
                    $where['payed'] = '0';
                if(!empty($_GET['day']))
                {
                    $from = strtotime($this->data->get('day'));
                    if(is_numeric($from))
                    {
                        $to = $from + 86400;
                        if(!empty($_GET['dayTo']))
                        {
                            $dayTo = strtotime($this->data->get('dayTo'));
                            if(is_numeric($dayTo) && $dayTo > $from)
                                $to = $dayTo;
                        }
                        $where['date_add'] = '>='.$from;
                        $where['+date_add'] = '<'.$to;
                    }
                    
                }
                $carts = $this->cart_model->getCarts($where);
                if($carts)
                    foreach ($carts as $cart) {
                        $cart->totalFormat = $cart->total;
                        if($cart->products)
                        {
                            if ($cart->products[0]->product_alias)
                                $cart->totalFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->total);
                            foreach ($cart->products as $product) {
                                if($product->product_alias)
                                    $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                                else if(!empty($product->product_options))
                                {
                                    $options = unserialize($product->product_options);
                                    $product->info = new stdClass();
                                    $product->info->id = $product->id;
                                    $product->info->photo = $options['photo'];
                                    $product->info->cart_photo = $product->info->admin_photo = $options['cart_photo'];
                                    $product->info->article = $product->info->article_show = $options['article'];
                                    $product->info->name = $options['name'];
                                    $product->info->link = $options['photo'] ?? '';
                                    $product->price_format = $product->price;
                                    $product->sum_format = $product->price * $product->quantity;
                                    $product->product_options = false;
                                }
                                break;
                            }
                        }
                    }
            }
            $this->load->admin_view('index_view', array('carts' => $carts));
        }
    }

    public function add()
    {
        $_SESSION['alias']->breadcrumb = array('Корзина' => 'admin/'.$_SESSION['alias']->alias, 'Додати покупку' => '');
        $_SESSION['alias']->name = 'Корзина. Додати покупку';
        $this->load->admin_view('add_view');
    }

    public function add_virtualProduct()
    {
        $time = time();
        $cart_id = $this->data->post('cart_id');
        if(empty($cart_id))
            if($user_id = $this->data->post('user_id'))
            {
                $product_price = $this->data->post('product-price');
                $product_quantity = $this->data->post('product-quantity');
                $cart_id = $this->db->insertRow('s_cart', array('user' => $user_id, 'total' => round($product_price * $product_quantity, 3), 'status' => 0, 'date_add' => $time, 'date_edit' => $time));
            }
        if($cart_id)
        {
            $data = ['cart' => $cart_id,
                        'user' => $_SESSION['user']->id,
                        'active' => 1,
                        'product_alias' => 0,
                        'product_id' => 0,
                        'storage_alias' => 0,
                        'storage_invoice' => 0,
                        'discount' => 0,
                        'bonus' => 0,
                        'quantity_returned' => 0,
                        'date' => $time];
            $data['price'] = $data['price_in'] = $this->data->post('product-price');
            $data['quantity'] = $data['quantity_wont'] = $this->data->post('product-quantity');
            $product_options = ['photo' => false, 'cart_photo' => false,
                                    'article' => $this->data->post('product-article'),
                                    'name' => $this->data->post('product-name')];
            $name_field = 'product-image';
            if(!empty($_FILES[$name_field]['name']))
            {
                $path = IMG_PATH;
                $path = substr($path, strlen(SITE_URL));
                $path = substr($path, 0, -1);
                if(!is_dir($path))
                    mkdir($path, 0777);
                $path .= '/'.$_SESSION['alias']->alias;
                if(!is_dir($path))
                    mkdir($path, 0777);
                $f_100 = ceil($cart_id / 100) * 100;
                $path .= '/'.$f_100;
                if(!is_dir($path))
                    mkdir($path, 0777);
                $path .= '/';
                $sub_path = $_SESSION['alias']->alias.'/'.$f_100.'/';

                $name = $_FILES[$name_field]['name'];
                $n = explode('.', $name);
                if (count($n) > 1) {
                    array_pop($n);
                    $name = implode('.', $n);
                }
                $name = $cart_id .'-'.$data['date'].'-'.$this->data->latterUAtoEN(trim($name));

                $this->load->library('image');
                if($this->image->upload($name_field, $path, $name))
                {
                    $extension = $this->image->getExtension();
                    $this->image->save();

                    $product_options['photo'] = IMG_PATH.$sub_path.$name.'.'.$extension;

                    if($this->image->loadImage($path, $name, $extension))
                    {
                        $this->image->preview(128, 128);
                        $this->image->save('cart');

                        $product_options['cart_photo'] = $sub_path.'cart_'.$name.'.'.$extension;
                    }
                }
            }
            $data['product_options'] = serialize($product_options);

            $this->db->insertRow('s_cart_products', $data);
            $this->db->insertRow('s_cart_history', ['cart' => $cart_id, 'status' => 0, 'show' => 0, 'user' => $_SESSION['user']->id, 'comment' => "Додано віртуальний товар <strong>{$product_options['article']}</strong> {$product_options['name']}", 'date' => $data['date']]);
            $this->_updateTotal($cart_id);
        }
        $this->redirect("admin/{$_SESSION['alias']->alias}/{$cart_id}");
    }

    public function searchClient()
    {
        if($by = $this->data->post('by'))
        {
            $where = ['name' => '%'.$by];
            if(is_numeric($by) && $by > 0)
                $where = $by;
            $this->db->select('wl_users as u', 'id, name', $where);
            $this->load->json(['data' => $this->db->get('array')]);
        }
        $this->load->json(false);
    }

    private function getInvoicesByProduct($alias, $id)
    {
        $cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $alias, 'alias1');
        $productInvoices = array();
        if($cooperation)
        {
            foreach ($cooperation as $storage) {
                if($storage->type == 'storage')
                {
                    $invoices = $this->load->function_in_alias($storage->alias2, '__get_Invoices_to_Product', $id);

                    if($invoices)
                    {
                        foreach ($invoices as $invoice) {
                            $productInvoices[] = $invoice;
                        }
                    }
                }
            }
        }

        if(empty($productInvoices)) return false;

        return $productInvoices;
    }

    public function updateproductoptions()
    {
        if($cartId = $this->data->post('cart'))
        {
            if($cart = $this->db->getAllDataById('s_cart', $cartId))
            {
                if($product = $this->db->getAllDataById('s_cart_products', $this->data->post('productRow')))
                {
                    if($product->cart == $cartId)
                    {
                        $price = -1;
                        $list = $changePrice = array();
                        foreach ($_POST as $row => $rowValue) {
                            $row = explode('-', $row);
                            if(count($row) == 2 && $row[0] == 'option' && is_numeric($row[1]) && is_numeric($rowValue))
                            {
                                if($info = $this->load->function_in_alias($product->product_alias, '__get_Option_Info', $row[1]))
                                {
                                    $my_option = new stdClass();
                                    $my_option->id = $info->id;
                                    $my_option->changePrice = $info->changePrice;
                                    $my_option->name = $info->name;
                                    $my_option->value_id = $rowValue;
                                    $my_option->value_name = '';
                                    if(!empty($info->values) && $rowValue)
                                        foreach ($info->values as $value) {
                                            if($value->id == $rowValue)
                                            {
                                                $my_option->value_name = $value->name;
                                                break;
                                            }
                                        }
                                    if(isset($info->changePrice) && $info->changePrice)
                                        $changePrice[$info->id] = $rowValue;
                                    $list[] = $my_option;
                                }
                            }
                        }

                        if(!empty($changePrice))
                            $price = $this->load->function_in_alias($product->product_alias, '__get_Price_With_options', array('product' => $product->product_id, 'options' => $changePrice));

                        $update = array();
                        $product_options = serialize($list);
                        if($product->product_options != $product_options)
                            $update['product_options'] = $product_options;
                        if($product->price != $price && $price >= 0)
                            $update['price'] = $price;
                        if(!empty($update))
                        {
                            $this->db->updateRow('s_cart_products', $update, $product->id);
                            if($product->price != $price && $price >= 0)
                                $this->_updateTotal($cart);
                        }
                    }
                }
            }
        }
        $this->redirect();
    }

    public function remove()
    {
        $res = array('result' => false, 'total' => 0);
        if($id = $this->data->post('id'))
            if($product = $this->db->getAllDataById("s_cart_products", $id))
            {
                $this->db->deleteRow("s_cart_products", $id);

                if($product->storage_alias && $product->storage_invoice)
                {
                    $reserve = array('invoice' => $product->storage_invoice, 'amount' => -$product->quantity);
                    $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                }
                $res['total'] = $this->_updateTotal($product->cart);

                if(!empty($_POST['toHistory']))
                {
                    $toHistory = array();
                    $toHistory['cart'] = $product->cart;
                    $toHistory['show'] = 1;
                    $toHistory['user'] = $_SESSION['user']->id;
                    $toHistory['comment'] = $this->data->post('toHistory');
                    $toHistory['date'] = time();
                    $this->db->insertRow('s_cart_history', $toHistory);
                }

                $res['result'] = true;
            }

        $this->json($res);
    }

    public function showProductInvoices()
    {
        if($this->data->post('alias') && $this->data->post('product')){
            $alias = $this->data->post('alias');
            $product = $this->data->post('product');
            $userType = $this->data->post('userType');

            $invoice_where = array('id' => $product, 'user_type' => $userType);
            $res = $this->getInvoicesByProduct($alias, $invoice_where);

            $this->json($res);
        }
    }

    public function changeProductInvoice()
    {
        $res = array('result' => false);
        if($row_id = $this->data->post('row_id'))
            if($cart_product = $this->db->select('s_cart_products as cp', 'cart, product_alias, product_id, storage_alias, storage_invoice, price, quantity', $row_id)
                                    ->join('wl_users', 'type as user_type', '#cp.user')
                                    ->get())
        {
            $storage_alias_id = $this->data->post('storage_alias_id');
            $chenge_price = $this->data->post('chenge_price');

            $storage_alias_id = explode(".", $storage_alias_id);
            if(count($storage_alias_id) == 2)
                if($invoice = $this->load->function_in_alias($storage_alias_id[0], '__get_Invoice', array('id' => $storage_alias_id[1], 'user_type' => $cart_product->user_type)))
                {
                    $toReserveQuantity = $cart_product->quantity;
                    if($invoice->amount_free < $cart_product->quantity)
                        $toReserveQuantity = $cart_product->amount_free;
                    if($toReserveQuantity > 0)
                        $this->load->function_in_alias($storage_alias_id[0], '__set_Reserve', ['invoice' => $storage_alias_id[1], 'amount' => $toReserveQuantity]);
                    $this->load->function_in_alias($cart_product->storage_alias, '__set_Reserve', ['invoice' => $cart_product->storage_invoice, 'amount' => -$cart_product->quantity]);

                    $data = [];
                    $data['storage_alias'] = $storage_alias_id[0];
                    $data['storage_invoice'] = $storage_alias_id[1];
                    if($chenge_price == 'true')
                        $data['price'] = $res['price'] = $invoice->price_out;
                    else
                        $res['price'] = $cart_product->price;
                    $data['price_in'] = $invoice->price_in;

                    if($this->db->updateRow('s_cart_products', $data, $row_id))
                    {
                        $comment = '';
                        if($product = $this->load->function_in_alias($cart_product->product_alias, '__get_Product', $cart_product->product_id))
                        {
                            if(!empty($product->article_show))
                                $comment .= "<strong>{$product->article_show}</strong> ";
                            else if(!empty($product->article))
                                $comment .= "<strong>{$product->article}</strong> ";
                            $comment .= $product->name.': ';
                        }
                        $res['sum'] = $res['price'] * $cart_product->quantity;
                        $res['sumFormat'] = $this->load->function_in_alias($cart_product->product_alias, '__formatPrice', $res['sum']);
                        $res['priceFormat'] = $this->load->function_in_alias($cart_product->product_alias, '__formatPrice', $res['price']);

                        if($storage_old = $this->load->function_in_alias($cart_product->storage_alias, '__get_storage_info'))
                        {
                            $comment .= "{$storage_old->name} ({$cart_product->price}) => {$invoice->storage_name} ({$invoice->price_out})";
                        }
                        $toHistory = array();
                        $toHistory['cart'] = $cart_product->cart;
                        $toHistory['status'] = $toHistory['show'] = 0;
                        $toHistory['user'] = $_SESSION['user']->id;
                        $toHistory['comment'] = $comment;
                        $toHistory['date'] = time();
                        $this->db->insertRow('s_cart_history', $toHistory);

                        if($chenge_price == 'true')
                        {
                            $res['total'] = $this->_updateTotal($cart_product->cart);
                            $res['totalFormat'] = $this->load->function_in_alias($cart_product->product_alias, '__formatPrice', $res['total']);
                        }
                        $res['product_alias'] = $cart_product->product_alias;
                        $res['product_id'] = $cart_product->product_id;
                        $res['storage_name'] = $invoice->storage_name;
                        $res['invoice_id'] = $storage_alias_id[1];
                        $res['amount_free'] = $invoice->amount_free - $cart_product->quantity;
                        $res['text'] = $invoice->storage_name .' / '.$res['amount_free'] .' од.';

                        $res['result'] = true;
                    }
                }
            
        }

        $this->json($res);
    }

    public function changeProductQuantity()
    {
        $_SESSION['notify'] = new stdClass();
        if($quantity = $this->data->post('quantity'))
            if($id = $this->data->post('id'))
                if($product = $this->db->getAllDataById("s_cart_products", $id))
        {
            if($product->storage_alias && $product->storage_invoice)
                if($invoice = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', $product->storage_invoice))
                {
                    if($invoice->amount_free < $quantity)
                    {
                        $quantity = $invoice->amount_free;
                        $_SESSION['notify']->errors = "Увага! Встановлено максимальну наявну кількість на складі: <strong>{$invoice->amount_free} од.</strong>";
                    }

                    $reserve = array('invoice' => $product->storage_invoice, 'amount' => ($quantity - $product->quantity));
                    $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                }

            if($this->db->updateRow("s_cart_products", array('quantity' => $quantity), $id))
            {
                $this->_updateTotal($product->cart);

                if(!empty($_POST['toHistory']))
                {
                    $toHistory = array();
                    $toHistory['cart'] = $product->cart;
                    $toHistory['user'] = $_SESSION['user']->id;
                    $toHistory['comment'] = $this->data->post('toHistory');
                    $toHistory['comment'] .= ' '.$quantity;
                    $toHistory['date'] = time();
                    $this->db->insertRow('s_cart_history', $toHistory);
                }

                if(empty($_SESSION['notify']->errors))
                    $_SESSION['notify']->success = "Кількість оновлено до <strong>{$quantity} од.</strong>";
            }
        }

        $this->redirect();
    }

    public function saveToHistory($pay = null)
    {
        $this->load->smodel('cart_model');
        $data = $cartUpdate = $info = array();
        $data['show'] = 1;
        if($pay && isset($pay->cart_id))
        {
            $cartId = $data['cart'] = $pay->cart_id;
            $data['status'] = 0;
            if(!empty($pay->min_status))
            {
                if($cart = $this->db->getAllDataById($this->cart_model->table(), $cartId))
                {
                    if($cart->status < $pay->min_status)
                        return false;
                    if(!empty($pay->cart_status) && $pay->cart_status > $cart->status)
                        $data['status'] = $cartUpdate['status'] = $pay->cart_status;
                }
            }
            else if(!empty($pay->cart_status))
            {
                if($cart = $this->db->getAllDataById($this->cart_model->table(), $cartId))
                {
                    if($pay->cart_status > $cart->status)
                        $data['status'] = $cartUpdate['status'] = $pay->cart_status;
                }
            }
            $cartUpdate['payment_alias'] = $pay->alias;
            $cartUpdate['payment_id'] = $pay->id;
            $cartUpdate['payed'] = $pay->amount ?? 0;
            $data['comment'] = $pay->comment;
            $data['user'] = 0;
        }
        else if(isset($_POST['cart']) && is_numeric($_POST['cart']))
        {
            $cartId = $data['cart'] = $this->data->post('cart');
            $data['status'] = $cartUpdate['status'] = $this->data->post('status') ? $this->data->post('status') : 1;
            $data['comment'] = $this->data->post('comment');
            $data['user'] = $cartUpdate['manager'] = $_SESSION['user']->id;
        }
        $data['date'] = $cartUpdate['date_edit'] = time();

        if(empty($cartId))
            return false;

        if($this->db->insertRow($this->cart_model->table('_history'), $data))
        {
            $this->db->updateRow($this->cart_model->table(), $cartUpdate, $cartId);

            if($cart = $this->sendMail_Change_status($cartId, $data['comment']))
            {
                if($cart->products[0]->storage_invoice)
                    foreach($cart->products as $product) {
                        if($cart->action == 'closed')
                        {
                            $reserve = array('invoice' => $product->storage_invoice, 'amount' => $product->quantity, 'reserve' => true);
                            $this->load->function_in_alias($product->storage_alias, '__set_Book', $reserve);
                        }
                        elseif($cart->action == 'canceled')
                        {
                            $reserve = array('invoice' => $product->storage_invoice, 'amount' => -$product->quantity);
                            $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                        }
                    }

                if($this->notify_client_sms_on_delivered && $cart->action == 'delivered' && !empty($cart->ttn) && !empty($cart->user_phone))
                {
                    $this->load->library('turbosms');
                    $this->turbosms->send($cart->user_phone, 'Замовлення #'.$cart->id.' відправлено. ТТН '.$cart->ttn);
                }

                if (!is_object($pay) && $cart->payment_alias && $cart->payment_id) {
                    if($cart->action == 'confirmed' || $cart->action == 'closed')
                        $this->load->function_in_alias($cart->payment_alias, '__confirmPayment', $cart->payment_id, true);
                    if($cart->action == 'canceled')
                        $this->load->function_in_alias($cart->payment_alias, '__cancelPayment', $cart->payment_id, true);
                }

                if(isset($_POST['cart']) && empty($_POST['ajax']))
                    $this->redirect();

                return true;
            }
        }
        return false;        
    }

    public function send_mail()
    {
        if($cart_id = $this->data->get('cart_id'))
            if($cart = $this->sendMail_Change_status($cart_id))
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->success = 'Лист успішно вислано';
            }
        $this->redirect();
    }

    private function sendMail_Change_status($cartId, $comment = '')
    {
        $this->load->smodel('cart_model');
        if($cart = $this->cart_model->getById($cartId))
        {
            $this->load->library('mail');
            
            if($_SESSION['language'] && !empty($cart->user_language) && $cart->user_language != $_SESSION['language'])
                $_SESSION['language'] = $cart->user_language;

            $info['id'] = $cart->id;
            $info['action'] = $cart->action;
            $info['status'] = $cart->status;
            $info['status_name'] = $cart->status_name;
            $info['status_weight'] = $cart->status_weight;
            $info['comment'] = $comment;
            $info['info'] = $cart->comment;
            $info['date'] = date('d.m.Y H:i', $cart->date_edit);
            $info['user_name'] = $cart->user_name;
            $info['user_email'] = $cart->user_email;
            $info['user_phone'] = $cart->user_phone;
            $info['link'] = SITE_URL.$_SESSION['alias']->alias.'/'.$info['id'];
            $info['pay_link'] = SITE_URL.$_SESSION['alias']->alias.'/'.$cart->id.'/pay';
            $info['admin_link'] = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$info['id'];
            $cart->subTotal = 0;
            $shop_alias = $cart->products[0]->product_alias;
            if(empty($shop_alias))
            {
                if($row = $this->db->select('s_cart_products', 'product_alias', ['product_alias' => '>0'])->limit(1)->get())
                    $shop_alias = $row->product_alias;
            }
            foreach ($cart->products as $product) {
                if($product->product_alias)
                {
                    $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                    if($product->storage_invoice)
                        $product->storage = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', array('id' => $product->storage_invoice, 'user_type' => $cart->user_type));
                    $product->price_format =  $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                    $cart->subTotal += $product->price * $product->quantity + $product->discount;
                    $product->sum_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity);
                    if($product->discount)
                    {
                        $product->sumBefore_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity + $product->discount);
                        $product->discountFormat = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->discount);
                    }
                }
                else if(!empty($product->product_options))
                {
                    $options = unserialize($product->product_options);
                    $product->info = new stdClass();
                    $product->info->id = $product->id;
                    $product->info->photo = $options['photo'];
                    $product->info->cart_photo = $product->info->admin_photo = $options['cart_photo'];
                    $product->info->article = $product->info->article_show = $options['article'];
                    $product->info->name = $options['name'];
                    $product->info->link = $options['photo'] ?? '';
                    $cart->subTotal += $product->price * $product->quantity + $product->discount;
                    $product->price_format = $this->load->function_in_alias($shop_alias, '__formatPrice', $product->price);
                    $product->sum_format = $this->load->function_in_alias($shop_alias, '__formatPrice', $product->price * $product->quantity);
                    $product->product_options = false;
                }
            }
            if ($cart->subTotal != $cart->total)
                $info['subTotalFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->subTotal);
            if($cart->discount)
                $info['discountFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->discount);
            
            $info['payed'] = $cart->payed;
            $info['total'] = $cart->total;
            $info['total_formatted'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->total);
            $info['discount'] = $cart->discount;
            $info['products'] = $cart->products;
            $info['delivery'] = false;
            if($cart->shipping_id && !empty($cart->shipping_info))
            {
                $cart->shipping_info = unserialize($cart->shipping_info);
                if($cart->shipping = $this->cart_model->getShippings(array('id' => $cart->shipping_id)))
                {
                    $cart->shipping = $cart->shipping[0];
                    $cart->shipping->text = $cart->shipping->name.' ';
                    if($cart->shipping->wl_alias)
                        $cart->shipping->text .= $this->load->function_in_alias($cart->shipping->wl_alias, '__get_info', $cart->shipping_info);
                    else
                    {
                        if(!empty($cart->shipping_info['city']))
                            $cart->shipping->text .= "<p>Місто: <b>{$cart->shipping_info['city']}</b> </p>";
                        if(!empty($cart->shipping_info['department']))
                            $cart->shipping->text .= "<p>Відділення: <b>{$cart->shipping_info['department']}</b> </p>";
                        if(!empty($cart->shipping_info['address']))
                            $cart->shipping->text .= "<p>Адреса: <b>{$cart->shipping_info['address']}</b> </p>";
                    }
                    if(!empty($cart->shipping_info['recipient']))
                        $cart->shipping->text .= "<p>Отримувач: <b>{$cart->shipping_info['recipient']}</b> </p>";
                    if(!empty($cart->shipping_info['phone']))
                        $cart->shipping->text .= "<p>Контактний телефон: <b>{$cart->shipping_info['phone']}</b> </p>";
                    $info['delivery'] = $cart->shipping->text;
                    if(!empty($cart->shipping_info['price']))
                    {
                        $info['shippingPrice'] = $cart->shipping_info['price'];
                        $info['shippingPriceFormat'] = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->shipping_info['price']);
                    }
                }
            }
            $info['payment'] = false;
            if($cart->payment_alias && $cart->payment_id)
                $info['payment'] = $this->load->function_in_alias($cart->payment_alias, '__get_info', $cart->payment_id);
            else if($cart->payment_alias == 0 && $cart->payment_id)
                if($payment = $this->cart_model->getPayments(array('id' => $cart->payment_id)))
                    $info['payment'] = $payment[0];

            $this->mail->sendTemplate('change_status', $cart->user_email, $info);

            if($_SESSION['language'])
                $_SESSION['language'] = $_SESSION['all_languages'][0];

            return $cart;
        }
        return false;
    }

    public function editComment()
    {
        $data = array();
        $res = array('result' => false);
        $id = $this->data->post('id');
        $data['comment'] = $this->data->post('comment');
        $data['user'] = $_SESSION['user']->id;
        $data['date'] = time();

        if($this->db->updateRow('s_cart_history', $data, $id)){
            $res['result'] = true;
        }
        $this->load->json($res);
    }

    public function editManagerComment()
    {
        $res = array('result' => false);
        if($id = $this->data->post('id'))
        {
            $data = ['manager_comment' => $this->data->post('comment')];
            if($this->db->updateRow('s_cart', $data, $id))
                $res['result'] = true;
        }
        $this->load->json($res);
    }

    public function set__shipping_pay()
    {
        if($cart_id = $this->data->post('cart_id'))
            if(is_numeric($cart_id))
            {
                $this->load->smodel('cart_model');
                if($order = $this->cart_model->getById($cart_id))
                {
                    $subTotal = 0;
                    if($order->products)
                        foreach($order->products as $product)
                        {
                            $subTotal += $product->price * $product->quantity;
                        }
                    $subTotal -= $order->discount;
                    $shipping_price = $this->data->post('shipping_price');

                    $update = $shipping_info = [];
                    $update['total'] = $subTotal + $shipping_price;
                    if(!empty($order->shipping_info))
                        $shipping_info = unserialize($order->shipping_info);
                    $shipping_info['price'] = $shipping_price;
                    $update['shipping_info'] = serialize($shipping_info);
                    $this->db->updateRow($this->cart_model->table(), $update, $order->id);
                }
            }
        $this->redirect();
    }

    public function saveTTN()
    {
        $res = array('result' => false);
        if($id = $this->data->post('cart'))
        {
            $data = ['ttn' => $this->data->post('comment')];
            if($this->db->updateRow('s_cart', $data, $id))
            {
                if($status = $this->data->post('status'))
                    $this->saveToHistory();
                else
                {
                    $history = ['cart' => $id, 'status' => 0];
                    $history['comment'] = 'ТТН доставки: '.$this->data->post('comment');
                    $history['user'] = $_SESSION['user']->id;
                    $history['date'] = time();
                    $this->db->insertRow('s_cart_history', $history);
                }
                $res['result'] = true;
            }
        }
        $this->load->json($res);
    }

    public function __set_Payment($pay)
    {
        if(isset($pay->cart_id))
            return $this->saveToHistory($pay);
        return false;
    }

    public function getProductByArticle()
    {
        $article = $this->data->post('product');
        $userType = $this->data->post('userType');
        $userId = $this->data->post('userId');
        $cartId = $this->data->post('cartId');

        $where = ['alias2' => $_SESSION['alias']->id, 'type' => 'cart'];
        if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $where))
            foreach ($cooperation as $shop) {
                $showHeader = true;

                if($products = $this->load->function_in_alias($shop->alias1, '__get_Products', array('article' => '%'.$article, 'additionalFileds' => ['user_type' => $userType])))
                {
                    if($storages = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => $shop->alias1, 'type' => 'storage')))
                    {
                        foreach ($products as $product) {
                            if($showHeader)
                            {
                                echo("<h3>Товари</h3>");
                                echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
                                echo("<tr>");
                                echo("<td>Товар</td>");
                                echo("<td>Склад</td>");
                                echo("<td>Наявність / Доступно</td>");
                                echo("<td>Ціна</td>");
                                echo("<td></td>");
                                echo("</tr>");
                                $showHeader = false;
                            }

                            if(!empty($product->article_show))
                                $product->article = $product->article_show;
                            $product->name = html_entity_decode($product->name);
                            echo("<tr>");
                            if(!empty($product->admin_photo))
                                echo "<td><img src=".IMG_PATH. $product->admin_photo." width='90' alt=''> <strong>{$product->article}</strong> <br>{$product->name}</td>";
                            else
                                echo("<td><strong>{$product->article}</strong> <br>{$product->name}</td>");

                            $showInvoices = false;
                            foreach ($storages as $storage) {
                                if($invoices = $this->load->function_in_alias($storage->alias2, '__get_Invoices_to_Product', ['id' => $product->id, 'user_type' => $userType]))
                                    foreach ($invoices as $invoice) {
                                        if(empty($invoice->amount_free))
                                            continue;
                                        if($showInvoices)
                                            echo "<tr><td></td>";
                                        $showInvoices = true;
                                        echo("<td>{$invoice->storage_name} <br><i>{$invoice->storage_time}</i></td>");
                                        echo("<td>{$invoice->amount} / <u title='Доступно'>{$invoice->amount_free}</u></td>");
                                        echo("<td>{$invoice->price_out} {$invoice->currency}</td>");
                                        echo("<td><form method='post' action='".SITE_URL."admin/{$_SESSION['alias']->alias}/addProduct'><input type='hidden' value='{$userId}' name='userId'><input type='hidden' name='cartId' value='{$cartId}'><input type='hidden' name='productId' value='{$product->id}'><input type='hidden' name='price' value='{$invoice->price_out}'><input type='hidden' name='storageId' value='{$storage->alias2}'><input type='hidden' name='invoiceId' value='{$invoice->id}'><input type='hidden' name='price_in' value='{$invoice->price_in}'><button type='submit' class='btn btn-sm btn-warning'>Додати</button></form></td>");
                                        echo("</tr>");
                                    }
                            }
                            if(!$showInvoices)
                            {
                                echo("<td></td>");
                                echo("<td>{$product->price} грн</td>");
                                echo("<td><form method='post' action='".SITE_URL."admin/{$_SESSION['alias']->alias}/addProduct'><input type='hidden' value='{$userId}' name='userId'><input type='hidden' name='cartId' value='{$cartId}'><input type='hidden' name='productId' value='{$product->id}'><input type='hidden' name='price' value='{$product->price}'><button type='submit' class='btn btn-sm btn-warning'>Додати</button></form></td>");
                                echo("</tr>");
                            }
                        }
                    }
                    else
                        foreach ($products as $product) {
                            if($showHeader)
                            {
                                echo("<h3>Товари</h3>");
                                echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
                                echo("<tr>");
                                echo("<td>Артикул</td>");
                                echo("<td>Товар</td>");
                                echo("<td>Ціна</td>");
                                echo("<td></td>");
                                echo("</tr>");
                                $showHeader = false;
                            }

                            if(!empty($product->article_show))
                                $product->article = $product->article_show;
                            $product->name = html_entity_decode($product->name);
                            echo("<tr>");
                            echo("<td>{$product->article}</td>");
                            if(!empty($product->admin_photo))
                                echo "<td><img src=".IMG_PATH. $product->admin_photo." width='90' alt=''> {$product->name}</td>";
                            else
                                echo("<td>{$product->name}</td>");
                            echo("<td>{$product->price} грн</td>");
                            echo("<td><form method='post' action='".SITE_URL."admin/{$_SESSION['alias']->alias}/addProduct'><input type='hidden' value='{$userId}' name='userId'><input type='hidden' name='cartId' value='{$cartId}'><input type='hidden' name='productId' value='{$product->id}'><input type='hidden' name='price' value='{$product->price}'><button type='submit' class='btn btn-sm btn-warning'>Додати</button></form></td>");
                            echo("</tr>");
                        }
                    echo("</table></div>");
                }
            }
    }

    public function addProduct()
    {
        $data = array();
        $data['cart'] = $this->data->post('cartId');
        $data['storage_alias'] = (int) $this->data->post('storageId');
        $data['storage_invoice'] = (int) $this->data->post('invoiceId');
        $data['product_id'] = $this->data->post('productId');
        $data['active'] = $data['quantity'] = $data['quantity_wont'] = 1;
        $data['quantity_returned'] = $data['discount'] = 0;
        $data['price'] = $this->data->post('price');
        $data['price_in'] = $this->data->post('price_in') ?? $data['price'];
        $data['product_alias'] = $this->db->getQuery("SELECT wl_alias FROM `s_shopshowcase_products` WHERE `id` = {$data['product_id']} ")->wl_alias;
        $data['user'] = $this->data->post('userId') === 'false' ? $_SESSION['user']->id : $this->data->post('userId');
        $data['product_options'] = '';
        $data['date'] = time();

        $updateRow = true;
        if(empty($data['cart']))
        {
            $data['cart'] = $this->db->insertRow('s_cart', array('user' => $data['user'], 'total' => $data['price'], 'status' => 0, 'date_add' => $data['date'], 'date_edit' => $data['date']));
            $updateRow = false;
        }

        $this->db->insertRow('s_cart_products', $data);
        if($updateRow)
            $this->_updateTotal($data['cart']);

        if($data['storage_alias'] && $data['storage_invoice'])
        {
            $reserve = array('invoice' => $data['storage_invoice'], 'amount' => 1);
            $this->load->function_in_alias($data['storage_alias'], '__set_Reserve', $reserve);
        }

        $this->redirect("/admin/{$_SESSION['alias']->alias}/{$data['cart']}");
    }

    public function findUser()
    {
        $res = array('result' => false);
        if($this->data->post('userInfo'))
        {
            $info = $this->data->post('userInfo');

            $userIds = '';
            if($byUserInfo = $this->db->getAllDataByFieldInArray('wl_user_info', array('value' => '%'.$info)))
            {
                $ids = array();
                foreach ($byUserInfo as $row) {
                    if(!in_array($row->user, $ids))
                        $ids[] = $row->user;
                }
                if(!empty($ids))
                    $userIds = 'OR u.id IN ('.implode(', ', $ids).')';
            }

            $users = $this->db->getQuery("SELECT u.id, u.email, u.name, u.type as type_id, t.title as type_name, ui.value as user_phone FROM `wl_users` as u 
                                            LEFT JOIN `wl_user_types` as t ON t.id = u.type
                                            LEFT JOIN `wl_user_info` as ui ON ui.field = 'phone' AND ui.user = u.id
                WHERE u.name LIKE '%{$info}%' OR `email` LIKE '%{$info}%' {$userIds}", 'array');
            if($users)
            {
                $res['result'] = true;
                $res['user'] = $users;
            }
            $this->json($res);
        }
    }

    public function saveNewUser()
    {
        $res = array('result' => false, 'message' => '');
        if(trim($this->data->post('name')) != '' && ($this->data->post('email') || $this->data->post('phone')))
        {
            $data = array();

            $data['name'] = $name = $this->data->post('name');
            $data['email'] = $email = $this->data->post('email');
            $data['photo'] = NULL;
            $userInfo['phone'] = $phone = $this->data->post('phone');
            if(!empty($phone))
            {
                $this->load->library('validator');
                $userInfo['phone'] = $phone = $this->validator->getPhone($phone);
            }

            if($email || $phone)
            {
                if($email)
                {
                    if($this->db->getAllDataById('wl_users', $email, 'email'))
                        $res['message'] = 'Користувач з таким е-мейлом вже є';
                    else
                        $res['result'] = true;
                }
                if($phone && $res['message'] == '')
                {
                    if($this->db->getAllDataByFieldInArray('wl_user_info', array('field' => 'phone', 'value' => $phone)))
                    {
                        $res['message'] = 'Користувач з таким телефоном вже є';
                        $res['result'] = false;
                    }
                    else
                        $res['result'] = true;
                }
            }

            if($res['result'] == true)
            {
                $setPassword = false;
                if($email)
                {
                    $setPassword = true;
                    $data['password'] = bin2hex(openssl_random_pseudo_bytes(4));
                }
                $comment = 'by manager ('.$_SESSION['user']->id.') '.$_SESSION['user']->name;
                $this->load->model('wl_user_model');
                if($user = $this->wl_user_model->add($data, $userInfo, $_SESSION['option']->new_user_type, $setPassword, $comment))
                {
                    if($email)
                        $this->db->updateRow('wl_users', array('reset_key' => $data['password']), $user->id);
                    $res['id'] = $user->id;
                }
                else {
                    $res['message'] = 'Помилка при створені користувача';
                    $res['result'] = false;
                }
            }
        }

        $this->json($res);
    }

    public function finishAddCart()
    {
        $_SESSION['notify'] = new stdClass();
        if($cartId = $this->data->post('cart'))
        {
            $this->load->smodel('cart_model');
            if($cart = $this->cart_model->getById($cartId))
            {
                if($cart->status == 0)
                {
                    $this->db->updateRow($this->cart_model->table(), array('status' => 1, 'date_edit' => time(), 'manager' => $_SESSION['user']->id), $cartId);

                    $data = array();
                    $data['cart'] = $cartId;
                    $data['status'] = 1;
                    $data['show'] = 0;
                    $data['user'] = $_SESSION['user']->id;
                    $data['comment'] = $_SESSION['notify']->success = 'Замовлення сформовано';
                    $data['date'] = time();
                    $this->db->insertRow('s_cart_history', $data);

                    if(!empty($cart->user_email) && $cart->products)
                    {
                        $cart->subTotal = 0;
                        $shop_alias = $cart->products[0]->product_alias;
                        if(empty($shop_alias))
                        {
                            if($row = $this->db->select('s_cart_products', 'product_alias', ['product_alias' => '>0'])->limit(1)->get())
                                $shop_alias = $row->product_alias;
                        }
                        foreach ($cart->products as $product) {
                            if($product->product_alias)
                            {
                                $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                                if($product->storage_invoice)
                                    $product->storage = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', array('id' => $product->storage_invoice, 'user_type' => $cart->user_type));
                                $product->price_format =  $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                                $cart->subTotal += $product->price * $product->quantity + $product->discount;
                                $product->sum_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity);
                                if($product->discount)
                                {
                                    $product->sumBefore_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity + $product->discount);
                                    $product->discountFormat = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->discount);
                                }
                            }
                            else if(!empty($product->product_options))
                            {
                                $options = unserialize($product->product_options);
                                $product->info = new stdClass();
                                $product->info->id = $product->id;
                                $product->info->photo = $options['photo'];
                                $product->info->cart_photo = $product->info->admin_photo = $options['cart_photo'];
                                $product->info->article = $product->info->article_show = $options['article'];
                                $product->info->name = $options['name'];
                                $product->info->link = $options['photo'] ?? '';
                                $cart->subTotal += $product->price * $product->quantity + $product->discount;
                                $product->price_format = $this->load->function_in_alias($shop_alias, '__formatPrice', $product->price);
                                $product->sum_format = $this->load->function_in_alias($shop_alias, '__formatPrice', $product->price * $product->quantity);
                                $product->product_options = false;
                            }
                        }

                        $this->load->library('mail');

                        $info['id'] = $info['order_id'] = $cart->id;
                        $info['comment'] = $cart->comment;
                        $info['status'] = $cart->status;
                        $info['status_name'] = $cart->status_name;
                        $info['status_weight'] = $cart->status_weight;
                        $info['date'] = date('d.m.Y H:i', $cart->date_edit);
                        $info['user_name'] = $cart->user_name;
                        $info['user_email'] = $cart->user_email;
                        $info['user_phone'] = $cart->user_phone;
                        $info['link'] = SITE_URL.$_SESSION['alias']->alias.'/'.$info['id'];
                        $info['pay_link'] = SITE_URL.$_SESSION['alias']->alias.'/'.$cart->id.'/pay';
                        $info['delivery'] = $info['payment'] = $info['new_user'] = false;
                        $user = $this->db->getAllDataById('wl_users', $cart->user);
                        if(!empty($user->reset_key) && $user->last_login == 0 && $_SESSION['option']->usePassword)
                        {
                            $info['new_user'] = true;
                            $info['password'] = $user->reset_key;
                            $this->db->updateRow('wl_users', array('reset_key' => ''), $user->id);
                        }
                        
                        $info['subTotal'] = $info['subTotalFormat'] = $info['total'] = $info['totalFormat'] = $cart->total;
                        $info['discount'] = $info['discount_formatted'] = $cart->discount;
                        if($shop_alias)
                            $info['totalFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $info['total']);
                        
                        if($info['discount'])
                        {
                            $sum = $info['subTotal'] = $info['subTotalFormat'] = $info['total'] + $info['discount'];
                            if($shop_alias)
                            {
                                $info['subTotalFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $sum);
                                $info['discount_formatted'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $info['discount']);
                            }
                        }
                        $info['products'] = $cart->products;
                        
                        $this->mail->sendTemplate('checkout', $cart->user_email, $info);
                    }
                }
            }
        }
        $this->redirect();
    }

    public function settings()
    {
        $this->load->smodel('cart_model');
        $uri = $this->data->uri(3);
        if(empty($uri))
        {
            $_SESSION['alias']->name .= '. Налаштування';
            $shippings = $this->cart_model->getShippings();
            $payments = $this->cart_model->getPayments();
            $this->load->admin_view('settings/index_view', array('shippings' => $shippings, 'payments' => $payments));
        }
        elseif($uri == 'shipping')
        {
            $id = $this->data->uri(4);
            $shipping = false;
            if(is_numeric($id))
            {
                $_SESSION['alias']->name .= '. Налаштування доставки #'.$id;
                $shipping = $this->cart_model->getShippings(array('id' => $id), false);
                if(empty($shipping))
                    $this->load->page_404(false);
            }
            else if(empty($id))
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/settings');
            else if($id == 'add')
                $_SESSION['alias']->name .= '. Додати просту доставку';
            else
                $this->load->page_404(false);
            $this->load->admin_view('settings/shipping_view', array('shipping' => $shipping));
        }
        elseif($uri == 'payment')
        {
            $id = $this->data->uri(4);
            $payment = false;
            if(is_numeric($id))
            {
                $_SESSION['alias']->name .= '. Налаштування оплати #'.$id;
                $payment = $this->cart_model->getPayments(array('id' => $id), true);
                if($payment)
                    $payment = $payment[0];
                else
                    $this->load->page_404(false);
            }
            else if(empty($id))
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/settings');
            else if($id == 'add')
                $_SESSION['alias']->name .= '. Додати просту оплату';
            else
                $this->load->page_404(false);
            $this->load->admin_view('settings/payment_view', array('payment' => $payment));
        }
        else
            $this->load->page_404(false);
    }

    public function settings_change_position()
    {
        $res = array('result' => false);
        if(isset($_POST['id']) && is_numeric($_POST['position']))
        {
            $id = explode('-', $_POST['id']);
            if(count($id) == 2 && in_array($id[0], array('shipping', 'payment')) && is_numeric($id[1]))
            {
                $this->load->smodel('cart_model');
                $this->load->model('wl_position_model');

                $this->wl_position_model->table = $this->cart_model->table('_payments');
                if($id[0] == 'shipping')
                    $this->wl_position_model->table = $this->cart_model->table('_shipping');
                $newposition = $_POST['position'] + 1;
                
                if($this->wl_position_model->change($id[1], $newposition))
                    $res['result'] = true;
            }
        }
        $this->load->json($res);
    }

    public function settings_change_active()
    {
        $res = array('result' => false);
        if(isset($_POST['id']) && is_numeric($_POST['active']))
        {
            $id = explode('-', $_POST['id']);
            if(count($id) == 2 && in_array($id[0], array('shipping', 'payment')) && is_numeric($id[1]))
            {
                $this->load->smodel('cart_model');

                $table = $this->cart_model->table('_payments');
                if($id[0] == 'shipping')
                    $table = $this->cart_model->table('_shipping');

                $active = ($_POST['active'] > 0) ? 1 : 0;
                
                if($this->db->updateRow($table, array('active' => $active), $id[1]))
                    $res['result'] = true;
            }
        }
        $this->load->json($res);
    }

    public function save_shipping()
    {
        if(is_numeric($_POST['id']))
        {
            $this->load->smodel('cart_model');
            $shipping = array('active' => 0, 'pay' => -2);
            $shipping['type'] = $this->data->post('type');
            if(isset($_POST['pay']) && is_numeric($_POST['pay']))
            {
                $shipping['pay'] = $_POST['pay'] < 0 ? $_POST['pay'] : 0;
                if(isset($_POST['pay_to']) && $_POST['pay_to'] > 0)
                    $shipping['pay'] = $_POST['pay_to'];
            }
            $shipping['price'] = $this->data->post('price') ?? 0;
            $shipping['active'] = ($this->data->post('active') > 0 || $_POST['id'] == 0) ? 1 : 0;
            if($_SESSION['language'])
            {
                $name = $info = array();
                foreach ($_SESSION['all_languages'] as $lang) {
                    $name[$lang] = $this->data->post('name_'.$lang);
                    $info[$lang] = $this->data->post('info_'.$lang);
                }
                $shipping['name'] = serialize($name);
                $shipping['info'] = serialize($info);
            }
            else
            {
                $shipping['name'] = $this->data->post('name');
                $shipping['info'] = $this->data->post('info');
            }

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Оплату оновлено';

            if($_POST['id'] == 0)
            {
                $_SESSION['notify']->success = 'Оплату додано';
                $shipping['wl_alias'] = 0;
                $shipping['position'] = $this->db->getCount($this->cart_model->table('_shipping')) + 1;
                $id = $this->db->insertRow($this->cart_model->table('_shipping'), $shipping);
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/settings/shipping/'.$id);
            }
            else
                $this->db->updateRow($this->cart_model->table('_shipping'), $shipping, $_POST['id']);
        }
        $this->redirect();
    }

    public function delete_shipping()
    {
        if(is_numeric($_POST['id']))
        {
            $this->load->smodel('cart_model');

            if($shipping = $this->db->getAllDataById($this->cart_model->table('_shipping'), $_POST['id']))
            {
                $this->db->deleteRow($this->cart_model->table('_shipping'), $shipping->id);
                $this->db->executeQuery("UPDATE `{$this->cart_model->table('_shipping')}` SET `position` = position - 1 WHERE `position` > '{$shipping->position}'");
                if($shipping->wl_alias)
                    $this->db->deleteRow('wl_aliases_cooperation', array('alias1' => $_SESSION['alias']->id, 'alias2' => $shipping->id, 'type' => 'shipping'));
            }
        }
        $this->redirect('admin/'.$_SESSION['alias']->alias.'/settings');
    }

    public function save_payment()
    {
        if(is_numeric($_POST['id']))
        {
            $this->load->smodel('cart_model');
            $payment = array('active' => 0);
            $payment['active'] = ($_POST['active'] > 0 || $_POST['id'] == 0) ? 1 : 0;
            if($_SESSION['language'])
            {
                $name = $info = array();
                foreach ($_SESSION['all_languages'] as $lang) {
                    $name[$lang] = $this->data->post('name_'.$lang);
                    $info[$lang] = $this->data->post('info_'.$lang);
                    $tomail[$lang] = $this->data->post('tomail_'.$lang);
                }
                $payment['name'] = serialize($name);
                $payment['info'] = serialize($info);
                $payment['tomail'] = serialize($tomail);
            }
            else
            {
                $payment['name'] = $this->data->post('name');
                $payment['info'] = $this->data->post('info');
                $payment['tomail'] = $this->data->post('tomail');
            }

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Оплату оновлено';

            if($_POST['id'] == 0)
            {
                $_SESSION['notify']->success = 'Оплату додано';
                $payment['wl_alias'] = 0;
                $payment['position'] = $this->db->getCount($this->cart_model->table('_payments')) + 1;
                $this->db->insertRow($this->cart_model->table('_payments'), $payment);
            }
            else
                $this->db->updateRow($this->cart_model->table('_payments'), $payment, $_POST['id']);
        }
        $this->redirect('admin/'.$_SESSION['alias']->alias.'/settings');
    }

    public function delete_payment()
    {
        if(is_numeric($_POST['id']))
        {
            $this->load->smodel('cart_model');

            if($payment = $this->db->getAllDataById($this->cart_model->table('_payments'), $_POST['id']))
            {
                $this->db->deleteRow($this->cart_model->table('_payments'), $payment->id);
                $this->db->executeQuery("UPDATE `{$this->cart_model->table('_payments')}` SET `position` = position - 1 WHERE `position` > '{$payment->position}'");
                if($payment->wl_alias)
                    $this->db->deleteRow('wl_aliases_cooperation', array('alias1' => $_SESSION['alias']->id, 'alias2' => $payment->id, 'type' => 'payment'));
            }
        }
        $this->redirect('admin/'.$_SESSION['alias']->alias.'/settings');
    }

    public function save_new_price()
    {
        $_SESSION['notify'] = new stdClass();
        if(!$_SESSION['user']->admin)
        {
            $_SESSION['notify']->errors = 'Редагути ціну у замовленні може виключно адміністратор';
            $this->redirect();
        }
        if(empty($_POST['password']))
        {
            $_SESSION['notify']->errors = 'Невірний пароль для підтвердження зміни ціни';
            $this->redirect();
        }
        else
        {
            $this->load->model('wl_user_model');
            $manager = $this->wl_user_model->getInfo($_SESSION['user']->id, false);
            $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $manager->email, $_POST['password']);
            if($password != $manager->password)
            {
                $_SESSION['notify']->errors = 'Невірний пароль для підтвердження зміни ціни';
                $this->redirect();
            }
        }
        if($cartId = $this->data->post('cart-id'))
        {
            $price = $this->data->post('product-new-price');
            $name = $this->data->post('product-name');
            if($productRowId = $this->data->post('product-row-id'))
                if($productRow = $this->db->getAllDataById('s_cart_products', $productRowId))
                    if($productRow->cart == $cartId && $price != $productRow->price)
                    {
                        $this->db->updateRow('s_cart_products', array('price' => $price, 'discount' => 0), $productRowId);
                        
                        $data = array();
                        $data['cart'] = $cartId;
                        $data['user'] = $_SESSION['user']->id;
                        $data['show'] = 0;
                        $data['comment'] = $_SESSION['notify']->success = 'Зміна ціни для "<strong>'.$name.'</strong>" '.$productRow->price.' => '.$this->load->function_in_alias($productRow->product_alias, '__formatPrice', $price);
                        $data['date'] = time();
                        $this->db->insertRow('s_cart_history', $data);

                        $this->_updateTotal($cartId);
                    }
        }
        $this->redirect();
    }

    public function reNew()
    {
        $_SESSION['notify'] = new stdClass();
        if(!$_SESSION['user']->admin)
        {
            $_SESSION['notify']->errors = 'Повернути до статусу "Нове замовлення" може виключно адміністратор';
            $this->redirect();
        }
        if(empty($_POST['password']))
        {
            $_SESSION['notify']->errors = 'Невірний пароль для підтвердження повернення до статусу "Нове замовлення"';
            $this->redirect();
        }
        else
        {
            $this->load->model('wl_user_model');
            $manager = $this->wl_user_model->getInfo($_SESSION['user']->id, false);
            $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $manager->email, $_POST['password']);
            if($password != $manager->password)
            {
                $_SESSION['notify']->errors = 'Невірний пароль для підтвердження повернення статусу "Нове замовлення"';
                $this->redirect();
            }
        }
        if($id = $this->data->post('cart'))
        {
            $this->db->updateRow('s_cart', array('status' => 1), $id);
            $data = array();
            $data['cart'] = $id;
            $data['status'] = 1;
            $data['user'] = $_SESSION['user']->id;
            $data['comment'] = $_SESSION['notify']->success = 'Повернено до стану "Нове замовлення"';
            $data['date'] = time();
            $this->db->insertRow('s_cart_history', $data);

            if($this->data->post('reserve_cancel'))
            {
                if($products = $this->db->getAllDataByFieldInArray('s_cart_products', $id, 'cart'))
                    foreach ($products as $product) {
                        if($product->storage_alias && $product->storage_invoice)
                        {
                            $reserve = array('invoice' => $product->storage_invoice, 'amount' => -$product->quantity);
                            $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                        }
                    }
            }
        }
        $this->redirect();
    }

    public function delete()
    {
        if($_SESSION['user']->admin && !empty($_POST['id']) && !empty($_POST['password']))
        {
            $_SESSION['notify'] = new stdClass();
            $this->load->model('wl_user_model');
            $admin = $this->wl_user_model->getInfo(0, false);
            $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $_SESSION['user']->email, $_POST['password']);
            if($password == $admin->password)
            {
                if(is_numeric($_POST['id']) && $_POST['id'] > 0)
                {
                    if($this->data->post('storage_cancel') == 1)
                    {
                        if ($products = $this->db->getAllDataByFieldInArray('s_cart_products', $_POST['id'], 'cart')) {
                            $cart = $this->db->getAllDataById('s_cart', $_POST['id']);
                            foreach ($products as $product) {
                                if($cart->status > 5)
                                {
                                    $reserve = array('invoice' => $product->storage_invoice, 'amount' => -$product->quantity);
                                    $this->load->function_in_alias($product->storage_alias, '__set_Book', $reserve);
                                }
                                else
                                {
                                    $reserve = array('invoice' => $product->storage_invoice, 'amount' => -$product->quantity);
                                    $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                                }
                            }
                        }
                    }
                    if($this->data->post('payment_cancel') == 1)
                    {
                        $cart = $this->db->getAllDataById('s_cart', $_POST['id']);
                        if($cart->payment_alias && $cart->payment_id)
                        {
                            $_POST['info'] = 'Виделено замовлення #'.$_POST['id'];
                            $this->load->function_in_alias($cart->payment_alias, '__cancelPayment', $cart->payment_id, true);
                        }
                    }

                    $this->db->deleteRow('s_cart', $_POST['id']);
                    $this->db->deleteRow('s_cart_history', $_POST['id'], 'cart');
                    $this->db->deleteRow('s_cart_products', $_POST['id'], 'cart');

                    $this->db->register('profile_data', 'Замовлення #'.$_POST['id'].' видалено');

                    $_SESSION['notify']->success = 'Замовлення #'.$_POST['id'].' видалено';
                    $this->redirect('admin/'.$_SESSION['alias']->alias);
                }
                else
                    $_SESSION['notify']->errors = 'error $_POST[id]='.$_POST['id'];
            }
            else
                $_SESSION['notify']->errors = 'Невірний пароль адміністратора';
        }
        $this->redirect();
    }

    public function addPayment()
    {
        $_SESSION['notify'] = new stdClass();
        if($cartId = $this->data->post('cart'))
            if($amount = $this->data->post('amount'))
                if($cart = $this->db->getAllDataById('s_cart', $cartId))
                {
                    $amount = (float) $amount;
                    $cart->payed = (float) $cart->payed;
                    $payed = $amount + $cart->payed;
                    $diff = $cart->total - $payed;
                    if($diff > 0 && $diff < 0.01)
                        $payed = $cart->total;
                    $updateData = ['payed' => $payed, 'date_edit' => time()];
                    if(isset($cart->{'1c_status'}))
                        $updateData['1c_status'] = 0;

                    if($method = $this->data->post('method'))
                    {
                        $updateData['payment_alias'] = 0;
                        $updateData['payment_id'] = $method;
                    }
                    $this->db->updateRow('s_cart', $updateData, $cartId);

                    $toHistory = ['cart' => $cartId, 'status' => 0, 'show' => 1];
                    $toHistory['user'] = $_SESSION['user']->id;
                    $toHistory['comment'] = "<strong>Внесено оплату: <u>{$amount}</u>. Загальна сума оплачено: {$payed}</strong> ".$this->data->post('comment');
                    $toHistory['date'] = time();
                    $this->db->insertRow('s_cart_history', $toHistory);

                    $_SESSION['notify']->success = "<strong>Внесено оплату: <u>{$amount}</u>. Загальна сума оплачено: {$payed}</strong> ";
                }
        if(empty($_SESSION['notify']->success))
            $_SESSION['notify']->errors = 'Помилка оплати! Перевірте дані.';
        $this->redirect();
    }

    public function bonus()
    {
        $_SESSION['alias']->name .= '. Бонус-коди';
        if($id = $this->data->uri(3))
        {
            if(is_numeric($id))
            {
                if($bonus = $this->db->getAllDataById('s_cart_bonus', $id))
                {
                    $bonus->mode = 1;
                    if($bonus->code == 'all')
                        $bonus->mode = 0;
                    $this->load->admin_view('bonus/edit_view', array('bonus' => $bonus));
                }
                else
                    $this->load->page_404(false);
            }
            elseif($id == 'add')
            {
                $bonus = new stdClass();
                $bonus->id = 0;
                $bonus->mode = 1;
                $bonus->count_do = $bonus->discount_max = $bonus->order_min = -1;
                $bonus->code = 'Автогенерація';
                $bonus->info = '';
                $this->load->admin_view('bonus/edit_view', array('bonus' => $bonus));
            }
            else
                $this->load->page_404(false);
        }
        else
        {
            $bonuses = $this->db->select('s_cart_bonus as b')
                                ->join('wl_users', 'name as manager_name', '#b.manager')
                                ->get('array');
            $this->load->admin_view('bonus/index_view', array('bonuses' => $bonuses));
        }
    }

    public function save_bonus()
    {
        if(isset($_POST['id']))
        {
            $_SESSION['notify'] = new stdClass();
            $bonus = array();
            if(isset($_POST['onlyActive']) && $_POST['onlyActive'] == 1 && is_numeric($_POST['id']) && $_POST['id'] > 0)
            {
                if($_POST['code'] != 'all')
                {
                    $code = mb_strtoupper($this->data->post('code'));
                    if($list = $this->db->getAllDataByFieldInArray('s_cart_bonus', $code, 'code'))
                    {
                        foreach ($list as $row) {
                            if($row->id != $_POST['id'])
                            {
                                $_SESSION['notify']->errors = 'Бонус-код <strong>'.$row->code.'</strong> вже використовується!';
                                $this->redirect();
                            }
                        }
                    }
                }
                $bonus['status'] = 1;
                $this->db->updateRow('s_cart_bonus', $bonus, $_POST['id']);
                $_SESSION['notify']->success = 'Бонус-код '.$bonus['code'].' активовано!';
                $this->redirect();
            }
            if($_POST['mode'] == 0)
                $bonus['code'] = 'all';
            else
                $bonus['code'] = mb_strtoupper($this->data->post('code'));
            $bonus['info'] = $this->data->post('info');
            $bonus['count_do'] = -1;
            if($_POST['count_do'] >= 0)
                $bonus['count_do'] = $this->data->post('count_do_numbers');
            $bonus['from'] = strtotime($this->data->post('from'));
            $bonus['to'] = strtotime($this->data->post('to'));
            if($bonus['to'] <= $bonus['from'])
                $bonus['to'] = 0;
            $bonus['discount_type'] = 1; //fixsum
            if($this->data->post('type_do') == 'persent')
            {
                $bonus['discount_type'] = 2; //persent
                $bonus['discount'] = $this->data->post('persent');
            }
            else
                $bonus['discount'] = $this->data->post('fixsum');
            $bonus['discount_max'] = $bonus['order_min'] = -1;
            if(!empty($_POST['maxActive']))
                $bonus['discount_max'] = $this->data->post('maxDiscount');
            if(!empty($_POST['minActive']))
                $bonus['order_min'] = $this->data->post('minSum');
            $bonus['manager'] = $_SESSION['user']->id;
            $bonus['date'] = time();
            if($_POST['id'] == 0)
            {
                $bonus['status'] = 0;
                if($_POST['mode'] == 1 && $_POST['generate'] == 1)
                {
                    $generateLength = $this->data->post('generateLength');
                    if($generateLength < 4)
                        $generateLength = 8;
                    do {
                        $code = bin2hex(openssl_random_pseudo_bytes($generateLength));
                        $code = mb_strtoupper($code);
                        $code = substr($code, $generateLength);
                        $bonus['code'] = $code;
                    }
                    while ($this->db->getAllDataById('s_cart_bonus', $code, 'code'));
                }
                elseif($bonus['code'] != 'all')
                {
                    if($this->db->getAllDataById('s_cart_bonus', $bonus['code'], 'code'))
                        $_SESSION['notify']->errors = 'Бонус-код <strong>'.$bonus['code'].'</strong> вже використовується!';
                }
                $id = $this->db->insertRow('s_cart_bonus', $bonus);
                $_SESSION['notify']->success = 'Бонус-код <strong>'.$bonus['code'].'</strong> успішно згенеровано та додано';
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/bonus/'.$id);
            }
            elseif(is_numeric($_POST['id']) && $_POST['id'] > 0)
            {
                $bonus['status'] = $this->data->post('status');
                if(empty($bonus['code']))
                {
                    $generateLength = 8;
                    do {
                        $code = bin2hex(openssl_random_pseudo_bytes($generateLength));
                        $code = mb_strtoupper($code);
                        $code = substr($code, $generateLength);
                        $bonus['code'] = $code;
                    }
                    while ($this->db->getAllDataById('s_cart_bonus', $code, 'code'));
                }
                elseif($bonus['code'] != 'all')
                {
                    if($list = $this->db->getAllDataByFieldInArray('s_cart_bonus', $bonus['code'], 'code'))
                    {
                        foreach ($list as $row) {
                            if($row->id != $_POST['id'])
                            {
                                $_SESSION['notify']->errors = 'Бонус-код <strong>'.$bonus['code'].'</strong> вже використовується!';
                                if($bonus['status'] == 1)
                                    $bonus['status'] = 0;
                                break;
                            }
                        }
                    }
                }
                $this->db->updateRow('s_cart_bonus', $bonus, $_POST['id']);

                
                $_SESSION['notify']->success = 'Бонус-код <strong>'.$bonus['code'].'</strong>  оновлено';
            }
        }
        $this->redirect();
    }

    public function __sidebar($alias)
    {
        if($statuses = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_status', array('weight' => '<10')))
        {
            $ids = array(3);
            foreach ($statuses as $status) {
                $ids[] = $status->id;
            }
            $alias->counter = $this->db->getCount($_SESSION['service']->table, array('status' => $ids));
        }
        return $alias;
    }

    public function __tab_profile($user_id)
    {   
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('cart_model');
        ob_start();
        $this->load->view('admin/__tab_profile', array('orders' => $this->cart_model->getCarts(array('user' => $user_id))));
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = 'Замовлення';
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }

    public function __dashboard_subview($user_id = 0)
    {   
        if($user_id === 0 || !is_numeric($user_id))
            $user_id = $_SESSION['user']->id;
        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('cart_model');
        ob_start();
        $where = array();
        $where['status'] = array(1, 2, 3, 4, 5);
        $where['manager'] = array(0, $_SESSION['user']->id);
        $carts = $this->cart_model->getCarts($where);
        if($carts)
            foreach ($carts as $cart) {
                $cart->totalFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->total);
                if($cart->products)
                    foreach ($cart->products as $product) {
                        $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                        break;
                    }
            }
        $_SESSION['option']->uniqueDesign = 0;
        $this->load->view('admin/index_view', array('carts' => $carts, '__dashboard_subview' => true));
        $subview = ob_get_contents();
        ob_end_clean();
        return $subview;
    }

    public function __get_cart($cart_id)
    {
        $this->load->smodel('cart_model');
        if($cart = $this->cart_model->getById($cart_id))
        {
            $cart->name = 'Замовлення #'.$cart->id.' від '.date('d.m.Y H:i', $cart->date_add);
            $cart->totalFormat = $cart->total;
            $cart->subTotal = $cart->subTotalFormat = $cart->shippingPrice = $cart->shippingPriceFormat = 0;

            if($cart->shipping_id && !empty($cart->shipping_info))
            {
                $shipping_info = @unserialize($cart->shipping_info);
                if(is_array($shipping_info))
                    $cart->shipping_info = $shipping_info;
                if($cart->shipping = $this->cart_model->getShippings(array('id' => $cart->shipping_id)))
                {
                    $cart->shipping = $cart->shipping[0];
                    if($_SESSION['language'])
                    {
                        @$name = unserialize($cart->shipping->name);
                        if(isset($name[$_SESSION['language']]))
                            $cart->shipping->name = $name[$_SESSION['language']];
                        else if(is_array($name))
                            $cart->shipping->name = array_shift($name);
                        @$info = unserialize($cart->shipping->info);
                        if(isset($info[$_SESSION['language']]))
                            $cart->shipping->info = $info[$_SESSION['language']];
                        else if(is_array($info))
                            $cart->shipping->info = array_shift($info);
                    }
                    $cart->shipping->text = '';
                    if($cart->shipping->wl_alias && is_array($shipping_info))
                        $cart->shipping->text = $this->load->function_in_alias($cart->shipping->wl_alias, '__get_info', $cart->shipping_info);  
                }
                if(!empty($cart->shipping_info['price']))
                    $cart->shippingPrice = $cart->shipping_info['price'];
            }

            if($cart->products)
            {
                foreach ($cart->products as $product) {
                    $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                    if($product->storage_invoice)
                        $product->storage = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', array('id' => $product->storage_invoice, 'user_type' => $cart->user_type));
                    $product->price_format =  $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                    $cart->subTotal += $product->price * $product->quantity + $product->discount;
                    $product->sum_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity);
                    if($product->discount)
                    {
                        $product->sumBefore_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity + $product->discount);
                        $product->discountFormat = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->discount);
                    }
                }
                $cart->subTotalFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->subTotal);
                
                if($cart->discount)
                    $cart->discountFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->discount);
                
                if($cart->shippingPrice)
                    $cart->shippingPriceFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->shippingPrice);

                $cart->totalFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->total);

                if($cart->payed > 0 && $cart->payed < $cart->total)
                    $cart->payedFormat = $this->load->function_in_alias($cart->products[0]->product_alias, '__formatPrice', $cart->payed);
            }
        }
        return $cart;
    }

    public function __cart_subview($cart_id)
    {
        if($cart = $this->__get_cart($cart_id))
        {
            ob_start();
            $this->load->view('admin/__cart_subview', array('cart' => $cart));
            $subview = ob_get_contents();
            ob_end_clean();
            return $subview;
        }
        return false;
    }

    // $cartId => object $cart or numeric $cart->id
    private function _updateTotal($cartId)
    {
        $cart = $cartId;
        if(is_numeric($cartId))
            $cart = $this->db->getAllDataById('s_cart', $cartId);
        if (is_object($cart) && !empty($cart->id)) {
            if(isset($cart->{'1c_status'}) && $cart->{'1c_status'} > 0)
                $this->db->updateRow('s_cart', ['1c_status' => 0], $cart->id);
            $total = $this->db->getQuery("SELECT SUM(quantity * price) as totalPrice FROM `s_cart_products` WHERE `cart` = $cart->id")->totalPrice;
            if ($total) {
                if($cart->bonus)
                {
                    $this->load->smodel('cart_model');
                    $discount = $this->cart_model->getBonusDiscount($cart->bonus, $total);
                    $total -= $discount;
                }
                if($cart->shipping_id)
                    if($shipping = $this->db->getAllDataById('s_cart_shipping', array('id' => $cart->shipping_id, 'active' => 1)))
                        if($shipping->pay >= 0 && $total < $shipping->pay)
                            $total += $shipping->price;
            }
            $total = round($total, 3);
            $this->db->updateRow('s_cart', ['total' => $total, 'date_edit' => time()], $cartId);

            if($cart->payment_alias && $cart->payment_id)
                $this->load->function_in_alias($cart->payment_alias, '__editPayment', ['id' => $cart->payment_id, 'credit' => $total], true);
            return $total;
        }
        return false;
    }

}

?>