<?php if ($echoSectionTag) { ?>
    <section id="section_<?= $section_id ?>" class="m-t-15">
<?php } ?>

    <div class="row m-b-15">
        <div class="col-sm-10">
            <input type="text" class="editSection form-control" name="title-<?= $language ?>" data-section_id="<?= $section_id ?>" value="<?= ${'title_' . $language} ?? '' ?>" placeholder="Видимий заголовок [section_title_<?= $language ?>]" title="Видимий заголовок [section_title_<?= $language ?>]">
        </div>
        <div class="col-sm-2">
            <a href="#modal-config-section" class="btn btn-success pull-right" data-toggle="modal" data-section_id="<?= $section_id ?>" title="Налаштування секції [<?= $name ?>]"><i class="fa fa-cogs" aria-hidden="true"></i> Налаштування секції</a>
        </div>
    </div>
    <textarea id="section-editor-<?= $section_id ?>-<?= $language ?>"><?= ${'value_' . $language} ?? '' ?></textarea>

<?php if ($echoSectionTag) { ?>
    </section>
<?php }

$_SESSION['alias']->js_init[] = "var sectionEditor{$section_id}{$language} = CKEDITOR.replace( 'section-editor-{$section_id}-{$language}' ); sectionEditor{$section_id}{$language}.on('blur', function(ev) { let value = CKEDITOR.instances['section-editor-{$section_id}-{$language}'].getData(); wl_sections_set({$section_id}, 'value-{$language}', value); } );";

?>