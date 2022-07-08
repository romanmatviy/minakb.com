<div class="col-md-6">
	<div class="panel panel-inverse panel-info">
	    <div class="panel-heading">
	        <h4 class="panel-title">Додаткові наштування</h4>
	    </div>
	    <div class="panel-body">
			<?php if(!empty($options))
			foreach ($options as $option)
				if($option->name == 'groupBy')
				{ ?>
				<form action="<?=SITE_URL?>admin/<?=$alias->alias?>/save_group_by" method="POST" class="form-horizontal">
					<input type="hidden" name="service" value="<?=$alias->service?>">
					<div class="form-group">
						<label class="col-md-4 control-label">Групування товарів при порівнянні</label>
						<div class="col-md-8">
							<select class="form-control" name="groupBy">
								<?php $rounds = array(  'Без групування (alias)' => 'alias',
														'Коренева група (grandParent)' => 'grandParent',
														'Батьківська група (parent)' => 'parent');
								foreach ($rounds as $name => $value) {
									if($value == $option->value)
										echo "<option value=\"{$value}\" selected>{$name}</option>";
									else
										echo "<option value=\"{$value}\">{$name}</option>";
								}
								 ?>
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
			<?php } ?>
		</div>
	</div>
</div>