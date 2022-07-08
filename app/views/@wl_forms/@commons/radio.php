<?php if(!empty($field->options)) { ?>
	<div class="form-group">
		<label><?= $field->title?></label>
		<?php foreach ($field->options as $option) { ?>
		<label for="<?= $field->name.'-'.$option->id?>"><input type="<?= $field->type_name?>" name="<?= $field->name?>" id="<?= $field->name.'-'.$option->id?>" value="<?= $option->value?>" <?= ($option->value == @$field->value)? 'checked' : '' ?> ><?= $option->title?></label>
		<?php } ?>
	</div>
<?php } ?>