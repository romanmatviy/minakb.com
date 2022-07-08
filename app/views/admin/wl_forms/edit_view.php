<?php $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="javascript:;" class="btn btn-warning btn-xs" onclick="toggle(hidden_form)"><i class="fa fa-plus"></i> Додати поле</a>
                	<?php if($tableExist) { ?>
                		<a href="<?= SITE_URL.'admin/wl_forms/info/'.$form->name?>" class="btn btn-info btn-xs"><i class="fa fa-list"></i> Дивитися дані форми</a>
                	<?php } ?>
                	<a href="javascript:;" class="btn btn-danger btn-xs" onclick="deleteForm(<?= $tableExist ? true : false ?>)"><i class="fa fa-trash-o"></i> Видалити форму</a>
                </div>
                <h4 class="panel-title">Наявні поля:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th width="100px" nowrap>ID</th>
                                <th title="title">Заголовок</th>
								<th title="name">Назва поля</th>
								<th>Тип поля</th>
								<th title="required">Обов'язкове поле</th>
                            </tr>
                        </thead>
                        <tbody>
							<?php if($fields) foreach ($fields as $field) { ?>
								<tr>
									<td><?=$field->id?></td>
									<td><?=$field->title?></td>
									<td><a href="<?=SITE_URL?>admin/wl_forms/<?php echo $form->name.'/'.$field->name?>"><?=$field->name?></a></td>
									<td><?=$field->input_type_name?></td>
									<td><?=($field->required)?'Так':'Ні'?></td>
								</tr>
							<?php } else { ?>
								<tr>
									<td colspan="5" class="text-center"><h4>Додайте поля і тоді створіть таблицю</h4></td>
								</tr>
							<?php } if($tableExist) {?>
								<tr>
									<td colspan="5" class="text-center"><h5>Службові поля</h5></td>
								</tr>
								<tr>
									<td>-</td>
									<td>Дата додачі</td>
									<td>date_add</td>
									<td>text</td>
									<td>Hi</td>
								</tr>
								<tr>
									<td>-</td>
									<td>Мова</td>
									<td>language</td>
									<td>text</td>
									<td>Hi</td>
								</tr>
							<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<form action="<?=SITE_URL?>admin/wl_forms/edit_form" method="POST" class="form-horizontal">
		<div class="col-md-6">
			<div class="panel panel-inverse">
		        <div class="panel-heading">
		            <h4 class="panel-title">Редагувати форму</h4>
		        </div>
				<div  class="panel-body">
						<input type="hidden" value="<?= $form->id?>" name="formId">
						<p class="text-center"><?= SITE_URL.'save/'.$form->name?></p>
						<table>
							<?php if(!$tableExist) { ?>
							<div class="form-group">
								<label class="col-md-4 control-label">Створити таблицю?</label>
								<div class="col-md-8">
									<input type="checkbox" data-render="switchery" class="form-control" name="create" value="1">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4 control-label">table*</label>
								<div class="col-md-8">
									<input type="text" class="form-control" name="table" value="<?= $form->table?>" placeholder="table" required>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4 control-label">alias/uri форми*</label>
								<div class="col-md-8">
									<input type="text" class="form-control" name="name" placeholder="feedback" value="<?= $form->name?>" required>
									<small>англ. літери. <?= SITE_URL.'save/'?>* - лінк надсилання даних</small>
								</div>
							</div>
							<?php } else { ?>
							<input type="hidden" name="name" value="<?= $form->name?>">
							<div class="form-group">
								<label class="col-md-4 control-label">Показувати в sidebar? <br><small>Меню навігації панелі керування</small></label>
								<div class="col-md-8">
									<input type="checkbox" class="form-control" data-render="switchery" name="sidebar" <?= $form->sidebar == 1 ? 'checked' : '' ?> value="1">
								</div>
							</div>
							<?php } ?>
							<div class="form-group">
								<label class="col-md-4 control-label">Захищено Google recaptcha <br><small>Вимагає заповненої галочки</small></label>
								<div class="col-md-8">
									<input type="checkbox" name="captcha" data-render="switchery" <?= $form->captcha == 1 ? 'checked' : '' ?> value="1" >
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4 control-label">Назва форми <br><small>Відображається у sidebar</small></label>
								<div class="col-md-8">
									<input type="text" class="form-control" name="title" value="<?= $form->title?>" placeholder="Зворотній зв'язок">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4 control-label">Метод передачі інформації <br><small>method="GET/POST"</small></label>
								<div class="col-md-8">
									<label><input type="radio" name="type" value="get" required <?= $form->type == 1 ? 'checked' : '' ?>> GET</label> 
									<label class="m-l-15"><input type="radio" name="type" value="post" <?= $form->type == 2 ? 'checked' : '' ?>> POST</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-4 control-label">Відправляти email при отриманні заявки</label>
								<div class="col-md-8">
									<input type="checkbox" name="send_mail" data-render="switchery" onchange="show(this, 'templates')" <?= $form->send_mail == 1 ? 'checked' : '' ?> value="1">
									<a href="<?= SITE_URL?>admin/wl_forms/createMailTemplate/<?= $form->id ?>" class="btn btn-sm btn-success pull-right"><i class="fa fa-plus"></i> Створити розсилку на основі форми</a>
								</div>
							</div>

							<?php if($form->send_mail) { ?>
							<div class="form-group" id="templates" <?= $form->send_mail == 0 ? 'hidden' : '' ?> >
								<label class="col-md-4 control-label">Шаблони листів</label>
								<div class="col-md-8">
								<?php if($templates) {?>
									<?php foreach ($templates as $template){ ?>
									<label><input type="checkbox" name="templates[]" value="<?= $template->id?>" <?= (isset($template->checked) && $template->checked == 1) ? 'checked' : '' ?> > <a target="_blank" href="<?= SITE_URL.'admin/wl_mail_template/'.$template->id ?>"> <?= isset($template->title) ? $template->title : $template->id ?></a></label><br>
									<?php } ?>
								<?php } ?>
								</div>
							</div>
							<?php } ?>

							<div class="form-group" >
								<label class="col-md-4 control-label">Дія після</label>
								<div class="col-md-8">
									<select name="after" class="form-control" id="after" onchange="doAfter()">
										<option value="1" <?= $form->success >= 1 ? 'selected' : '' ?> >Повернутись на ту саму сторінку</option>
										<option value="2" <?= $form->success == 2 ? 'selected' : '' ?> >Загрузити notify_view</option>
										<option value="4" <?= $form->success == 4 ? 'selected' : '' ?> >Відповідь ajax</option>
										<option value="3" <?= $form->success == 3 ? 'selected' : '' ?> >Інша сторінка</option>
									</select>
									<input type="text" class="form-control" value="<?= $form->success != 2 && $form->success != 4 ? $form->success_data : '' ?>" name="afterValue" id="doAfterValue" <?= $form->success <= 2 || $form->success == 4? 'style="display:none" disabled' : '' ?> >
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-4 control-label">Відправляти sms (turbosms) <br><small>при отриманні заявки</small></label>
								<div class="col-md-8">
									<input type="checkbox" name="send_sms" data-render="switchery" onchange="show(this, 'sms_text')" <?= $form->send_sms == 1 ? 'checked' : '' ?> value="1" >
								</div>
							</div>
							<div class="form-group" id="sms_text" <?= $form->send_sms == 0 ? 'hidden' : '' ?> >
								<label class="col-md-4 control-label">Смс текст</label>
								<div class="col-md-8">
									<input type="text" class="form-control" value="<?= $form->sms_text?>" name="sms_text" id="sms_text" >
								</div>
							</div>

							<div class="form-group">
		                    	<div class="col-md-4"></div>
		                        <div class="col-md-8">
		                        	<input type="submit" class="btn btn-sm btn-warning " value="Зберегти">
								</div>
							</div>
						</table>
				</div>
			</div>
		</div>
		<div class="col-md-6" id="notifyText" <?= $form->success != 2 && $form->success != 4 ? 'style="display: none"' : '' ?> >
			<div class="panel panel-inverse">
		        <div class="panel-heading">
		            <h4 class="panel-title">Текст для <span><?= $form->success == 2  ? 'notify_view' : 'ajax' ?></span></h4>
		        </div>
				<div  class="panel-body">
					<div class="form-horizontal">
						<?php if($_SESSION['all_languages']) {
							if(!is_object($form->success_data))
								$form->success_data = json_decode($form->success_data);
						foreach($_SESSION['all_languages'] as $lang) { ?>
							<div class="form-group">
								<label class="col-md-3 control-label"><?= $lang?></label>
								<div class="col-md-9">
									<input type="text" class="form-control" name="lang[<?= $lang?>]" value="<?= $form->success == 2 || $form->success == 4 ? $form->success_data->$lang : '' ?>" >
								</div>
							</div>
						<?php } } else { ?>
							<div class="form-group">
								<label class="col-md-3 control-label">Текст</label>
								<div class="col-md-9">
									<input type="text" class="form-control" name="lang" value="<?= $form->success == 2 || $form->success == 4 ? $form->success_data : '' ?>" >
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- ДОДАТИ ПОЛЕ -->
	<div class="col-md-6" id="hidden_form" style="display: none;" >
		<div class="panel panel-inverse">
	        <div class="panel-heading">
	            <h4 class="panel-title">Додати поле</h4>
	        </div>
			<div  class="panel-body">
				<form action="<?=SITE_URL?>admin/wl_forms/add_field" method="POST" class="form-horizontal">
					<input type="text" name="form" value="<?= $form->id ?>" hidden>
					<input type="text" name="form_name" value="<?= $form->name ?>" hidden>
					<input type="text" name="form_table" value="<?= $form->table ?>" hidden>
					<table>
						<div class="form-group">
							<label class="col-md-3 control-label">name*</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="name" id="name" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">title*</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="title" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">input_type*</label>
							<div class="col-md-9">
								<select name="input_type" class="form-control" onchange="changeInputType(this)" id="input_type" required>
								<?php $input_types = $this->db->getAllData('wl_input_types');
									foreach ($input_types as $type) {
										echo('<option value="'.$type->id.'"');
										echo('>'.$type->name.'</option>');
									} ?>
								</select>
							</div>
						</div>
						<div class="form-group" id="hiddenValue" hidden>
							<label class="col-md-3 control-label">value</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="value[]">
								<input type="text" class="form-control" name="value[]">
								<button class="btn btn-sm btn-warning" onclick="addAnotherValue(event)"> Додати ще поле</button>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">required</label>
							<div class="col-md-9">
								<label><input type="radio" name="required" value="1">Так</label>
								<label><input type="radio" name="required" value="0" checked>Ні</label>
							</div>
						</div>
						<div class="form-group">
							<div class="col-lg-3"></div>
							<div class="col-md-9">
								<input type="submit" value="Додати" class="btn btn-sm btn-warning" <?php if($names) {?> onclick="checkName()" <?php } ?> id="submit">
								<span id="name_error" style="color:red"></span>
							</div>
						</div>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
	function toggle(el) 
	{
		el.style.display = (el.style.display == 'none') ? '' : 'none'
	}

	function show (el, name) 
	{
		if($(el).is(":checked")){
			$('#'+name).show();
			$("input[name="+name+"]").removeAttr("disabled");
		} else {
			$('#'+name).hide();
			$("input[name="+name+"]").attr("disabled", "disabled");
		}
	}

	function doAfter () 
	{
		$("#doAfterValue, #notifyText").hide().attr("disabled", "disabled");

		if($("#after").val() == 2){
			$("#notifyText").show();
			$("#notifyText h4 span").text('notify_view');
		}
		if($("#after").val() == 4){
			$("#notifyText").show();
			$("#notifyText h4 span").text('ajax');
		}

		if($("#after").val() == 3)
			$("#doAfterValue").show().removeAttr("disabled");

	}

	function changeInputType (t) 
	{
		if(t.value == 8 || t.value == 9 || t.value == 10){
			$("#hiddenValue").show();
			$("div #hiddenValue input[type='text']").removeAttr("disabled");
		}
		else{
			$("#hiddenValue").hide();
			$("div").filter(":hidden").children("input[type='text']").attr("disabled", "disabled");
		}
	}

	function addAnotherValue (event) {
		event.preventDefault();
		$("#hiddenValue button").before('<input type="text" class="form-control" name="value[]">')
	}

	<?php if($names) { ?>
	function checkName () 
	{
		var name = $("#name").val();
		var names = ["<?= $names?>"]
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
	<?php } ?>

	function deleteForm(tableExist) 
	{
		var go = deleteTable = false;

		if(confirm('Видалити форму?'))
			go = true;

		if(go && tableExist && confirm('Видалити таблицю і дані з бази даних?'))
			deleteTable = true;

		if(go)
		{
			$.ajax({
				url : '<?= SITE_URL?>admin/wl_forms/deleteForm',
				method : 'POST',
				data : {
					form : <?= $form->id?>,
					deleteTable : deleteTable,
					tableName : '<?= $form->table?>'
				},
				success : function (res) {
					window.location.href = '<?= SITE_URL?>admin/wl_forms';
				}
			})
		}
	}

</script>
