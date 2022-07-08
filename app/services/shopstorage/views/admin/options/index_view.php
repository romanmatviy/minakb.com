<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-warning btn-xs"><i class="fa fa-refresh"></i> Оновити склад</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/optionsImport" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування структури файлу</a>
            	</div>
                <h4 class="panel-title">Поточні налаштування</h4>
            </div>
            <?php
            if(isset($_SESSION['notify'])){ 
	        	require APP_PATH.'views/admin/notify_view.php';
	        }
	        ?>

            <div class="panel-body">
                <form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/options_save" method="POST" class="form-horizontal">
	                <table class="table table-striped table-bordered">
	                    <tbody>
							<tr>
								<td>Адреса посилання</td>
								<td><?=$_SESSION['alias']->alias?></td>
							</tr>
							<tr>
								<td>Назва складу</td>
								<td><input type="text" name="name" value="<?=$storage->name?>" class="form-control"></td>
							</tr>
							<tr>
								<td>Термін</td>
								<td><input type="text" name="time" value="<?=$storage->time?>" class="form-control"></td>
							</tr>
							<tr>
								<th>Валюта у прайсі</th>
								<td>
									<select name="currency" class="form-control">
										<option value="USD" <?=($storage->currency == 'USD') ? 'selected' : ''?>>$ USD</option>
										<option value="EUR" <?=($storage->currency == 'EUR') ? 'selected' : ''?>>€ EUR</option>
										<option value="UAH" <?=($storage->currency == 'UAH') ? 'selected' : ''?>>грн UAH</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Статус складу</td>
								<td>
									<input type="hidden" name="active_old" value="<?=$storage->active?>">
									<select name="active" class="form-control" required>
										<option value="1" <?=($storage->active == 1) ? 'selected' : ''?>>1 Активний</option>
										<option value="0" <?=($storage->active == 0) ? 'selected' : ''?>>0 Відключено</option>
									</select>
								</td>
							</tr>
							<?php if(isset($options)) foreach ($options as $option) { ?>
								<tr>
									<td><?=$option->title?></td>
									<td>
										<?php
										if($option->type == 'bool') { 
											if($option->value == 1) echo('Так');
											else echo('Ні');
										} else { 
											echo($option->value);
										}
										?>
									</td>
								</tr>
							<?php } ?>
								<tr>
									<td colspan="2">
										<a href="<?=SITE_URL?>admin/wl_aliases/<?=$_SESSION['alias']->alias?>" target="_blank">Для зміни параметрів перейдіть <?=SITE_URL?>admin/wl_aliases/<?=$_SESSION['alias']->alias?></a>
									</td>
								</tr>
							<?php if($_SESSION['option']->markUpByUserTypes == 0) { ?>
								<tr>
									<td>Стандартна націнка товару відносно приходу (20%)</td>
									<td><input type="number" name="markup" value="<?=$storage->markup?>" min="0" class="form-control"></td>
								</tr>
							<?php } ?>
							<tr>
								<td>Склад додано</td>
								<td><?=date('d.m.Y H:i', $storage->date_add) .' '.$storage->user_name.' ('.$storage->user_add.')'?></td>
							</tr>
							<tr>
								<td></td>
								<td><button type="submit" class="btn btn-sm btn-success col-md-6">Зберегти</button></td>
							</tr>
						</tbody>
                	</table>
                </form>
			</div>
		</div>
	</div>


<?php if(!empty($_SESSION['option']->markUpByUserTypes)) { ?>
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Індивідуальна націнка товару відносно приходу</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/markup_save" method="POST" class="form-horizontal">
                    <table class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Група</th>
								<th>Націнка (Наприклад 20 %)</th>
                            </tr>
                        </thead>
                        <tbody>
							<?php 
							$groups = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
							foreach($groups as $group) if($group->id > 1) { ?>
								<tr>
									<td>
										<?=$group->title?> <?=(isset($_SESSION['option']->new_user_type) && $_SESSION['option']->new_user_type == $group->id)?'<u>(*по замовчуванню)</u>':''?>	
									</td>
									<td>
										<input type="number" name="markup-<?=$group->id?>" value="<?=(isset($storage->markup[$group->id]))?$storage->markup[$group->id] : 0?>" min="0" class="form-control">
									</td>
								</tr>
							<?php } ?>
							<tr>
								<td></td>
								<td><button type="submit" class="btn btn-sm btn-success col-md-6">Зберегти</button></td>
							</tr>
						</tbody>
					</table>
					<p><u>*по замовчуванню</u> - ціна для неавторизованих відвідувачів/покупців</p>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php } if($storage->active == 0) { ?>
		<div class="col-md-6">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h4 class="panel-title">Очистити всі позиції складу</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/trancate" method="POST" class="form-inline">
            		<p>На складі наявно <strong><?=$this->db->getCount($_SESSION['service']->table.'_products', $_SESSION['alias']->id, 'storage')?> позицій товарів</strong>. Увага! Дані відновленню не підлягають.</p>
                	<div class="form-group m-r-10">
						<input type="password" name="password" class="form-control" required placeholder="Пароль адміністратора" autocomplete="off">
					</div>
					<button type="submit" class="btn btn-sm btn-danger">Очистити склад</button>
				</form>
			</div>
		</div>
	</div>
<?php } ?>

</div>

<div class="row">
	<?php
	$this->db->select('s_shopstorage_updates as s', '*', $_SESSION['alias']->id, 'storage');
	$this->db->join('wl_users', 'name, email', '#s.manager');
	$this->db->order('date DESC');
	$this->db->limit(10);
	$history = $this->db->get('array');
	if($history)
	{
	?>
		<div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-warning btn-xs"><i class="fa fa-refresh"></i> Оновити склад</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/optionsImport" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування структури файлу</a>
            	</div>
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
		                			<?php } else 
		                			{
		                				echo "Автооновлення"; ?>
		                				<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/history/'.$update->id?>" class="btn btn-info btn-xs">Детально</a>
		                			<?php } ?>
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
                Виведено 10 останніх оновлень. Для повного архіву звертайтеся до розробників.
            </div>
        </div>
    <?php } ?>
</div>