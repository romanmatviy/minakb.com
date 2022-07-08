<?php if(isset($_SESSION['notify'])) { 
require APP_PATH.'views/admin/notify_view.php';
} ?>
      
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<?php if($_SESSION['option']->useGroups == 1){ ?>
                	<div class="panel-heading-btn">
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
                	</div>
                <?php } ?>
                <h4 class="panel-title"><?=$_SESSION['admin_options']['word:product_add']?></h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" enctype="multipart/form-data" class="form-horizontal">
					<input type="hidden" name="id" value="0">
	                    	<?php if($_SESSION['option']->ProductUseArticle) { ?>
	                    		<div class="form-group">
			                        <label class="col-md-3 control-label">Артикул</label>
			                        <div class="col-md-9">
			                            <input type="text" class="form-control" name="article" value="<?=$this->data->re_post('article')?>" placeholder="Артикул" required>
			                        </div>
			                    </div>
							<?php } ?>
							<div class="form-group">
		                        <label class="col-md-3 control-label">Фото</label>
		                        <div class="col-md-9">
		                            <input type="file" name="photo[]" accept="image/jpg,image/jpeg,image/png" multiple id="add-images" onchange="imagesPreview(this, '.gallery')">
		                            <div class="gallery"></div>
		                        </div>
		                    </div>
							<?php if($_SESSION['option']->useGroups) {
								$this->load->smodel('shop_model');
								if($groups = $this->shop_model->getGroups(-1))
								{
									echo ('<div class="form-group">');
									if($_SESSION['option']->ProductMultiGroup)
			                        	echo ('<label class="col-md-3 control-label">Оберіть групу <br><br> <input type="search" id="search" class="form-control" placeholder="Пошук групи" /></label>');
			                        else
			                        	echo ('<label class="col-md-3 control-label">Оберіть групу</label>');
			                        echo ('<div class="col-md-9">');
									if($_SESSION['option']->ProductMultiGroup)
									{
										$_SESSION['alias']->js_load[] = 'assets/jstree/jstree.min.js';
										$_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/init-jstree.js';
										echo '<link rel="stylesheet" href="'.SITE_URL.'assets/jstree/themes/default/style.min.css" />';
										echo '<input type="hidden" name="product_groups" id="selected" value="" />';
										$product_groups = array();
										require_once '_groupsTree.php';
									}
									else
									{
										$list = array();
										$emptyChildsList = array();
										foreach ($groups as $g) {
											$list[$g->id] = $g;
											$list[$g->id]->child = array();
											if(isset($emptyChildsList[$g->id])) {
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

										echo('<select name="group" class="form-control">');
										echo ('<option value="0">Немає</option>');
										if(!empty($list))
										{
											function showList($all, $list, $parent = 0, $level = 0)
											{
												$prefix = '';
												for ($i=0; $i < $level; $i++) { 
													$prefix .= '- ';
												}
												foreach ($list as $g) if($g->parent == $parent) {
													if(empty($g->child)){
														$selected = '';
														if(isset($_GET['group']) && $_GET['group'] == $g->id) $selected = 'selected';
														if(isset($_SESSION['_POST']['group']) && $_SESSION['_POST']['group'] == $g->id) $selected = 'selected';
														echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
													} else {
														echo('<optgroup label="'.$prefix.$g->name.'">');
														$l = $level + 1;
														$childs = array();
														foreach ($g->child as $c) {
															$childs[] = $all[$c];
														}
														showList ($all, $childs, $g->id, $l);
														echo('</optgroup>');
													}
												}
												return true;
											}
											showList($list, $list);
										}
										echo('</select>');
									}
									echo "</div></div>";
								} else { ?>
									<div class="note note-info">
										<h4>Увага! В налаштуваннях адреси не створено жодної групи!</h4>
										<p>
										    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати групу</a>
		                                </p>
									</div>
								<?php }
							}
							if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
								<div class="form-group">
			                        <label class="col-md-3 control-label">Назва <?=$lang?></label>
			                        <div class="col-md-9">
			                            <input type="text" class="form-control" name="name_<?=$lang?>" value="<?=$this->data->re_post('name_'.$lang)?>" placeholder="Назва <?=$lang?>" required>
			                        </div>
			                    </div>
							<?php } else { ?>
								<div class="form-group">
			                        <label class="col-md-3 control-label">Назва</label>
			                        <div class="col-md-9">
			                            <input type="text" class="form-control" name="name" value="<?=$this->data->re_post('name')?>" placeholder="Назва" required>
			                        </div>
			                    </div>
							<?php } ?>
							<div class="form-group">
		                        <label class="col-md-3 control-label">Ціна</label>
		                        <div class="col-md-9">
		                        	<?php if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']))
						            {
						            	echo '<div class="row"><div class="col-md-7"><input type="number" name="price" value="'.$this->data->re_post('price').'" placeholder="99.99" min="0" step="0.01" required class="form-control "></div>';
						            	echo '<div class="col-md-5"><select name="currency" class="form-control col-md-5">';
						            	foreach ($_SESSION['currency'] as $currency => $value) {
						            		if($currency == $this->data->re_post('currency'))
							            		echo '<option value="'.$currency.'" selected>'.$currency.' (Поточний курс 1 '.$currency.' = '.$value.' у.о. продажу)</option>';
							            	else
							            		echo '<option value="'.$currency.'">'.$currency.' (Поточний курс 1 '.$currency.' = '.$value.' у.о. продажу)</option>';
						            	}
						            	echo '</select></div></div>';
						            }
						            else { ?>
										<div class="input-group">
				                            <input type="number" class="form-control" name="price" value="<?=$this->data->re_post('price')?>" placeholder="99.99" min="0" step="0.01" required>
				                            <span class="input-group-addon">y.o.</span>
				                        </div>
								    <?php } ?>
		                        </div>
		                    </div>
		                    <?php if($_SESSION['option']->useAvailability) { ?>
	                    		<div class="form-group">
			                        <label class="col-md-3 control-label">Наявність (од.)</label>
			                        <div class="col-md-9">
			                            <input type="number" min="0" class="form-control" name="availability" value="<?=$this->data->re_post('availability', 0)?>">
			                        </div>
			                    </div>
							<?php }
							if ($options = $this->shop_model->getOptionsToGroup(0, false)) {
								$init_select2 = true;
								foreach ($options as $option) {
									if($option->toCart)
										continue;
									if($option->type_name == 'select') { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<select name="option-<?=$option->id?>" class="form-control select2">
													<?php if(!$this->data->re_post('option-'.$option->id))
													echo "<option disabled selected>Оберіть потрібне</option>";
													foreach ($option->values as $value) {
														$selected = ($this->data->re_post('option-'.$option->id) == $value->id) ? 'selected' : '';
														echo "<option value={$value->id} {$selected}>{$value->name}</option>";
													} ?>
												</select>
											</div>
										</div>
										<?php if($init_select2) {
											$init_select2 = false;
											echo '<link rel="stylesheet" href="'.SITE_URL.'assets/select2/select2.min.css" />';
											$_SESSION['alias']->js_load[] = 'assets/select2/select2.min.js';
											$_SESSION['alias']->js_init[] = "$('.select2').select2();";
										}
									}
									else if($option->type_name == 'checkbox-select2') { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<select name="option-<?=$option->id?>[]" class="form-control select2" multiple="multiple">
													<?php if(!$this->data->re_post('option-'.$option->id))
													echo "<option disabled selected>Оберіть потрібне</option>";
													foreach ($option->values as $value) {
														$selected = (isset($_SESSION['_POST']['option-'.$option->id]) && in_array($value->id, $_SESSION['_POST']['option-'.$option->id])) ? 'selected' : '';
														echo "<option value={$value->id} {$selected}>{$value->name}</option>";
													} ?>
												</select>
											</div>
										</div>
										<?php if($init_select2) {
											$init_select2 = false;
											echo '<link rel="stylesheet" href="'.SITE_URL.'assets/select2/select2.min.css" />';
											$_SESSION['alias']->js_load[] = 'assets/select2/select2.min.js';
											$_SESSION['alias']->js_init[] = "$('.select2').select2();";
										}
									}
									else if($option->type_name == 'checkbox') { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<?php foreach ($option->values as $value) {
													$checked = (isset($_SESSION['_POST']['option-'.$option->id]) && in_array($value->id, $_SESSION['_POST']['option-'.$option->id])) ? 'checked' : '';
													echo('<input type="checkbox" name="option-'.$option->id.'[]" value="'.$value->id.'" id="option-'.$value->id.'" '.$checked.'> <label for="option-'.$value->id.'">'.$value->name.'</label> ');
												} ?>
											</div>
										</div>
									<?php }
									else if($option->type_name == 'radio') { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<?php foreach ($option->values as $value) {
													echo('<input type="radio" name="option-'.$option->id.'" value="'.$value->id.'" id="option-'.$value->id.'"> <label for="option-'.$value->id.'">'.$value->name.'</label> ');
												} ?>
											</div>
										</div>
									<?php }
									else if($option->type_name == 'text') { 
										if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $lang) { ?>
											<div class="form-group">
												<label class="col-md-3 control-label"><?=$option->name?> <strong><?=$lang?></strong></label>
												<div class="col-md-9">
													<input type="text" name="option-<?=$option->id?>-<?=$lang?>" class="form-control">
												</div>
											</div>
									<?php } } else { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<textarea name="option-<?=$option->id?>" class="form-control"></textarea>
											</div>
										</div>
									<?php } }
									else if($option->type_name == 'textarea') { 
										if($_SESSION['language']) { foreach ($_SESSION['all_languages'] as $lang) { ?>
											<div class="form-group">
												<label class="col-md-3 control-label"><?=$option->name?> <strong><?=$lang?></strong></label>
												<div class="col-md-9">
													<textarea name="option-<?=$option->id?>-<?=$lang?>" class="form-control"></textarea>
												</div>
											</div>
									<?php } } else { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<textarea name="option-<?=$option->id?>" class="form-control"></textarea>
											</div>
										</div>
									<?php } }
									else if($option->type_name != 'text') { ?>
										<div class="form-group">
											<label class="col-md-3 control-label"><?=$option->name?></label>
											<div class="col-md-9">
												<input type="<?=$option->type_name?>" name="option-<?=$option->id?>" class="form-control">
											</div>
										</div>
									<?php }
								}
							} ?>
							<div class="form-group">
		                        <label class="col-md-3 control-label"></label>
		                        <div class="col-md-9">
		                            <button type="submit" class="btn btn-sm btn-success">Додати</button>
		                        </div>
		                    </div>
	                    </table>
	                </div>
	            </form>
            </div>
        </div>
    </div>
</div>

<style>
	.gallery { padding: 5px }
	.gallery img { height: 120px; width: auto; padding: 5px }
	@media screen and (max-width: 576px) {
		.gallery img { height: 80px }
	}
</style>
<script type="text/javascript">
    var imagesPreview = function(input, placeToInsertImagePreview) {
        $(placeToInsertImagePreview).empty();
        if (input.files) {
            var filesAmount = input.files.length;
            for (i = 0; i < filesAmount; i++) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    $($.parseHTML('<img>')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                }
                reader.readAsDataURL(input.files[i]);
            }
        }
    };
	function setChilds (parent) {
		if($('#group-'+parent).prop('checked')){
			$('.parent-'+parent).prop('checked', true);
		} else {
			$('.parent-'+parent).prop('checked', false);
		}
	}
</script>