<div class="row">
    <div class="col-md-12">
<?php
	$_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js';
	if($_SESSION['language'])
	{
		$data = array();
		if(is_array($ntkd))
			foreach ($ntkd as $n) {
				$data[$n->language] = clone $n;
			}
		else
			$data[$_SESSION['language']] = clone $ntkd;
		
		if(count($data) != count($_SESSION['all_languages']))
			foreach ($_SESSION['all_languages'] as $lang) {
				if(empty($data[$lang]))
				{
					$data[$lang] = new stdClass();
					$data[$lang]->name = '';
					$data[$lang]->title = '';
					$data[$lang]->keywords = '';
					$data[$lang]->description = '';
					$data[$lang]->text = '';
					$data[$lang]->list = '';
					$data[$lang]->meta = '';
				}
			}
		$ntkd = $data;
	}
	if(empty($ntkd))
	{
		$ntkd = new stdClass();
		$ntkd->name = '';
		$ntkd->title = '';
		$ntkd->keywords = '';
		$ntkd->description = '';
		$ntkd->text = '';
		$ntkd->list = '';
		$ntkd->meta = '';
	}


	if($_SESSION['language']){ ?>
		<ul class="nav nav-tabs">
		    <?php foreach ($_SESSION['all_languages'] as $lang) { ?>
		    	<li class="<?=($_SESSION['language'] == $lang) ? 'active' : ''?>"><a href="#language-tab-<?=$lang?>" data-toggle="tab" aria-expanded="true"><?=$lang?></a></li>
	        <?php } ?>
    	</ul>
    	<div class="tab-content">
			<?php foreach ($_SESSION['all_languages'] as $lang) { ?>
				<div class="tab-pane fade <?=($_SESSION['language'] == $lang) ? 'active in' : ''?>" id="language-tab-<?=$lang?>">
					<label class="col-md-2 control-label">Назва сторінки:</label>
					<div class="col-md-4">
                        <input type="text" onChange="save('name', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->name?>" class="form-control">
                    </div>
					<button type="button" class="btn btn-info" onclick="showEditTKD('<?=$lang?>')">Редагувати title, keywords, description, meta, SiteMap</button>
					<div class="row m-t-5" id="tkd-<?=$lang?>" style="display:none; border: 1px solid white;">
    					<div class="col-md-12">
							<label class="col-md-2 control-label">title:</label>
							<div class="col-md-4">
		                        <input type="text" onChange="save('title', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->title?>" placeholder="<?=$ntkd[$lang]->name?>" class="form-control">
		                    </div>
		                    <label class="col-md-2 control-label">keywords:</label>
							<div class="col-md-4">
		                        <input type="text" onChange="save('keywords', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->keywords?>" class="form-control">
		                    </div>
		                    <label class="col-md-2 control-label m-t-5">description: (max 230)</label>
							<div class="col-md-10 m-t-5">
		                        <input class="form-control" onChange="save('description', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->description?>" maxlength="230">
		                    </div>
		                </div>
		                <?php $where = array('alias' => $alias->id, 'content' => $content);
			    		$this->db->select('wl_sitemap', 'time, changefreq, priority', $where);
						$siteMap = $this->db->get(); 
						if(empty($siteMap))
						{
							$siteMap = new stdClass();
							$siteMap->time = 0;
							$siteMap->changefreq = 'daily';
							$siteMap->priority = 5;
						}
						?>
						<div class="col-md-12 m-t-5">
							<label class="col-md-2 control-label">Сторінка включена до індексації:</label>
							<div class="col-md-1">
								<input type="checkbox" data-render="switchery" <?=($siteMap->priority >= 0) ? 'checked' : ''?> value="1"  onChange="save('SiteMapIndex', this, '<?=$lang?>')" />
							</div>
							<label class="col-md-1 control-label SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>Частота оновлення:</label>
							<div class="col-md-2 SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>
								<select onChange="save('changefreq', this, '<?=$lang?>')" class="form-control">
									<?php $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'); 
									foreach ($changefreq as $freq) {
										echo('<option value="'.$freq.'"');
										if($siteMap->changefreq == $freq) echo(' selected');
										echo(">$freq</option>");
									}
									?>
								</select>
			                </div>
			                <label class="col-md-1 control-label SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>Пріорітетність:</label>
							<div class="col-md-1 SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>
			                    <input type="number" onChange="save('priority', this, '<?=$lang?>')" value="<?=$siteMap->priority/10?>" placeholder="0.5" min="0" max="1" step="0.1" class="form-control priority">
			                </div>
			                <label class="col-md-2 control-label">Остання зміна: <strong><?=($siteMap->time) ? date('d.m.Y H:i', $siteMap->time) : 'Не індексовано'?></strong></label>
						</div>
	                    <dic class="col-md-12 m-t-5">
	    					<label class="col-md-2 control-label">meta:</label>
							<div class="col-md-10">
		                        <textarea class="form-control" onChange="save('meta', this, '<?=$lang?>')"><?=$ntkd[$lang]->meta?></textarea>
		                    </div>
	    				</dic>
    				</div>
    				<div class="row m-t-5">
	    				<dic class="col-md-12">
	    					<label class="col-md-2 control-label m-t-5">Короткий опис (анонс у списку):</label>
							<div class="col-md-10 m-t-5">
		                        <textarea class="form-control" onChange="save('list', this, '<?=$lang?>')"><?=$ntkd[$lang]->list?></textarea>
		                    </div>
	    				</dic>
	    			</div>
					<div class="row m-t-5">
						<dic class="col-md-12">
							<label class="control-label">Вміст сторінки:</label><br>
							<textarea class="t-big" onChange="save('text', this, '<?=$lang?>')" id="editor-<?=$lang?>"><?=$ntkd[$lang]->text?></textarea>
							<button class="btn btn-success m-t-5" onClick="saveText('<?=$lang?>')"><i class="fa fa-save"></i> Зберегти текст вмісту сторінки</button>
						</dic>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } else { ?>
		<label class="col-md-2 control-label">Назва сторінки:</label>
		<div class="col-md-4">
            <input type="text" onChange="save('name', this)" value="<?=$ntkd->name?>" class="form-control">
        </div>
		<button type="button" class="btn btn-info" onclick="showEditTKD('lang')">Редагувати title, keywords, description, meta, SiteMap</button>
		<div class="row m-t-5" id="tkd-lang" style="display:none; border: 1px solid white;">
			<div class="col-md-12 m-t-5">
				<label class="col-md-2 control-label">title:</label>
				<div class="col-md-4">
                    <input type="text" onChange="save('title', this)" value="<?=$ntkd->title?>" placeholder="<?=$ntkd->name?>" class="form-control">
                </div>
                <label class="col-md-2 control-label">keywords:</label>
				<div class="col-md-4">
                    <input type="text" onChange="save('keywords', this)" value="<?=$ntkd->keywords?>" class="form-control">
                </div>
                <label class="col-md-2 control-label m-t-5">description: (max 230)</label>
				<div class="col-md-10 m-t-5">
                    <input class="form-control" onChange="save('description', this)" value="<?=$ntkd->description?>" maxlength="230">
                </div>
            </div>
            <?php $where = array('alias' => $alias->id, 'content' => $content);
    		$this->db->select('wl_sitemap', 'time, changefreq, priority', $where);
			$siteMap = $this->db->get(); 
			if(empty($siteMap))
			{
				$siteMap = new stdClass();
				$siteMap->time = 0;
				$siteMap->changefreq = 'daily';
				$siteMap->priority = 5;
			}
			?>
			<div class="col-md-12 m-t-5">
				<label class="col-md-2 control-label">Сторінка включена до індексації:</label>
				<div class="col-md-1">
					<input type="checkbox" data-render="switchery" <?=($siteMap->priority >= 0) ? 'checked' : ''?> value="1"  onChange="save('SiteMapIndex', this)" />
				</div>
				<label class="col-md-1 control-label SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>Частота оновлення:</label>
				<div class="col-md-2 SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>
					<select onChange="save('changefreq', this)" class="form-control">
						<?php $changefreq = array('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'); 
						foreach ($changefreq as $freq) {
							echo('<option value="'.$freq.'"');
							if($siteMap->changefreq == $freq) echo(' selected');
							echo(">$freq</option>");
						}
						?>
					</select>
                </div>
                <label class="col-md-1 control-label SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>Пріорітетність:</label>
				<div class="col-md-1 SiteMap" <?=($siteMap->priority < 0) ? 'style="display:none"' : ''?>>
                    <input type="number" onChange="save('priority', this)" value="<?=$siteMap->priority/10?>" placeholder="0.5" min="0" max="1" step="0.1" class="form-control priority">
                </div>
                <label class="col-md-2 control-label">Остання зміна: <strong><?=($siteMap->time) ? date('d.m.Y H:i', $siteMap->time) : 'Не індексовано'?></strong></label>
			</div>
            <dic class="col-md-12 m-t-5 m-b-10">
				<label class="col-md-2 control-label">meta:</label>
				<div class="col-md-10">
                    <textarea class="form-control t-big" onChange="save('meta', this)"><?=$ntkd->meta?></textarea>
                </div>
			</dic>
		</div>
		<div class="row m-t-5">
			<dic class="col-md-12">
				<label class="col-md-2 control-label m-t-5">Короткий опис (анонс у списку):</label>
				<div class="col-md-10 m-t-5">
	                <textarea class="form-control" onChange="save('list', this)"><?=$ntkd->list?></textarea>
	            </div>
			</dic>
		</div>
		<div class="row m-t-5">
			<dic class="col-md-12">
				<label class="control-label">Вміст сторінки:</label><br>
				<textarea class="t-big" onChange="save('text', this)" id="editor"><?=$ntkd->text?></textarea>
				<button class="btn btn-success m-t-5" onClick="saveText(false)"><i class="fa fa-save"></i> Зберегти текст вмісту сторінки</button>
			</dic>
		</div>
	<?php } ?>

	</div>
</div>

<script type="text/javascript" src="<?=SITE_URL?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>assets/ckfinder/ckfinder.js"></script>
<script type="text/javascript">
	<?php if($_SESSION['language']) foreach($_SESSION['all_languages'] as $lng) echo "CKEDITOR.replace( 'editor-{$lng}' ); "; else echo "CKEDITOR.replace( 'editor' ); "; ?>
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

<script type="text/javascript">
	var data;
	function save (field, e, lang) {
	    var value = '';
	    if(e != false) value = e.value;
	    else value = data;
        $.ajax({
            url: "<?=SITE_URL?>admin/wl_ntkd/save",
            type: 'POST',
            data: {
            	alias: '<?=$alias->id?>',
            	content: '<?=$content?>',
                field: field,
                data: value,
                language: lang,
                json: true
            },
            success: function(res){
                if(res['result'] == false){
                    $.gritter.add({title:"Помилка!",text:res['error']});
                } else {
                	language = '';
                	if(lang) language = lang;
                	$.gritter.add({title:field+' '+language,text:"Дані успішно збережено!"});

                	if(field == 'SiteMapIndex')
                	{
                		if($('.SiteMap').is(":hidden")){
							$('.SiteMap').slideDown("slow");
							var priority = $('.priority').val();
							if(priority < 0) $('.priority').val(priority * -1);
					    } else {
							$('.SiteMap').slideUp("fast");
					    }
                	}
                }
            },
            error: function(){
                $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
            },
            timeout: function(){
            	$.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            }
        });
	}
	function saveText(lang){
		if(lang != false){
			data = CKEDITOR.instances['editor-'+lang].getData();
		} else {
			data = CKEDITOR.instances['editor'].getData();
		}
		save('text', false, lang);
	}
	function showEditTKD (lang) {
		if($('#tkd-'+lang).is(":hidden")){
			$('#tkd-'+lang).slideDown("slow");
	    } else {
			$('#tkd-'+lang).slideUp("fast");
	    }
	}
</script>

<style type="text/css">
	textarea.t-big{
		height: 450px;
	}
</style>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />