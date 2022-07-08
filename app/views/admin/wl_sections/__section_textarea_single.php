<?php if ($echoSectionTag) { ?>
    <section id="section_<?= $section_id ?>" class="m-t-15">
<?php } ?>

    <div class="row m-b-15">
        <div class="col-sm-10">
            <input type="text" class="editSection form-control" name="title" data-section_id="<?= $section_id ?>" value="<?= $title ?>" placeholder="Видимий заголовок [section_title]" title="Видимий заголовок [section_title]">
        </div>
        <div class="col-sm-2">
            <a href="#modal-config-section" class="btn btn-success pull-right" data-toggle="modal" data-section_id="<?= $section_id ?>" title="Налаштування секції [<?= $name ?>]"><i class="fa fa-cogs" aria-hidden="true"></i> Налаштування секції</a>
        </div>
    </div>
    <textarea id="section-editor-<?= $section_id ?>"><?= $value ?></textarea>

<?php if ($echoSectionTag) { ?>
    </section>
<?php }

$_SESSION['alias']->js_init[] = "var sectionEditor{$section_id} = CKEDITOR.replace( 'section-editor-{$section_id}' ); sectionEditor{$section_id}.on('blur', function(ev) { let value = CKEDITOR.instances['section-editor-{$section_id}'].getData(); wl_sections_set({$section_id}, 'value', value); } );";

?>