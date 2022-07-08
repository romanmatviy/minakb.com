<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/edit/'.$template->id?>" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Редагувати шаблон</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-list"></i> До всіх шаблонів</a>
            	</div>
                <h4 class="panel-title">Попередній перегляд <strong><?=$template->name?></strong></h4>
            </div>
            <div class="panel-body">
            	<?php $canInit = true;
            	$logs = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_log', ['template' => $template->id]);
            		if($logs)
            			foreach ($logs as $log) {
            				if($log->emails_count != $log->emails_sent) { $canInit = false; ?>
            					<div class="alert alert-warning">
							        <i class="fa fa-mail fa-2x pull-left"></i>
							        <h4>Активна розсилка</h4>
							        <p>Виконано <?=$log->emails_sent .' з '.$log->emails_count?>. <?=$log->emails_sent == 0 ? 'Поставлено в чергу' : 'Остання відправка'?> <?=date('d.m.Y H:i', $log->date)?>
							        	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/force_sendmail?id=<?=$template->id?>" class="btn btn-warning btn-xs">Форсувати розсилку</a>
							        	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/cancel_sendmail?id=<?=$log->id?>" class="btn btn-danger btn-xs" onclick="return confirm('Дісно скасувати розсилку?')">Скасувати розсилку</a>
							        </p>
							    </div>
            				<?php }
            			}
            	 ?>
            	<div class="row">
	            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/sendMail" method="POST" class="form-horizontal col-md-8" onsubmit="showLoading()">
	            		<input type="hidden" name="id" value="<?=$template->id?>">

	            		<h3><?=$template->id?>. <?=html_entity_decode($template->name)?></h3>
						<p>Лист відправлятиметься від імені <strong><?=html_entity_decode($template->from)?> < <?=SITE_EMAIL?> ></strong></p>
						<p>Тема: <strong><?=html_entity_decode($template->theme)?></strong></p>
						<p>Дата додачі: <strong><?= date("d.m.Y H:i", $template->date_add) ?></strong></p>
						<p>Дата редагування: <strong><?= date("d.m.Y H:i", $template->date_edit) ?></strong></p>
						<p>Дата останньої розсилки: <strong><?= $template->last_do ? date("d.m.Y H:i", $template->last_do) : "" ?></strong></p>
						<?php if($canInit) { ?>
							<div class="row">
								<div class="col-md-8">
									<input type="password" name="password" placeholder="Пароль підтвердження" class="form-control" required>
									<label><input type="checkbox" name="all_users" value="1"> Не враховувати налаштування клієнта</label>
								</div>
								<div class="col-md-4">
									<button type="submit" class="btn btn-warning"><i class="fa fa-envelope-o"></i> Зробити розсилку!</button>
								</div>
							</div>
						<?php } ?>
					</form>
					<div class="col-md-4">
						<h4>Отримувачі: <?= ($mails) ? count($mails) : 0 ?> <?php if($mails) { ?><small><a target="_blank" href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$template->id?>/receivers">Дивитися</a></small><?php } ?></h4>
						<?php $list = [];
						if($userTypes = $this->db->getAllDataByFieldInArray('wl_user_types', ['id' => $template->to_user_types]))
							foreach ($userTypes as $type)
								$list[] = $type->title;
							echo implode(', ', $list);
						 ?>
						<hr>
						<h4>Прикріплені файли:</h4>
						<?php if($template->files)
							foreach ($template->files as $file) {
								echo '<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/files/'.$file->id.'">'.$file->name.'</a><br>';
							}
							else
								echo "Відсутні";
						?>
						<hr>
						<h4>Тестова перевірка шаблону</h4>
						<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/sendTestMail" method="POST" onsubmit="showLoading()">
							<input type="hidden" name="id" value="<?= $template->id ?>">
							<p>Кому</p>
							<select name="receiver" class="form-control">
							<?php if(!in_array(1, $template->to_user_types))
								$mails = $this->db->getAllDataByFieldInArray('wl_users', ['type' => '<3']);
							foreach ($mails as $m) if($m->type < 3) {?>
								<option value="<?= $m->id?>"><?= $m->email.' ('.$m->name?>)</option>
							<?php } ?>
							</select>
							<p><input type="submit" class="btn btn-success m-t-5" value="Надіслати"></p>
						</form>
					</div>
             	</div>
             </div>
         </div>
	</div>
</div>
<div class="previous"><?=html_entity_decode($template->text)?></div>

<?php if($logs) { ?>
	<div class="row">
	    <div class="col-md-12">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <h4 class="panel-title">Архів розсилок по шаблону <strong><?=$template->name?></strong></h4>
	            </div>
	            <div class="panel-body">
	                <div class="table-responsive">
	                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
	                        <thead>
	                        	<tr>
									<th>Остання розсилка</th>
									<th>Статус</th>
									<th>Отримувачі</th>
									<th>Від</th>
								</tr>
							</thead>
							<tbody>
								<?php $userTypes = $this->db->getAllData('wl_user_types');
								$userTypesList = [];
								foreach ($userTypes as $type) {
									$userTypesList[$type->id] = $type->title;
								}
								foreach($logs as $log){ ?>
									<tr>
										<td><?=date('d.m.Y H:i', $log->date)?></td>
										<td><?=$log->emails_count != $log->emails_sent ? 'Активна' : 'Виконано'?></td>
										<td>Всіх емейлів: <?=$log->emails_count?>
											<br>
											<?php $list = [];
											foreach (unserialize($log->to_user_types) as $type)
												$list[] = $userTypesList[$type];
											echo implode(', ', $list);
										 ?>
										</td>
										<td><?=$log->from?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
	        </div>
	    </div>
	</div>
<?php } ?>

<style type="text/css">
	.previous {
		display: block;
	    padding: 9.5px;
	    margin: 0 0 10px;
	    font-size: 13px;
	    line-height: 1.42857143;
	    color: #333;
	    background-color: #f5f5f5;
	    border: 1px solid #ccc;
	    border-radius: 4px;
        font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
	}
</style>
<script type="text/javascript">
function showLoading() {
	$("#saveing").show();
	$("input[type=submit]").attr('disabled', true);
}
</script>