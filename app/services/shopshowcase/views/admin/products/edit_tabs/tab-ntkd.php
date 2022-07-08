<?php if($_SESSION['language'] && $language) { ?>
	<div class="input-group">
	    <span class="input-group-addon">Назва</span>
	    <?php if($_SESSION['option']->ProductUseArticle) {
	    	if(mb_strlen($ntkd[$language]->name) > mb_strlen($product->article))
			{
				$name = explode(' ', $ntkd[$language]->name);
				if(array_pop($name) == $product->article)
					$ntkd[$language]->name = implode(' ', $name);
			}
	    	$pageNames[$language] = $ntkd[$language]->name;
	    ?>
	    <input type="text" value="<?=$ntkd[$language]->name?>" class="form-control" placeholder="Назва" onChange="saveNameWithArticle(this, '<?=$language?>')">
	    <?php } else { ?>
	    	<input type="text" value="<?=$ntkd[$language]->name?>" class="form-control" placeholder="Назва" onChange="save('name', this, '<?=$language?>')">
	    <?php } ?>
	</div>
	<small onClick="showEditTKD('<?=$language?>')" class="badge badge-info">Редагувати title, keywords, description</small>
	<div id="tkd-<?=$language?>" class="tkd">
		<div class="input-group">
		    <span class="input-group-addon">title</span>
		    <input type="text" value="<?=$ntkd[$language]->title?>" class="form-control" placeholder="<?=$ntkd[$language]->name?>" onChange="save('title', this, '<?=$language?>')">
		</div>
		<div class="input-group">
		    <span class="input-group-addon">keywords</span>
		    <input type="text" value="<?=$ntkd[$language]->keywords?>" class="form-control" placeholder="keywords" onChange="save('keywords', this, '<?=$language?>')">
		</div>
		<div class="input-group">
		    <span class="input-group-addon">description</span>
		    <input type="text" value="<?=$ntkd[$language]->description?>" class="form-control" placeholder="<?=$ntkd[$language]->list?>" onChange="save('description', this, '<?=$language?>')" maxlength="155">
		    <span class="input-group-addon">max: 155</span>
		</div>
	</div>

	<?php if(!empty($options_parents)) { 
		$showh3 = true;	
		foreach ($options_parents as $option_id) {
			if(isset($productOptions[$option_id])) {
				foreach ($productOptions[$option_id] as $option) {
					if($option->type_name == 'text' || $option->type_name == 'textarea')
					{
						if($showh3)
						{
							echo "<h3>Властивості товару</h3>";
							$showh3 = false;
						}

						$value = '';
						if(isset($product_options_values[$option->id][$language])) $value = $product_options_values[$option->id][$language];
						echo('<label>'.$option->name);
						if($option->type_name == 'textarea')
						{
							if($option->sufix != '')
								echo("({$option->sufix})");
							echo(':</label>');
							echo('<textarea onChange="saveOption(this, \''.$option->name.' '.$language.'\')" name="option-'.$option->id.'-'.$language.'">'.$value.'</textarea>');
						}
						else
						{
							echo(':</label>');
							if($option->sufix != '')
								echo('<div class="input-group">');
							echo('<input type="text" onChange="saveOption(this, \''.$option->name.' '.$language.'\')" name="option-'.$option->id.'-'.$language.'" value="'.$value.'" class="form-control">');
							if($option->sufix != '')
							{
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
								echo('</div>');
							}
						}
					}
				}
			}
		}
	}
	$_SESSION['alias']->js_init[] = "var editor_{$language} = CKEDITOR.replace( 'editor-{$language}' ); editor_{$language}.on('blur', function(ev) { saveText('{$language}' ) } );";
	?>
	<br>
	<label class="control-label">Короткий опис:</label><br>
	<textarea onChange="save('list', this, '<?=$language?>')"><?=$ntkd[$language]->list?></textarea>
	<h3>Опис:</h3>
	<textarea id="editor-<?=$language?>"><?=$ntkd[$language]->text?></textarea>

	<?php include APP_PATH . 'views' . DIRSEP . 'admin' . DIRSEP . 'wl_sections' . DIRSEP . '__sections.php';
} else { ?>
	<div class="input-group">
	    <span class="input-group-addon">Назва</span>
	    <?php if($_SESSION['option']->ProductUseArticle) {
	    	if(!empty($ntkd->name) && mb_strlen($ntkd->name) > mb_strlen($product->article))
			{
				$name = explode(' ', $ntkd->name);
				if(array_pop($name) == $product->article)
					$ntkd->name = implode(' ', $name);
			} ?>
	    	<input type="text" value="<?=$ntkd->name ?? ''?>" class="form-control" placeholder="Username" onChange="saveNameWithArticle(this)">
	    <?php } else { ?>
	    	<input type="text" value="<?=$ntkd->name ?? ''?>" class="form-control" placeholder="Username" onChange="save('name', this)">
	    <?php } ?>
	</div>
	<small onClick="showEditTKD('block')" class="badge badge-info">Редагувати title, keywords, description</small>
	<div id="tkd-block" class="tkd">
		<div class="input-group">
		    <span class="input-group-addon">title</span>
		    <input type="text" value="<?=$ntkd->title?>" class="form-control" placeholder="<?=$ntkd->name?>" onChange="save('title', this)">
		</div>
		<div class="input-group">
		    <span class="input-group-addon">keywords</span>
		    <input type="text" value="<?=$ntkd->keywords?>" class="form-control" placeholder="keywords" onChange="save('keywords', this)">
		</div>
		<div class="input-group">
		    <span class="input-group-addon">description</span>
		    <input type="text" value="<?=$ntkd->description?>" class="form-control" placeholder="<?=$ntkd->list?>" onChange="save('description', this)" maxlength="155">
		    <span class="input-group-addon">max: 155</span>
		</div>
	</div>
	<?php if(!empty($options_parents)) { 
		$showh3 = true;	
		foreach ($options_parents as $option_id) {
			if(isset($productOptions[$option_id])) {
				foreach ($productOptions[$option_id] as $option) {
					if($option->type_name == 'text' || $option->type_name == 'textarea')
					{
						if($showh3)
						{
							echo "<h3>Властивості товару</h3>";
							$showh3 = false;
						}

						$value = '';
						if(isset($product_options_values[$option->id])) $value = $product_options_values[$option->id];
						echo('<label>'.$option->name);
						if($option->type_name == 'textarea')
						{
							if($option->sufix != '')
								echo("({$option->sufix})");
							echo(':</label>');
							echo('<textarea onChange="saveOption(this, \''.$option->name.'\')" name="option-'.$option->id.'">'.$value.'</textarea>');
						}
						else
						{
							echo(':</label>');
							if($option->sufix != '')
								echo('<div class="input-group">');
							echo('<input type="text" onChange="saveOption(this, \''.$option->name.'\')" name="option-'.$option->id.'" value="'.$value.'" class="form-control">');
							if($option->sufix != '')
							{
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
								echo('</div>');
							}
						}
					}
				}
			}
		}
	} ?>
	<label class="control-label">Короткий опис:</label><br>
	<textarea onChange="save('list', this)" class="form-control"><?=$ntkd->list?></textarea>
	<h3>Опис:</h3>
	<textarea onChange="save('text', this)" id="editor"><?=html_entity_decode($ntkd->text, ENT_QUOTES, 'utf-8')?></textarea>

	<?php include APP_PATH . 'views' . DIRSEP . 'admin' . DIRSEP . 'wl_sections' . DIRSEP . '__sections.php'; ?>

<?php $_SESSION['alias']->js_init[] = "var editor = CKEDITOR.replace( 'editor' ); editor.on('blur', function(ev) { saveText(false) } );"; } ?>