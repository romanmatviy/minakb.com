<?php
if(!$_SESSION['language'] || $_SESSION['language'] == $language)
{
	$ntkd = new stdClass();
	$ntkd->name = $_SESSION['alias']->name;
	$ntkd->title = ($_SESSION['alias']->title == $_SESSION['alias']->name) ? '' : $_SESSION['alias']->title;
	$ntkd->keywords = $_SESSION['alias']->keywords;
	$ntkd->description = ($_SESSION['alias']->list == $_SESSION['alias']->description) ? '' : $_SESSION['alias']->description;
	$ntkd->text = $_SESSION['alias']->text;
	$ntkd->list = $_SESSION['alias']->list;
	$ntkd->meta = $_SESSION['alias']->meta;
	
	if($_SESSION['language'])
	{
		$pageNames[$language] = $ntkd->name;
		$language_attr = ", '{$language}'";
		$language_block = "-{$language}";
		$language_block_name = "'{$language}'";
		$_SESSION['alias']->js_init[] = "var editor_{$language} = CKEDITOR.replace( 'editor-{$language}' ); editor_{$language}.on('blur', function(ev) { saveText('{$language}') } );";	
	}
	else
	{
		$language_attr = "";
		$language_block = "-block";
		$language_block_name = "'block'";
		$_SESSION['alias']->js_init[] = "var editor = CKEDITOR.replace( 'editor-block' ); editor.on('blur', function(ev) { saveText('block') } );";	
	}
}
else
{
	$where = array();
	$where['alias'] = $_SESSION['alias']->id;
	$where['content'] = $_SESSION['alias']->content;
	if(isset($language))
		$where['language'] = $language;
	$ntkd = $this->db->getAllDataById('wl_ntkd', $where);

	if(empty($ntkd))
	{
		$this->db->insertRow('wl_ntkd', $where);
		$ntkd = new stdClass();
		$ntkd->name = $ntkd->title = $ntkd->keywords = $ntkd->description = $ntkd->text = $ntkd->list = $ntkd->meta = '';
		$ntkd->use_sections = false;
	}

	$pageNames[$language] = $ntkd->name;
	$language_attr = ", '{$language}'";
	$language_block = "-{$language}";
	$language_block_name = "'{$language}'";
	$_SESSION['alias']->js_init[] = "var editor_{$language} = CKEDITOR.replace( 'editor-{$language}' ); editor_{$language}.on('blur', function(ev) { saveText('{$language}') } );";	
}

?>
<div class="input-group">
    <span class="input-group-addon">Назва</span>
    <input type="text" value="<?=$ntkd->name?>" class="form-control" placeholder="Назва" onChange="save('name', this <?=$language_attr?>)">
</div>

<small onClick="showEditTKD(<?=$language_block_name?>)" class="badge badge-info">Редагувати title, keywords, description, meta</small>

<div id="tkd<?=$language_block?>" class="tkd">
	<div class="input-group">
	    <span class="input-group-addon">title</span>
	    <input type="text" value="<?=$ntkd->title?>" class="form-control" placeholder="<?=$ntkd->name?>" onChange="save('title', this <?=$language_attr?>)">
	</div>
	<div class="input-group">
	    <span class="input-group-addon">keywords</span>
	    <input type="text" value="<?=$ntkd->keywords?>" class="form-control" placeholder="keywords" onChange="save('keywords', this <?=$language_attr?>)">
	</div>
	<div class="input-group">
	    <span class="input-group-addon">description</span>
	    <input type="text" value="<?=$ntkd->description?>" class="form-control" placeholder="<?=$ntkd->list?>" onChange="save('description', this <?=$language_attr?>)" maxlength="230">
	    <span class="input-group-addon">max: 230</span>
	</div>
	<div class="input-group">
	    <span class="input-group-addon">meta (додатково)</span>
	    <textarea class="form-control" onChange="save('meta', this <?=$language_attr?>)"><?=$ntkd->meta?></textarea>
	</div>
</div>
<br>
<label class="control-label">Короткий опис (анонс у списку):</label><br>
<textarea class="form-control" onChange="save('list', this <?=$language_attr?>)"><?=$ntkd->list?></textarea>
<label>Опис:</label><br>
<textarea onChange="save('text', this <?=$language_attr?>)" id="editor<?=$language_block?>"><?=html_entity_decode($ntkd->text, ENT_QUOTES, 'utf-8')?></textarea>

<?php include APP_PATH . 'views' . DIRSEP . 'admin' . DIRSEP . 'wl_sections' . DIRSEP . '__sections.php'; ?>