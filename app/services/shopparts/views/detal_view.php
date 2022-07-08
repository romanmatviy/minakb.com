<div class="container">
    <?php if(!$this->userIs()) { ?>
        <div class="row">
            <div class="alert alert-warning">
                <h4>Увага!</h4>
                <p>Для отримання оптових цін необхідно <a href="<?=SITE_URL?>login">увійти</a> або <a href="<?=SITE_URL?>signup">зареєструватися</a>.</p>
            </div>
        </div>
    <?php } ?>

    <div class="row">
        <div class="alert alert-warning">
            <h4>Увага!</h4>
            <p>При замовленні товарів не з Львівського складу на суму менше 500 грн, додатково оплачується вартість доставки 30 грн.</p>
        </div>
    </div>

    <div class="row">
        <div class="inner-box ads-details-wrapper">
            <h3>Оригінали та замінники</h3>
            
            <?php if(trim($_SESSION['alias']->text) != '') { ?>
                <div class="ads-details-info col-md-8">
                    <?= html_entity_decode($_SESSION['alias']->text)?>
                </div>
            <?php }

            $cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1');
            
            $products_on_page = array();
            $analogs = array();
            $count_products = 0;

            if($cooperation)
            {
                $products = !isset($products) ? $this->shop_model->getProducts('%'.$product->article) : $products;
                $showStorages = true;
                $storages = array();
                    foreach ($cooperation as $storage) {
                        if($storage->type == 'storage')
                            $storages[] = $storage->alias2;
                    }
                foreach ($products as $product) {
                    $products_on_page[] = $product->id;

                    $count = 0;
                    if(isset($product->options['2-analohy']) && $product->options['2-analohy']->value != ''){
                        $productAnalogs = explode(';', $product->options['2-analohy']->value);
                        foreach ($productAnalogs as $analog) { 
                        	$analog = trim($analog);
                            if($analog != '' && !in_array($analog, $analogs)) $analogs[] = $analog;
                        }
                    }
                    
                    $invoices = $this->shop_model->getInvoices($product->id, $storages);
                    if($invoices)
                    {
                        foreach ($invoices as $invoice) {
                            if($invoice->amount_free > 0) {
                                if($showStorages)
                                {
                                    echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
                                    echo("<tr>");
                                    echo("<td>Бренд</td>");
                                    echo("<td>Артикул</td>");
                                    echo("<td>Опис</td>");
                                    echo("<td>Термін</td>");
                                    // echo("<td>Постачальник</td>");
                                    echo("<td>Ціна USA</td>");
                                    echo("<td>Ціна грн</td>");
                                    echo("<td>Кількість</td>");
                                    echo("<td></td>");
                                    echo("</tr>");
                                    $showStorages = false;
                                }
                                
                                $class = ($invoice->storage == 4 || $invoice->storage == 6) ? 'class="success"' : '';

                                echo("<tr>");
                                if($count == 0){ 
                                    echo "<td {$class}>". ((isset($product->options['1-vyrobnyk']) && $product->options['1-vyrobnyk']->value != '') ? nl2br($product->options['1-vyrobnyk']->value) : '')."</td>";
                                    echo("<td {$class}>{$product->article}</td>");
                                    echo "<td {$class}>".html_entity_decode($product->name)."</td>";
                                    $count++;
                                } else echo "<td></td><td></td><td></td>";
                                
                                echo("<td {$class}>{$invoice->storage_time}</td>");
                                // echo("<td>{$invoice->storage_name}</td>");
                                echo("<td {$class}>\$".round($invoice->price_out, 2)."</td>");
                                $invoice->price_out_uah = round($invoice->price_out * $currency_USD, 2);
                                echo("<td {$class} title='1 USD = {$currency_USD} грн'>{$invoice->price_out_uah}</td>");
                                if($invoice->amount_free < 5)
                                    echo("<td {$class} title='Увага! Існує ймовірність відсутності товару на складі'>< 5 од.</td>");
                                else
                                    echo("<td {$class} title='Товар в достатній кількості на складі'>>= 5 од.</td>");
                                echo("<td {$class}><button class='btn btn-sm' data-toggle='modal' data-target='#cartModal' data-productName='{$product->name}' data-productQuantity='{$invoice->amount_free}' data-productArticle='{$product->article}' data-productBrand=". ((isset($product->options['1-vyrobnyk']) && $product->options['1-vyrobnyk']->value != '') ? nl2br($product->options['1-vyrobnyk']->value) : '')." data-productPrice='{$invoice->price_out}' data-product='{$invoice->product}' data-alias='{$_SESSION['alias']->id}'  data-invoice='{$invoice->id}' data-storage='{$invoice->storage}' ><i class='fa fa-shopping-cart'></i></button></td>");
                                echo("</tr>");
                                $count_products++;
                            }
                        }
                    }
                }
                if(!$showStorages)
                {
                    echo("</table></div>");
                }
            }

            if($count_products == 0)
            {
                ?>
                <div class="alert alert-danger">
                <h4><?=$product->article?> <?=$product->name?></h4>
                <p>Увага! Товар відсутній на складі.</p>
            </div>
            <?php
            }

            if(!empty($analogs))
            {
                $showStorages = true;
                foreach ($analogs as $analog) {
                    $article = (string) $analog;
                    $article = trim($article);
                    $article = strtoupper($article);
                    $article = str_replace('-', '', $article);
                    $analog = str_replace(' ', '', $article);
                    $analog = $this->shop_model->getProducts('%'.$analog);
                    if($analog)
                    {
                        foreach ($analog as $product) {
                            if(in_array($product->id, $products_on_page)) continue;
                            
                            $count = 0;
                            $invoices = $this->shop_model->getInvoices($product->id, $storages);
                            if($invoices)
                            {
                                foreach ($invoices as $invoice) {
                                    if($invoice->amount_free > 0) {
                                        if($showStorages)
                                        {
                                            echo("<h3> Аналоги для ".$_SESSION['alias']->name.'</h3>');
                                            echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
                                            echo("<tr>");
                                            echo("<td>Бренд</td>");
                                            echo("<td>Артикул</td>");
                                            echo("<td>Опис</td>");
                                            echo("<td>Термін</td>");
                                            // echo("<td>Постачальник</td>");
                                            echo("<td>Ціна USA</td>");
                                            echo("<td>Ціна грн</td>");
                                            echo("<td>Кількість</td>");
                                            echo("<td></td>");
                                            echo("</tr>");
                                            $showStorages = false;
                                        }
                                        $class = ($invoice->storage == 4 || $invoice->storage == 6) ? 'class="success"' : '';

                                        echo("<tr>");
                                        if($count == 0){ 
                                            echo "<td {$class}>". ((isset($product->options['1-vyrobnyk']) && $product->options['1-vyrobnyk']->value != '') ? nl2br($product->options['1-vyrobnyk']->value) : '')."</td>";
                                            echo("<td {$class}>{$product->article}</td>");
                                            echo "<td {$class}>".html_entity_decode($product->name)."</td>";
                                            $count++;
                                        } else echo "<td></td><td></td><td></td>";

                                        echo("<td {$class}>{$invoice->storage_time}</td>");
                                        // echo("<td {$class}>{$invoice->storage_name}</td>");
                                        echo("<td {$class}>\${$invoice->price_out}</td>");
                                        $invoice->price_out_uah = round($invoice->price_out * $currency_USD, 2);
                                        echo("<td {$class} title='1 USD = {$currency_USD} грн'>{$invoice->price_out_uah}</td>");
                                        if($invoice->amount_free < 5)
                                            echo("<td {$class} title='Увага! Існує ймовірність відсутності товару на складі'>< 5 од.</td>");
                                        else
                                            echo("<td {$class} title='Товар в достатній кількості на складі'>>= 5 од.</td>");
                                        echo("<td {$class}><button class='btn btn-sm' data-toggle='modal' data-target='#cartModal' data-productName='{$product->name}' data-productQuantity='{$invoice->amount_free}' data-productArticle='{$product->article}' data-productBrand=". ((isset($product->options['1-vyrobnyk']->value) && $product->options['1-vyrobnyk']->value != '') ? nl2br($product->options['1-vyrobnyk']->value) : '')." data-productPrice='{$invoice->price_out}' data-product='{$invoice->product}' data-alias='{$_SESSION['alias']->id}'  data-invoice='{$invoice->id}' data-storage='{$invoice->storage}' ><i class='fa fa-shopping-cart'></i></button></td>");
                                        echo("</tr>");
                                        $count_products++;
                                    }
                                }
                            }
                        }
                    }
                }
                if(!$showStorages)
                {
                    echo("</table></div>");
                }
            }
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" style="margin: 15% auto;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group">
                        <label class="control-label col-md-3">Кількість:</label><div class="col-md-12"></div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-btn"><button class="btn btn-success" id="modalButtonMinus">-</button></span>
                                <input type="text" class="form-control" value="1" style="height: 39px;" id="cartQuantity">
                                <span class="input-group-btn"><button class="btn btn-success" id="modalButtonPlus">+</button></span>
                            </div>
                        </div>
                        <div class="col-md-9" hidden id="modalError">
                            <h5 style="color:red">Ви хочете перевищити максимальну кількість товарів на складі</h5>
                        </div>
                    </div>
                </div>
                <p>Сума : <span id="modalTotalProductPrice"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
                <button type="button" class="btn btn-primary" id="addProductToCart">Додати</button>
            </div>
        </div>
    </div>
</div>