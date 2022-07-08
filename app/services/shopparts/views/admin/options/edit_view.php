<div class="row">
	<div class="col-md-9">
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
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_option" method="POST" class="form-horizontal">
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
                        	<?php $option->alias = explode('-', $option->alias); array_shift($option->alias); $option->alias = implode('-', $option->alias); ?>
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
								$options = false;
								foreach ($types as $type) {
									$selected = '';
									if($type->id == $option->type){
										$selected = 'selected';
										if($type->options == 1) $options = true;
									}
									echo("<option value='{$type->id}' {$selected}>{$type->name}</option>");
								}
								?>
							</select>
                        </div>
                    </div>

					<?php if($options){ ?>
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

						<?php
							echo('<table id="options" class="table table-striped table-bordered nowrap col-md-12"><tbody>');
							echo('<tr><th colspan="');
							$colspan = 2;
							if($_SESSION['language']) $colspan += count($_SESSION['all_languages']);
							else $colspan++;
							echo($colspan.'"><h4 class="pull-left">Властивості параметру</h4> <button type="button" onClick="addOptionRow()" class="pull-right btn btn-warning">Додати властивість</button></th></tr>');
							$options = array();
							if($_SESSION['language']){
								$options = $this->db->getAllDataByFieldInArray($this->options_model->table(), ($option->id * -1), 'group');
								echo("<tr><td></td>");
								foreach ($_SESSION['all_languages'] as $lang) {
									echo("<td>{$lang}</td>");
								}
								echo("<td></td></tr>");
							} else {
								$this->db->select($this->options_model->table().' as o', '*', -$option->id, 'group');
								$this->db->join($this->options_model->table('_options_name'), 'id as name_id, name', '#o.id', 'option');
				                $options = $this->db->get('array');
							}
							
							if($options){
								$i = 1;
								if($_SESSION['language']){
									foreach ($options as $opt){
										$names_db = $this->db->getAllDataByFieldInArray($this->options_model->table('_options_name'), $opt->id, 'option');
										$names = array();
										if($names_db){
											foreach ($names_db as $name) {
												@$names[$name->language]->id = $name->id;
												$names[$name->language]->name = $name->name;
											}
										}
										echo('<tr id="option_'.$opt->id.'">');
										echo("<td>#{$i}</td>");
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
												echo("<td><input type='text' name='option_{$value_id}' value='{$value}' class='form-control'></td>");
											} else {
												echo("<td>Error {$lang}</td>");
											}
										}
										echo('<td><button type="button" onClick="deleteOptionRow('.$opt->id.')" class="btn btn-danger">Видалити властивість</button>');
										echo('</tr>');
										$i++;
									}
								} else {
									foreach ($options as $opt) {
										echo('<tr id="option_'.$opt->id.'">');
										echo("<td>#{$i}</td>");
										echo("<td><input type='text' name='option_{$opt->name_id}' value='{$opt->name}' class='form-control'></td>");
										echo('<td><button type="button" onClick="deleteOptionRow('.$opt->id.')" class="btn btn-danger">Видалити властивість</button>');
										echo('</tr>');
										$i++;
									}
								}
							} else {
								echo("<tr>");
								if($_SESSION['language']){
									echo("<tr><td>#1</td>");
									foreach ($_SESSION['all_languages'] as $lang) {
										echo("<td><input type='text' name='option_0_{$lang}[]' class='form-control' required></td>");
									}
								} else {
									echo("<td>#1</td><td><input type='text' name='option_0[]' class='form-control' required></td>");
								}
								echo("<td></td>");
								echo("</tr>");
							}
						} ?>
						</tbody>
					</table>

					<div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-sm btn-success col-md-2">Зберегти</button>
                        </div>
                    </div>
				</form>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	function addOptionRow () {
		var countRows = $('#options tr').length;
		<?php if($_SESSION['language']){ ?>
			countRows = countRows - 1;
			var appendText = '<tr><td>#' + countRows + '</td>';
			<?php foreach ($_SESSION['all_languages'] as $lang) { ?>
				appendText += '<td><input type="text" name="option_0_<?=$lang?>[]" class="form-control"></td>';
		<?php } } else { ?>
			var appendText = '<tr><td>#' + countRows + '</td>';
			appendText += '<td><input type="text" name="option_0[]" class="form-control"></td>';
		<?php } ?>
		appendText += '<td>*Пустий рядок зараховуватися не буде</td></tr>';
		$('#options').append(appendText);
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
					}
				}
			});
		}
	}

	function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}
</script>