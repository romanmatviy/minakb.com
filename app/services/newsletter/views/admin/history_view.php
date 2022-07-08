<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title">Архів розсилок</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                        	<tr>
								<th>Шаблон</th>
								<th>Остання розсилка</th>
								<th>Статус</th>
								<th>Отримувачі</th>
								<th>Від</th>
							</tr>
						</thead>
						<tbody>
							<?php if($logs) {
							$userTypes = $this->db->getAllData('wl_user_types');
							$userTypesList = [];
							foreach ($userTypes as $type) {
								$userTypesList[$type->id] = $type->title;
							}
							foreach($logs as $log){ ?>
								<tr>
									<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$log->template?>"><?=$log->name?></a></td>
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
							<?php } } ?>
						</tbody>
					</table>
				</div>
			</div>
        </div>
    </div>
</div>