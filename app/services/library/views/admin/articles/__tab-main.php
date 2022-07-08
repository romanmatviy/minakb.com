<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" class="form-horizontal">
	<input type="hidden" name="id" value="<?=$article->id?>">
	<?php $options_parents = array();
	if($_SESSION['option']->useGroups)
	{
		$this->load->smodel('library_model');
		$groups = $this->library_model->getGroups(-1);
		if($groups)
		{
			$list = array();
			$emptyChildsList = array();
			foreach ($groups as $g) {
				$list[$g->id] = $g;
				$list[$g->id]->child = array();
				if(isset($emptyChildsList[$g->id])){
					foreach ($emptyChildsList[$g->id] as $c) {
						$list[$g->id]->child[] = $c;
					}
				}
				if($g->parent > 0) {
					if(isset($list[$g->parent]->child)) $list[$g->parent]->child[] = $g->id;
					else {
						if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
						else $emptyChildsList[$g->parent] = array($g->id);
					}
				}
			}

			if($list)
			{
				if(is_array($article->group))
				{
					foreach ($article->group as $parent) {
						while ($parent != 0) {
							array_unshift($options_parents, $parent);
							$parent = $list[$parent]->parent;
						}
					}
				}
				else
				{
					$parent = $article->group;
					while ($parent != 0) {
						array_unshift($options_parents, $parent);
						$parent = $list[$parent]->parent;
					}
				}
			}
		?>
		<div class="form-group">
	        <label class="col-md-3 control-label">Оберіть <?=$_SESSION['admin_options']['word:groups_to_delete']?></label>
	        <div class="col-md-9">
			<?php if($_SESSION['option']->articleMultiGroup && !empty($list)){
				function showList($article_group, $all, $list, $parent = 0, $level = 0, $parents = array())
				{
					$ml = 15 * $level;
					foreach ($list as $g) if($g->parent == $parent) {
						$class = '';
						if($g->parent > 0 && !empty($parents)){
							$class = 'class="';
							foreach ($parents as $p) {
								$class .= ' parent-'.$p;
							}
							$class .= '"';
						}
						if(empty($g->child)){
							$checked = '';
							if(in_array($g->id, $article_group)) $checked = 'checked';
							echo ('<input type="checkbox" name="group[]" value="'.$g->id.'" id="group-'.$g->id.'" '.$class.' '.$checked.'>');
							echo ('<label for="group-'.$g->id.'">'.$g->name.'</label>');
							echo ('<br>');
						} else {
							echo ('<input type="checkbox" id="group-'.$g->id.'" '.$class.' onChange="setChilds('.$g->id.')">');
							echo ('<label for="group-'.$g->id.'">'.$g->name.'</label>');
							$l = $level + 1;
							$childs = array();
							foreach ($g->child as $c) {
								$childs[] = $all[$c];
							}
							$ml = 15 * $l;
							echo ('<div style="margin-left: '.$ml.'px">');
							$parents2 = $parents;
							$parents2[] = $g->id;
							showList ($article_group, $all, $childs, $g->id, $l, $parents2);
							echo('</div>');
						}
					}

					return true;
				}
				showList($article->group, $list, $list);
			} else {
				echo('<input type="hidden" name="group_old" value="'.$article->group.'">');
				echo('<input type="hidden" name="position_old" value="'.$article->position.'">');
				echo('<select name="group" class="form-control">');
				echo ('<option value="0">Немає</option>');
				if(!empty($list)){
					function showList($article_group, $all, $list, $parent = 0, $level = 0)
					{
						$prefix = '';
						for ($i=0; $i < $level; $i++) { 
							$prefix .= '- ';
						}
						foreach ($list as $g) if($g->parent == $parent) {
							if(empty($g->child)){
								$selected = '';
								if($article_group == $g->id) $selected = 'selected';
								echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
							} else {
								echo('<optgroup label="'.$prefix.$g->name.'">');
								$l = $level + 1;
								$childs = array();
								foreach ($g->child as $c) {
									$childs[] = $all[$c];
								}
								showList ($article_group, $all, $childs, $g->id, $l);
								echo('</optgroup>');
							}
						}
						return true;
					}
					showList($article->group, $list, $list);
				}
				echo('</select>');
			}
			echo "</div></div>";
		}
	} ?>
	<div class="form-group">
        <label class="col-md-3 control-label">Власна адреса</label>
        <div class="col-md-9">
            <div class="input-group">
                <span class="input-group-addon">/<?=$url.'/'?></span>
                <input type="text" name="alias" value="<?=$article->alias?>" required class="form-control">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Стан</label>
        <div class="col-md-9">
            <input type="radio" name="active" value="1" <?=($article->active == 1)?'checked':''?> id="active-1"><label for="active-1">Публікація активна</label>
			<input type="radio" name="active" value="0" <?=($article->active == 0)?'checked':''?> id="active-0"><label for="active-0">Публікацію тимчасово відключено</label>
        </div>
    </div>
    <?php array_unshift($options_parents, 0);
    if(!empty($options_parents) && $_SESSION['option']->articleUseOptions) { 
		$product_options = array();
		if($options = $this->db->getAllDataByFieldInArray($this->library_model->table('_article_options'), $article->id, 'article'))
			foreach ($options as $option) {
				$product_options[$option->option] = $option->value;
			}
    ?>
		<center><h4>Властивості <?=$_SESSION['admin_options']['word:articles']?></h4></center>
		<?php $this->load->smodel('options_model');
			foreach ($options_parents as $option_id) {
				if($options = $this->options_model->getOptions($option_id))
					foreach ($options as $option) 
					{
						$value = (isset($product_options[$option->id])) ? $product_options[$option->id] : '';
						echo('<div class="form-group">');
						echo('<label class="col-md-3 control-label">'.$option->name);
						if($option->sufix != '') echo " ({$option->sufix})";
						echo('</label> <div class="col-md-9">');
						if($option->type_name == 'checkbox')
						{
							$where = ($_SESSION['language']) ? "AND n.language = '{$_SESSION['language']}'" : '';
							$option_values = array();
							$this->db->executeQuery("SELECT o.*, n.id as name_id, n.name FROM `{$this->library_model->table('_options')}` as o LEFT JOIN `{$this->library_model->table('_options_name')}` as n ON n.option = o.id {$where} WHERE o.group = '-{$option->id}'");
							if($this->db->numRows() > 0)
			                    $option_values = $this->db->getRows('array');
			                
							if(!empty($option_values))
							{
								$value = explode(',', $value);
								foreach ($option_values as $ov) {
									$checked = '';
									if(in_array($ov->id, $value)) $checked = ' checked';
									echo('<input type="checkbox" name="option-'.$option->id.'[]" value="'.$ov->id.'" id="option-'.$ov->id.'" '.$checked.'> <label for="option-'.$ov->id.'">'.$ov->name.'</label> ');
								}
							}
						}
						elseif($option->type_name == 'radio')
						{
							$where = ($_SESSION['language']) ? "AND n.language = '{$_SESSION['language']}'" : '';
							$option_values = $this->db->getQuery("SELECT o.*, n.id as name_id, n.name FROM `{$this->library_model->table('_options')}` as o LEFT JOIN `{$this->library_model->table('_options_name')}` as n ON n.option = o.id {$where} WHERE o.group = '-{$option->id}'", 'array');
							if(!empty($option_values))
							{
								$checked = ($value == '' || $value == 0) ? ' checked' : '';
								echo('<input type="radio" name="option-'.$option->id.'" value="0" id="option-'.$option->id.'-0" '.$checked.'> <label for="option-'.$option->id.'-0">Не вказано</label> ');
								foreach ($option_values as $ov) {
									$checked = ($value == $ov->id) ? ' checked' : '';
									echo('<input type="radio" name="option-'.$option->id.'" value="'.$ov->id.'" id="option-'.$ov->id.'" '.$checked.'> <label for="option-'.$ov->id.'">'.$ov->name.'</label> ');
								}
							}
						}
						elseif($option->type_name == 'select')
						{
							$where = '';
							if($_SESSION['language']) $where = "AND n.language = '{$_SESSION['language']}'";
							$option_values = array();
							$this->db->executeQuery("SELECT o.*, n.id as name_id, n.name FROM `{$this->library_model->table('_options')}` as o LEFT JOIN `{$this->library_model->table('_options_name')}` as n ON n.option = o.id {$where} WHERE o.group = '-{$option->id}'");
							if($this->db->numRows() > 0){
			                    $option_values = $this->db->getRows('array');
			                }
							echo('<select name="option-'.$option->id.'" class="form-control"> ');
							echo("<option value='0'>Не вказано</option>");
							if(!empty($option_values)){
								foreach ($option_values as $ov) {
									$selected = '';
									if($value == $ov->id) $selected = ' selected';
									echo("<option value='{$ov->id}'{$selected}>{$ov->name}</option>");
								}
							}
							echo("</select> ");
						}
						elseif($option->type_name == 'number')
						{
							if($option->sufix != '')
								echo('<div class="input-group">');
							echo('<input type="'.$option->type_name.'" step="0.01" name="option-'.$option->id.'" value="'.$value.'"  class="form-control" onChange="saveOption(this, \''.$option->name.'\')"> ');
							if($option->sufix != '')
							{
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
								echo('</div>');
							}
						}
						else
						{
							if($option->sufix != '')
								echo('<div class="input-group">');
							echo('<input type="'.$option->type_name.'" name="option-'.$option->id.'" value="'.$value.'"  class="form-control" onChange="saveOption(this, \''.$option->name.'\')"> ');
							if($option->sufix != '')
							{
								echo("<span class=\"input-group-addon\">{$option->sufix}</span>");
								echo('</div>');
							}
						}
						echo('</div></div>');
					}
			}
		}
	?>
    <div class="form-group">
        <label class="col-md-3 control-label">Додано</label>
        <div class="col-md-9">
            <p><?=$article->author_add .'. ' . $article->author_add_name . date(' d.m.Y H:i', $article->date_add)?></p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Востаннє редагувано</label>
        <div class="col-md-9">
            <p><?=$article->author_edit .'. ' . $article->author_edit_name . date(' d.m.Y H:i', $article->date_edit)?></p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-3 control-label">Після збереження:</label>
        <div id="after_save" class="col-md-9">
            <input type="radio" name="to" value="edit" id="to_edit" checked="checked"><label for="to_edit">продовжити редагування</label>
			<input type="radio" name="to" value="category" id="to_category"><label for="to_category">до списку <?=$_SESSION['admin_options']['word:articles_to_all']?></label>
			<input type="radio" name="to" value="new" id="to_new"><label for="to_new"><?=$_SESSION['admin_options']['word:article_add']?></label>
        </div>
    </div>
	<div class="form-group">
        <label class="col-md-3 control-label"></label>
        <div class="col-md-9">
            <button type="submit" class="btn btn-sm btn-success">Зберегти</button>
        </div>
    </div>
</form>

<style type="text/css">
	input[type="radio"]{
		min-width: 15px;
		height: 15px;
		margin-left: 15px;
		margin-right: 5px;
	}
	input[type="checkbox"]{
		margin-right: 5px;
	}
	#after_save label {
		font-weight: normal;
		width: auto;
		padding-right: 10px;
	}
</style>
<script type="text/javascript">
	function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}
	function setChilds (parent) {
		if($('#group-'+parent).prop('checked')){
			$('.parent-'+parent).prop('checked', true);
		} else {
			$('.parent-'+parent).prop('checked', false);
		}
	}
</script>