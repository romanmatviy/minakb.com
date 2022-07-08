<?php if ($echoSectionTag) { ?>
	<section id="section_<?= $section_id ?>" class="m-t-15">
<?php } ?>

	<div class="row">
		<div class="col-sm-10">
			<input type="text" class="editSection form-control" name="title" data-section_id="<?= $section_id ?>" value="<?= $title ?>" placeholder="Видимий заголовок [section_title]" title="Видимий заголовок [section_title]">
		</div>
		<div class="col-sm-2">
			<a href="#modal-config-section" class="btn btn-success pull-right" data-toggle="modal" data-section_id="<?= $section_id ?>" title="Налаштування секції [<?= $name ?>]"><i class="fa fa-cogs" aria-hidden="true"></i> Налаштування секції</a>
		</div>
	</div>
	<textarea name="value" rows="3" class="editSection form-control m-t-15" data-section_id="<?= $section_id ?>" placeholder="Текстове поле <?= $_SESSION['language'] ? '/ одномовний' : '' ?>"><?= $value ?></textarea>

<?php if ($echoSectionTag) { ?>
	</section>
<?php } ?>