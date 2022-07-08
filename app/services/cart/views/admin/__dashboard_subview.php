<div class="panel panel-inverse">
    <div class="panel-heading">
        <div class="panel-heading-btn">
            <a href="<?=SITE_URL?>admin/cart" class="btn btn-xs btn-success"><i class="fa fa-list"></i> До всіх замовлень</a>
        </div>
        <h4 class="panel-title">Корзина. Активні замовлення</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="search-row">
                <form action="<?=SITE_URL?>admin/cart">
                    <div class="col-lg-8 col-sm-8 search-col">
                        <input type="number" name="id" min="1" class="form-control" placeholder="№ Замовлення" required>
                    </div>
                    <div class="col-lg-4 col-sm-4 search-col">
                        <button class="btn btn-primary btn-search btn-block"><i class="fa fa-search"></i><strong> Знайти</strong></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered nowrap" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Статус</th>
                        <th>Замовлення</th>
                        <th>Покупець</th>
                    </tr>
                </thead>
                <tbody>
                <?php $this->wl_alias_model->init('admin');
                $where = array();
                $where['status'] = array(1, 2, 3, 4, 5);
                $this->db->select('s_cart as c', '*', $where);
                $this->db->join('s_cart_status', 'name as status_name', '#c.status');
                $this->db->join('wl_users', 'name as user_name, email as user_email', '#c.user');
                $this->db->join('wl_user_info', 'value as user_phone', array('user' => '#c.user', 'field' => 'phone'));
                $this->db->order('date_add DESC');
                $this->db->limit(15);
                $carts =  $this->db->get('array');
                if(!empty($carts))
                {
                    $ids = array();
                    foreach ($carts as $cart) {
                        $cart->products = false;
                        $ids[] = $cart->id;
                    }
                    if($products = $this->db->getAllDataByFieldInArray('s_cart_products', array('cart' => $ids), 'cart'))
                        foreach ($carts as $cart) {
                            foreach ($products as $p) {
                                if($p->cart == $cart->id)
                                {
                                    if(!is_array($cart->products))
                                        $cart->products = array();
                                    $cart->products[] = clone $p;
                                }
                            }
                        }
                    $ids = array();
                    $activeDay = false;
                    foreach($carts as $cart)
                    {
                        if(in_array($cart->id, $ids))
                            continue;
                        $ids[] = $cart->id;
                        $day = date('d.m.Y', $cart->date_add);
                        if($activeDay != $day)
                        {
                            echo "<tr><th colspan=5>{$day}</th></tr>";
                            $activeDay = $day;
                        }

                    $color = 'default';
                    switch ($cart->status) {
                        case 1:
                        case 4:
                            $color = 'warning';
                            break;
                        case 2:
                            $color = 'success';
                            break;
                        case 3:
                            $color = 'primary';
                            break; 
                        case 5:
                            $color = 'danger';
                            break;
                    }
                    ?>
                <tr class="<?=$color?>">
                    <td title="<?= date('d.m.Y H:i', $cart->date_add)?>">
                        <a href="<?=SITE_URL?>admin/cart/<?=$cart->id?>" class="btn btn-<?=$color?> btn-xs"><?=$cart->id?></a>
                        <br>
                        <?= date('H:i', $cart->date_add)?>
                        <br>
                        на <strong><?= $cart->total?> грн</strong>
                    </td>
                    <td>
                        <strong><?= $cart->status_name?></strong> <?= $cart->date_edit > 0 ? '<br>від '.date('d.m.Y H:i', $cart->date_edit) : '' ?>
                    </td>
                    <td>
    <?php if($cart->products) {
        if($info = $this->load->function_in_alias($cart->products[0]->product_alias, '__get_Product', $cart->products[0]->product_id)) {
            if($info->photo) { ?>
                <a href="<?=SITE_URL?>admin/cart/<?=$cart->id?>" class="left">
                    <img src="<?=IMG_PATH?><?=(isset($info->cart_photo)) ? $info->cart_photo : $info->photo ?>" alt="<?=$this->text('Фото'). ' '. $info->name ?>" width="90">
                </a>
            <?php } if(!empty($info->article)) { ?>
                <a href="<?=SITE_URL.$info->link?>" target="_blank"><?= $info->article ?></a> <br>
            <?php } 
            echo '<strong>'.$info->name.'</strong>';
            if(!empty($product->product_options))
            {
                $product->product_options = unserialize($product->product_options);
                $opttext = '';
                foreach ($product->product_options as $key => $value) {
                    $opttext .= "{$key}: <strong>{$value}</strong>, ";
                }
                $opttext = substr($opttext, 0, -2);
                echo "<p>{$opttext}</p>";
            }
            else
                echo "<br>";
        }
        if($cart->products && count($cart->products) > 1) { ?>
            <p><a href="<?=SITE_URL?>admin/cart/<?=$cart->id?>" class="btn btn-<?=$color?> btn-xs">+ <?=count($cart->products) - 1?> товар</a></p>
        <?php } } ?>
                    </td>
                    <td>
                        <strong><?= ($cart->user_name != '') ? '<a href="'.SITE_URL.'admin/wl_users/'.$cart->user_email.'">'.$cart->user_name.'</a>' : 'Гість'?></strong>
                        <br>
                        <?= $cart->user_phone?>
                    </td>
                </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="8">Нові замовлення відсутні</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style type="text/css">
    .search-row {
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    .search-row .search-col {
        padding: 0;
        position: relative;
    }
    .search-row .search-col:first-child .form-control {
        border: 1px solid #16A085;
        border-radius: 3px 0 0 3px;
        margin-bottom: 20px;
    }
</style>