<?php if ($echoSectionTag) { ?>
	<section id="section_<?= $section_id ?>" class="m-t-15">
<?php } ?>

	<div class="row m-b-15">
		<div class="col-sm-10">
			<?php if($_SESSION['language']) { foreach($_SESSION['all_languages'] as $lang) { ?>
				<div class="input-group">
                    <span class="input-group-addon"><?=$lang?></span>
                    <input type="text" class="editSection form-control" name="title-<?=$lang?>" data-section_id="<?= $section_id ?>" value="<?= ${'title_' . $lang} ?>" placeholder="Видимий заголовок [section_title] / <?=$lang?>" title="Видимий заголовок [section_title] / <?=$lang?>">
                </div>
			<?php } } else { ?>
				<input type="text" class="editSection form-control" name="title" data-section_id="<?= $section_id ?>" value="<?= $title ?>" placeholder="Видимий заголовок [section_title]" title="Видимий заголовок [section_title]">
			<?php } ?>
		</div>
		<div class="col-sm-2">
			<a href="#modal-config-section" class="btn btn-success pull-right" data-toggle="modal" data-section_id="<?= $section_id ?>" title="Налаштування секції [<?= $name ?>]"><i class="fa fa-cogs" aria-hidden="true"></i> Налаштування секції</a>
		</div>
	</div>
	
	<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
	<div id="fileupload-section-<?= $section_id ?>" class="fileupload-buttonbar">

		<div class="row">
			<div class="col-sm-2">
				<!-- The fileinput-button span is used to style the file input field as button -->
			    <span class="btn btn-primary fileinput-button f-left">
			        <i class="glyphicon glyphicon-plus"></i>
			        <span>Додати фото</span>
			        <input type="file" name="photos[]" multiple>
			    </span>
			</div>
			<div class="col-sm-10">
				<?php if($_SESSION['language']) { foreach($_SESSION['all_languages'] as $lang) { ?>
					<div class="input-group">
                        <span class="input-group-addon"><?=$lang?></span>
                        <input type="text" class="editSection form-control" name="value-<?=$lang?>" data-section_id="<?= $section_id ?>" value="<?= ${'value_' . $lang} ?>" placeholder="Видимий опис [section_value] / <?=$lang?>" title="Видимий опис [section_value] / <?=$lang?>">
                    </div>
				<?php } } else { ?>
					<input type="text" class="editSection form-control" name="value" data-section_id="<?= $section_id ?>" value="<?= $value ?>" placeholder="Видимий опис [section_value]" title="Видимий опис [section_value]">
				<?php } ?>
			</div>
		</div>
	    
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

	<table id="PHOTOS-<?= $section_id ?>" class="table">
	    <tbody class="files">
	        <?php if(!empty($_SESSION['alias']->images)) {
	        	$pos = 1;
	            foreach($_SESSION['alias']->images as $photo)
	            {
	                if($photo->section_id != $section_id)
	                	continue;
	                if($pos != $photo->position)
	                {
	                    $photo->position = $pos;
	                    $this->db->updateRow('wl_images', array('position' => $pos), $photo->id);
	                }
	                $pos++;
	            }
	            foreach($_SESSION['alias']->images as $photo) {
	                if($photo->section_id != $section_id) continue;
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
	                            foreach ($_SESSION['all_languages'] as $lang) { ?>
	                                <div class="input-group">
	                                    <span class="input-group-addon"><?=$lang?></span>
	                                    <input name="title-<?=$lang?>" type="text" value="<?=(isset($texts[$lang])) ? $texts[$lang] : ''?>" class="form-control" placeholder="як назва сторінки / <?=$pageNames[$lang] ?? ''?>" onChange="savePhoto(<?=$photo->id?>, this)">
	                                </div>
	                        <?php } } else { ?>
	                            <textarea name="title" onChange="savePhoto(<?=$photo->id?>, this)" placeholder="<?=$_SESSION['alias']->name?>"><?=($photo->title != $_SESSION['alias']->name) ? $photo->title : ''?></textarea>
	                        <?php } ?>
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

<?php if ($echoSectionTag) { ?>
	</section>
<?php } 

$_SESSION['alias']->js_init[] = "initFileUpload({$section_id});"; ?>