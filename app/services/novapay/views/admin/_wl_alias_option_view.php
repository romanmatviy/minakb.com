<div class="col-md-6">
	<div class="panel panel-inverse panel-warning">
	    <div class="panel-heading">
	        <h4 class="panel-title"><i class="fa fa-key" aria-hidden="true"></i> RSA ключі доступу</h4>
	    </div>
	    <div class="panel-body">
	    	<form action="<?=SITE_URL?>admin/<?=$alias->alias?>/save_rsakeys" method="POST" class="form-horizontal" enctype="multipart/form-data">
		    	<?php $path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP;
		    	if (file_exists($path.'public.pem')) { ?>
		    		<div class="form-group">
						<label class="col-md-6 control-label">Публічний RSA ключ 
							<a href="/admin/<?=$alias->alias?>/public_pem" class="btn btn-success btn-xs">наявний</a>. Змінити:
						</label>
						<div class="col-md-6">
							<input type="file" name="public_key" accept=".txt, .pem" title="Змінити публічний RSA ключ">
						</div>
					</div>
		    	<?php } else { ?>
		    	 <div class="form-group">
					<label class="col-md-4 control-label">Публічний RSA ключ <label class="btn btn-danger">відсутній</label></label>
					<div class="col-md-8">
						<input type="file" name="public_key" accept=".txt, .pem" class="btn btn-danger" required title="Встановити публічний RSA ключ">
					</div>
					<strong>Наявність ключа <u>обов'язкова</u>!</strong>
				</div>
				<?php } if (file_exists($path.'private.pem')) { ?>
		    		<div class="form-group">
						<label class="col-md-6 control-label">Приватний RSA ключ 
							<a href="/admin/<?=$alias->alias?>/private_pem" class="btn btn-success btn-xs">наявний</a>. Змінити:
						</label>
						<div class="col-md-6">
							<input type="file" name="private_key" accept=".txt, .pem" title="Змінити приватний RSA ключ">
						</div>
					</div>
		    	<?php } else { ?>
		    	 <div class="form-group">
					<label class="col-md-4 control-label">Приватний RSA ключ <label class="btn btn-danger">відсутній</label></label>
					<div class="col-md-8">
						<input type="file" name="private_key" accept=".txt, .pem" class="btn btn-danger" required title="Встановити приватний RSA ключ">
					</div>
					<strong>Наявність ключа <u>обов'язкова</u>!</strong>
				</div>
				<?php } ?>
				<div class="form-group">
					<label class="col-md-4 control-label"></label>
					<div class="col-md-8">
						<button type="submit" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>
						<?php if (file_exists($path.'private.pem')) { ?>
							<a href="/admin/<?=$alias->alias?>/test_rsa_keys" class="btn btn-warning"><i class="fa fa-bolt" aria-hidden="true"></i> Тест ключів</a>
						<?php } ?>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="panel panel-inverse panel-info">
	    <div class="panel-heading">
	        <h4 class="panel-title">Режим оплати та статус замовлення після успішної оплати</h4>
	    </div>
	    <div class="panel-body">
	    	<form action="<?=SITE_URL?>admin/<?=$alias->alias?>/save_options" method="POST" class="form-horizontal">
			<?php $isset = false;
			if(!empty($options))
				foreach ($options as $option) {
					if($option->name == 'mode') { ?>
					<div class="form-group">
						<label class="col-md-4 control-label">Режим оплати</label>
						<div class="col-md-8">
							<select name="mode" class="form-control">
								<option value="payment" <?=($option->value == 'payment') ? 'selected' : ''?>>Пряме списання</option>
								<option value="complete-hold" <?=($option->value == 'complete-hold') ? 'selected' : ''?>>Списання після підтвердження продавцем</option>
								<option value="confirm-delivery-hold" <?=($option->value == 'confirm-delivery-hold') ? 'selected' : ''?>>Надійна покупка</option>
							</select>
						</div>
					</div>
			<?php } if($option->name == 'successPayStatusToCart' && !empty($cooperation)) {
					$isset = true; ?>
					<input type="hidden" name="service" value="<?=$alias->service?>">
					<div class="form-group">
						<label class="col-md-4 control-label">Статус замовлення у корзині після успішної оплати</label>
						<div class="col-md-8">
							<select name="status" class="form-control">
								<option value="0">Авто (STATUS_ID 4)</option>
								<?php if($statuses = $this->load->function_in_alias($cooperation[0]->alias1, '__get_cart_statuses'))
								foreach ($statuses as $status) {
									$selected = ($status->id == $option->value) ? 'selected' : '';
									echo "<option value='{$status->id}' {$selected}>{$status->id}. {$status->name}</option>";
								} ?>
							</select>
						</div>
					</div>
					
			<?php } }
			if(!$isset) { ?>
				<div class="alert alert-warning">
			        <i class="fa fa-exclamation-triangle fa-2x pull-left"></i>
			        <h4>Увага! Додайте до NovaPay Загальне налаштування <strong>successPayStatusToCart</strong> зі значенням <strong>0</strong></h4>
			    </div>
			<?php } ?>
				<div class="form-group">
					<label class="col-md-4 control-label"></label>
					<div class="col-md-8">
						<button type="submit" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>