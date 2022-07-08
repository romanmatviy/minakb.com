<div class="row">
	<div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/promo/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати акцію</a>
                </div>
                <h4 class="panel-title">Поточні акції</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Id</th>
								<th>Статус</th>
								<th>Період дії</th>
								<th>Відсоток знижки</th>
								<th>Інформація</th>
								<th>Редаговано</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php if($promotions) foreach($promotions as $promo) { ?>
							<tr>
								<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/promo/<?=$promo->id?>" class="btn btn-info btn-xs" title="Редагувати"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <?=$promo->id?></a></td>
								<td><?=$promo->status == 1 && time() < $promo->to ? 'Активна' : $promo->status == 0 ? 'Відключено' : 'Протерміновано'?></td>
								<td><?=date('d.m.Y H:i', $promo->from).' - '.date('d.m.Y H:i', $promo->to)?></td>
								<td><?=$promo->percent?>%</td>
								<td><?=nl2br($promo->info)?></td>
								<td><?=date('d.m.Y H:i', $promo->date_edit)?></td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="6" class="text-center">
									<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/promo/add" class="btn btn-warning"><i class="fa fa-plus"></i> Додати акцію</a>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>