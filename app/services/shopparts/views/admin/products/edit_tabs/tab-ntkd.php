<?php if($_SESSION['language'] && $lang) { ?>
	<label>Назва:</label> <input type="text" onChange="save('name', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->name?>" class="form-control"><br>
	<br>
	<small style="text-align: center; cursor: pointer; display: block" onClick="showEditTKD('<?=$lang?>')">Редагувати title, keywords, description</small>
	<br>
	<div id="tkd-<?=$lang?>" class="tkd">
		<label>title:</label> <input type="text" onChange="save('title', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->title?>" class="form-control"><br>
		<label>keywords:</label> <input type="text" onChange="save('keywords', this, '<?=$lang?>')" value="<?=$ntkd[$lang]->keywords?>" class="form-control"><br>
		<label>description:</label><br>
		<textarea onChange="save('description', this, '<?=$lang?>')" class="form-control"><?=$ntkd[$lang]->description?></textarea>
	</div>

	<?php if(!empty($options_parents)) { ?>
		<h3>Властивості <?=$_SESSION['admin_options']['word:products']?></h3>
		<?php 			
			foreach ($options_parents as $option_id) {
				$options = $this->options_model->getOptions($option_id);
				if($options){
					foreach ($options as $option) if($option->type_name == 'text' || $option->type_name == 'textarea') {
						
						$value = '';
						if(isset($product_options[$option->id][$lang])) $value = $product_options[$option->id][$lang];
						echo('<label>'.$option->name.':</label>');
						if($option->type_name == 'textarea'){
							echo('<textarea onChange="saveOption(this, \''.$option->name.' '.$lang.'\')" name="option-'.$option->id.'-'.$lang.'">'.$value.'</textarea>');
							if($option->sufix != '') {
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
							}
						} else {
							echo('<div class="input-group">');
							echo('<input type="text" onChange="saveOption(this, \''.$option->name.' '.$lang.'\')" name="option-'.$option->id.'-'.$lang.'" value="'.$value.'" class="form-control">');
							if($option->sufix != '') {
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
							}
							echo('</div>');
						}
						echo('<br><br>');
					}
				}
			}
		}
	?>
	<br>
	<label class="control-label">Короткий опис:</label><br>
	<textarea onChange="save('list', this, '<?=$lang?>')"><?=$ntkd[$lang]->list?></textarea>
	<h3>Опис:</h3>
	<textarea id="editor-<?=$lang?>"><?=$ntkd[$lang]->text?></textarea>
	<button class="btn btn-success m-t-5" onClick="saveText('<?=$lang?>')"><i class="fa fa-save"></i> Зберегти текст опису сторінки</button>

<?php } else { ?>

	<label>Назва:</label> <input type="text" onChange="save('name', this)" value="<?=$ntkd->name?>" class="form-control"><br>
	<br>
	<small style="text-align: center; cursor: pointer; display: block" onClick="showEditTKD('block')">Редагувати title, keywords, description</small>
	<br>
	<div id="tkd-block" class="tkd">
		<label>title:</label> <input type="text" onChange="save('title', this)" value="<?=$ntkd->title?>" class="form-control"><br>
		<label>keywords:</label> <input type="text" onChange="save('keywords', this)" value="<?=$ntkd->keywords?>" class="form-control"><br>
		<label>description:</label><br>
		<textarea onChange="save('description', this)" class="form-control"><?=$ntkd->description?></textarea>
	</div>
	<br>
	<?php if(!empty($options_parents)) { ?>
		<h3>Властивості <?=$_SESSION['admin_options']['word:products']?></h3>
		<?php 			
			foreach ($options_parents as $option_id) {
				$options = $this->options_model->getOptions($option_id);
				if($options){
					foreach ($options as $option) if($option->type_name == 'text' || $option->type_name == 'textarea') {
						
						$value = '';
						if(isset($product_options[$option->id])) $value = $product_options[$option->id];
						echo('<label>'.$option->name.':</label>');
						if($option->type_name == 'textarea'){
							echo('<textarea onChange="saveOption(this, \''.$option->name.'\')" name="option-'.$option->id.'">'.$value.'</textarea>');
							if($option->sufix != '') {
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
							}
						} else {
							echo('<div class="input-group">');
							echo('<input type="text" onChange="saveOption(this, \''.$option->name.'\')" name="option-'.$option->id.'-" value="'.$value.'" class="form-control">');
							if($option->sufix != '') {
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
							}
							echo('</div>');
						}
						echo('<br><br>');
					}
				}
			}
		}
	?>
	<br>
	<label class="control-label">Короткий опис:</label><br>
	<textarea onChange="save('list', this)" class="form-control"><?=$ntkd->list?></textarea>
	<label>Опис:</label><br>
	<textarea onChange="save('text', this)" id="editor"><?=html_entity_decode($ntkd->text, ENT_QUOTES, 'utf-8')?></textarea>
	<button class="btn btn-success m-t-5" onClick="saveText(false)"><i class="fa fa-save"></i> Зберегти текст опису сторінки</button>

<?php } ?>

<style>
	.tkd {
		border: 1px solid black;
		padding: 10px;
		display: none;
	}
	textarea {
		width: 100%;
		height: 100px;
	}
</style>