<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$template->id?>" class="btn btn-warning btn-xs"><i class="fa fa-ravelry"></i> До розсилки на основі шаблону</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-list"></i> До всіх шаблонів</a>
            	</div>
                <h4 class="panel-title">Редагувати шаблон <strong><?=$template->name?></strong></h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" class="form-horizontal" enctype="multipart/form-data">
            		<input type="hidden" name="id" value="<?=$template->id?>">
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Назва шаблону </label>
                        <div class="col-md-9"> <input type="text" name="name" value="<?=$template->name?>" required class="form-control"> </div>
                    </div>
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Тема листа </label>
                        <div class="col-md-9"> <input type="text" name="theme" value="<?=$template->theme?>" required class="form-control"> </div>
                    </div>
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Від кого </label>
                        <div class="col-md-9">
                        	<div class="input-group">
								<input type="text" name="from" value="<?=$template->from?>" placeholder="Адміністрація <?=SITE_NAME?>" class="form-control">
	                        	<span class="input-group-addon">< <?=SITE_EMAIL?> ></span>
				            </div>
                        </div>
                    </div>
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Кому </label>
                        <div class="col-md-9">
                        	<?php $user_types = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
                        	foreach ($user_types as $u_type) {
                        		$checked = in_array($u_type->id, $template->to_user_types) ? 'checked' : '';
                        	 	echo "<label><input type='checkbox' name='to[]' value='{$u_type->id}' {$checked}> {$u_type->title}</label> <br>";
                        	} ?>
                        </div>
                    </div>
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Файли </label>
                        <div class="col-md-9">
                        	<?php if($template->files)
                        	foreach ($template->files as $file) { ?>
                        		<div class="input-group row m-b-5">
                        			<span class="input-group-addon"><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/files/'.$file->id?>"><i class="fa fa-qrcode"></i> Дивитися</a></span>
									<input type="text" name="file-name-<?=$file->id?>" value="<?=$file->name?>" required class="form-control">
		                        	<span class="input-group-addon"> <button type="submit" name="file-delete" value="<?=$file->id?>" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Видалити</button> </span>
					            </div>
                        	<?php } ?>
                        	<div class="row">
                        		<label class="col-md-3 control-label"> Додати файли </label>
                        		<div class="col-md-9">
                        			<input type="file" name="files[]" multiple>
                        		</div>
                        	</div>
                        </div>
                    </div>
            		<div class="form-group">
                        <label class="col-md-3 control-label"> Шаблон створено </label>
                        <div class="col-md-9"> <?=date("d.m.Y H:i", $template->date_add)?> </div>
                    </div>
                    
	                <div class="form group">
	                	<h4>Слова для використання: <small>(можна використовувати в темі листа та при формуванні вмісту)</small></h4>
                        <p>Дані конкретного клієнта/отримувача листа: <strong>{id}, {name}, {email}, {registered} - дата реєстрації у вигляді d.m.Y H:i</strong></p>
                        <p><strong>{date}</strong> - дата на момент розсилки, наприклад <i><?=date('d.m.Y')?></i>, <strong>{dateTime}</strong> - дата та час на момент розсилки, наприклад <i><?=date('d.m.Y H:i')?></i></p>
                        <p><strong>{SITE_URL}</strong> - адреса сайту <i><?=SITE_URL?></i></p>
                        <p><strong>{SERVER_URL}</strong> - адреса серверу (те саме що адреса сайту на основній мові) <i><?=SERVER_URL?></i></p>
                        <p><strong>{IMAGE_PATH}</strong> - шлях до папки, де розсташовуються зображення <i><?=IMG_PATH?></i></p>
                        <p><strong>{SITE_NAME}</strong> - назва сайту (від домену/адреса) <i><?=SITE_NAME?></i></p>
	                	<textarea id="editor" name="text"><?=$template->text?></textarea>
	                </div>
	                <div class="form-group text-center">
                        <label><input type="radio" name="after" value="edit" checked required>Зберегти і вернутися</label>
						<label style="margin-left:15px"><input type="radio" name="after" value="sent">Зберегти і перейти на розсилку</label>
						<br>
						<button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-save"></i> Зберегти</button>
                    </div>
	            </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?=SITE_URL?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>assets/ckfinder/ckfinder.js"></script>
<script type="text/javascript">
	CKEDITOR.replace( 'editor', {
     extraPlugins: 'colorbutton'
    } );
	CKFinder.setupCKEditor( null, {
		basePath : '<?=SITE_URL?>assets/ckfinder/',
		filebrowserBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html',
		filebrowserImageBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html?type=Images',
		filebrowserFlashBrowseUrl : '<?=SITE_URL?>assets/ckfinder/ckfinder.html?type=Flash',
		filebrowserUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Files',
		filebrowserImageUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Images',
		filebrowserFlashUploadUrl : '<?=SITE_URL?>assets/ckfinder/core/connector/asp/connector.asp?command=QuickUpload&type=Flash',
	});
</script>