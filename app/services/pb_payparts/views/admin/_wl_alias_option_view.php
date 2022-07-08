<div class="col-md-6">
	<div class="panel panel-inverse panel-info">
	    <div class="panel-heading">
	        <h4 class="panel-title">Статус замовлення після успішної оплати</h4>
	    </div>
	    <div class="panel-body">
			<?php $isset = false;
			 if(!empty($options) && !empty($cooperation))
			foreach ($options as $option) if($option->name == 'successPayStatusToCart') {
				$isset = true; ?>
				<p><strong>Авто</strong>. По замовчування статус замовлення <strong>Не змінюється</strong></p>
				<form action="<?=SITE_URL?>admin/<?=$alias->alias?>/save_successPayStatusToCart" method="POST" class="form-horizontal">
					<input type="hidden" name="service" value="<?=$alias->service?>">
					<div class="form-group">
						<label class="col-md-4 control-label">Статус замовлення у корзині після успішної оплати</label>
						<div class="col-md-8">
							<select name="status" class="form-control">
								<option value="0">Авто (не змінюється)</option>
								<?php if($statuses = $this->load->function_in_alias($cooperation[0]->alias1, '__get_cart_statuses'))
								foreach ($statuses as $status) {
									$selected = ($status->id == $option->value) ? 'selected' : '';
									echo "<option value='{$status->id}' {$selected}>{$status->id}. {$status->name}</option>";
								} ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-4 control-label"></label>
						<div class="col-md-8">
							<input type="submit" class="btn btn-success" value="Зберегти">
						</div>
					</div>
				</form>
			<?php break; }
			if(empty($cooperation)) { ?>
				<div class="alert alert-warning">
			        <i class="fa fa-exclamation-triangle fa-2x pull-left"></i>
			        <h4>Увага! Налаштуйте співпрацю зі сервісом <strong>cart</strong></h4>
			    </div>
			<?php }
			if(!$isset && !empty($cooperation)) { ?>
				<div class="alert alert-warning">
			        <i class="fa fa-exclamation-triangle fa-2x pull-left"></i>
			        <h4>Увага! Додайте до pb_payparts Загальне налаштування <strong>successPayStatusToCart</strong> зі значенням <strong>0</strong></h4>
			    </div>
			<?php } ?>
		</div>
	</div>
</div>