<div class="col-lg-6" >
	<div class="panel panel-inverse">
        <div class="panel-heading">
        	<div class="panel-heading-btn">
                <a href="<?=SITE_URL.'admin/wl_forms/'.$form->name?>" class="btn btn-info btn-xs">До форми</a>
                <a href="javascript:;" class="btn btn-danger btn-xs" onclick="deleteField()"><i class="fa fa-trash-o"></i> Видалити поле</a>
            </div>
            <h4 class="panel-title">Редагувати поле:</h4>
        </div>
		<div  class="panel-body">
			<form action="<?=SITE_URL?>admin/wl_forms/edit_field" method="POST" class="form-horizontal">
				<input type="text" name="id" value="<?=$field_name->id?>" hidden>
				<input type="text" name="form_id" value="<?=$field_name->form?>" hidden>
			 	<div class="form-group">
			 		<label class="col-md-3 control-label">id</label>
					<div class="col-md-9">
						<input type="text" class="form-control" value="<?=$field_name->id?>" disabled>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">name</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="name" id="name" value="<?=$field_name->name?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">input_type</label>
					<div class="col-md-9">
						<select name="input_type" class="form-control" onchange="changeInputType(this)" id="input_type" required>
						<?php $input_types = $this->db->getAllData('wl_input_types');
						foreach ($input_types as $type) {
							echo('<option value="'.$type->id.'"');
							if($type->id == $field_name->input_type) echo " selected";
							echo('>'.$type->name.'</option>');
						} ?>
						</select>
					</div>
				</div>
				<div class="form-group" id="hiddenValue" <?= !isset($field_name->options) ? 'hidden' : ''?>>
					<label class="col-md-3 control-label">value</label>
					<div class="col-md-9">
						<?php if(isset($field_name->options)) { foreach ($field_name->options as $option) { ?>
						<input type="text" class="form-control" name="value[]" value="<?=$option->value?>" required>
						<?php } } else { ?>
						<input type="text" class="form-control" name="value[]">
						<input type="text" class="form-control" name="value[]">
						<?php } ?>
						<button class="btn btn-sm btn-warning" onclick="addAnotherValue(event)"> Додати поле</button>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">required</label>
					<div class="col-md-9">
						<label><input type="radio" name="required" value="1" <?= ($field_name->required == 1)?'checked':'' ?>>Так</label>
						<label><input type="radio" name="required" value="0" <?= ($field_name->required == 0)?'checked':'' ?>>Ні</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">title</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="title" value="<?=$field_name->title?>">
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-3"></div>
					<div class="col-lg-9">
						<input type="submit" id="submit" class="btn btn-sm btn-warning " value="Зберегти" <?= $diff_name ? 'onclick="checkName()"' : '' ?> >
						<span id="name_error" style="color:red"></span>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>


<script>

	function checkName () {
		var name = $("#name").val();
		var names = ["<?= $diff_name?>"];
		for(var n in names){
			if(names[n] == name){
				$('#submit').prop('disabled', true);
				$('#name_error').html("Співпадають імена");
				setTimeout(function() {
				$('#submit').prop('disabled', false);
			}, 100)
			}
		}
	}

	function addAnotherValue (event) {
		event.preventDefault();
		$("#hiddenValue button").before('<input type="text" class="form-control" name="value[]">')
	}

	function changeInputType (t) {
		if(t.value == 8 || t.value == 9 || t.value == 10){
			$("#hiddenValue").show();
			$("div #hiddenValue input[type='text']").removeAttr("disabled");
		}
		else{
			$("#hiddenValue").hide();
			$("div").filter(":hidden").children("input[type='text']").attr("disabled", "disabled");
		}
	}

	function deleteField() 
	{
		if(confirm('Видалити поле?')){
			$.ajax({
				url : '<?= SITE_URL?>admin/wl_forms/deleteField',
				method : 'POST',
				data : {
					field : <?= $field_name->id?>,
					fieldName : '<?= $field_name->name?>',
					tableName : '<?= $form->table?>'
				},
				success : function (res) {
					window.location.href = '<?= SITE_URL?>admin/wl_forms/<?= $form->name?>';
				}
			})
		}
	}

</script>
