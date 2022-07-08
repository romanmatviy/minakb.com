<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/optionsimport" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування структури файлу імпорту</a>
            	</div>
                <h4 class="panel-title">Оновити склад через файл імпорту <strong>xls, xlsx, csv</strong></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/view_before_import" enctype="multipart/form-data" method="POST" class="form-horizontal">
                		<input type="hidden" name="checkPrice" value="-1">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <tbody>
                        	<tr>
								<th>Вхідний прайс (xls, xlsx, csv)</th>
								<td>
									<?php if(isset($_GET['file'])) { echo $this->data->get('file'); ?>
										<input type="hidden" name="file" value="<?=$this->data->get('file')?>">
										<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-xs btn-info">Скасувати</a>
									<?php } else { ?>
										<input type="file" name="price" required="required" class="form-control">
									<?php } ?>
								</td>
							</tr>
							<?php /*
							<tr>
								<th>Вхідна ціна</th>
								<td>
									<select name="checkPrice" class="form-control">
										<option value="-1">Собівартість</option>
										<?php if(!empty($_SESSION['option']->markUpByUserTypes)){ 
											foreach($groups as $group) if($group->id > 1) { ?>
												<option value="<?=$group->id?>"><?=$group->title?></option>
										<?php } } else { ?>
											<option value="1">Продажна (з націнкою)</option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<?php */
			            	$cooperation = $this->db->getQuery("SELECT c.*, a1.alias as alias1_name FROM wl_aliases_cooperation as c LEFT JOIN wl_aliases as a1 ON c.alias1 = a1.id WHERE c.alias2 = {$_SESSION['alias']->id}", 'array');
							if($cooperation) {

									$storage = $this->db->getAllDataById($_SESSION['service']->table, $_SESSION['alias']->id);
								?>
							<tr>
								<th>Валюта у прайсі</th>
								<td><?php if($storage->currency == 'USD') echo '$ USD';
								 		elseif($storage->currency == 'EUR') echo '€ EUR';
								 		else echo 'грн UAH'; ?>
									<input type="hidden" name="currency" value="<?=$storage->currency?>">
								</td>
							</tr>
							<?php if($storage->currency == 'EUR') { ?>
								<tr>
									<th>Курс валют 1€ = ?$</th>
									<td>
										<?php $currency_EUR = 1.07; 
										if($history && $history[0]->currency == 'EUR') $currency_EUR = $history[0]->price_for_1;
										?>
										<input type="number" name="currency_to_1" value="<?=round($currency_EUR, 2)?>" step="0.01" required="required" class="form-control">
									</td>
								</tr>
							<?php } elseif($storage->currency == 'UAH') { ?>
								<tr>
									<th>Курс валют 1$ = ? грн</th>
									<td>
										<?php $currency_USD = $this->load->function_in_alias('currency', '__get_Currency', 'USD'); ?>
										<input type="number" name="currency_to_1" value="<?=round($currency_USD, 2)?>" step="0.01" required="required" class="form-control">
									</td>
								</tr>
							<?php } ?>
							<tr>
								<th>Відсутні товари додати до магазину</th>
								<td>
									<label><input type="radio" checked="checked" name="insert" value="1"> Так (довше)</label>
									<label><input type="radio" name="insert" value="0"> Ні (швидше)</label>
								</td>
							</tr>
							<tr>
								<th>Наявні товари, що відсутні у прайсі видаляти зі складу</th>
								<td>
									<label><input type="radio" checked="checked" name="delete" value="1"> Так (довше)</label>
									<label><input type="radio" name="delete" value="0"> Ні (швидше)</label>
								</td>
							</tr>
							<?php if(count($cooperation) == 1) 
								{
									echo '<input type="hidden" name="shop" value="'.$cooperation[0]->alias1.'">';
								} else {
							?>
							<tr>
								<td>Магазин</td>
								<td>
									<select name="shop" class="form-control">
										<?php foreach($cooperation as $shop) { ?>
											<option value="<?=$shop->alias1?>"><?=$shop->alias1_name?></option>
										<?php } ?>
									</select>
									</td>
								</tr>
							<?php } } /* ?>
							<tr>
								<td colspan="2">
									<label><input type="radio" name="view" value="500" checked>Вивести перші 500 позицій (швидше)</label>
									<label><input type="radio" name="view" value="0">Вивести всі позиції (довше)</label>
								</td>
							</tr>
							*/ ?>
							<tr>
								<td></td>
								<td><button type="submit" class="btn btn-sm btn-success">Перевірити прайс</button></td>
							</tr>
						</tbody>
					</table>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<?php if(isset($_SESSION['import'])) { ?>
			<div class="alert alert-success fade in">
		        <span class="close" data-dismiss="alert">×</span>
		        <i class="fa fa-check fa-2x pull-left"></i>
		        <h4>Прайс успішно імпортовано!</h4>
		        <?=$_SESSION['import']?>
		    </div>
		<?php unset($_SESSION['import']); } if(isset($_SESSION['admin_options']['importInfo'])) { ?>
			<div class="alert alert-warning">
		        <i class="fa fa-info fa-2x pull-left"></i>
		        <h4>Інформація щодо імпорту</h4>
		        <?=$_SESSION['admin_options']['importInfo']?>
		    </div>
		<?php } ?>
	</div>
</div>

<div class="row">
	<?php if($history) {
	?>
		<div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Історія оновлення</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
	                <table class="table table-striped table-bordered nowrap" width="100%">
	                	<tr>
	                		<th>Дата</th>
	                		<th>Менеджер</th>
	                		<th>Валюта</th>
	                		<th>Курс</th>
	                		<th>Додано</th>
	                		<th>Оновлено</th>
	                		<th>Видалено</th>
	                		<th>Файл</th>
	                	</tr>
	                	<?php foreach ($history as $update) { ?>
		                	<tr>
		                		<td><?=date('d.m.Y H:i', $update->date)?></td>
		                		<td><?php if($update->manager > 0) { ?>
		                			<a href="<?=SITE_URL.'admin/wl_users/'.$update->email?>"><?=$update->manager.'. '.$update->name?></a> 
		                			<?php } else echo "Автооновлення "; ?>
		                			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/history/'.$update->id?>" class="btn btn-info btn-xs">Детально</a>
		                		</td>
		                		<td><?=$update->currency?></td>
		                		<td><?=$update->price_for_1?></td>
		                		<td><?=$update->inserted?></td>
		                		<td><?=$update->updated?></td>
		                		<td><?=$update->deleted?></td>
		                		<td><?=($update->manager > 0 && $update->id == $history[0]->id) ? '<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/view_last_import" class="btn btn-info btn-xs">'.$update->file.'</a>' : $update->file?></td>
		                	</tr>
	                	<?php } ?>
	                </table>
                </div>
                Виведено 20 останніх оновлень. Для повного архіву звертайтеся до розробників.
            </div>
        </div>
    <?php } ?>
</div>