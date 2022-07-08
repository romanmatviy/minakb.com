<div class="col-md-6">
	<div class="panel panel-inverse panel-info">
	    <div class="panel-heading">
	        <h4 class="panel-title">Формат виводу ціни (<strong>Перед ціною</strong> <i>ціна (число)</i> <strong>Після ціни</strong>)</h4>
	    </div>
	    <div class="panel-body">
			<?php if(!empty($options))
			foreach ($options as $option) if($option->name == 'price_format')
			{
				$before = $after = '';
				$penny = 1; $round = 2;
				if(!empty($option->value))
				{
					$price_format = unserialize($option->value);
					if(isset($price_format['before']))
						$before = $price_format['before'];
					if(isset($price_format['after']))
						$after = $price_format['after'];
					if(isset($price_format['round']))
						$round = $price_format['round'];
					if(isset($price_format['penny']))
						$penny = $price_format['penny'];
				}
			?>
				<form action="<?=SITE_URL?>admin/<?=$alias->alias?>/save_price_format" method="POST" class="form-horizontal">
					<input type="hidden" name="service" value="<?=$alias->service?>">
					<div class="form-group">
						<label class="col-md-4 control-label">Перед ціною</label>
						<div class="col-md-8">
							<input type="text" name="before" value="<?=$before?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-4 control-label">Точність ціни</label>
						<div class="col-md-8">
							<select class="form-control" name="round">
								<?php $rounds = array(  'До цілого (2.3 => 2.00, 4.5 => 5.00)' => 0,
														'1 після цілого (2.32 => 2.30, 4.52 => 4.50, 1.75 => 1.80)' => 1,
														'2 після цілого (2.324 => 2.35, 4.527 => 4.55)' => 2);
								foreach ($rounds as $name => $value) {
									if($value == $round)
										echo "<option value=\"{$value}\" selected>{$name}</option>";
									else
										echo "<option value=\"{$value}\">{$name}</option>";
								}
								 ?>
							</select>
						</div>
						 <br><center>Кількість знаків після коми, копійки <u>округляються до 5</u></center>
					</div>
					<div class="form-group">
						<label class="col-md-4 control-label">Копійки</label>
						<div class="col-md-8">
							<select class="form-control" name="penny">
								<?php $rounds = array(  'Ніколи не виводити' => 0,
														'Авто (якщо є, виводити)' => 1,
														'Завжди виводити *.**' => 2 );
								foreach ($rounds as $name => $value) {
									if($value == $penny)
										echo "<option value=\"{$value}\" selected>{$name}</option>";
									else
										echo "<option value=\"{$value}\">{$name}</option>";
								}
								 ?>
							</select>
						</div>
						 <br><center>Кількість знаків після коми, копійки</center>
					</div>
					<div class="form-group">
						<label class="col-md-4 control-label">Після ціни</label>
						<div class="col-md-8">
							<input type="text" name="after" value="<?=$after?>" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-4 control-label"></label>
						<div class="col-md-8">
							<input type="submit" class="btn btn-success" value="Зберегти">
						</div>
					</div>
				</form>
			<?php } ?>
		</div>
	</div>
</div>