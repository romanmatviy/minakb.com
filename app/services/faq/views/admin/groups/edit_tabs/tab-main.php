<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_group" method="POST">
	<input type="hidden" name="id" value="<?=$group->id?>">
	<table class="table table-striped table-bordered">
		<tr>
			<th>Власне посилання</th>
			<td> <?=SITE_URL.$_SESSION['alias']->alias.'/'?>
				<input type="text" name="alias" value="<?=$group->alias?>" class="form-control" required>
			</td>
		</tr>
		<?php
		if (isset($_SESSION['admin_options']['groups:additional_fields']) && $_SESSION['admin_options']['groups:additional_fields'] != '') {
			$fields = explode(',', $_SESSION['admin_options']['groups:additional_fields']);
			foreach ($fields as $field) {
		?>
			<tr>
				<th><?=$field?></th>
				<td><input type="text" name="<?=$field?>" value="<?=$group->$field?>" class="form-control"></td>
			</tr>
		<?php
			}
		}
		?>
		<tr>
			<th>Стан активності</th>
			<td>
				<input type="radio" name="active" value="1" <?=($group->active == 1)?'checked':''?> id="active-1"><label for="active-1">активна</label>
				<input type="radio" name="active" value="0" <?=($group->active == 0)?'checked':''?> id="active-0"><label for="active-0">відключено</label>
			</td>
		</tr>
		<tr>
			<th>Додано</th>
			<td><?=$group->author_add .'. ' . $group->author_add_name . date(' d.m.Y H:i', $group->date_add)?></td>
		</tr>
		<tr>
			<th>Востаннє редагувано</th>
			<td><?=$group->author_edit .'. ' . $group->author_edit_name . date(' d.m.Y H:i', $group->date_edit)?></td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit" class="btn btn-sm btn-success col-md-6">Зберегти</button></td>
		</tr>
	</table>
</form>