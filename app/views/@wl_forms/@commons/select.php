<?php if(!empty($field->options)) { ?>
<div class="form-group">
	<label><?= $field->title?></label>
	<select name="<?= $field->name?>" id="<?= $field->name.'-'.$option->id?>" class="form-control">
		<?php foreach ($field->options as $option) { ?>
		<option value="<?= $option->value?>" <?= ($option->value == @$field->value)? 'selected' : '' ?>><?= $option->title?></option>
		<?php } ?>
	</select>
</div>
<?php } ?>