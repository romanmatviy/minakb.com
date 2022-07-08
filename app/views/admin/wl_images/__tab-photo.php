<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
<div id="fileupload-section-0" class="fileupload-buttonbar">
    <div class="pull-right">
        <label>
            <input type="checkbox" data-render="switchery" checked data-classname="switchery switchery-small" id="resizer" />
            Зменшити оригінал
        </label>
        <br>
        <label>
            <input type="checkbox" data-render="switchery" data-classname="switchery switchery-small" id="newMain" />
            Нове фото - головне
        </label>
    </div>

    <!-- The fileinput-button span is used to style the file input field as button -->
    <span class="btn btn-success fileinput-button f-left">
        <i class="glyphicon glyphicon-plus"></i>
        <span>Додати фото</span>
        <input type="file" name="photos[]" multiple>
    </span>
    
    <!-- The global file processing state -->
    <span class="fileupload-process"></span>


    <!-- The global progress state -->
    <div class="col-lg-5 col-sm-12 fileupload-progress fade">
        <!-- The global progress bar -->
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-success" style="width:0%;"></div>
        </div>
        <!-- The extended global progress state -->
        <div class="progress-extended">&nbsp;</div>
    </div>

    <!-- The table listing the files available for upload/download -->
    <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
</div>

<div class="clear"> </div>

<table id="PHOTOS-0" class="table">
    <tbody class="files">
        <?php if(!empty($_SESSION['alias']->images)) {
            $pos = 1;
            foreach($_SESSION['alias']->images as $photo)
            {
                if($photo->section_id > 0) continue;
                if($pos != $photo->position)
                {
                    $photo->position = $pos;
                    $this->db->updateRow('wl_images', array('position' => $pos), $photo->id);
                }
                $pos++;
            }
            foreach($_SESSION['alias']->images as $photo) {
                if($photo->section_id > 0) continue;
             ?>
                <tr id="photo-<?=$photo->id?>" class="template-download fade in">
                    <td class="move sortablehandle"><i class="fa fa-sort"></i></td>
                    <td class="preview">
                        <a href="<?=IMG_PATH.$photo->path?>">
                            <img src="<?=IMG_PATH.$photo->admin_path?>">
                        </a>
                    </td>
                    <td>
                        <?php if($_SESSION['language']) {
                            $texts = array();
                            if($gettexts = $this->db->getAllDataByFieldInArray('wl_media_text', $photo->id, 'content'))
                                foreach ($gettexts as $text) {
                                    $texts[$text->language] = $text->text;
                                }
                            foreach ($_SESSION['all_languages'] as $language) { ?>
                                <div class="input-group">
                                    <span class="input-group-addon"><?=$language?></span>
                                    <input name="title-<?=$language?>" type="text" value="<?=(isset($texts[$language])) ? $texts[$language] : ''?>" class="form-control" placeholder="<?=$pageNames[$language]?>" onChange="savePhoto(<?=$photo->id?>, this)">
                                </div>
                        <?php } } else { ?>
                            <textarea name="title" onChange="savePhoto(<?=$photo->id?>, this)" placeholder="<?=$_SESSION['alias']->name?>"><?=($photo->title != $_SESSION['alias']->name) ? $photo->title : ''?></textarea>
                        <?php } ?>
                        <div class="input-group">
                            <span class="input-group-addon"><?= SITE_URL ?></span>
                            <input name="link" type="text" value="<?= $photo->link ?>" class="form-control" placeholder="Внутрішнє посилання" onChange="savePhoto(<?=$photo->id?>, this)">
                        </div>
	                    <?php if(!empty($ADDITIONAL_IMAGE_OPTIONS)) {
		                    ?><form class='form-options'>
		                    <input type="hidden" name='id' value='<?=$photo->id?>'><?php
		                    $photo->options = unserialize( $photo->options );

		                    foreach ($ADDITIONAL_IMAGE_OPTIONS as $options)
			                    foreach ($options as $option) {
				                    if ( $option->changePrice && in_array( $option->type_name, [ 'checkbox-select2' ] ) ) {
					                    $value = $ADDITIONAL_IMAGE_OPTIONS_VALUE[ $option->id ];

					                    $where = array('option' => '#o.id');
					                    if($_SESSION['language'])
						                    $where['language'] = $_SESSION['language'];
					                    $this->db->select($this->shop_model->table('_options').' as o', '*', -$option->id, 'group')
					                             ->join($this->shop_model->table('_options_name').' as n', 'id as name_id, name', $where);
					                    if($option->sort == 0)
						                    $this->db->order('position ASC');
					                    if($option->sort == 1)
						                    $this->db->order('name ASC', 'n');
					                    if($option->sort == 2)
						                    $this->db->order('name DESC', 'n');
					                    $option_values = $this->db->get('array');

					                    echo( '<div class="form-group">' );
					                    echo( '<label class="col-md-2 control-label">'.$option->name );
					                    if ( $option->sufix != '' ) {
						                    echo " ({$option->sufix})";
					                    }
					                    echo( '</label> <div class="col-md-10">' );

					                    if($option->toCart && $option->type_name != 'checkbox' && $option->type_name != 'checkbox-select2')
					                    {
						                    if(!empty($option_values))
						                    {
							                    $names = array();
							                    foreach ($option_values as $ov) {
								                    $names[] = '<strong>'.$ov->name.'</strong>';
							                    }
							                    echo implode(', ', $names);
							                    if($option->changePrice)
							                    {
								                    $changePriceOptions[$option->id] = clone $option;
								                    $changePriceOptions[$option->id]->values = $option_values;
							                    }
						                    }
					                    }

					                    if($option->type_name == 'checkbox' || $option->type_name == 'checkbox-select2')
					                    {
						                    if(!empty($option_values))
						                    {
							                    if($option->changePrice)
							                    {
								                    $changePriceOptions[$option->id] = clone $option;
								                    $changePriceOptions[$option->id]->values = array();
							                    }

							                    $value = explode(',', $value);
							                    if($option->type_name == 'checkbox')
								                    foreach ($option_values as $ov) {
									                    $checked = '';
									                    if(in_array($ov->id, $value))
									                    {
										                    if(isset($photo->options[$option->id]) && in_array($ov->id, $photo->options[$option->id]))
											                    $checked = ' checked';

										                    echo('<input type="checkbox" name="option['.$option->id.'][]" value="'.$ov->id.'" id="option-'.$ov->id.'" '.$checked.'> <label for="option-'.$ov->id.'">'.$ov->name.'</label> ');
									                    }
								                    }
							                    else
							                    {
								                    echo('<select name="option['.$option->id.']" class="form-control select2"> <option value="0">Не вибрано</option>');
								                    foreach ($option_values as $ov) {
									                    $selected = '';
									                    if(in_array($ov->id, $value))
									                    {
										                    if(isset($photo->options[$option->id]) && $ov->id == $photo->options[$option->id])
											                    $selected = ' selected';

										                    echo("<option value='{$ov->id}'{$selected}>{$ov->name}</option>");
									                    }
								                    }
								                    echo("</select> ");

							                    }

						                    }
					                    }

					                    echo( '</div></div>' );
				                    }
			                    }
		                    ?></form><?php
	                    } ?>
                    </td>
                    <td class="navigation">
    	                <button name="main" class="btn btn-warning PHOTO_MAIN" onClick="savePhoto(<?=$photo->id?>, this)" <?= ($photo->position == 1) ? 'disabled="disabled"' : '' ?>>
    					    <i class="glyphicon glyphicon-eye-open"></i>
    					    <span>Головне</span>
    					</button>
                        <button class="btn btn-danger" onClick="deletePhoto(<?=$photo->id?>)">
                            <i class="glyphicon glyphicon-trash"></i>
                            <span>Видалити</span>
                        </button>
                        <br>
                        Додано: <?=date('d.m.Y H:i', $photo->date_add)?>
                        <?=mb_substr($photo->user_name, 0, 16, 'utf-8')?>
                        <br>
                        <span id="pea-saveing-<?=$photo->id?>" class="saveing"><img src="<?=SITE_URL?>style/admin/images/icon-loading.gif">Зберігання..</span>
                    </td>
                </tr>
        <?php } } ?>
    </tbody>
</table>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled style="display:none">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Скасувати</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr id="photo-{%=file.id%}" class="template-download fade">
        <td class="preview">
            {% if (file.thumbnailUrl) { %}
                <a href="{%=file.url%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
            {% } %}
        </td>
        <td>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%#file.error%}</div>
            {% } else { %}
                <?php if($_SESSION['language']) {
                    foreach ($_SESSION['all_languages'] as $language) { ?>
                        <div class="input-group">
                            <span class="input-group-addon"><?=$language?></span>
                            <input name="title-<?=$language?>" type="text" value="" class="form-control" placeholder="<?=$pageNames[$language]?>" onChange="savePhoto({%=file.id%}, this)">
                        </div>
                <?php } } else { ?>
                    <textarea name="title" onChange="savePhoto({%=file.id%}, this)" placeholder="<?=$_SESSION['alias']->name?>"></textarea>
                <?php } ?>
            {% } %}
        </td>
        <td class="navigation">
        	<button name="main" class="btn btn-warning PHOTO_MAIN" onClick="savePhoto({%=file.id%}, this)" disabled="disabled">
			    <i class="glyphicon glyphicon-eye-open"></i>
			    <span>Головне</span>
			</button>
            <button class="btn btn-danger delete" onClick="deletePhoto({%=file.id%})">
                <i class="glyphicon glyphicon-trash"></i>
                <span>Видалити</span>
            </button>
            <br>
            Додано: {%=file.date%}
            <a href="<?=SITE_URL?>profile/<?=$_SESSION['user']->id?>"><?=mb_substr($_SESSION['user']->name, 0, 16, 'utf-8')?></a>
            <br>
            <span id="pea-saveing-{%=file.id%}" class="saveing"><img src="<?=SITE_URL?>style/admin/images/icon-loading.gif">Зберігання..</span>
        </td>
    </tr>
{% } %}
</script>

<?php 
$_SESSION['alias']->js_load[] = "assets/blueimp/js/vendor/jquery.ui.widget.js";
// $_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.iframe-transport.js";
$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload.js";
$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload-process.js";
$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload-image.js";
$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload-validate.js";
$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload-ui.js";
$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.blueimp-gallery.min.js";
// $_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload-audio.js";
// $_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload-video.js";
// $_SESSION['alias']->js_load[] = "assets/blueimp/js/cors/jquery.xdr-transport.js";

$_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js';
?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />


<!-- The Templates plugin is included to render the upload/download listings -->
<script src="https://blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>

<style type="text/css">
    td.move {
        width: 30px;
        cursor: move;
    }
    .fileupload-progress .progress-extended {
        margin-top: 5px;
    }
    .error {
        color: red;
    }
    td.preview {
        width: 150px;
    }
    td.preview a img {
        width: 150px;
    }
    td textarea {
        width: 100%;
        height: 100%;
    }
    td.navigation {
        width: 150px;
        font-size: 15px;
    }
    td.navigation button.btn {
    	margin: 2px;
    	width: 150px;
    	font-size: 16px;
    }
    .saveing {
        display: none;
        font-size: 95%;
    }
    .saveing img {
        width: 35px;
    }

    #tab-photo .form-group .select2-container{
		min-width: 100%;
	}

    @media (min-width: 481px) {
      .navigation {
        list-style: none;
        padding: 0;
      }
      .navigation li {
        display: inline-block;
      }
      .navigation li:not(:first-child):before {
        content: "| ";
      }
    }
</style>
<?php
$_SESSION['alias']->js_init[] = '
	$("#tab-photo .form-group .select2").on("change", function (e) {
		let select = e.currentTarget,
			$select = $(select),
			$form = $select.parents(".form-options");
			$form
			$.ajax({
		      url: "'.SITE_URL.'admin/wl_photos/saveImgOption",
		      type: "POST",
		      data: $form.serialize(),
			  success: function(res){ 
				if(res["result"] == false){
					$.gritter.add({title:"Помилка!", text:label + " " + res["error"]});
				} else {
					$.gritter.add({title:label, text:"Дані успішно збережено!"});
				}
			  },
			  error: function(){
					$.gritter.add({title:"Помилка!", text:"Помилка! Спробуйте ще раз!"});
			},
			timeout: function(){
				$.gritter.add({title:"Помилка!", text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
				}
			});
	});
';
?>
<!-- blueimp Gallery styles -->
<link rel="stylesheet" href="<?=SITE_URL?>assets/blueimp/css/blueimp-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="<?=SITE_URL?>assets/blueimp/css/jquery.fileupload.css">
<link rel="stylesheet" href="<?=SITE_URL?>assets/blueimp/css/jquery.fileupload-ui.css">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="<?=SITE_URL?>assets/blueimp/css/jquery.fileupload-noscript.css"></noscript>
<noscript><link rel="stylesheet" href="<?=SITE_URL?>assets/blueimp/css/jquery.fileupload-ui-noscript.css"></noscript>