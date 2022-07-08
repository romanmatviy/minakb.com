<div class="row">
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <?php $color = 'default';
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
                } $colspan = 6; ?>
            <label class="label label-<?=$color?> pull-right"><?= $cart->status_name ?? 'Формування' ?></label>
            <h4 class="panel-title"> <a href="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/<?=$cart->id?>" target="_blank">Замовлення <label class="label label-<?=$color?>">#<?=$cart->id?></label> від <?=date('d.m.Y H:i', $cart->date_add)?></a></h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered nowrap" width="100%">
                <thead>
                    <tr>
                        <?php if(!empty($cart->products[0]->info->article)) { ?>
                            <th>Артикул</th>
                        <?php } ?>
                        <th>Продукт</th>
                        <?php if(!empty($cart->products[0]->storage)) { $colspan++; ?>
                            <th>Склад</th>
                        <?php } ?>
                        <th>Ціна</th>
                        <th>Кількість од.</th>
                        <th>Разом</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($cart->products) foreach($cart->products as $product) { ?>
                    <tr id="productId-<?= $product->id ?>">
                        <?php if(!empty($product->info->article)) { ?>
                            <td><a href="<?=SITE_URL.$product->info->link?>" target="_blank"><?= $product->info->article_show ?? $product->info->article ?></a></td>
                        <?php } ?>

                        <td>
                            <?php if(!empty($product->info->photo) && !empty($product->info->admin_photo)) { ?>
                                <a href="<?=SITE_URL.$product->info->link?>" class="left">
                                    <img src="<?=IMG_PATH?><?=(isset($product->info->cart_photo)) ? $product->info->cart_photo : $product->info->admin_photo ?>" alt="<?=$this->text('Фото'). ' '. $product->info->name ?>" width="90">
                                </a>
                            <?php }
                            if(!empty($product->info))
                                echo '<strong>'.$product->info->name.'</strong>';
                            $product_options = [];
                            if(!empty($product->product_options))
                            {
                                $product->product_options = unserialize($product->product_options);
                                foreach ($product->product_options as $option) {
                                    $product_options[$option->id] = $option->value_id;
                                    echo "<br>{$option->name}: <strong>{$option->value_name}</strong>";
                                } 
                            } ?>
                        </td>

                        <?php if(!empty($product->storage)) { ?>
                        <td width="20%">
                            <?php echo $product->storage->storage_name; ?>
                        </td>
                        <?php } ?>

                        <td><?= $product->price_format ?></td>
                        <td><?= $product->quantity ?></td>
                        <td><strong><?= $product->sum_format ?></strong></td>
                    </tr>
                    <?php } 
                    if ($cart->subTotal != $cart->total) { ?>
                        <tr>
                            <td colspan="<?=$colspan?>" class="text-right" >
                                Сума: <strong><?= $cart->subTotalFormat ?></strong>
                            </td>
                        </tr>
                        <?php if ($cart->discount) { ?>
                        <tr>
                            <td colspan="<?=$colspan?>" class="text-right" >
                                Знижка: <strong><?= $cart->discountFormat ?></strong>
                            </td>
                        </tr>
                        <?php } if ($cart->shippingPrice) { ?>
                        <tr>
                            <td colspan="<?=$colspan?>" class="text-right" >
                                Доставка: <strong><?= $cart->shippingPriceFormat ?></strong>
                            </td>
                        </tr>
                    <?php } } ?>
                    <tr>
                        <td colspan="<?=$colspan?>" class="text-right" >
                            <h4 class="m-0">До оплати: <strong><?= $cart->totalFormat ?></strong> <?php 
                                if($cart->payed == 0) echo "<label class=\"label label-danger\">Не оплачено</label>";
                                elseif($cart->payed >= $cart->total) echo "<label class=\"label label-success\">Оплачено повністю</label>";
                                else echo "<label class=\"label label-warning\">Часткова оплата <u>{$cart->payedFormat}</u></label>"; 
                                ?></h4>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if($cart->shipping_id || !empty($cart->shipping_info)) {
                echo '<legend><i class="fa fa-truck" aria-hidden="true"></i> Доставка</legend>';
                if(!empty($cart->shipping->name))
                    echo "<p>Служба доставки: <b>{$cart->shipping->name}</b> </p>";
                if(!empty($cart->shipping->text))
                    echo "<p>{$cart->shipping->text}</p>";
                elseif(is_array($cart->shipping_info))
                {
                    if(!empty($cart->shipping_info['city']))
                        echo "<p>Місто: <b>{$cart->shipping_info['city']}</b> </p>";
                    if(!empty($cart->shipping_info['department']))
                        echo "<p>Відділення: <b>{$cart->shipping_info['department']}</b> </p>";
                    if(!empty($cart->shipping_info['address']))
                        echo "<p>Адреса: <b>{$cart->shipping_info['address']}</b> </p>";
                }
                elseif(!empty($cart->shipping_info) && is_string($cart->shipping_info))
                    echo "<p>{$cart->shipping_info}</p>";
                if(!empty($cart->ttn))
                    echo "<p>ТТН доставки: <b>{$cart->ttn}</b> </p>";
                if(!empty($cart->shipping_info['recipient']))
                    echo "<p>Отримувач: <b>{$cart->shipping_info['recipient']}</b> </p>";
                if(!empty($cart->shipping_info['phone']))
                    echo "<p>Контактний телефон: <b>{$cart->shipping_info['phone']}</b> </p>";
            } ?>

        </div>
    </div>
</div>