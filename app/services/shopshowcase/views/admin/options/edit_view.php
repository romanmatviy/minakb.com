<div class="row">
    <div class="panel panel-inverse">
        <div class="panel-heading">
        	<div class="panel-heading-btn">
            	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>
				<button onClick="showUninstalForm()" class="btn btn-danger btn-xs m-l-10">Видалити <?=$_SESSION['admin_options']['word:option']?></button>
            </div>
            <h4 class="panel-title">Основні дані</h4>
        </div>

    	<div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
            <i class="fa fa-trash fa-2x pull-left"></i>
            <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete_option" method="POST">
            	<p>Ви впевнені що бажаєте видалити <?=$_SESSION['admin_options']['word:option']?> із всіма властивостями, якщо такі є?</p>
				<input type="hidden" name="id" value="<?=$option->id?>">
				<input type="submit" value="Видалити" class="btn btn-danger">
				<button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
			</form>
        </div>

        <?php if(isset($_SESSION['notify'])){ 
        	require APP_PATH.'views/admin/notify_view.php';
        } ?>

		<div class="panel-body">
        	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_option" method="POST" enctype="multipart/form-data" class="form-horizontal">
				<input type="hidden" name="id" value="<?=$option->id?>">

				<div class="form-group">
                    <label class="col-md-3 control-label">Стан</label>
                    <div class="col-md-9">
                        <label class="radio-inline">
                        	<input type="radio" name="active" value="1" <?=($option->active == 1)?'checked':''?>>
                            Властивість активна
                        </label>
                        <label class="radio-inline">
                        	<input type="radio" name="active" value="0" <?=($option->active == 0)?'checked':''?>>
                            Властивість тимчасово відключено
                        </label>
                    </div>
                </div>

				<div class="form-group">
                    <label class="col-md-3 control-label">Власна адреса</label>
                    <div class="col-md-9">
                    	<?php
                    	 $option->originalAlias = $option->alias;
                    	 $option->alias = explode('-', $option->alias); array_shift($option->alias); $option->alias = implode('-', $option->alias); ?>
						<div class="input-group">
                            <span class="input-group-addon"><?=$option->id?>-</span>
							<input type="text" name="alias" value="<?=$option->alias?>" placeholder="alias" required class="form-control">
                        </div>
                    </div>
                </div>

				<?php
				if($_SESSION['option']->useGroups)
				{
					$groups = $this->groups_model->getGroups(-1);
					if($groups)
					{
						$list = array();
						$emptyChildsList = array();
						foreach ($groups as $g)
						{
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

						echo '<div class="form-group">';
							echo '<label class="col-md-3 control-label">Група</label>';
							echo '<div class="col-md-9">';
								echo('<select name="group" class="form-control">');
									echo('<option value="0">Немає</option>');
									if(!empty($list))
									{
										function showList($option_id, $all, $list, $parent = 0, $level = 0)
										{
											$prefix = '';
											for ($i=0; $i < $level; $i++) { 
												$prefix .= '- ';
											}
											foreach ($list as $g) if($g->parent == $parent) {
												$selected = '';
												if($option_id == $g->id) $selected = 'selected';
												echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
												if(!empty($g->child)){
													$l = $level + 1;
													$childs = array();
													foreach ($g->child as $c) {
														$childs[] = $all[$c];
													}
													showList ($option_id, $all, $childs, $g->id, $l);
												}
											}
											return true;
										}
										showList($option->group, $list, $list);
									}
								echo('</select>');
						echo("</div></div>");
					}
				}

				$ns = $this->db->getAllDataByFieldInArray($this->options_model->table('_options_name'), $option->id, 'option');
				if($_SESSION['language']){
					$names = array();
					foreach ($ns as $n) {
						$names[$n->language] = $n;
					}
				 foreach ($_SESSION['all_languages'] as $lang) { 
				 	if(empty($names[$lang])){
						$data = array();
						$data['option'] = $option->id;
						$data['language'] = $lang;
						$data['name'] = '';
						if($this->db->insertRow($this->options_model->table('_options_name'), $data)){
							@$names[$lang]->name = '';
							$names[$lang]->sufix = '';
						}
				 	}
				 	?>
				 	<div class="form-group">
                        <label class="col-md-3 control-label">Назва <?=$lang?></label>
                        <div class="col-md-9">
                        	<input type="text" name="name_<?=$lang?>" value="<?=$names[$lang]->name?>" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Суфікс (розмірність) <?=$lang?></label>
                        <div class="col-md-9">
                        	<input type="text" name="sufix_<?=$lang?>" value="<?=$names[$lang]->sufix?>" class="form-control">
                        </div>
                    </div>
				<?php } } else { ?>
					<div class="form-group">
                        <label class="col-md-3 control-label">Назва</label>
                        <div class="col-md-9">
                        	<input type="text" name="name" value="<?=$ns[0]->name?>" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Суфікс (розмірність)</label>
                        <div class="col-md-9">
                        	<input type="text" name="sufix" value="<?=$ns[0]->sufix?>" class="form-control">
                        </div>
                    </div>
				<?php } ?>

				<div class="form-group">
                    <label class="col-md-3 control-label">Тип</label>
                    <div class="col-md-9">
                    	<select name="type" class="form-control" required>
							<?php 
							$types = $this->db->getAllData('wl_input_types');
							$useOptions = false;
							foreach ($types as $type) {
								$selected = '';
								if($type->id == $option->type){
									$selected = 'selected';
									if($type->options == 1) $useOptions = true;
								}
								echo("<option value='{$type->id}' {$selected}>{$type->name}</option>");
							}
							?>
						</select>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-md-3 control-label">Основна характеристика товару (витягується завжди)</label>
                    <div class="col-md-9">
                        <label class="radio-inline">
                        	<input type="radio" name="main" value="1" <?=($option->main == 1)?'checked':''?>>
                            Так
                        </label>
                        <label class="radio-inline">
                        	<input type="radio" name="main" value="0" <?=($option->main == 0)?'checked':''?>>
                            Ні
                        </label>
                        <?php $alias = explode('-', $option->alias);
	        			if($alias[0] == $option->id)
	        				array_shift($alias); ?>
                        <p><i>В каталозі у змінній товару <strong>$product-><?=implode('_', $alias)?></strong></i></p>
                    </div>
                </div>
				<?php if($useOptions) { ?>
					<div class="form-group">
	                    <label class="col-md-3 control-label">Сортування</label>
	                    <div class="col-md-9">
	                    	<select name="sort" class="form-control" required>
								<option value="0" <?=($option->sort == 0)?'selected' : ''?>>Ручне</option>
								<option value="1" <?=($option->sort == 1)?'selected' : ''?>>Пряме текстове (а..Я, 0..9)</option>
								<option value="2" <?=($option->sort == 2)?'selected' : ''?>>Зворотнє текстове (Я..а, 9..0)</option>
								<option value="3" <?=($option->sort == 3)?'selected' : ''?>>Пряме числове (0..9)</option>
								<option value="4" <?=($option->sort == 4)?'selected' : ''?>>Зворотнє числове (9..0)</option>
							</select>
							<?php if($option->sort > 0) { ?>
								<label><input type="checkbox" name="savePositionToManual" value="1"> Зберегти позиції для ручного сортування</label>
							<?php } ?>
	                    </div>
	                </div>
					<div class="form-group">
                        <label class="col-md-3 control-label">Елемент фільтру (для пошуку)</label>
                        <div class="col-md-9">
                            <label class="radio-inline">
                            	<input type="radio" name="filter" value="1" <?=($option->filter == 1)?'checked':''?>>
                                Так
                            </label>
                            <label class="radio-inline">
                            	<input type="radio" name="filter" value="0" <?=($option->filter == 0)?'checked':''?>>
                                Ні
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Відносно корзини</label>
                        <div class="col-md-9">
                            <label class="radio-inline">
                            	<input type="radio" name="toCart" value="1" <?=($option->toCart == 1)?'checked':''?>>
                                властивість впливає на товар (обирає покупець перед додачею в корзину)
                            </label>
                            <label class="radio-inline">
                            	<input type="radio" name="toCart" value="0" <?=($option->toCart == 0)?'checked':''?>>
                                властивість описує товар (менеджер фіксує значення для товау)
                            </label>
                        </div>
                    </div>
                    <?php if($option->toCart == 1) { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Впливає на ціну</label>
	                        <div class="col-md-9">
	                            <label class="radio-inline">
	                            	<input type="radio" name="changePrice" value="1" <?=($option->changePrice)?'checked':''?>>
	                                Так
	                            </label>
	                            <label class="radio-inline">
	                            	<input type="radio" name="changePrice" value="0" <?=(!$option->changePrice)?'checked':''?>>
	                                Ні
	                            </label>
	                        </div>
	                    </div>
					<?php }
						echo('<table id="options" class="table table-striped table-bordered nowrap col-md-12"><tbody>');
						echo('<tr><th colspan="');
						$colspan = 3;
						if($_SESSION['language']) $colspan += count($_SESSION['all_languages']);
						else $colspan++;
						echo($colspan.'"><h4 class="pull-left">Властивості параметру</h4>
							<button type="button" onClick="addOptionRow()" class="pull-right btn btn-warning"><i class="fa fa-plus"></i> Додати властивість</button>
							<button type="submit" class="pull-right btn btn-success m-r-15"><i class="fa fa-save"></i> Зберегти</button>
							</th></tr>');

						$options = array();
						$this->db->select($this->options_model->table().' as o', '*', -$option->id, 'group');
						if($_SESSION['language'])
						{
							echo("<tr><td></td><td>Фото</td>");
							foreach ($_SESSION['all_languages'] as $lang) {
								echo("<td>{$lang}</td>");
							}
							echo("<td></td></tr>");
							$this->db->join($this->options_model->table('_options_name').' as n', 'name', array('option' => '#o.id', 'language' => $_SESSION['language']));
						}
						else
							$this->db->join($this->options_model->table('_options_name').' as n', 'id as name_id, name', '#o.id', 'option');
						if($option->sort == 0)
							$this->db->order('position ASC');
						if($option->sort == 1 || $option->sort == 3)
								$this->db->order('name ASC', 'n');
						if($option->sort == 2 || $option->sort == 4)
							$this->db->order('name DESC', 'n');
						
						if($options = $this->db->get('array'))
						{
							if($option->sort == 3 || $option->sort == 4)
							{
								function tofloat($num) {
								    $dotPos = strrpos($num, '.');
								    $commaPos = strrpos($num, ',');
								    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
								        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
								  
								    if (!$sep) {
								        return floatval(preg_replace("/[^0-9]/", "", $num));
								    }

								    return floatval(
								        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
								        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
								    );
								}
								$for_sort = [];
								foreach ($options as $opt) {
									$for_sort[] = tofloat($opt->name);
								}
								$sort_type = $option->sort == 3 ? SORT_ASC : SORT_DESC;
								array_multisort($for_sort, $sort_type, SORT_NUMERIC, $options);
								unset($for_sort);
							}

							if($option->sort == 0 || isset($_SESSION['optionsavePositionToManual']))
							{
								$pos = 1;
					            foreach ($options as $opt) {
					                if($pos != $opt->position)
					                {
					                    $opt->position = $pos;
					                    $this->db->updateRow($this->options_model->table(), array('position' => $pos), $opt->id);
					                }
					                $pos++;
					            }
					            unset($_SESSION['optionsavePositionToManual']);
					        }

							$i = 1;
							
							foreach ($options as $opt) {
								
								$rowspan = ($option->changePrice) ? 'rowspan="2"' : '';
								$disableBG = ($opt->active) ? '' : 'class="warning" title="Властивість відключено"';
								echo('<tr id="option_'.$opt->id.'" '.$disableBG.'>');
								if($option->sort)
									echo("<td {$rowspan}>#{$i}</td>");
								else
									echo("<td {$rowspan} class=\"move sortablehandle\"><i class=\"fa fa-sort\"></i> #{$i}</td>");

								if($opt->photo == 0)
									echo('<td '.$rowspan.'><label class="btn btn-warning" ><input onchange="uploadPhoto(this)" type="file" name="photo['.$opt->id.']" value="+" style="display: none;"><span>+</span></label></td>');
								else
								{
									$photoPath = $_SESSION["alias"]->alias."/options/".$option->originalAlias."/".$opt->photo; 
									echo('<td '.$rowspan.'><img style="width: 100px; height: auto; display: inline-block;" src='.IMG_PATH.$photoPath.' >');
									echo(' <button class="btn btn-danger" onClick="deletePropertyPhoto('.$opt->id.',\''.$photoPath.'\')" title="Видалити фото">-</button></td>');
								}

								if($_SESSION['language'])
								{
									$names_db = $this->db->getAllDataByFieldInArray($this->options_model->table('_options_name'), $opt->id, 'option');
									$names = array();
									if($names_db)
										foreach ($names_db as $name) {
											@$names[$name->language]->id = $name->id;
											$names[$name->language]->name = $name->name;
										}

									foreach ($_SESSION['all_languages'] as $lang) {
										$value = '';
										$value_id = 0;
										if(isset($names[$lang])){
											$value = $names[$lang]->name;
											$value_id = $names[$lang]->id;
										} else {
											$data = array();
											$data['option'] = $opt->id;
											$data['language'] = $lang;
											$data['name'] = '';
											$this->db->insertRow($this->options_model->table('_options_name'), $data);
											$value_id = $this->db->getLastInsertedId();
										}
										if($value_id > 0) {
											$value = htmlspecialchars($value);
											echo("<td><input type='text' name='option_{$value_id}' value=\"{$value}\" class='form-control'></td>");
										} else {
											echo("<td>Error {$lang}</td>");
										}
									}
								}
								else
									echo("<td><input type='text' name='option_{$opt->name_id}' value='{$opt->name}' class='form-control'></td>");

								echo('<td><button type="button" onClick="activeOptionRow('.$opt->id.')" class="btn btn-warning" title="Активувати/відключити властивість"><i class="fa fa-check-square-o" aria-hidden="true"></i></button> <button type="button" onClick="deleteOptionRow('.$opt->id.')" class="btn btn-danger" title="Видалити властивість"><i class="fa fa-trash" aria-hidden="true"></i></button>');
								echo('</tr>');

								if ($option->changePrice) { ?>
									<tr id="option_price_<?=$opt->id?>">
									<td colspan="<?=count($_SESSION['all_languages']) + 2 ?>">
										
					                    	<?php $action = $value = $currency = 0;
					                    	if(!empty($opt->changePrice) && !is_numeric($opt->changePrice))
					                    	{
					                    		$changePrice = unserialize($opt->changePrice);
					                    		$action = $changePrice['action'];
					                    		$value = $changePrice['value'];
					                    		$currency = $changePrice['currency'];
					                    	}
					                    	?>
					                        <label class="col-md-3 control-label">Зміна ціни за замовчуванням</label>
					                        <div class="col-md-9">
					                        	<div class="row">
					                        	<div class="col-xs-2">
					                        		<select class="form-control" name="changePrice-action_<?=$opt->id?>" onchange="setChangePrice(<?=$opt->id?>, this.value)">
						                            	<option value="0">відключено</option>
						                            	<option value="+" <?=($action === '+') ? 'selected' : ''?>>+</option>
						                            	<option value="-" <?=($action === '-') ? 'selected' : ''?>>-</option>
						                            	<option value="*" <?=($action === '*') ? 'selected' : ''?>>*</option>
						                            </select>
					                        	</div>
												<div class="col-xs-8">    
						                            <input type="number" name="changePrice-value-<?=$opt->id?>" value="<?=$value?>" class="form-control changePrice-set-<?=$opt->id?>" <?=($action === 0) ? 'disabled' : ''?>>
						                        </div>
						                        <div class="col-xs-2">
						                            <select class="form-control changePrice-set-<?=$opt->id?>" name="changePrice-currency-<?=$opt->id?>" <?=($action === 0) ? 'disabled' : ''?>>
						                            	<option value="0">y.o.</option>
						                            	<?php if(!empty($_SESSION['currency']) && is_array($_SESSION['currency'])) {
						                            		foreach ($_SESSION['currency'] as $code => $value) {
						                            			$selected = ($currency === $code) ? 'selected' : '';
											            		echo '<option value="'.$code.'" '.$selected.'>'.$code.'</option>';
											            	}
						                            	} ?>
						                            	<option value="p" <?=($currency === 'p') ? 'selected' : ''?>>%</option>
						                            </select>
						                        </div>
						                        </div>
					                        </div>
				                	</td>
									</tr>
								<?php }

								$i++;
							}
						}
						else
						{
							echo("<tr>");
							if($_SESSION['language']){
								echo("<tr><td>#1</td><td></td>");
								foreach ($_SESSION['all_languages'] as $lang) {
									echo("<td><input type='text' name='option_0_{$lang}[]' class='form-control'></td>");
								}
							} else {
								echo("<td>#1</td><td></td><td><input type='text' name='option_0[]' class='form-control'></td>");
							}
							echo("<td></td>");
							echo("</tr>");
						}
					} ?>
					</tbody>
				</table>

				<div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="submit" class="btn btn-success col-md-2"><i class="fa fa-save"></i> Зберегти</button>
                        <?php if($useOptions) { ?>
                        	<button type="button" onClick="addOptionRow()" class="m-l-10 btn btn-warning"><i class="fa fa-plus"></i> Додати властивість</button>
                        <?php } ?>
                    </div>
                </div>
			</form>
		</div>
	</div>
</div>

<style type="text/css">
	td.move {
        cursor: move;
    }
</style>
<script type="text/javascript">
	function setChangePrice(id, value) {
		if(value == '0')
			$('.changePrice-set-'+id).prop('disabled', true);
		else
			$('.changePrice-set-'+id).prop('disabled', false);
	}

	function addOptionRow () {
		var countRows = $('#options tr').length;
		<?php if($_SESSION['language']){ ?>
			countRows = countRows - 1;
			var appendText = '<tr><td>#' + countRows + '</td><td></td>';
			<?php foreach ($_SESSION['all_languages'] as $lang) { ?>
				appendText += '<td><input type="text" name="option_0_<?=$lang?>[]" class="form-control"></td>';
		<?php } } else { ?>
			var appendText = '<tr><td>#' + countRows + '</td><td></td>';
			appendText += '<td><input type="text" name="option_0[]" class="form-control"></td>';
		<?php } ?>
		appendText += '<td>*Пустий рядок зараховуватися не буде</td></tr>';
		$('#options').append(appendText);
	}

	function activeOptionRow (id) {
		$.ajax({
			url: "<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/activeOptionProperty",
			type: 'POST',
			data: {
				id :  id,
				json : true
			},
			success: function(res){
				if(res['result'] == false){
					alert('Помилка! Спробуйте щераз');
				} else {
					$('#option_'+id).toggleClass("warning");
				}
			}
		});
	}

	function deleteOptionRow (id) {
		if(confirm("Ви впевнені що бажаєте видалити властивість?")){
			$.ajax({
				url: "<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/deleteOptionProperty",
				type: 'POST',
				data: {
					id :  id,
					json : true
				},
				success: function(res){
					if(res['result'] == false){
						alert('Помилка! Спробуйте щераз');
					} else {
						$('#option_'+id).slideUp("fast");
						$('#option_price_'+id).slideUp("fast");
					}
				}
			});
		}
	}

	function deletePropertyPhoto(id, path) {
		$.ajax({
			url: "<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/deletePropertyPhoto",
			type: 'POST',
			data: {
				id :  id,
				path :  path,
				json : true
			},
			success: function(res){
				document.location.reload();
			}
		});
	}

	function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}

	function uploadPhoto(event) {
		$(event).next().html('<i class="fa fa-image"></i> Збережіть дані');
		$(event).parent().attr('class', 'btn btn-success');
	}

<?php if($option->sort == 0) { ?>
window.onload  = function()
{
	$( "#options tbody" ).sortable({
      handle: ".sortablehandle",
      update: function( event, ui ) {
            $('#saveing').css("display", "block");
            $.ajax({
                url: ALIAS_ADMIN_URL+"change_suboption_position",
                type: 'POST',
                data: {
                    id: ui.item.attr('id'),
                    position: ui.item.index(),
                    json: true
                },
                success: function(res){
                    if(res['result'] == false){
                        alert("Помилка! Спробуйте ще раз!");
                    }
                    $('#saveing').css("display", "none");
                },
                error: function(){
                    alert("Помилка! Спробуйте ще раз!");
                    $('#saveing').css("display", "none");
                },
                timeout: function(){
                    alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
                    $('#saveing').css("display", "none");
                }
            });
        }
    });
}
<?php } ?>
</script>