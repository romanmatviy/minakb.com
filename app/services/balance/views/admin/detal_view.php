<div class="row">
    <div class="col-md-6">
        <?php $color = 'warning';
        if($payment->status == 2) $color = 'success';
        if($payment->status > 2) $color = 'danger';
        ?>

        <div class="panel panel-<?=$payment->security_check ? 'inverse':'danger'?>">
            <div class="panel-heading">
                <h4 class="panel-title">Платіж #<?=$payment->id?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <tr>
                            <th width="250px" nowrap>Сформовано</th>
                            <td><?=date('d.m.Y H:i', $payment->date_add) ?> </td>
                        </tr>
                        <tr>
                            <th nowrap>Статус оплати</th>
                            <td><label class="label label-<?=$color?>"><?php
                                switch ($payment->status) {
                                    case 1:
                                        echo "Очікує обробки";
                                        break;
                                    case 2:
                                        echo "Підтверджено";
                                        break;
                                    case 3:
                                        echo "Скасовано";
                                        break;
                                    case 4:
                                        echo "Підтверджено => Скасовано";
                                        break;
                                } ?></label></td>
                        </tr>
                        <?php if(!empty($payment->currency)) { ?>
                            <tr>
                                <th>Курс валют</th>
                                <td><?=$payment->currency?> грн/1$</td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th>Дебет</th>
                            <td>$<?=$payment->debit?></td>
                        </tr>
                        <?php if(!($payment->debit && $payment->status == 1 && empty($payment->credit))) { ?>
                        <tr>
                            <th>Кредит</th>
                            <td>$<?=$payment->credit?>
                                
                                <?php if($payment->credit > 0 && $payment->action_id) { ?>
                                    <a href="<?=SITE_URL?>admin/<?=$payment->action_alias_link?>/<?=$payment->action_id?>" class="btn btn-info btn-xs" target="_blank">Замовлення #<?=$payment->action_id?></a>
                            <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Баланс (залишок)</th>
                            <td>$<?=$payment->balance?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>Інформація до заявки</th>
                            <td><?=$payment->action?></td>
                        </tr>
                        <tr>
                            <th>Коментар</th>
                            <td><?=$payment->info?></td>
                        </tr>
                        <?php if ($payment->manager > 0){ ?>
                            <tr>
                                <th width="150px" nowrap>Менеджер</th>
                                <td>
                                    <strong><?=$payment->manager.'. '.$payment->manager_name; ?></strong> <br>
                                    Опрацьовано <strong><?=date('d.m.Y H:i', $payment->date_edit)?></strong>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th>Цифровий підпис платежу</th>
                            <td><label class="label label-<?=$payment->security_check ? 'success':'danger'?>"><?=$payment->security_check ? 'Коректний':'Помилка!'?></label> <?=$payment->security_check ? $payment->sign : $this->balance_model->getPaymentSign($payment)?></td>
                        </tr>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Клієнт</h4>
                </div>
                <div class="panel-body">
                    <p><a href="<?=SITE_URL?>admin/wl_users/<?=$payment->client_email?>#tabs-<?=$_SESSION['alias']->alias?>" class="btn btn-primary btn-xs"><?=$payment->user.'. '.$payment->client_name?></a></p>
                    <p>Баланс на даний момент: <strong>$<?=$payment->client_balance_now?></strong></p>
                    <p>email: <strong><?=$payment->client_email?></strong></p>
                    <p>Телефон: <strong><?=$payment->client_phone?></strong></p>
                </div>
            </div>
        </div>
        <?php if($payment->credit > 0 && $payment->action_alias && $payment->action_id) {
            echo $this->load->function_in_alias($payment->action_alias, '__cart_subview', $payment->action_id, true);
         }


        if($payment->status == 2 && $payment->debit && $payment->security_check && (time() < ($payment->date_edit + 24*3600))) { ?>
        <div class="row">
            <div class="panel panel-inverse panel-danger">
                <div class="panel-heading">
                    <h4 class="panel-title">Скасувати платіж</h4>
                </div>
                <div class="panel-body">
                     <form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/save" method="POST">
                        <h4 class=" label label-info">Допускається не більше 24 год від підтвердження платежу</h4>
                        <input type="hidden" name="id" value="<?=$payment->id?>">
                        <input type="hidden" name="status" value="3">
                        <h4>Інформація до заявки:</h4>
                        <textarea name="info" style="height:80px;" class="form-control"></textarea>
                        <p></p>
                        <p><button type="submit" class="btn btn-sm btn-danger">Зберегти</button></p>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<?php if($payment->status == 1 && $payment->debit && $payment->security_check) { ?>
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse panel-success">
            <div class="panel-heading">
                <h4 class="panel-title">Менеджер підтвердити</h4>
            </div>
            <div class="panel-body">
                 <form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/save" method="POST" id="debit_form">
                    <h4>Статус заявки: <u>Перевірено/поповнено</u></h4>
                    <input type="hidden" name="id" value="<?=$payment->id?>">
                    <input type="hidden" name="status" value="2">
                    
                    <?php if(!empty($_SESSION['currency'])) { ?>
                    <input type="hidden" name="debit" value="<?=$payment->debit?>">

                    <div class="row">
                        <div class="col-md-4">
                            <h4>Поповнена сума:</h4>
                            <input type="number" name="amount_do" value="<?=round($payment->debit, 3)?>" class="form-control" step="0.001" oninput="debitReCount()" onchange="debitReCount()">
                        </div>
                        <div class="col-md-3">
                            <h4>Валюта (курс):</h4>
                            <select name="currency" class="form-control" onchange="debitReCount()">
                                <option value="USD" data-rate="1">USD (1)</option>
                                <option value="UAH" data-rate="<?=$_SESSION['currency']['USD']?>">UAH (<?=$_SESSION['currency']['USD']?>)</option>
                                <?php /*foreach ($_SESSION['currency'] as $code => $rate) {
                                    echo "<option value='{$code}' data-rate='{$rate}'>{$code} ({$rate})</option>";
                                }*/ ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <h4>Зарахується:</h4>
                            <h5>$<?=$payment->debit?></h5>
                        </div>
                    </div>
                    <?php } else { ?>
                        <h4>Поповнена сума:</h4>
                        <input type="number" name="debit" value="<?=$payment->debit?>" class="form-control" step="0.01">
                    <?php } ?>
                    
                    <h4>Інформація до заявки:</h4>
                    <textarea name="info" style="height:80px;" class="form-control"></textarea>
                    <p>*Якщо вказана накладна і достатня сума для оплати, то присвоється статус "Оплачено".</p>
                    <p><button type="submit" class="btn btn-sm btn-success">Зберегти</button></p>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-inverse panel-danger">
            <div class="panel-heading">
                <h4 class="panel-title">Менеджер скасувати</h4>
            </div>
            <div class="panel-body">
                 <form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/save" method="POST">
                    <h4>Статус заявки: <u>Скасовано/кошти не надійшли</u></h4>
                    <input type="hidden" name="id" value="<?=$payment->id?>">
                    <input type="hidden" name="status" value="3">
                    <h4>Інформація до заявки:</h4>
                    <textarea name="info" style="height:80px;" class="form-control"></textarea>
                    <p></p>
                    <p><button type="submit" class="btn btn-sm btn-danger">Зберегти</button></p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function debitReCount() {
        var amount_do = $('#debit_form input[name=amount_do]').val();
        var rate = $('#debit_form select[name=currency] option:selected').data('rate');
        var debit = amount_do / rate;
        debit = debit.toFixed(3);
        $('#debit_form input[name=debit]').val(debit);
        $('#debit_form h5').text('$'+debit);
    }
</script>
<?php } ?>

<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />