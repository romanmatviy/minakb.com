<div class="<?=(empty($_SESSION['language']) || $language == $_SESSION['language']) ? 'sections' : 'sections-'.$language?>">
	<?php if (!empty($_SESSION['alias']->section))
		foreach ($_SESSION['alias']->section as $section) {
			$section_id = $section->id;
			$echoSectionTag = true;
			foreach (['alias_id', 'content_id', 'type', 'name', 'access', 'position', 'title', 'value'] as $key)
				$$key = $section->$key;
			if ($_SESSION['language'])
			{
				if(in_array($type, ['text_multi', 'textarea_multi', 'images']))
				{
					foreach ($_SESSION['all_languages'] as $lang) {
						${'title_' . $lang} = $section->{'title_' . $lang};
						${'value_' . $lang} = $section->{'value_' . $lang};
					}
					if($section->type != 'images' || $language == $_SESSION['language'])
						require APP_PATH . "views/admin/wl_sections/__section_{$section->type}.php";
				}
				else if($language == $_SESSION['language'])
					require APP_PATH . "views/admin/wl_sections/__section_{$section->type}.php";
			}
			else
				require APP_PATH . "views/admin/wl_sections/__section_{$section->type}.php";
		}
	?>
</div>

<?php if (empty($_SESSION['language']) || $language == $_SESSION['language']) { ?>

<section class="text-center m-t-15">
	<a href="#modal-add-section" class="btn btn-warning" data-toggle="modal" data-position="0"><i class="fa fa-plus" aria-hidden="true"></i> Додати секцію</a>
</section>

<div class="modal fade" id="modal-add-section" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Додати секцію</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" id="alias_id" value="<?= $_SESSION['alias']->id ?>">
				<input type="hidden" id="content_id" value="<?= $_SESSION['alias']->content ?>">
				<input type="hidden" id="section_position" value="0">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-md-3 control-label">Тип секції</label>
						<div class="col-md-9">
							<select id="section_type" class="form-control">
								<option value="text_single">Текстове поле <?= $_SESSION['language'] ? '/ одномовний' : '' ?></option>
								<?php if ($_SESSION['language']) { ?>
									<option value="text_multi">Текстове поле / мультимовний</option>
								<?php } ?>
								<option value="textarea_single">Текстовий редактор <?= $_SESSION['language'] ? '/ одномовний' : '' ?></option>
								<?php if ($_SESSION['language']) { ?>
									<option value="textarea_multi">Текстовий редактор / мультимовний</option>
								<?php } if(!empty($_SESSION['option']->folder)) { ?>
									<option value="images">Зображення</option>
									<option value="videos">Відео</option>
									<!-- <option value="audios">Аудіо</option>
									<option value="files">Файли</option> -->
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Службова назва</label>
						<div class="col-md-9">
							<input type="text" id="section_name" class="form-control" value="" required placeholder="анг. літери" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Видимий заголовок</label>
						<div class="col-md-9">
							<input type="text" id="section_title" class="form-control" value="" required placeholder="для користувачів" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Доступ</label>
						<div class="col-md-9">
							<select id="section_access" class="form-control">
								<option value="all">Всі користувачі</option>
								<option value="login">Тільки авторизовані користувачі (залогінені)</option>
								<option value="manager">Тільки адміністрація (менеджери та адміністратори)</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Скасувати</a>
				<a href="javascript:;" class="btn btn-sm btn-warning" onclick="addSection()"><i class="fa fa-plus" aria-hidden="true"></i> Додати секцію</a>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-config-section" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Налаштування секції [<strong></strong>]</h4>
			</div>
			<div class="modal-body">
				<input type="hidden" id="section_id" value="0">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-md-3 control-label">Тип секції</label>
						<div class="col-md-9">
							<select name="type" class="form-control">
								<option value="text_single">Текстове поле <?= $_SESSION['language'] ? '/ одномовний' : '' ?></option>
								<?php if ($_SESSION['language']) { ?>
									<option value="text_multi">Текстове поле / мультимовний</option>
								<?php } ?>
								<option value="textarea_single">Текстовий редактор <?= $_SESSION['language'] ? '/ одномовний' : '' ?></option>
								<?php if ($_SESSION['language']) { ?>
									<option value="textarea_multi">Текстовий редактор / мультимовний</option>
								<?php } if(!empty($_SESSION['option']->folder)) { ?>
									<option value="images">Зображення</option>
									<option value="videos">Відео</option>
									<!-- <option value="audios">Аудіо</option>
									<option value="files">Файли</option> -->
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Службова назва</label>
						<div class="col-md-9">
							<input type="text" name="name" class="form-control" value="" required placeholder="анг. літери" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">attr</label>
						<div class="col-md-9">
							<textarea name="attr" class="form-control"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Доступ</label>
						<div class="col-md-9">
							<select name="access" class="form-control">
								<option value="all">Всі користувачі</option>
								<option value="login">Тільки авторизовані користувачі (залогінені)</option>
								<option value="manager">Тільки адміністрація (менеджери та адміністратори)</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#modal-delete-section" class="btn btn-sm btn-danger pull-left" data-toggle="modal" data-dismiss="modal"><i class="fa fa-trash-o" aria-hidden="true"></i> Видалити</a>
				<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i> Закрити вікно</a>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-delete-section" aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Видалити секцію [<strong></strong>]?</h4>
			</div>
			<div class="modal-body alert alert-warning">
				<input type="hidden" id="section_id" value="0">

				<i class="fa fa-exclamation-triangle fa-2x pull-left m-r-15" aria-hidden="true"></i>
				<p>Увага! <strong>При видаленні секції, <u>буде видалено всі мульмедійні дані в ній</u>:<br> тексти, відео, зображення, файли..</strong></p>
			</div>
			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-sm btn-white pull-left" data-dismiss="modal"> <i class="fa fa-times" aria-hidden="true"></i> Закрити вікно</a>
				<button onclick="deleteSection()" class="btn btn-sm btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i> Видалити</button>
			</div>
		</div>
	</div>
</div>

<style type="text/css">
	section {
		border-top: 5px solid #2d353c;
		padding-top: 15px;
	}
</style>

<?php } ?>