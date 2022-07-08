<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse panel-warning">
            <div class="panel-heading">
                <h4 class="panel-title">Поповнити баланс</h4>
            </div>
            <div class="panel-body">
                 <form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/debit" method="POST" id="debit_form">
                    <h4>Поповнити баланс</h4>
                    <input type="hidden" name="user" value="<?=$user->id?>">
                    
                    <?php if(!empty($_SESSION['currency'])) { ?>
                    <input type="hidden" name="debit" value="0">

                    <div class="row">
                        <div class="col-md-4">
                            <h4>Поповнити на:</h4>
                            <input type="number" name="amount_do" value="0" class="form-control" step="0.001" min="0.001" required oninput="debitReCount()" onchange="debitReCount()">
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
                            <h5>$0</h5>
                        </div>
                    </div>
                    <?php } else { ?>
                        <h4>Поповнити на:</h4>
                        <input type="number" name="debit" value="0" class="form-control" step="0.001" min="0.001" required>
                    <?php } ?>
                    
                    <h4>Інформація до заявки:</h4>
                    <textarea name="info" style="height:80px;" class="form-control"></textarea>
                    <p>*Якщо сума достатня для оплати замовлення, то присвоється статус "Оплачено".</p>
                    <p><button type="submit" class="btn btn-sm btn-success">Поповнити</button></p>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-inverse panel-success">
            <div class="panel-heading">
                <h4 class="panel-title">Баланс <strong><?=$user->name?></strong></h4>
            </div>
            <div class="panel-body">.
                <h4>Поточний баланс користувача: <strong>$<?=$user->balance?></strong></h4>
                <p>Цифровий підпис балансу: <label class="label label-<?=$user->balance_security_check ? 'success':'danger'?>"><?=$user->balance_security_check ? 'Коректний':'Помилка!'?></label> <?=$user->balance_correct_sign?></p>
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

<?php require 'list_view.php'; ?>