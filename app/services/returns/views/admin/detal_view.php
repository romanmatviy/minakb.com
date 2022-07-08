<div class="row">
    <div class="col-md-6">
        <?php $color = 'responsive';
        if($return->status == 2)
            $color = 'success';
        if($return->status == 3)
            $color = 'danger';
         ?>
        <div class="panel panel-<?=$color?>">
            <div class="panel-heading">
                <h4 class="panel-title">Детальна інформація</h4>
            </div>
            <div class="panel-body">
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <tr>
                            <th>Id</th>
                            <td><?=$return->id ?></td>
                        </tr>
                        <tr>
                            <th>Поточний статус</th>
                            <td><?php switch ($return->status) {
                                    case 1:
                                        if($return->manager > 0)
                                            echo "Повернення дозволено";
                                        else
                                            echo "Очікує обробки";
                                        break;
                                    case 2:
                                        echo "Підтверджено";
                                        break;
                                    case 3:
                                        echo "Скасовано";
                                        break;
                                } ?></td>
                        </tr>
                        <tr>
                            <th>Клієнт</th>
                            <td><a href="<?=SITE_URL?>admin/wl_users/<?= $return->user_email?>">#<?= $return->user_id?>. <?= $return->user_name?></a> <?=($return->user_phone) ? '('.$return->user_phone.')' : '' ?></td>
                        </tr>
                        <tr>
                            <th>Замовлення</th>
                            <td><a href="<?=SITE_URL?>admin/cart/<?=$return->cart_id?>">#<?=$return->cart_id?></a></td>
                        </tr>
                        <tr>
                            <th>Товар</th>
                            <td><a href="/admin/<?=$return->product_link?>"><strong><?=$return->product_article?></strong> <?=$return->product_manufacturer.' '.$return->product_name?></a></td>
                        </tr>
                        <tr>
                            <th>Продажна ціна (за яку продано)</th>
                            <td>$<?=$return->price?></td>
                        </tr>
                        <tr>
                            <th>Кількість одиниць на повернення</th>
                            <td><strong><?=$return->quantity?></strong></td>
                        </tr>
                        <?php if($return->storage_alias) { ?>
                            <tr>
                                <th>Склад</th>
                                <td><?=$return->storage_alias?>. <?=$return->storage_name?> (Накладна приходу #<?=$return->storage_invoice?>)</td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th>Причина повернення</th>
                            <td><?=$return->reason?></td>
                        </tr>
                        <?php if($return->status > 1 && !empty($return->info)) { ?>
                            <tr>
                                <th>Інформація до заявки</th>
                                <td><?=$return->info?></td>
                            </tr>
                        <?php } ?>
                        <?php if(!empty($return->ttn)) { ?>
                            <tr>
                                <th>ТТН повернення</th>
                                <td><?=$return->ttn?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th>Дата заявки</th>
                            <td><?php print_r(date('d.m.Y H:i', $return->date_add)) ?></td>
                        </tr>
                    </table>
                </div>
                <?php if ($return->manager > 0){ ?>
                <div class="table-responsive m-t-15">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <tr>
                            <th>Менеджер</th>
                            <td><a href="<?=SITE_URL?>admin/wl_users/<?= $return->manager_email?>">#<?= $return->manager?>. <?= $return->manager_name?></a></td>
                        </tr>
                        <tr>
                            <th>Дата оброблення</th>
                            <td><?=date('d.m.Y H:i', $return->date_manage)?></td>
                        </tr>
                        <?php if ($return->money > 0){ ?>
                            <tr>
                                <th>Платіжний механізм</th>
                                <td><?=($return->money == 1) ? 'Поповнено позитивний баланс' : 'Повернено готівкою' ?></td>
                            </tr>
                        <?php } if($return->status == 2) { ?>
                            <tr>
                                <th>Поповнення складу</th>
                                <td><?=$return->updateStorage?> од.</td>
                            </tr>
                            <tr>
                                <th>1С синхронізація</th>
                                <td><?= ($return->date_synchronization > 0) ? date('d.m.Y H:i', $return->date_synchronization) :'Очікуємо' ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            <?php }  ?>
            </div>
        </div>
    </div> 
    <div class="col-md-6">
    <?php if($return->status == 1) { ?>
        <div class="row">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h4 class="panel-title">Менеджер попереднє підтвердити</h4>
                </div>
                <div class="panel-body">
                     <form action="<?=SITE_URL?>admin/returns/save" method="POST">
                        <h4>Статус заявки: <u>Повернення дозволено</u> (попереднє підтвердження)</h4>
                        <input type="hidden" name="id" value="<?=$return->id?>">
                        <input type="hidden" name="status" value="1">
                        <h4>Інформація до заявки: (буде виведено клієнту)</h4>
                        <textarea name="info" rows="3" class="form-control"><?=$return->info?></textarea>
                        <?php $Cache = rand(0, 999); ?>
                        <input type="hidden" name="code_hidden" value="<?=$Cache?>">
                        <div class="form-group m-t-15">
                            <label class="col-md-3 control-label">Код перевірки <strong><?=$Cache?></strong></label>
                            <div class="col-md-6">
                                <input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
                            </div>
                            <button type="submit" class="col-md-3 btn btn-sm btn-info">Підтвердити заявку</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4 class="panel-title">Менеджер підтвердити</h4>
                </div>
                <div class="panel-body">
                     <form action="<?=SITE_URL?>admin/returns/save" method="POST">
                        <h4>Статус заявки: <u>Підтвердити</u></h4>
                        <input type="hidden" name="id" value="<?=$return->id?>">
                        <input type="hidden" name="status" value="2">
                        <h4>Повернено одиниць:</h4>
                        <input type="number" name="quantity" value="<?=$return->quantity?>" class="form-control" step="1" min="1" max="<?=$return->quantity_buy - $return->quantity_returned?>" required>
                        <h4>ТТН повернення:</h4>
                        <input type="text" name="ttn" value="<?=$return->ttn?>" class="form-control">
                        <?php if($return->storage_alias) { ?>
                            <h4>Залишок:</h4>
                            <label><input type="radio" name="toStorage" value="1" required> Зарахувати залишок на склад</label>
                            <label><input type="radio" name="toStorage" value="0"> БЕЗ зарахування залишку на склад</label>
                        <?php } if($return->payment_alias > 0 && $return->payment_id > 0) { ?>
                            <h4>Після підтвердження вартість товару/ів: </h4>
                            <label><input type="radio" name="moneyTo" value="cash" required> повернути готівкою (без зарахування на баланс)</label> <br>
                            <label><input type="radio" name="moneyTo" value="balance"> зарахувати клієнту на позитивний баланс</label>
                        <?php } ?>
                        <h4>Інформація до заявки: (буде виведено клієнту)</h4>
                        <textarea name="info" rows="3" class="form-control"></textarea>

                        <?php $Cache = rand(0, 999); ?>
                        <input type="hidden" name="code_hidden" value="<?=$Cache?>">
                        <div class="form-group m-t-15">
                            <label class="col-md-3 control-label">Код перевірки <strong><?=$Cache?></strong></label>
                            <div class="col-md-6">
                                <input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
                            </div>
                            <button type="submit" class="col-md-3 btn btn-sm btn-success">Підтвердити заявку</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h4 class="panel-title">Менеджер скасувати</h4>
                </div>
                <div class="panel-body">
                     <form action="<?=SITE_URL?>admin/returns/save" method="POST">
                        <h4>Статус заявки: <u>Скасувати повернення</u></h4>
                        <input type="hidden" name="id" value="<?=$return->id?>">
                        <input type="hidden" name="status" value="3">
                        
                        <h4>Інформація до заявки: (буде виведено клієнту)</h4>
                        <textarea name="info" rows="3" class="form-control"></textarea>

                        <?php $Cache = rand(0, 999); ?>
                        <input type="hidden" name="code_hidden" value="<?=$Cache?>">
                        <div class="form-group m-t-15">
                            <label class="col-md-3 control-label">Код перевірки <strong><?=$Cache?></strong></label>
                            <div class="col-md-6">
                                <input type="number" name="code_open" placeholder="<?=$Cache?>" min="0" class="form-control" required>
                            </div>
                            <button type="submit" class="col-md-3 btn btn-sm btn-danger">Скасувати заявку</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />