<?php $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />
<!-- begin row -->
<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-6">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
            <div class="panel-heading">
            	<?php if($alias->id > 0) { ?>
            		<div class="panel-heading-btn">
	                	<a href="<?=SITE_URL?>admin/wl_ntkd/<?=$alias->alias?>/main" class="btn btn-info btn-xs"><i class="fa fa-edit"></i> Редагувати назву</a>
	                </div>
            	<?php } ?>
                <h4 class="panel-title"><?=($alias->id == 0) ? 'Додати' : 'Редагувати'?> головний адрес "<?=$alias->alias?>"</h4>
            </div>
            <div class="panel-body">
	            <form action="<?=SITE_URL?>admin/wl_aliases/save" method="POST" class="form-horizontal">
	            	<input type="hidden" name="id" value="<?=$alias->id?>">
					<input type="hidden" name="service" value="<?=$alias->service?>">

	                <table class="table table-striped table-bordered">
	                    <tbody>
							<tr>
								<td title="Обов'язкове поле">Адреса посилання*</td>
								<td><input type="text" name="alias" value="<?=$alias->alias?>" required class="form-control"></td>
							</tr>
							<tr>
								<td>Назва сторінки</td>
								<td><?php if($alias->id > 0) { ?>
										<input type="text" value="<?=$alias->name?>" disabled class="form-control">
									<?php } else { ?>
										<input type="text" name="name" value="<?=$this->data->re_post('name', $alias->name)?>" class="form-control">
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td title="Обов'язкове поле">ADMIN іконка</td>
								<td><input type="text" name="admin_ico" value="<?=$this->data->re_post('admin_ico', $alias->admin_ico)?>" class="form-control"></td>
							</tr>
							<?php if($alias->id > 0) { ?>
								<tr>
									<td>ADMIN вага сортування</td>
									<td><input type="number" name="admin_order" value="<?=$this->data->re_post('admin_order', $alias->admin_order)?>" class="form-control"></td>
								</tr>
							<?php } if(isset($options)) foreach ($options as $option) if($option->type) { ?>
								<tr>
									<td><?=$option->title?></td>
									<td>
										<?php if($option->type == 'bool') { ?>
											<input name="<?=$option->name?>" type="checkbox" data-render="switchery" <?=($option->value == 1) ? 'checked' : ''?> value="1" />
										<?php } else { ?>
											<input type="<?=$option->type?>" name="<?=$option->name?>" value="<?=$option->value?>" <?=$option->type == 'number'?'step="0.01"':''?> class="form-control">
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
							<tr>
								<td></td>
								<td><button type="submit" class="btn btn-sm btn-success col-md-6"><?=($alias->id == 0)?'Додати':'Зберегти'?></button></td>
							</tr>
						</tbody>
                	</table>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>

    <?php $cooperation = $this->db->getQuery("SELECT c.*, a1.alias as alias1_name, a2.alias as alias2_name FROM wl_aliases_cooperation as c LEFT JOIN wl_aliases as a1 ON c.alias1 = a1.id LEFT JOIN wl_aliases as a2 ON c.alias2 = a2.id WHERE c.alias1 = {$alias->id} OR c.alias2 = {$alias->id}", 'array');
    
    if($alias->id > 1 && $alias->service > 0){
			$path = APP_PATH.'services'.DIRSEP.$alias->service_name.DIRSEP.'views/admin/_wl_alias_option_view.php';
			if(file_exists($path)){
			    require_once($path);
			}
    	} ?>

    <?php if($alias->id > 1) { ?>
    	<div class="col-md-6">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	            	<div class="panel-heading-btn">
	                	<a href="<?=SITE_URL?>admin/wl_aliases/add_admin_option/<?=$alias->alias?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати налаштування</a>
	                </div>
	                <h4 class="panel-title">Адреси співпраці</h4>
	            </div>
	            <div class="panel-body">
	            	<?php if($cooperation) {
					?>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Адреса 1</th>
									<th>Адреса 2</th>
									<th>Тип співпраці</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach ($cooperation as $row) {
								?>
									<tr>
										<td><a href="<?=SITE_URL.'admin/wl_aliases/'.$row->alias1_name?>"><?=$row->alias1.' '.$row->alias1_name?></a></td>
										<td><a href="<?=SITE_URL.'admin/wl_aliases/'.$row->alias2_name?>"><?=$row->alias2.' '.$row->alias2_name?></a></td>
										<td><?=$row->type?></td>
										<td><a href="<?=SITE_URL?>admin/wl_aliases/deleteCooperation?id=<?=$row->id?>">Скасувати</a></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } else { ?>
						<div class="note note-info">
							<h4>Налаштування відсутні!</h4>
						</div>
					<?php } ?>
	            </div>
	        </div>
	    </div>
	    
	<?php } ?>
</div>
<!-- end row -->

<?php if($alias->id > 0) { ?>

	<div class="row">
		<div class="col-md-6">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	            	<div class="panel-heading-btn">
	                	<a href="<?=SITE_URL?>admin/wl_aliases/add_admin_option/<?=$alias->alias?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати налаштування</a>
	                </div>
	                <h4 class="panel-title">Додаткові налаштування у панелі керування</h4>
	            </div>
	            <div class="panel-body">
	            	<?php
	            	$optionsAdditionall = $this->db->getAllDataByFieldInArray('wl_options', array('alias' => -$alias->id));
	            	if($optionsAdditionall){
	            		$_SESSION['alias']->js_load[] = 'assets/white-lion/wl_aliases-delete-option.js'; 
	            	?>
						<table class="table table-striped table-bordered">
							<tbody>
								<?php 
								foreach ($optionsAdditionall as $opt) {
									if($opt->name == 'sub-menu') { 
										$menu = unserialize($opt->value);
								?>
									<tr id="option-<?=$opt->id?>">
										<th>
											Підменю "<?=$menu['name']?>" <br>
											<a href="#modal-delete-option" class="btn btn-xs btn-danger" data-toggle="modal" data-id="<?=$opt->id?>" data-title="<?=$menu['name']?>">Видалити параметр</a>
										</th>
										<td>
											<?=SITE_URL.'admin/'.$alias->alias.'/'.'<input type="text" id="sub-menu-alias-'.$opt->id.'" value="'. $menu['alias'] .'" onchange="saveSubMenu('.$opt->id.')" placeHolder="alias">'?>
											<br>name: <?='<input type="text" id="sub-menu-name-'.$opt->id.'" value="'. $menu['name'] .'" onchange="saveSubMenu('.$opt->id.')" placeHolder="name">'?>
										</td>
									</tr>
								<?php } else { ?>
									<tr id="option-<?=$opt->id?>">
										<th>
											<?=$opt->name?> <br>
											<a href="#modal-delete-option" class="btn btn-xs btn-danger" data-toggle="modal" data-id="<?=$opt->id?>" data-title="<?=$opt->name?>">Видалити параметр</a>
										</th>
										<td><?='<input type="text" id="opt-'.$opt->id.'" value="'. $opt->value .'" onchange="saveOption('.$opt->id.')">'?></td>
									</tr>
								<?php } } ?>
							</tbody>
						</table>

						<div class="modal fade" id="modal-delete-option" aria-hidden="true" style="display: none;">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
										<h4 class="modal-title">Видалити параметр</h4>
									</div>
									<div class="modal-body">
										Дані відновити неможливо.
										<input type="hidden" id="option-id-to-delete" value="0">
									</div>
									<div class="modal-footer">
										<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Скасувати</a>
										<a href="javascript:;" class="btn btn-sm btn-danger" onclick="deleteOption()">Видалити</a>
									</div>
								</div>
							</div>
						</div>
					<?php } else { ?>
						<div class="note note-info">
							<h4>Налаштування відсутні! <a href="<?=SITE_URL?>admin/wl_aliases/add_admin_option/<?=$alias->alias?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати налаштування</a></h4>
						</div>
					<?php } ?>
	            </div>
	        </div>
	    </div>

	    <div class="col-md-6">
	        <!-- begin panel -->
	        <div class="panel panel-inverse" data-sortable-id="form-stuff-2">
	            <div class="panel-heading">
	                <h4 class="panel-title">Видалити головний адрес "<?=$alias->alias?>"</h4>
	            </div>
	            <div class="panel-body">
		            <form action="<?=SITE_URL?>admin/wl_aliases/delete" method="POST">
		            	<input type="hidden" name="id" value="<?=$alias->id?>">
		            	<div class="note note-danger">
		            		<h4>Увага!</h4>
		            		<p>Буде знищено всю інформацію що пов'язана з даною головною адресою <br>(всі підсторінки <b><i><?=$alias->alias?>/*</i></b>)</p>
		            	</div>
		            	<div class="form-group">
	                        <label class="col-md-4 control-label">Пароль адміністратора для підтвердження</label>
	                        <div class="col-md-8">
	                            <input type="password" name="admin-password" class="form-control" required />
	                            <br>
	                        </div>
	                    </div>
	                    
	                    <div class="col-md-5"></div>
		            	<button type="submit" class="btn btn-sm btn-danger col-md-3">Видалити</button>
		            </form>
		        </div>
		    </div>
		</div>
	</div>

	<script type="text/javascript">
		function saveSubMenu (id) {
			$.ajax({
				url: "<?=SITE_URL?>admin/wl_aliases/saveSubMenu",
				type: 'POST',
				data: {
					id: id,
					alias :  $('#sub-menu-alias-'+id).val(),
					name :  $('#sub-menu-name-'+id).val(),
					json : true
				},
				success: function(res){
					if(res['result'] == false){
						$.gritter.add({title:"Помилка!",text:res['error']});
					} else {
						$.gritter.add({title:"Дані успішно збережено!"});
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
		function saveOption (id) {
			$.ajax({
				url: "<?=SITE_URL?>admin/wl_services/saveOption",
				type: 'POST',
				data: {
					id: id,
					value :  $('#opt-'+id).val(),
					json : true
				},
				success: function(res){
					if(res['result'] == false){
						$.gritter.add({title:"Помилка!",text:res['error']});
					} else {
						$.gritter.add({title:"Дані успішно збережено!"});
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
	</script>

<?php } ?>