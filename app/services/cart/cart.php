<?php

/*

 	Service "Shop Cart 2.6.1"
	for WhiteLion 1.4

*/

class cart extends Controller {

    private $marketing = array();
    private $use__profile_view = true; // for list, detal subview
    private $email_required = false; // then phone required
    private $index_as_checkout_view = false;

    function __construct()
    {
        parent::__construct();
        if(empty($_SESSION['cart']))
            $_SESSION['cart'] = new stdClass();
        $_SESSION['cart']->initJsStyle = true;

        // if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1'))
        //     foreach ($cooperation as $c) {
        //         if($c->type == 'marketing')
        //             $this->marketing[] = $c->alias2;
        //     }
    }

    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index()
    {
        $this->load->smodel('cart_model');

        if($order_id = $this->data->uri(1))
        {
            if(is_numeric($order_id) && $order_id > 0)
            {
                if($this->userIs() || $this->data->get('key'))
                    $this->__view_order_by_id($order_id);
                else
                    $this->redirect('login?redirect='.$_SESSION['alias']->alias.'/'.$order_id);
            }
            else
                $this->load->page_404();
        }

        if($this->index_as_checkout_view)
        {
            $this->checkout();
            exit;
        }

        $product_alias = 0;
        if($products = $this->cart_model->getProductsInCart(-1, 0))
        {
            $product_alias = $products[0]->product_alias;
            $products = $this->setProductsInfo($products);
        }
        $this->wl_alias_model->setContent();
        $this->load->page_view('index_view', $this->addTotalData(compact('products'), $product_alias));
    }

    private function __view_order_by_id($id, $return = false)
    {
        $this->load->smodel('cart_model');
        if($cart = $this->cart_model->getById($id))
        {
            if(!$return)
            {
                $this->wl_alias_model->setContent($id);
                $_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Замовлення №').$id;
            }
            $go = false;
            if($this->userIs() && $cart->user == $_SESSION['user']->id || $this->userCan())
                $go = true;
            else if($key = $this->data->get('key'))
                if($user = $this->db->getAllDataById('wl_users', $key, 'auth_id'))
                    if($cart->user == $user->id)
                        $go = true;
            if($go)
            {
                if(!$return)
                    $_SESSION['alias']->breadcrumbs = array($this->text('До всіх замовлень') => $_SESSION['alias']->alias.'/my', $this->text('Замовлення №').$id => '');

                $cart->totalFormat = $cart->total;
                $cart->subTotal = $cart->subTotalFormat = $cart->shippingPrice = $cart->shippingPriceFormat = 0;
                $cart->shipping = $cart->payment = false;

                if($cart->shipping_id && !empty($cart->shipping_info))
                {
                    $cart->shipping_info = unserialize($cart->shipping_info);
                    if($cart->shipping = $this->cart_model->getShippings(array('id' => $cart->shipping_id)))
                    {
                        $cart->shipping->text = '';
                        if($cart->shipping->wl_alias)
                            $cart->shipping->text = $this->load->function_in_alias($cart->shipping->wl_alias, '__get_info', $cart->shipping_info);
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
                        if(!empty($product->product_alias))
                        {
                            $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $product->product_id);
                            if($product->storage_invoice)
                                $product->storage = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', array('id' => $product->storage_invoice, 'user_type' => $cart->user_type));
                            $product->price_format =  $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                            $cart->subTotal += $product->price * $product->quantity + $product->discount;
                            $product->sum_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity);
                            if($product->discount)
                                $product->sum_before_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity + $product->discount);
                        }
                        else if(!empty($product->product_options))
                        {
                            $options = unserialize($product->product_options);
                            $product->info = new stdClass();
                            $product->info->id = $product->id;
                            $product->info->photo = $options['photo'];
                            $product->info->cart_photo = $product->info->admin_photo = IMG_PATH.$options['cart_photo'];
                            $product->info->article = $product->info->article_show = $options['article'];
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
                        if ($cart->subTotal != $cart->total)
                            $cart->subTotalFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->subTotal);
                        $cart->totalFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->total);
                        if($cart->discount)
                            $cart->discountFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->discount);
                        if($cart->shippingPrice)
                            $cart->shippingPriceFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->shippingPrice);
                    }
                    if($cart->payed > 0 && $cart->payed < $cart->total)
                    {
                        $cart->toPay = $cart->toPayFormat = $cart->total - $cart->payed;
                        if($shop_alias)
                        {
                            $cart->payedFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->payed);
                            $cart->toPayFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->toPay);
                        }
                    }
                    else if($cart->payed < $cart->total)
                    {
                        $cart->toPay = $cart->toPayFormat = $cart->total;
                        $cart->payedFormat = 0;
                        if($shop_alias)
                            $cart->toPayFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->toPay);
                    }
                    else if($cart->payed > $cart->total)
                    {
                        $cart->toPay = $cart->toPayFormat = 0;
                        if($shop_alias)
                            $cart->payedFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart->payed);
                    }
                }

                if($cart->payment_alias && $cart->payment_id)
                    $cart->payment = $this->load->function_in_alias($cart->payment_alias, '__get_info', $cart->payment_id);
                else if($cart->payment_id)
                {
                    $cart->payment = $this->cart_model->getPayments(array('id' => $cart->payment_id));
                    if($cart->payment)
                        $cart->payment = $cart->payment[0];
                }

                if($return)
                {
                    $cart->return_url = $_SESSION['alias']->alias.'/success?order='.$cart->id;
                    return $cart;
                }

                if($this->data->uri(2) == 'print')
                    $this->load->view('print_view', array('cart' => $cart));
                elseif($this->data->uri(2) == 'pay' && !empty($cart->toPay))
                {
                    if($payments = $this->cart_model->getPayments(array('active' => 1, 'wl_alias' => '>0')))
                    {
                        // Вказує менеджер. Оплату при оформленні замовлення заблоковано
                        if(!empty($cart->shipping) && $cart->shipping->pay == -3 && $cart->status == 1)
                        {
                            $_SESSION['notify'] = new stdClass();
                            $_SESSION['notify']->errors = $this->text('Оплата замовлення після підтвердження менеджером!');

                            $accessKey = '';
                            if(!$this->userIs() && $this->data->get('key'))
                                $accessKey = '?key='.$this->data->get('key');
                            $this->redirect($_SESSION['alias']->alias.'/'.$cart->id.$accessKey);
                        }

                        $cooperation_where['alias1'] = $_SESSION['alias']->id;
                        $cooperation_where['type'] = 'payment';
                        $ntkd = array('alias' => '#c.alias2', 'content' => 0);
                        if($_SESSION['language'])
                            $ntkd['language'] = $_SESSION['language'];
                        $payments = $this->db->select('wl_aliases_cooperation as c', 'alias2 as id', $cooperation_where)
                                                ->join('s_cart_payments', 'name as payname, info as payinfo', ['wl_alias' => '#c.alias2'])
                                                ->join('wl_ntkd', 'name, list as info', $ntkd)
                                                ->get('array');
                        if(count($payments) == 1)
                        {
                            $accessKey = '';
                            if(!$this->userIs() && $this->data->get('key'))
                                $accessKey = '&key='.$this->data->get('key');
                            $this->redirect($_SESSION['alias']->alias.'/pay?cart='.$cart->id.'&method='.$payments[0]->id.$accessKey);
                        }
                        else
                        {
                            foreach ($payments as $pay) {
                                if(!empty($pay->payname))
                                {
                                    if($_SESSION['language'])
                                    {
                                        $name = unserialize($pay->payname);
                                        if(isset($name[$_SESSION['language']]))
                                            $pay->name = $name[$_SESSION['language']];
                                    }
                                    else
                                        $pay->name = $pay->payname;
                                }
                            }
                            if($this->userIs() && $this->use__profile_view)
                                $this->load->profile_view('pay_view', array('cart' => $cart, 'payments' => $payments));
                            else
                                $this->load->page_view('pay_view', array('cart' => $cart, 'payments' => $payments));
                        }
                    }
                    else
                        $this->redirect($_SESSION['alias']->alias.'/'.$cart->id);
                }
                else
                {
                    $showPayment = false;
                    if(!empty($_SESSION['notify']->meta))
                        $_SESSION['alias']->meta = $_SESSION['notify']->meta;

                    if($cart->payed < $cart->total)
                        if($payments = $this->cart_model->getPayments(array('active' => 1, 'wl_alias' => '>0')))
                            $showPayment = true;
                    if($showPayment)
                    {
                        // Вказує менеджер. Оплату при оформленні замовлення заблоковано
                        if(!empty($cart->shipping) && $cart->shipping->pay == -3 && $cart->status == 1)
                            $showPayment = false;
                    }
                    if($this->userIs() && $this->use__profile_view)
                        $this->load->profile_view('detal_view', compact('cart', 'showPayment'));
                    else
                        $this->load->page_view('detal_view', array('cart' => $cart, 'showPayment' => $showPayment, 'echoContainer' => true));
                }
                exit;
            }
            else
                $this->load->notify_view(array('errors' => $this->text('Немає прав для перегляду даного замовлення. Будь ласка, ').'<a href="'.SITE_URL.'login">'.$this->text('авторизуйтеся').'</a>'));
        }
        else if ($return)
            return false;
        else
            $this->load->page_404(false);
    }

    public function my($user = 0)
    {
        if($this->userIs())
        {
            $this->wl_alias_model->setContent();
            $_SESSION['alias']->link = implode('/', $this->data->url());
            $_SESSION['alias']->name = $this->text('Мої замовлення');

            if($user == 0)
                $user = $_SESSION['user']->id;
            if($id = $this->data->uri(2))
            {
                if($this->userCan() && is_numeric($id))
                    $user = $id;
                else
                    $this->load->page_404(false);
            }

            $showPayment = false;
            $this->load->smodel('cart_model');
            $orders = $this->cart_model->getCarts(compact('user'));
            if($orders)
            {
                $check_payments = false;
                foreach ($orders as &$order) {
                    if($order->shipping_name_ntkd)
                        $order->shipping_name = $order->shipping_name_ntkd;
                    else if($order->shipping_name && $_SESSION['language'])
                    {
                        $name = @unserialize($order->shipping_name);
                        $order->shipping_name = $name[$_SESSION['language']] ?? $order->shipping_name;
                    }
                    if(!empty($order->shipping_info))
                    {
                        $shipping_info = @unserialize($order->shipping_info);
                        if(is_array($shipping_info))
                            $order->shipping_info = $shipping_info;
                    }

                    if($order->status_weight < 90)
                        $check_payments = true;
                    if($order->total && !empty($order->products))
                    {
                        $order->total_format = $order->total;
                        if($order->products[0]->product_alias)
                            $order->total_format = $this->load->function_in_alias($order->products[0]->product_alias, '__formatPrice', $order->total);
                    }
                    else
                        $order->total_format = 0;
                }

                if($check_payments)
                    if($status = $this->db->getAllDataByFieldInArray($this->cart_model->table('_status'), 10, 'weight'))
                        if($payments = $this->cart_model->getPayments(array('active' => 1, 'wl_alias' => '>0')))
                            $showPayment = true;
            }

            if($this->use__profile_view)
                $this->load->profile_view('list_view', compact('orders', 'showPayment'));
            else
                $this->load->page_view('list_view', compact('orders', 'showPayment'));
            exit;
        }
        else
            $this->redirect('login');
    }

    public function addProduct($return_product = false)
    {
        $res = array('result' => false, 'subTotal' => 0);
        if($this->data->post('productKey') && $this->data->post('quantity') != 0)
        {
            $wl_alias = $id = $storage_alias = $storage_id = 0;
            $key = explode('-', $this->data->post('productKey'));
            if(count($key) >= 2 && is_numeric($key[0]) && is_numeric($key[1]))
            {
                $wl_alias = $key[0];
                $id = $key[1];
                if(isset($key[3]) && is_numeric($key[2]) && is_numeric($key[3]))
                {
                    $storage_alias = $key[2];
                    $storage_id = $key[3];
                }
            }
            $quantity = $this->data->post('quantity');

            if($id > 0 && is_numeric($quantity) && $quantity > 0)
            {
                $where = array('id' => $id);

                $product_options = $changePrice = array();
                if(!empty($_POST['options']) && is_array($_POST['options']))
                    foreach ($_POST['options'] as $option_id => $option_value) {
                        if(is_numeric($option_id) && is_numeric($option_value))
                        {
                            if($info = $this->load->function_in_alias($wl_alias, '__get_Option_Info', $option_id))
                            {
                                $my_option = new stdClass();
                                $my_option->id = $info->id;
                                $my_option->changePrice = $info->changePrice;
                                $my_option->name = $info->name;
                                $my_option->value_id = $option_value;
                                $my_option->value_name = '';
                                if(!empty($info->values))
                                    foreach ($info->values as $value) {
                                        if($value->id == $option_value)
                                        {
                                            $my_option->value_name = $value->name;
                                            break;
                                        }
                                    }
                                if(isset($info->changePrice) && $info->changePrice)
                                    $changePrice[$info->id] = $option_value;
                                $product_options[] = $my_option;
                            }
                        }
                    }
                if(!empty($changePrice))
                    $where['options'] = $changePrice;
                $where['additionalFileds'] = array('quantity' => $quantity);

                if($product = $this->load->function_in_alias($wl_alias, '__get_Product', $where))
                {
                    $product->name = html_entity_decode($product->name, ENT_QUOTES, 'utf-8');
                    $product->product_options = $product_options;
                    $product->storage_alias = $product->storage_invoice = 0;
                    if($storage_alias && $storage_id)
                        if($invoice = $this->load->function_in_alias($storage_alias, '__get_Invoice', $storage_id))
                        {
                            $product->storage_alias = $storage_alias;
                            $product->storage_invoice = $storage_id;
                            if($invoice->price_out)
                            {
                                $product->price = $invoice->price_out;
                                $product->price_format = 0;
                            }
                            if($invoice->price_in)
                                $product->price_in = $invoice->price_in;
                            if($invoice->amount_free < $product->quantity)
                                $product->quantity = $invoice->amount_free;
                            $product->sum_format = 0;
                        }
                    if(isset($product->discount))
                    	$product->discount *= $product->quantity;

                    $product->sum = $product->price * $product->quantity;
                    if(empty($product->price_format))
                        $product->price_format = $this->load->function_in_alias($wl_alias, '__formatPrice', $product->price);
                    if(empty($product->sum_format))
                        $product->sum_format = $this->load->function_in_alias($wl_alias, '__formatPrice', $product->sum);

                    if($return_product)
                        return $product;

                    $this->load->smodel('cart_model');
                    if($product->key = $this->cart_model->addProduct($product))
                    {
                        $openProduct = new stdClass();
                        $openProduct->key = $product->key;
                        $openProduct->article = $product->article_show ?? $product->article;
                        $openProduct->price_format = $product->price_format;
                        $openProduct->sum_format = $product->sum_format;
                        $openProduct->quantity = $product->quantity;
                        $openProduct->photo = $product->photo ?? false;
                        $openProduct->admin_photo = !empty($product->admin_photo) ? IMG_PATH.$product->admin_photo : false;
                        $openProduct->cart_photo = !empty($product->cart_photo) ? IMG_PATH.$product->cart_photo : false;
                        $openProduct->link = $product->link;
                        $openProduct->name = $product->name;
                        $openProduct->product_options = '';
                        if(!empty($product->product_options))
                            foreach ($product->product_options as $option) {
                                if(!empty($openProduct->product_options))
                                    $openProduct->product_options .= '<br>';
                                $openProduct->product_options .= $option->name.': '.$option->value_name;
                            }
                            
                        $res['result'] = true;
                        $res['product'] = $openProduct;
                        $res = $this->addTotalData($res, $wl_alias);
                    }
                }
            }
        }
        $this->load->json($res);
    }

    public function removeProduct()
    {
        $res = array('result' => false, 'subTotal' => 0);
        if($id = $this->data->post('id'))
            if(is_numeric($id))
            {
                $this->load->smodel('cart_model');
                if($product = $this->cart_model->getProductInfo(compact('id')))
                {
                    $user_id = $this->cart_model->getUser();
                    if($product->user == $user_id || $this->userCan())
                    {
                        if($product->cart == 0)
                        {
                            if($this->db->deleteRow($this->cart_model->table('_products'), $id))
                            {
                                $res['result'] = true;
                                $res['userShipping'] = $this->cart_model->getUserShipping();
                                $res['shippings'] = $this->cart_model->getShippings(array('active' => 1));
                                $res = $this->addTotalData($res, $product->product_alias);
                            }
                            else
                                $res['error'] = $this->text('Помилка оновлення інформації');
                        }
                        else
                            $res['error'] = $this->text('Редагувати інформацію про товар можна лише на неоформлених замовленнях!');
                    }
                    else
                        $res['error'] = $this->text('У Вас відсутній доступ до даного товару!');
                }
                else
                    $res['error'] = $this->text('Товар у корзині не ідентифіковано');
            }
            else
                $res['error'] = 'POST[id] must be numeric';
        $this->load->json($res);
    }

    public function updateProduct()
    {
        $res = array('result' => false, 'subTotal' => 0);
        $id = $this->data->post('id');
        if(is_numeric($id) && $id > 0)
        {
            $this->load->smodel('cart_model');
            if($product = $this->cart_model->getProductInfo(array('id' => $id)))
            {
                $user_id = $this->cart_model->getUser();
                if($product->user == $user_id || $this->userCan())
                {
                    if($product->cart == 0)
                    {
                        $quantity = $this->data->post('quantity');
                        if(is_numeric($quantity) && $quantity > 0)
                        {
                            $res['quantity'] = $product->quantity;
                            if($product->storage_invoice)
                            {
                                if($invoice = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', $product->storage_invoice))
                                {
                                    if($invoice->amount_free > $quantity)
                                    {
                                        $data = array();
                                        if($invoice->price_out)
                                            $product->price = $data['price'] = $invoice->price_out;
                                        if($invoice->price_in)
                                            $product->price_in = $data['price_in'] = $invoice->price_in;
                                        $res['quantity'] = $data['quantity'] = $data['quantity_wont'] = $quantity;
                                        if($this->db->updateRow($this->cart_model->table('_products'), $data, $id))
                                        {
                                            $res['result'] = true;
                                            $res['priceFormat'] = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                                            $res['priceSumFormat'] = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $res['quantity']);
                                        }
                                        else
                                            $res['error'] = $this->text('Помилка оновлення інформації');
                                    }
                                    else
                                        $res['error'] = $this->text('Увага! Недостатня кількість товару на складі');
                                }
                                else
                                    $res['error'] = $this->text('Товар відсутній на складі');
                            }
                            else
                            {
                                $product->key = $product->id;
                                $product->quantity = $quantity;
                                $products = $this->setProductsInfo([$product]);
                                $product = $products[0];

                                $data = array();
                                $res['quantity'] = $data['quantity'] = $data['quantity_wont'] = $quantity;
                                $data['discount'] = !empty($product->info->discount) && $product->info->discount > 0 ? $product->info->discount * $quantity : 0;
                                if($this->db->updateRow($this->cart_model->table('_products'), $data, $id))
                                {
                                    $res['result'] = true;
                                    $res['priceFormat'] = $product->info->price_format;
                                    $res['priceSumFormat'] = $product->info->sum_format;
                                }
                                else
                                    $res['error'] = $this->text('Помилка оновлення інформації');
                            }
                        }
                        elseif(isset($_POST['quantity']))
                            $res['error'] = $this->text('Кількість має бути більше нуля');
                        if(isset($_POST['active']) && ($_POST['active'] == 0 || $_POST['active'] == 1))
                        {
                            if($this->db->updateRow($this->cart_model->table('_products'), ['active' => $_POST['active']], $id))
                                    $res['result'] = true;
                        }

                        $res['userShipping'] = $this->cart_model->getUserShipping();
                        $res['shippings'] = $this->cart_model->getShippings(array('active' => 1));
                        $res = $this->addTotalData($res, $product->product_alias);
                    }
                    else
                        $res['error'] = $this->text('Редагувати інформацію про товар можна лише на неоформлених замовленнях!');
                }
                else
                    $res['error'] = $this->text('У Вас відсутній доступ до даного товару!');
            }
            else
                $res['error'] = $this->text('Товар у корзині не ідентифіковано');
        }
        $this->load->json($res);
    }

    public function checkUser($get_user = false)
    {
        $res = array('result' => false, 'message' => '');
        $key = 'email';
        $email_phone = $this->data->post('email_phone');
        if(empty($email_phone))
            $email_phone = $this->data->post('email');
        if(!$this->email_required || empty($email_phone))
            if($phone = $this->data->post('phone'))
            {
                $key = 'phone';
                $email_phone = $phone;
            }

        $this->load->library('validator');
        if(!$this->validator->email('email', $email_phone))
        {
            if($email_phone = $this->validator->getPhone($email_phone))
            {
                $key = 'phone';
                $password = $email_phone;
            }
            else
                $key = false;
        }
        if($key)
        {
            $this->load->model('wl_user_model');
            $user = new stdClass();
            if($this->wl_user_model->userExists($email_phone, $key, $user))
            {
                if(!empty($user->password) && $_SESSION['option']->usePassword || $get_user)
                {
                    $res['result'] = true;
                    if($get_user)
                        $res['user'] = $user;
                    $res['email'] = $user->email;
                    $res['message'] = '<p>'.$this->text('Доброго дня', 0);
                    if(date('H') > 18 || date('H') < 6)
                        $res['message'] = '<p>'.$this->text('Доброго вечора', 0);
                    if(!empty($user->name))
                        $res['message'] .= ', <strong>'.$user->name.'</strong>';
                    $res['message'] .= '</p><p>';
                    $res['message'] .= $this->text('У магазині за Вашою email адресою <strong>наявний персональний кабінет покупця</strong>. <u>Ваші персональні дані - найвища цінність для нас!</u><p> Просимо вибачення за дискомфорт, та змушені просити Вас <strong>ввести пароль</strong>, який Ви отримали при здійсненні першої покупки <br>(знайдіть лист у Вашій електронній скринці з інформацією про першу покупку) або встановили його самостійно в процесі реєстрації. </p><p>Якщо не можете знайти/згадати пароль доступу до кабінету, пропонуємо скористатися процедурою відновлення паролю. </p><p>З повагою, адміністрація '.SITE_NAME).'</p>';
                }
            }
        }
        if($this->data->post('ajax') == true)
            $this->load->json($res);
        else
            return $res;
    }

    public function checkout()
    {
        $this->load->smodel('cart_model');

        // for login by facebook or google
        $cart_user_id = $this->cart_model->getUser(false);
        if($this->userIs() && $cart_user_id != $_SESSION['user']->id)
            $this->__user_login();

        if($products = $this->cart_model->getProductsInCart())
        {
            if($this->userIs() && empty($_SESSION['user']->phone))
            {
                if($info = $this->db->select('wl_user_info', "value as phone", ['user' => $_SESSION['user']->id, 'field' => 'phone'])
                                ->limit(1)
                                ->get())
                    $_SESSION['user']->phone = $info->phone;
            }

            $products = $this->setProductsInfo($products);
            $payments = $this->cart_model->getPayments(array('active' => 1));
            $userShipping = $this->cart_model->getUserShipping();
            $shippings = $this->cart_model->getShippings(array('active' => 1));

            $bonusCodes = $this->cart_model->bonusCodes();
            if($bonusCodes && !empty($bonusCodes->info))
                foreach ($bonusCodes->info as $key => &$discount)
                    if(is_numeric($discount))
                        $discount = $this->load->function_in_alias($products[0]->product_alias, '__formatPrice', $discount);

            $this->wl_alias_model->setContent(1);
            $this->load->page_view('checkout_view', $this->addTotalData(compact('products', 'shippings', 'userShipping', 'payments', 'bonusCodes'), $products[0]->product_alias));
        }
        else if($this->index_as_checkout_view)
            $this->load->notify_view(['errors' => $this->text('Корзина пуста')]);
        else
            $this->redirect($_SESSION['alias']->alias);
    }

    public function confirm()
    {
        $this->__before_confirm();

        $this->load->smodel('cart_model');
        if($products = $this->cart_model->getProductsInCart())
        {
            $this->load->library('validator');
            if(!$this->userIs())
            {
                $email_rules = $this->email_required ? 'required|email' : 'email';
                if($email = $this->data->post('email'))
                    $this->validator->setRules($this->text('email'), $email, $email_rules);
                $this->validator->setRules($this->text('Ім\'я Прізвище'), $this->data->post('name'), 'required|3..50');
                if(!empty($_POST['phone']) || !$this->email_required)
                    $this->validator->setRules($this->text('Контактний номер'), $this->data->post('phone'), 'required|phone');
            }
            $shippings = $this->cart_model->getShippings(array('active' => 1));
            if($shippings)
            {
                if(empty($_POST['recipientName']))
                {
                    if(isset($_POST['name']))
                        $_POST['recipientName'] = $_POST['name'];
                    elseif($this->userIs() && !empty($_SESSION['user']->name))
                        $_POST['recipientName'] = $_SESSION['user']->name;
                }
                if(empty($_POST['recipientPhone']))
                {
                    if(isset($_POST['phone']))
                        $_POST['recipientPhone'] = $_POST['phone'];
                    elseif($this->userIs() && !empty($_SESSION['user']->phone))
                        $_POST['recipientPhone'] = $_SESSION['user']->phone;
                }
                $this->validator->setRules($this->text('Ім\'я Прізвище отримувача'), $this->data->post('recipientName'), 'required');
                $this->validator->setRules($this->text('Контактний номер'), $this->data->post('recipientPhone'), 'required|phone');
            }

            if($this->validator->run())
            {
                $_POST['phone'] = !empty($_POST['phone']) ? $this->validator->getPhone($_POST['phone']) : '';
                $_POST['recipientPhone'] = !empty($_POST['recipientPhone']) ? $this->validator->getPhone($_POST['recipientPhone']) : '';

                $this->load->model('wl_user_model');
                $new_user = $new_user_password = $user_auth_id = false;
                if(!$this->userIs())
                {
                    $check = $this->checkUser(true);
                    if($check['result'] && $check['user'])
                    {
                        if($_SESSION['option']->usePassword)
                        {
                            $_SESSION['notify'] = new stdClass();
                            $_SESSION['notify']->errors = $check['message'];
                            $this->redirect();
                        }
                        else
                        {
                            if(empty($check['user']->auth_id))
                            {
                                $check['user']->auth_id = md5(time());
                                $this->db->updateRow('wl_users', ['auth_id' => $check['user']->auth_id], $check['user']->id);
                            }
                            $user_auth_id = $check['user']->auth_id;
                            $this->wl_user_model->setSession($check['user']);
                        }
                    }
                    else
                    {
                        $info = $additionall = array();
                        $info['status'] = 1;
                        $info['email'] = $this->data->post('email');
                        $info['phone'] = $this->data->post('phone');
                        $info['name'] = $this->data->post('name');
                        if($_SESSION['option']->usePassword)
                            $info['password'] = $new_user_password = bin2hex(openssl_random_pseudo_bytes(4));
                        $additionall = array();
                        if(!empty($this->cart_model->additional_user_fields))
                            foreach ($this->cart_model->additional_user_fields as $key) {
                                $additionall[$key] = $this->data->post($key);
                            }
                        if($user = $this->wl_user_model->add($info, $additionall, $_SESSION['option']->new_user_type, $_SESSION['option']->usePassword, 'cart autoregister'))
                        {
                            $user_auth_id = $user->auth_id;
                            $this->wl_user_model->setSession($user);
                        }
                        else
                        {
                            $_SESSION['notify'] = new stdClass();
                            $_SESSION['notify']->errors = 'Помилка створення профілю клієнта';
                            $this->redirect();
                        }
                        $new_user = true;
                    }
                }
                if (empty($_SESSION['user']->name))
                {
                    if(empty($_POST['name']) && !empty($_POST['recipientName']))
                        $_POST['name'] = $_POST['recipientName'];
                    if(!empty($_POST['name']))
                    {
                        $this->db->updateRow('wl_users', array('name' => $this->data->post('name')), $_SESSION['user']->id);
                        $_SESSION['user']->name = $this->data->post('name');
                    }
                }
                if (empty($_SESSION['user']->phone))
                {
                    if(empty($_POST['phone']) && !empty($_POST['recipientPhone']))
                        $_POST['phone'] = $_POST['recipientPhone'];
                    if(!empty($_POST['phone']))
                    {
                        $this->wl_user_model->setAdditional($_SESSION['user']->id, 'phone', $_POST['phone']);
                        $_SESSION['user']->phone = $this->data->post('phone');
                    }
                }
                if($_SESSION['language'] && $_SESSION['user']->language != $_SESSION['language'])
                {
                    $this->db->updateRow('wl_users', ['language' => $_SESSION['language']], $_SESSION['user']->id);
                    $_SESSION['user']->language = $_SESSION['language'];
                }
                $this->__user_login(false);

                $orderInfo = [];
                $delivery_name = '';
                $delivery = array('id' => 0, 'recipient' => '', 'info' => [], 'text' => '');
                if($shippings)
                    if($shippingId = $this->data->post('shipping-method'))
                        if(is_numeric($shippingId))
                            if($shipping = $this->cart_model->getShippings(array('id' => $shippingId, 'active' => 1)))
                            {
                                $orderInfo['shipping'] = $shipping;
                                $delivery['id'] = $shipping->id;
                                $delivery_name = $shipping->name;
                                if($shipping->wl_alias)
                                {
                                    $info = $this->load->function_in_alias($shipping->wl_alias, '__set_Shipping_from_cart');
                                    if(!empty($info['info']))
                                        $delivery['info'] = $info['info'];
                                    if(!empty($info['text']))
                                        $delivery['text'] = $info['text'];
                                }
                                elseif(!empty($shipping->type_fields))
                                {
                                    $fields = ['country' => 'Країна', 'city' => 'Місто', 'department' => 'Відділення', 'address' => 'Адреса'];
                                    foreach($shipping->type_fields as $field_key)
                                    {
                                        if($value = $this->data->post('shipping-'.$field_key))
                                        {
                                            $field_name = $this->text($fields[$field_key]);
                                            $delivery['info'][$field_key] = $value;
                                            $delivery['text'] .= "{$field_name}: {$value}<br>";
                                        }
                                    }
                                }

                                $delivery['info']['recipientName'] = $this->data->post('recipientName');
                                $delivery['info']['recipientPhone'] = $this->data->post('recipientPhone');
                                if($shipping->pay >= 0)
                                {
                                    $delivery['pay'] = $delivery['info']['pay'] = $shipping->pay;
                                    $delivery['price'] = $delivery['info']['price'] = $shipping->price;
                                }
                                $delivery['text'] .= '<br><br>'.$this->text('Отримувач').': <strong>'.$delivery['info']['recipientName'].', '.$this->data->formatPhone($delivery['info']['recipientPhone']).'</strong>';
                            }

                $payment = false;
                if($payment_method = $this->data->post('payment_method'))
                    if($payment = $this->cart_model->getPayments(array('id' => $payment_method, 'active' => 1)))
                        $payment = $payment[0];

                $orderInfo = $this->addTotalData($orderInfo, $products[0]->product_alias);
                if($cart = $this->cart_model->checkout($_SESSION['user']->id, $delivery, $payment))
                {
                    $this->load->library('mail');

                    $orderInfo['order_id'] = $cart['id'];
                    $orderInfo['comment'] = $cart['comment'];
                    $orderInfo['date'] = date('d.m.Y H:i');
                    $orderInfo['user_name'] = $_SESSION['user']->name;
                    $orderInfo['user_email'] = $_SESSION['user']->email;
                    $orderInfo['user_phone'] = $this->data->formatPhone($_SESSION['user']->phone);
                    $orderInfo['new_user'] = $new_user;
                    if($new_user && $new_user_password)
                        $orderInfo['password'] = $new_user_password;
                    $orderInfo['link'] = SITE_URL.$_SESSION['alias']->alias.'/'.$cart['id'];
                    $orderInfo['admin_link'] = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$cart['id'];
                    if(!$_SESSION['option']->usePassword && $user_auth_id)
                            $orderInfo['link'] .= '?key='.$user_auth_id;

                    // ціна не оновлюється для зареєстрованого клієнта
                    $products = $this->setProductsInfo($products, false);
                    // $sum = 0;
                    $shop_alias = $products[0]->product_alias;
                    if(empty($shop_alias))
                    {
                        if($row = $this->db->select('s_cart_products', 'product_alias', ['product_alias' => '>0'])->limit(1)->get())
                            $shop_alias = $row->product_alias;
                    }
                    foreach ($products as $product) {
                        $product_alias = empty($products[0]->product_alias) ? $shop_alias : $products[0]->product_alias;
                        if($product->price == $product->info->price)
                        {
                            $product->price_format = $product->info->price_format;
                            $product->sum_format = $product->info->sum_format;
                        }
                        else
                        {
                            $product->price_format = $this->load->function_in_alias($product_alias, '__formatPrice', $product->price);
                            $product->sum_format = $this->load->function_in_alias($product_alias, '__formatPrice', $product->price * $product->quantity);
                        }
                        if($product->discount)
                            $product->sumBefore_format = $this->load->function_in_alias($product_alias, '__formatPrice', $product->price * $product->quantity + $product->discount);
                        // $sum += $product->price * $product->quantity + $product->discount;

                        if($product->storage_invoice && $product->storage_alias)
                        {
                            $reserve = array('invoice' => $product->storage_invoice, 'amount' => $product->quantity);
                            $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                        }
                    }
                    // $orderInfo['sum_formatted'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $sum);
                    // $orderInfo['total_formatted'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $cart['total']);
                    $orderInfo['products'] = $products;
                    $orderInfo['delivery'] = '<strong>'.$delivery_name.'</strong><br> '.$delivery['text'];
                    $orderInfo['payment'] = $payment ?? '';

                    $email_manager_notify = $_SESSION['option']->email_manager ?? SITE_EMAIL;

                    $this->mail->sendTemplate('checkout', $_SESSION['user']->email, $orderInfo);
                    $this->mail->sendTemplate('checkout_manager', $email_manager_notify, $orderInfo);

                    if(!$_SESSION['option']->usePassword && $user_auth_id)
                    {
                        $_SESSION['user'] = new stdClass();
                        setcookie('auth_id', '', time() - 3600, '/');
                    }

                    if($payment && $payment->wl_alias > 0)
                    {
                        $pay = new stdClass();
                        $pay->id = $cart['id'];
                        $pay->total = $cart['total'];
                        $pay->payed = 0;
                        $pay->wl_alias = $_SESSION['alias']->id;
                        $pay->return_url = $_SESSION['alias']->alias.'/success?order='.$cart['id'];
                        if(!$_SESSION['option']->usePassword && $user_auth_id)
                            $pay->return_url .= '&key='.$user_auth_id;

                        $this->load->function_in_alias($payment->wl_alias, '__get_Payment', $pay);
                    }
                    else
                    {
                        $this->wl_alias_model->setContent(2);
                        if (!empty($_SESSION['alias']->text) || !empty($_SESSION['alias']->meta)) {
                            $keys = array();
                            foreach ($orderInfo as $key => $value) {
                                $name = '{order.'.$key;
                                if(!is_object($value) && !is_array($value))
                                    $keys[$name.'}'] = $value;
                                else
                                    foreach ($value as $keyO => $valueO) {
                                        if(!is_object($valueO) && !is_array($valueO))
                                            $keys[$name.'.'.$keyO.'}'] = $valueO;
                                        else
                                            foreach ($valueO as $key1 => $value1) {
                                                if(!is_object($value1) && !is_array($value1))
                                                    $keys[$name.'.'.$keyO.'.'.$key1.'}'] = $value1;
                                            }
                                    }
                            }
                            if (!empty($_SESSION['alias']->meta) && strripos($_SESSION['alias']->meta, 'transactionProducts') !== false) {
                                $transactionProducts = '';
                                foreach ($cart['products'] as $product) {
                                    $transactionProducts .= "{
                                        'sku': '{$product->info->article_show}',
                                        'name': '{$product->info->name}',
                                        'category': '{$product->info->group_name}',
                                        'price': '{$product->price}',
                                        'quantity': '{$product->quantity}'
                                    },";
                                }
                                $keys['{{$transactionProducts}}'] = substr($transactionProducts, 0, -1);
                            }
                            $keys['{name}'] = $_SESSION['alias']->name;
                            $keys['{SITE_NAME}'] = SITE_NAME;
                            $keys['{SITE_URL}'] = SITE_URL;
                            $keys['{IMG_PATH}'] = IMG_PATH;
                            foreach (['text', 'meta'] as $key) {
                                foreach ($keys as $keyR => $valueR) {
                                    $_SESSION['alias']->$key = str_replace($keyR, $valueR, $_SESSION['alias']->$key);
                                }
                            }
                        }
                        $_SESSION['notify'] = new stdClass();
                        $_SESSION['notify']->title = $_SESSION['alias']->name;
                        $_SESSION['notify']->success = $_SESSION['alias']->text;
                        $_SESSION['notify']->meta = $_SESSION['alias']->meta;
                        if(!$_SESSION['option']->usePassword && $user_auth_id)
                            $this->redirect($_SESSION['alias']->alias.'/success?order='.$cart['id'].'&key='.$user_auth_id);
                        else
                            $this->redirect($_SESSION['alias']->alias.'/success?order='.$cart['id']);
                    }
                }
            }
            else
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->errors = $this->validator->getErrors();
                $this->redirect();
            }
        }
        else
            $this->redirect($_SESSION['alias']->alias);
    }

    public function success()
    {
        if($order_id = $this->data->get('order'))
        {
            if(is_numeric($order_id) && $order_id > 0)
            {
                if($this->userIs() || !empty($_GET['key']))
                    $this->__view_order_by_id($order_id);
                else
                    $this->redirect('login');
            }
            else
                $this->load->page_404(false);
        }
        else
            $this->load->page_404(false);
    }

    // buy per one click
    public function buyProduct()
    {
        $this->load->library('validator');
        $this->validator->setRules($this->text('productKey'), $this->data->post('productKey'), 'required|3..50');
        if(!$this->userIs())
        {
            $email_rules = $this->email_required ? 'required|email' : 'email';
            if($email = $this->data->post('email'))
                $this->validator->setRules($this->text('email'), $email, $email_rules);
            $this->validator->setRules($this->text('Ім\'я Прізвище'), $this->data->post('name'), 'required|5..50');
            if(!empty($_POST['phone']) || !$this->email_required)
                $this->validator->setRules($this->text('Контактний номер'), $this->data->post('phone'), 'required|phone');
        }
        if($this->validator->run())
        {
            $this->load->smodel('cart_model');
            $this->load->model('wl_user_model');
            if($product = $this->addProduct(true))
            {
                $_POST['phone'] = !empty($_POST['phone']) ? $this->validator->getPhone($_POST['phone']) : '';
                $new_user = $new_user_password = $user_auth_id = false;
                if(!$this->userIs())
                {
                    $check = $this->checkUser(true);
                    if($check['result'] && $check['user'])
                    {
                        if($_SESSION['option']->usePassword)
                        {
                            $_SESSION['notify'] = new stdClass();
                            $_SESSION['notify']->errors = $check['message'];
                            $this->redirect();
                        }
                        else
                        {
                            $user_auth_id = $check['user']->auth_id;
                            $this->wl_user_model->setSession($check['user']);
                        }
                    }
                    else
                    {
                        $info = $additionall = array();
                        $info['status'] = 1;
                        $info['email'] = $this->data->post('email');
                        $info['phone'] = $this->data->post('phone');
                        $info['name'] = $this->data->post('name');
                        if($_SESSION['option']->usePassword)
                            $info['password'] = $new_user_password = bin2hex(openssl_random_pseudo_bytes(4));
                        $additionall = array();
                        if(!empty($this->cart_model->additional_user_fields))
                            foreach ($this->cart_model->additional_user_fields as $key) {
                                $additionall[$key] = $this->data->post($key);
                            }
                        if($user = $this->wl_user_model->add($info, $additionall, $_SESSION['option']->new_user_type, $_SESSION['option']->usePassword, 'cart autoregister'))
                        {
                            $user_auth_id = $user->auth_id;
                            $this->wl_user_model->setSession($user);
                        }
                        $new_user = true;
                    }
                }
                if (empty($_SESSION['user']->name) && !empty($_POST['name']))
                    $this->db->updateRow('wl_users', array('name' => $this->data->post('name')), $_SESSION['user']->id);
                if (empty($_SESSION['user']->phone) && !empty($_POST['phone']))
                    $this->wl_user_model->setAdditional($_SESSION['user']->id, 'phone', $_POST['phone']);

                $cart = $this->cart_model->checkout($_SESSION['user']->id, array(), false, $product->sum);
                $this->cart_model->addProduct($product, $_SESSION['user']->id, $cart['id']);
                if($product->storage_invoice && $product->storage_alias)
                {
                    $reserve = array('invoice' => $product->storage_invoice, 'amount' => $product->quantity);
                    $this->load->function_in_alias($product->storage_alias, '__set_Reserve', $reserve);
                }
                $cart = $this->addTotalData($cart, $product->wl_alias);
                $cart['order_id'] = $cart['id'];
                $cart['date'] = date('d.m.Y H:i');
                $cart['user_name'] = $_SESSION['user']->name;
                $cart['user_email'] = $_SESSION['user']->email;
                $cart['user_phone'] = $this->data->formatPhone($_POST['phone']);
                $cart['new_user'] = $new_user;
                if($new_user && $new_user_password)
                    $cart['password'] = $new_user_password;
                $cart['link'] = SITE_URL.$_SESSION['alias']->alias.'/'.$cart['id'];
                $cart['admin_link'] = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$cart['id'];
                if(!$_SESSION['option']->usePassword && $user_auth_id)
                    $cart['link'] .= '?key='.$user_auth_id;

                $product->info = $product;
                $cart['products'] = [$product];
                $cart['delivery'] = $cart['delivery_price'] = $cart['payment'] = '';

                $email_manager_notify = $_SESSION['option']->email_manager ?? SITE_EMAIL;

                $this->load->library('mail');
                $this->mail->sendTemplate('checkout', $_SESSION['user']->email, $cart);
                $this->mail->sendTemplate('checkout_manager', $email_manager_notify, $cart);

                if(!$_SESSION['option']->usePassword && $user_auth_id)
                {
                    $_SESSION['user'] = new stdClass();
                    setcookie('auth_id', '', time() - 3600, '/');
                }

                $this->wl_alias_model->setContent(2);
                if (!empty($_SESSION['alias']->text) || !empty($_SESSION['alias']->meta)) {
                    $keys = array();
                    foreach ($cart as $key => $value) {
                        $name = '{cart.'.$key;
                        if(!is_object($value) && !is_array($value))
                            $keys[$name.'}'] = $value;
                        else
                            foreach ($value as $keyO => $valueO) {
                                if(!is_object($valueO) && !is_array($valueO))
                                    $keys[$name.'.'.$keyO.'}'] = $valueO;
                                else
                                    foreach ($valueO as $key1 => $value1) {
                                        if(!is_object($value1) && !is_array($value1))
                                            $keys[$name.'.'.$keyO.'.'.$key1.'}'] = $value1;
                                    }
                            }
                    }
                    if (!empty($_SESSION['alias']->meta) && strripos($_SESSION['alias']->meta, 'transactionProducts') !== false) {
                        $transactionProducts = '';
                        foreach ($cart['products'] as $product) {
                            $transactionProducts .= "{
                                'sku': '{$product->info->article_show}',
                                'name': '{$product->info->name}',
                                'category': '{$product->info->group_name}',
                                'price': '{$product->price}',
                                'quantity': '{$product->quantity}'
                            },";
                        }
                        $keys['{{$transactionProducts}}'] = substr($transactionProducts, 0, -1);
                    }
                    $keys['{name}'] = $_SESSION['alias']->name;
                    $keys['{SITE_NAME}'] = SITE_NAME;
                    $keys['{SITE_URL}'] = SITE_URL;
                    $keys['{IMG_PATH}'] = IMG_PATH;
                    foreach (['text', 'meta'] as $key) {
                        foreach ($keys as $keyR => $valueR) {
                            $_SESSION['alias']->$key = str_replace($keyR, $valueR, $_SESSION['alias']->$key);
                        }
                    }
                }
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->title = $_SESSION['alias']->name;
                $_SESSION['notify']->success = $_SESSION['alias']->text;
                $_SESSION['notify']->meta = $_SESSION['alias']->meta;
                if(!$_SESSION['option']->usePassword && $user_auth_id)
                    $this->redirect($_SESSION['alias']->alias.'/success?order='.$cart['id'].'&key='.$user_auth_id);
                else
                    $this->redirect($_SESSION['alias']->alias.'/success?order='.$cart['id']);
            }
        }
        else
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->errors = $this->validator->getErrors();
            $this->redirect($_SESSION['alias']->alias);
        }
    }

    public function coupon()
    {
        if($code = $this->data->post('code'))
        {
            $this->load->smodel('cart_model');
            if($this->cart_model->applayBonusCode($code))
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->success = $this->text('Бонус-код застосовано!');
            }
            else
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->errors = $this->text('Бонус-код невірний або застарів');
            }
        }
        else
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->errors = $this->text('Введіть бонус-код');
        }
        $this->redirect();
    }

    public function pay()
    {
        $accessKey = '';
        if(!$this->userIs() && $this->data->get('key'))
            $accessKey = '?key='.$this->data->get('key');

        if(isset($_POST['method']) && is_numeric($_POST['method']) && isset($_POST['cart']) && is_numeric($_POST['cart']))
        {
            if($cart = $this->db->getAllDataById('s_cart', $_POST['cart']))
            {
                $go = false;
                if($this->userIs() && $cart->user == $_SESSION['user']->id || $this->userCan())
                    $go = true;
                else if($key = $this->data->get('key'))
                    if($user = $this->db->getAllDataById('wl_users', $key, 'auth_id'))
                        if($cart->user == $user->id)
                            $go = true;
                if($go)
                {
                    $cart->return_url = $_SESSION['alias']->alias.'/'.$cart->id.$accessKey;
                    $cart->wl_alias = $_SESSION['alias']->id;

                    $this->load->function_in_alias($this->data->post('method'), '__get_Payment', $cart);
                }
                else
                    $this->load->notify_view(array('errors' => $this->text('Немає прав для перегляду даного замовлення.')));
                exit;
            }
        }

        if(isset($_GET['method']) && is_numeric($_GET['method']) && isset($_GET['cart']) && is_numeric($_GET['cart']))
        {
            if($cart = $this->db->getAllDataById('s_cart', $_GET['cart']))
            {
                $go = false;
                if($this->userIs() && $cart->user == $_SESSION['user']->id || $this->userCan())
                    $go = true;
                else if($key = $this->data->get('key'))
                    if($user = $this->db->getAllDataById('wl_users', $key, 'auth_id'))
                        if($cart->user == $user->id)
                            $go = true;
                if($go)
                {
                    $cart->return_url = $_SESSION['alias']->alias.'/'.$cart->id.$accessKey;
                    $cart->wl_alias = $_SESSION['alias']->id;

                    $this->load->function_in_alias($this->data->get('method'), '__get_Payment', $cart);
                }
                else
                    $this->load->notify_view(array('errors' => $this->text('Немає прав для перегляду даного замовлення.')));
                exit;
            }
        }

        $this->redirect();
    }

    // by ajax update shipping & payments in checkout page
    public function get_shipping()
    {
        if($shipping_id = $this->data->post('shipping_id'))
        {
            $this->load->smodel('cart_model');
            if($shipping = $this->cart_model->getShippings(array('id' => $shipping_id, 'active' => 1)))
            {
                $shipping->html = '';
                if($shipping->wl_alias > 0)
                {
                    $userShipping = $this->cart_model->getUserShipping();
                    if($userShipping)
                        $userShipping->initShipping = false;
                    ob_start();
                    $this->load->function_in_alias($shipping->wl_alias, '__get_Shipping_to_cart', $userShipping);
                    $shipping->html = ob_get_contents();
                    ob_end_clean();
                }
                unset($shipping->active, $shipping->wl_alias, $shipping->position, $shipping->alias);
                $product_alias = 0;
                if($products = $this->cart_model->getProductsInCart())
                    $product_alias = $products[0]->product_alias;
                $this->load->json($this->addTotalData(compact('shipping'), $product_alias));
            }
        }
    }

    public function set__shippingToOrder()
    {
        if($order_id = $this->data->post('order_id'))
        {
            $this->load->smodel('cart_model');
            if($cart = $this->cart_model->getById($order_id))
            {
                $go = false;
                if($this->userIs() && $cart->user == $_SESSION['user']->id || $this->userCan())
                    $go = true;
                else if($accessKey = $this->data->post('accessKey'))
                    if($user = $this->db->getAllDataById('wl_users', $accessKey, 'auth_id'))
                        if($cart->user == $user->id)
                            $go = true;
                if($go)
                {
                    if(empty($_POST['recipientName']) && !empty($_POST['recipientName1']))
                    {
                        $_POST['recipientName'] = $_POST['recipientName1'];
                        if(!empty($_POST['recipientSurName']))
                            $_POST['recipientName'] .= ' ' . $_POST['recipientSurName'];
                    }

                    $this->load->library('validator');
                    $this->validator->setRules($this->text('Ім\'я Прізвище отримувача'), $this->data->post('recipientName'), 'required');
                    $this->validator->setRules($this->text('Контактний номер'), $this->data->post('recipientPhone'), 'required|phone');
                    if($this->validator->run())
                    {
                        $_POST['recipientPhone'] = $this->validator->getPhone($_POST['recipientPhone']);

                        $delivery_info = [];
                        if($shippingId = $this->data->post('shipping-method'))
                            if(is_numeric($shippingId))
                                if($shipping = $this->cart_model->getShippings(array('id' => $shippingId, 'active' => 1)))
                                {
                                    if($shipping->wl_alias)
                                    {
                                        $info = $this->load->function_in_alias($shipping->wl_alias, '__set_Shipping_from_cart');
                                        if(!empty($info['info']))
                                            $delivery_info = $info['info'];
                                    }
                                    elseif(!empty($shipping->type_fields))
                                    {
                                        foreach($shipping->type_fields as $field_key)
                                        {
                                            if($value = $this->data->post('shipping-'.$field_key))
                                                $delivery_info[$field_key] = $value;
                                        }
                                    }

                                    $delivery_info['recipientName'] = $this->data->post('recipientName');
                                    $delivery_info['recipientPhone'] = $this->data->post('recipientPhone');

                                    $subTotal = $delivery_info['price'] = 0;
                                    if($cart->products)
                                        foreach($cart->products as $product)
                                        {
                                            $subTotal += $product->price * $product->quantity;
                                        }
                                    $subTotal -= $cart->discount;

                                    $update = ['total' => $subTotal];
                                    if($shipping->pay >= 0 && ($shipping->pay == 0 || $shipping->pay > $subTotal))
                                    {
                                        $update['total'] = $subTotal + $delivery['price'];
                                        $delivery_info['price'] = $delivery['price'];
                                    }
                                    $update['shipping_id'] = $shipping->id;
                                    $update['shipping_info'] = serialize($delivery_info);
                                    $this->db->updateRow($_SESSION['service']->table, $update, $cart->id);
                                }

                        if($link = $this->data->post('redirect'))
                            $this->redirect($link);

                        $accessKey = '';
                        if(!$this->userIs() && $this->data->post('accessKey'))
                            $accessKey = '?key='.$this->data->post('accessKey');
                        $this->redirect($_SESSION['alias']->alias.'/'.$cart->id.$accessKey);
                    }
                    else
                    {
                        $_SESSION['notify'] = new stdClass();
                        $_SESSION['notify']->errors = $this->validator->getErrors();
                        $this->redirect();
                    }
                }
                else
                    $this->load->notify_view(array('errors' => $this->text('Немає прав для перегляду даного замовлення.')));
                exit;
            }
        }
        echo "error saving shipping data";
        exit;
    }

    public function getProductsInCart()
    {
        $this->load->smodel('cart_model');
        $res = array('count' => 0, 'subTotal' => 0, 'subTotalFormat' => '', 'discountTotal' => 0);
        if($products = $this->cart_model->getProductsInCart($this->cart_model->getUser(false), 0))
        {
            $res['products'] = [];
            $products = $this->setProductsInfo($products);
            foreach ($products as $product) {
                $openProduct = new stdClass();
                $openProduct->key = $product->key;
                $openProduct->product_options = $product->product_options;
                $openProduct->price = $product->price;
                $openProduct->price_format = $product->info->price_format;
                $openProduct->quantity = $product->quantity;
                $openProduct->discount = $product->discount;
                $openProduct->sum_format = $product->info->sum_format;
                $openProduct->id = $product->info->id;
                $openProduct->article = $product->info->article_show ?? $product->info->article;
                $openProduct->name = $product->info->name;
                $openProduct->link = $product->info->link;
                $openProduct->photo = $product->info->photo ?? false;
                $openProduct->admin_photo = !empty($product->info->admin_photo) ? IMG_PATH.$product->info->admin_photo : false;
                $openProduct->cart_photo = !empty($product->info->cart_photo) ? IMG_PATH.$product->info->cart_photo : false;
                $openProduct->options = $product->info->options ?? false;
                if(!empty($product->storage))
                {
                    $openProduct->storage = new stdClass();
                    $openProduct->storage->name = $product->storage->storage_name;
                    $openProduct->storage->time = $product->storage->storage_time;
                }
                $res['products'][] = $openProduct;
            }
            $res = $this->addTotalData($res, $products[0]->product_alias);
        }
        $this->load->json($res);
    }

    public function getCountProductsInCart()
    {
        $this->load->smodel('cart_model');
        $res = array('count' => 0);
        if($products = $this->cart_model->getProductsInCart(-1, 0))
        {
            $res['count'] = $this->cart_model->getProductsCountInCart();
            $res = $this->addTotalData($res, $products[0]->product_alias);
        }
        $this->load->json($res);
    }

    public function __getById($id)
    {
        return $this->__view_order_by_id($id, true);
    }

    public function __show_btn_add_product($product)
    {
        if(!empty($product))
            $this->load->view('__btn_add_product_subview', array('product' => $product));
        else
            echo "<p>Увага! Відсутня інформація про товар! (для генерації кнопки <strong>Додати товар до корзини</strong>)</p>";
        return true;
    }

    public function __show_minicart()
    {
        $this->load->smodel('cart_model');
        $res = array('subTotal' => 0, 'subTotalFormat' => '', 'discountTotal' => 0);
        if($res['products'] = $this->cart_model->getProductsInCart($this->cart_model->getUser(false), 0))
        {
            $res['products'] = $this->setProductsInfo($res['products']);
            $res = $this->addTotalData($res, $res['products'][0]->product_alias);
        }
        $_SESSION['option']->uniqueDesign = false;
        $this->load->view('__minicart_subview', $res);
        return true;
    }

    public function __get_cart_statuses()
    {
        $this->load->smodel('cart_model');
        return $this->cart_model->getStatuses();
    }

    public function __get_user_orders($user)
    {
        $this->load->smodel('cart_model');
        return $this->cart_model->getCarts(array('user' => $user));
    }

    public function __get_Search($content)
    {
        return false;
    }

    public function __user_login($with_notify = true)
    {
        if($this->userIs())
        {
            $_SESSION['notify'] = new stdClass();
            $this->load->smodel('cart_model');
            $cart_user_id = $this->cart_model->getUser(false);
            if($cart_user_id < 0)
            {
                $notify = '';
                if($this->db->getCount($this->cart_model->table('_products'), array('cart' => 0, 'user' => $_SESSION['user']->id)))
                {
                    if($_SESSION['option']->useCheckBox)
                    {
                        $notify = '<br><br><strong>'.$this->text('Увага! Ми помітили, що Ви раніше додавали товар до корзини. Перевірте корзину перед замовленням').'</strong>';
                        $this->db->updateRow($this->cart_model->table('_products'), array('active' => 0), array('cart' => 0, 'user' => $_SESSION['user']->id));
                    }
                    else
                        $this->db->deleteRow($this->cart_model->table('_products'), array('active' => 0, 'cart' => 0, 'user' => $_SESSION['user']->id));
                }
                $this->db->updateRow($this->cart_model->table('_products'), array('user' => $_SESSION['user']->id), array('cart' => 0, 'user' => $cart_user_id));
                $this->db->deleteRow($this->cart_model->table('_users'), -$cart_user_id);
                $_SESSION['cart']->user = $_SESSION['user']->id;

                unset($_COOKIE['cart_id']);
                setcookie('cart_id', null, -1, '/');

                if($with_notify)
                {
                    if(date('H') > 18 || date('H') < 6)
                        $_SESSION['notify']->success = $this->text('Доброго вечора', 0).', <strong>'.$_SESSION['user']->name.'</strong>! '.$this->text('Дякуємо що повернулися');
                    else
                        $_SESSION['notify']->success = $this->text('Доброго дня', 0).', <strong>'.$_SESSION['user']->name.'</strong>! '.$this->text('Дякуємо що повернулися');
                    $_SESSION['notify']->success .= $notify;
                }
            }
        }
    }

    private function setProductsInfo($products, $getStorages = true)
    {
        foreach ($products as $product) {
            $where = array('id' => $product->product_id);
            if(!empty($product->product_options))
            {
                if(!is_array($product->product_options))
                   $product->product_options = unserialize($product->product_options);
                $changePrice = [];
                foreach ($product->product_options as $option) {
                    if(is_object($option) && $option->changePrice)
                        $changePrice[$option->id] = $option->value_id;
                }
                if(!empty($changePrice))
                    $where['options'] = $changePrice;
            }
            $where['additionalFileds'] = array('quantity' => $product->quantity);
            if($product->product_alias)
                $product->info = $this->load->function_in_alias($product->product_alias, '__get_Product', $where);
            else if(!empty($product->product_options))
            {
                $shop_alias = $products[0]->product_alias;
                if(empty($shop_alias))
                {
                    if($row = $this->db->select('s_cart_products', 'product_alias', ['product_alias' => '>0'])->limit(1)->get())
                        $shop_alias = $row->product_alias;
                }
                $options = unserialize($product->product_options);
                $product->info = new stdClass();
                $product->info->id = $product->id;
                $product->info->photo = $options['photo'];
                $product->info->cart_photo = $product->info->admin_photo = IMG_PATH.$options['cart_photo'];
                $product->info->article = $options['article'];
                $product->info->name = $options['name'];
                $product->info->link = $options['photo'] ?? '';
                $cart->subTotal += $product->price * $product->quantity + $product->discount;
                if(empty($shop_alias))
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
            if($getStorages)
            {
                if($product->storage_invoice)
                {
                    if($product->storage = $this->load->function_in_alias($product->storage_alias, '__get_Invoice', $product->storage_invoice))
                    {
                        if($product->storage->price_out)
                        {
                            $product->price = $product->info->price = $product->storage->price_out;
                            $product->price_format = $product->info->price_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price);
                        }
                        if($product->storage->price_in)
                            $product->price_in = $product->storage->price_in;
                        if($product->storage->amount_free < $product->quantity)
                            $product->quantity = $product->storage->amount_free;
                        $product->sum_format = $product->info->sum_format = $this->load->function_in_alias($product->product_alias, '__formatPrice', $product->price * $product->quantity);
                    }
                }
                $product = $this->cart_model->checkProductInfo($product, $product->info);
            }
        }
        return $products;
    }

    private function addTotalData(Array $res, $shop_alias = 0)
    {
        if(empty($shop_alias))
        {
            if($row = $this->db->select('s_cart_products', 'product_alias', ['product_alias' => '>0'])->limit(1)->get())
                $shop_alias = $row->product_alias;
        }

        $subTotal = $total = $this->cart_model->getSubTotalInCart();
        // $subTotal += $this->cart_model->discountTotal;

        if (!empty($res['shippings']))
        {
            $i = 0;
            if(!empty($res['userShipping']) && $res['userShipping']->method_id > 0)
            {
                foreach ($res['shippings'] as $key => $shipping) {
                    if($res['userShipping']->method_id == $shipping->id)
                    {
                        $i = $key;
                        break;
                    }
                }
            }
            $shipping = $res['shippings'][$i];
            $res['shippingTypeFields'] = $shipping->type_fields;
            $res['shippingPayAction'] = $shipping->pay_action;
            $res['shippingInfo'] = $shipping->info;
            $res['shippingWlAlias'] = $shipping->wl_alias;
            if ($shipping->pay_action == 'money' && is_numeric($shipping->price))
            {
                if ($shipping->pay == 0 || $shipping->pay > $total)
                {
                    $total += $shipping->price;
                    $res['shippingPriceFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $shipping->price);
                }
                else
                    $res['shippingPriceFormat'] = $this->__get_shippingPayText($shipping->pay_action);
            }
            else
                $res['shippingPriceFormat'] = $this->__get_shippingPayText($shipping->pay_action);
        }
        elseif(!empty($res['shipping']))
        {
            if($res['shipping']->pay_action == 'money' && is_numeric($res['shipping']->price))
            {
                if($res['shipping']->pay == 0 || $res['shipping']->pay > $total)
                {
                    $total += $res['shipping']->price;
                    $res['shipping']->priceFormat = $this->load->function_in_alias($shop_alias, '__formatPrice', $res['shipping']->price);
                }
                else
                    $res['shipping']->priceFormat = $this->__get_shippingPayText($res['shipping']->pay_action);
            }
            else
                $res['shipping']->priceFormat = $this->__get_shippingPayText($res['shipping']->pay_action);
        }

        $res['subTotal'] = $res['subTotalFormat'] = $subTotal;
        $res['total'] = $res['totalFormat'] = $total;
        $res['productsCountInCart'] = $this->cart_model->getProductsCountInCart();
        $res['discountTotal'] = $this->cart_model->discountTotal;
        
        if($shop_alias > 0)
        {
            $res['subTotalFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $subTotal);
            $res['totalFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $total);
            if ($this->cart_model->discountTotal)
                $res['discountTotalFormat'] = $this->load->function_in_alias($shop_alias, '__formatPrice', $this->cart_model->discountTotal);
        }

        if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1'))
        {
            $del_products = false;
            if(empty($res['products']))
            {
                $del_products = true;
                $res['products'] = $this->cart_model->getProductsInCart();
            }
            foreach ($cooperation as $c) {
                if($c->type == 'cart_gift')
                    $res['cart_gift'] = $this->load->function_in_alias($c->alias2, 'return__gift_subview', $res);
            }
            if($del_products)
                unset($res['products']);
        }

        return $res;
    }

    private function __get_shippingPayText($pay_action)
    {
        if($pay_action == 'by_manager')
            return $this->text('Уточнюється після замовлення');
        return $this->text('безкоштовно');
    }

    private function __before_confirm()
    {
        if(!empty($_POST['name']) && !empty($_POST['surname']))
            $_POST['name'] .= ' ' . $_POST['surname'];
        if(!empty($_POST['recipientName']) && !empty($_POST['recipientSurName']))
            $_POST['recipientName'] .= ' ' . $_POST['recipientSurName'];
        if(!$this->userIs() && empty($_POST['phone']) && !empty($_POST['recipientPhone']))
            $_POST['phone'] = $_POST['recipientPhone'];

        // $fields = ['street' => 'Вулиця', 'house' => 'Будинок', 'apartament' => 'Квартира', 'entrance' => 'Під’їзд', 'floor' => 'Поверх', 'cash' => 'Підготувати решту з', 'wand_devices' => 'Кількість приборів палочок'];
        // $br = ['street', 'floor', 'cash'];
        // $shipping_address = '';
        // foreach ($fields as $key => $name) {
        //     if($value = $this->data->post($key))
        //     {
        //         $shipping_address .= "{$name}: {$value}. ";
        //         if(in_array($key, $br))
        //             $shipping_address .= '<br>';
        //     }
        // }
        // if(!empty($shipping_address))
        //     $_POST['shipping-address'] = $shipping_address;
    }

}
