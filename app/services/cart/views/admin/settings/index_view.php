<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/settings/shipping/add" class="btn btn-xs btn-warning"><i class="fa fa-plus"></i> Додати перевізника</a>
                </div>
                <h4 class="panel-title"><i class="fa fa-car"></i> Доставка (керування перевізниками)</h4>
            </div>
			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 50px"></th>
                            	<th>Перевізник</th>
                                <th>Тип доставки</th>
                            	<th>Вартість</th>
                            	<th>Інформація</th>
								<th>Стан</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($shippings) foreach ($shippings as $shipping) { ?>
                                <tr id="shipping-<?=$shipping->id?>" <?=$shipping->active ? '' : 'class="danger"' ?>>
                                    <td class="move sortablehandle"><i class="fa fa-sort"></i></td>
                                    <td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/settings/shipping/'.$shipping->id?>"><?=$shipping->name?></a></td>
                                    <td><?php if($shipping->wl_alias)
                                                echo "Спеціальна (власні налаштування)";
                                              else if($shipping->type == 1)
                                                echo('Без адреси');
                                              else if($shipping->type == 2)
                                                echo('У відділення');
                                              else if($shipping->type == 3)
                                                echo('За адресою');
                                              else if($shipping->type == 4)
                                                echo('Міжнародна (за адресою + країна)');
                                     ?></td>
                                    <td><?php if($shipping->pay == -3)
                                                echo "Вказує менеджер. Оплату при оформленні замовлення заблоковано";
                                              else if($shipping->pay == -2)
                                                echo "Не виводити";
                                              else if($shipping->pay == -1)
                                                echo('безкоштовно');
                                              else
                                              {
                                                if($shipping->pay > 0)
                                                    echo('Ціна до '.$shipping->pay.' y.o. = ');
                                                echo($shipping->price.' y.o.');
                                              }
                                     ?></td>
                                    <td><?=$this->data->getShortText($shipping->info)?></td>
                                    <td>
                                        <input type="checkbox" data-render="switchery" <?=($shipping->active == 1) ? 'checked' : ''?> value="1" onchange="changeActive(this, 'shipping-<?=$shipping->id?>')" />
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr><td colspan="6" class="text-center"><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/settings/shipping/add" class="btn btn-xs btn-warning"><i class="fa fa-plus"></i> Додати перевізника</a></td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/settings/payment/add" class="btn btn-xs btn-warning"><i class="fa fa-plus"></i> Додати просту оплату</a>
                </div>
                <h4 class="panel-title"><i class="fa fa-dollar"></i> Оплата</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 50px"></th>
                                <th>Оплата</th>
                                <th>Інформація</th>
                                <th>Стан</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($payments) foreach ($payments as $pay) { ?>
                                <tr id="payment-<?=$pay->id?>" <?=$pay->active ? '' : 'class="danger"' ?>>
                                    <td class="move sortablehandle"><i class="fa fa-sort"></i></td>
                                    <td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/settings/payment/'.$pay->id?>"><?=$pay->name?></a></td>
                                    <td><?=$this->data->getShortText($pay->info)?></td>
                                    <td>
                                        <input type="checkbox" data-render="switchery" <?=($pay->active == 1) ? 'checked' : ''?> value="1" onchange="changeActive(this, 'payment-<?=$pay->id?>')" />
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr><td colspan="4" class="text-center"><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/settings/payment/add" class="btn btn-xs btn-warning"><i class="fa fa-plus"></i> Додати просту оплату</a></td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/admin_settings.js';
      $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />