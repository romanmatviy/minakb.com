<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/bonus/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати бонус-код</a>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
            </div>
			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                            	<th>ID / Бонус-код</th>
                            	<th>Статус</th>
                            	<th>Знижка</th>
                            	<th>Залишок, термін</th>
								<th>Інформація</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php if(!empty($bonuses)){
							foreach($bonuses as $bonus){
							$color = 'default';
							switch ($bonus->status) {
								case 0:
									$color = 'warning';
									break;
								case 1:
									$color = 'success';
									break;
							}
							?>
						<tr class="<?=$color?>">
							<td>
								<?=$bonus->id?>
								<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/bonus/<?=$bonus->id?>" class="btn btn-<?=$color?> btn-xs"><?=$bonus->code?></a>
							</td>
							<td><?php switch ($bonus->status) {
								case 0:
									echo "Відключено";
									break;
								case 1:
									echo "Активний";
									break;
								default:
									echo "Архів";
									break;
							} ?></td>
							<td>
								<?php if($bonus->discount_type == 1)
									echo "Фіксовано <strong>{$bonus->discount} у.о.</strong> ";
								else
									echo "<strong>{$bonus->discount}%</strong> від суми у корзині ";
								if($bonus->discount_max > 0)
									echo "<br>Не більше ніж <strong>{$bonus->discount_max} у.о.</strong> ";
								if($bonus->order_min > 0)
									echo "<br>Мінімальна сума замовлення <strong>{$bonus->order_min} у.о.</strong> ";
								?>
							</td>
							<td>
								<?php if($bonus->from > 0)
									echo "Від <strong>".date('d.m.Y H:i', $bonus->from)."</strong>";
								if($bonus->to > 0)
									echo "<br>До <strong>".date('d.m.Y H:i', $bonus->to)."</strong> ";
								?>
							</td>
							<td><?=(!empty($bonus->info))?$bonus->info.'<br>':''?>
								<strong><?= $bonus->manager.'. '.$bonus->manager_name?></strong> <?= date('d.m.Y H:i', $bonus->date) ?></td>
						</tr>
						<?php } } else { ?>
							<tr>
								<td colspan="5">Бонус-коди відсутні
									<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/bonus/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати</a>
								</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
				<?php $this->load->library('paginator');
                echo $this->paginator->get(); ?>
			</div>
		</div>
	</div>
</div>