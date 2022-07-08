<?php if ($echoSectionTag) { ?>
    <section id="section_<?= $section_id ?>" class="m-t-15">
<?php } ?>

    <div class="row">
        <div class="col-sm-10">
            <input type="text" class="editSection form-control" name="title-<?= $language ?>" data-section_id="<?= $section_id ?>" value="<?= ${'title_' . $language} ?? '' ?>" placeholder="Видимий заголовок [section_title_<?= $language ?>]" title="Видимий заголовок [section_title_<?= $language ?>]">
        </div>
        <div class="col-sm-2">
            <a href="#modal-config-section" class="btn btn-success pull-right" data-toggle="modal" data-section_id="<?= $section_id ?>" title="Налаштування секції [<?= $name ?>]"><i class="fa fa-cogs" aria-hidden="true"></i> Налаштування секції</a>
        </div>
    </div>
    <textarea name="value-<?= $language ?>" rows="3" class="editSection form-control m-t-15" data-section_id="<?= $section_id ?>" placeholder="Текстове поле [section_value_<?= $language ?>]"><?= ${'value_' . $language} ?></textarea>

<?php if ($echoSectionTag) { ?>
    </section>
<?php } ?>