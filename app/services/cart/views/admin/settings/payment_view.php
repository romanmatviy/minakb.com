<div class="row">
    <div class="panel panel-inverse">
        <div class="panel-heading">
        	<div class="panel-heading-btn">
        		<?php if($payment) { ?>
            		<button onClick="$('#uninstall-form').slideToggle()" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Видалити оплату</button>
            	<?php } ?>
				<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/settings" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування корзини</a>
        	</div>
            <h4 class="panel-title">Деталі оплати</h4>
        </div>
        <?php if($payment) { ?>
        	<div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
				<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete_payment" method="POST">
					<p><i class="fa fa-trash"></i> Ви впевнені що бажаєте видалити оплату?</p>
					<input type="hidden" name="id" value="<?=$payment->id?>">
					<button class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Видалити</button>
					<button type="button" style="margin-left:15px" onClick="$('#uninstall-form').slideUp()" class="btn btn-info btn-xs">Скасувати</button>
				</form>
			</div>
		<?php } ?>
        <div class="panel-body">
        	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_payment" method="POST" class="form-horizontal">
				<input type="hidden" name="id" value="<?= $payment ? $payment->id : 0?>">
				<div class="col-md-6">
					<?php if($payment) { ?>
						<div class="form-group">
							<label class="col-md-3 control-label">Стан</label>
							<label class="col-md-4">
								<input type="radio" name="active" value="1" <?= $payment->active ? 'checked' : ''?>>
								Оплата активна
							</label>
							<label class="col-md-4">
								<input type="radio" name="active" value="0" <?= $payment->active ? '' : 'checked'?>>
								Оплата НЕ активна
							</label>
						</div>
					<?php }

					if($_SESSION['language'])
					{
						if($payment)
						{
							@$name = unserialize($payment->name);
							@$info = unserialize($payment->info);
						}
						foreach ($_SESSION['all_languages'] as $lang) {
							if($payment)
							{
								if(!isset($name[$lang]))
									$name[$lang] = $payment->name;
								if(!isset($info[$lang]))
									$info[$lang] = $payment->info;
							}
							?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва <?=$lang?></label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name_<?=$lang?>" value="<?=isset($name[$lang]) ? $name[$lang] : ''?>" placeholder="Назва <?=$lang?>" required>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-3 control-label">Опис <?=$lang?></label>
	                        <div class="col-md-9">
	                        	<textarea class="form-control" name="info_<?=$lang?>"><?=isset($info[$lang]) ? $info[$lang] : ''?></textarea>
	                        </div>
	                    </div>
					<?php } } else { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва</label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name" value="<?= $payment ? $payment->name : ''?>" placeholder="Назва" required>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-3 control-label">Опис</label>
	                        <div class="col-md-9">
	                        	<textarea class="form-control" name="info"><?= $payment ? $payment->info : ''?></textarea>
	                        </div>
	                    </div>
					<?php } ?>
						
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> <?= $payment ? 'Зберегти' : 'Додати'?></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                	<h4>Реквізити/додаткова інформація, що надсилаються листом при підтвердженні замовлення</h4>
                	<?php if($_SESSION['language'])
					{
						if($payment)
							@$tomail = unserialize($payment->tomail);
						foreach ($_SESSION['all_languages'] as $lang) {
							if($payment && !isset($tomail[$lang]))
								$tomail[$lang] = $payment->tomail; ?>
						<div class="form-group">
	                        <label class="control-label"><strong><?=$lang?></strong></label>
	                        <textarea class="form-control" name="tomail_<?=$lang?>" title="Реквізити оплати, які отримає клієнт листом на електронну скриньку" placeholder="Реквізити оплати, які отримає клієнт листом на електронну скриньку" style="min-height: 80px"><?=isset($tomail[$lang]) ? $tomail[$lang] : ''?></textarea>
	                    </div>
					<?php } } else { ?>
                        <textarea class="form-control" name="tomail" title="Реквізити оплати, які отримає клієнт листом на електронну скриньку" placeholder="Реквізити оплати, які отримає клієнт листом на електронну скриньку" style="min-height: 80px"><?= $payment ? $payment->tomail : ''?></textarea>
					<?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>