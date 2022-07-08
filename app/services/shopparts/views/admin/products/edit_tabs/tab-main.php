<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="id" value="<?=$product->id?>">
	<table class="table table-striped table-bordered">
		<tr>
			<td colspan="2">Внутрішній код товару на сайті (#id) <strong><?=$product->id?></strong></td>
		</tr>
		<tr>
			<th style="width:25%">Власна адреса посилання</th>
			<td>
				<div class="input-group">
					<?php
						$article = $this->data->latterUAtoEN($product->manufacturer_name.'-'.$product->article);
						$alias = substr($product->alias, strlen($article) + 1);
						echo('<span class="input-group-addon">/'.$url.'/'.$article.'-</span>');
					?>
                    <input type="text" name="alias" value="<?=$alias?>" required class="form-control">
                </div>
            </td>
		</tr>

		<tr>
			<th>Артикул</th>
			<td><input type="text" name="article" value="<?=$product->article?>" class="form-control" required></td>
		</tr>

		<tr>
			<th>Виробник</th>
			<td>
				<select name="manufacturer" class="form-control" required>
					<?php if($manufacturers) foreach ($manufacturers as $manufacturer) {
						$selected = ($manufacturer->id == $product->manufacturer) ? 'selected' : '';
						echo "<option value=\"{$manufacturer->id}\" {$selected}>{$manufacturer->name}</option>";
					} ?>
				</select>
			</td>
		</tr>

		<tr>
			<th>Назва товару</th>
			<td><input type="text" name="name" value="<?=$product->name?>" class="form-control" required></td>
		</tr>

		<tr>
			<th>Аналоги</th>
			<td>
			<?php 
			if($product->analogs == '')
				echo "Не вказано";
			else
				foreach (explode(',', $product->analogs) as $analog) {
					echo "<span class=\"label label-info\">{$analog}</span> ";
				}
			?>
			</td>
		</tr>

		<?php if($_SESSION['option']->useGroups)
		{
			$this->load->smodel('shop_model');
			$groups = $this->shop_model->getGroups(-1);
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

				echo "<tr><th>Оберіть {$_SESSION['admin_options']['word:groups_to_delete']}</th><td>";
				if($_SESSION['option']->ProductMultiGroup && !empty($list)){
					function showList($product_group, $all, $list, $parent = 0, $level = 0, $parents = array())
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
								if(in_array($g->id, $product_group)) $checked = 'checked';
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
								showList ($product_group, $all, $childs, $g->id, $l, $parents2);
								echo('</div>');
							}
						}

						return true;
					}
					showList($product->group, $list, $list);
				} else {
					echo('<select name="group" class="form-control">');
					echo ('<option value="0">Немає</option>');
					if(!empty($list)){
						function showList($product_group, $all, $list, $parent = 0, $level = 0)
						{
							$prefix = '';
							for ($i=0; $i < $level; $i++) { 
								$prefix .= '- ';
							}
							foreach ($list as $g) if($g->parent == $parent) {
								if(empty($g->child)){
									$selected = '';
									if($product_group == $g->id) $selected = 'selected';
									echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
								} else {
									echo('<optgroup label="'.$prefix.$g->name.'">');
									$l = $level + 1;
									$childs = array();
									foreach ($g->child as $c) {
										$childs[] = $all[$c];
									}
									showList ($product_group, $all, $childs, $g->id, $l);
									echo('</optgroup>');
								}
							}
							return true;
						}
						showList($product->group, $list, $list);
					}
					echo('</select>');
				}
				echo "</td></tr>";
			}
		} ?>

		<tr>
			<th>Оригінальність</th>
			<td>
				<input type="radio" name="orign" value="1" <?=($product->orign == 1)?'checked':''?> id="orign-1"><label for="orign-1">Оригінальна запчастина</label>
				<input type="radio" name="orign" value="0" <?=($product->orign == 0)?'checked':''?> id="orign-0"><label for="orign-0">Замінник оригіналу</label>
			</td>
		</tr>

		<tr>
			<th>Стан</th>
			<td>
				<input type="radio" name="active" value="1" <?=($product->active == 1)?'checked':''?> id="active-1"><label for="active-1">Товар активний</label>
				<input type="radio" name="active" value="0" <?=($product->active == 0)?'checked':''?> id="active-0"><label for="active-0">Товар тимчасово недоступний</label>
			</td>
		</tr>

		<?php if(empty($storages)) { ?>
			<tr>
				<th>Вартість (y.o.)</th>
				<td>
					<div class="input-group">
	                    <input type="number" name="price" value="<?=$product->price?>" min="0" step="0.01" required class="form-control">
	                    <span class="input-group-addon">y.o.</span>
	                </div>
				</td>
			</tr>
		<?php }

		if(!empty($options_parents)) {
				$showh3 = true;
			 $this->load->smodel('options_model');
				foreach ($options_parents as $option_id) {
					$options = $this->options_model->getOptions($option_id);
					if($options)
					{
						if($showh3)
						{
							echo "<tr><td colspan=\"2\"><h3>Властивості товару</h3></td></tr>";
							$showh3 = false;
						}
						foreach ($options as $option) 
						if($_SESSION['language'] == false || ($option->type_name != 'text' && $option->type_name != 'textarea'))
						{
							$value = '';
							if(isset($product_options[$option->id])) $value = $product_options[$option->id];
							echo('<tr>');
							echo('<th>'.$option->name);
							if($option->sufix != '') echo " ({$option->sufix})";
							echo('</th><td>');
							if($option->type_name == 'checkbox')
							{
								$where = '';
								if($_SESSION['language']) $where = "AND n.language = '{$_SESSION['language']}'";
								$option_values = array();
								$this->db->executeQuery("SELECT o.*, n.id as name_id, n.name FROM `{$this->shop_model->table('_options')}` as o LEFT JOIN `{$this->shop_model->table('_options_name')}` as n ON n.option = o.id {$where} WHERE o.group = '-{$option->id}'");
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
								$option_values = $this->db->getQuery("SELECT o.*, n.id as name_id, n.name FROM `{$this->shop_model->table('_options')}` as o LEFT JOIN `{$this->shop_model->table('_options_name')}` as n ON n.option = o.id {$where} WHERE o.group = '-{$option->id}'", 'array');
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
								$this->db->executeQuery("SELECT o.*, n.id as name_id, n.name FROM `{$this->shop_model->table('_options')}` as o LEFT JOIN `{$this->shop_model->table('_options_name')}` as n ON n.option = o.id {$where} WHERE o.group = '-{$option->id}'");
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
							elseif($option->type_name == 'textarea')
							{
								echo('<textarea onChange="saveOption(this, \''.$option->name.'\')" name="option-'.$option->id.'">'.$value.'</textarea>');
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
							echo('</td></tr>');
						}
					}
				}
			}
		?>
		<tr>
			<td>
				Після збереження:
			</td>
			<td id="after_save">
				<input type="radio" name="to" value="edit" id="to_edit" checked="checked"><label for="to_edit">продовжити редагування</label>
				<input type="radio" name="to" value="category" id="to_category"><label for="to_category">до списку <?=$_SESSION['admin_options']['word:products_to_all']?></label>
				<input type="radio" name="to" value="new" id="to_new"><label for="to_new"><?=$_SESSION['admin_options']['word:product_add']?></label>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit" class="btn btn-sm btn-success col-md-6">Зберегти</button></td>
		</tr>
	</table>
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
	img.f-left {
		margin-right: 10px;
		height: 80px;
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