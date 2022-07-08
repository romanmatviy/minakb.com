<div class="col-md-12">
    <div class="panel panel-inverse" data-sortable-id="profile-<?=$_SESSION['alias']->alias?>">
        <div class="panel-heading">
            <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered ">
	            <thead>
	                <tr>
						<th>Замовлення</th>
						<th>Статус</th>
						<th>Сума</th>
						<th>Дата</th>
					</tr>
	            </thead>
				<tbody>
					<?php if($orders) foreach($orders as $order) { ?>
					<tr>
						<td>
							<a class="btn-u" href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$order->id ?>">Замовлення <?= $order->id ?></a>
						</td>
						<td><?= $order->status_name ?></td>
						<td><?= $order->total ?> грн</td>
						<td><?= date('d.m.Y H:i', $order->date_add) ?></td>
					</tr>
					<?php } ?>
	            </tbody>
			</table>
			<?php
			$this->load->library('paginator');
			echo $this->paginator->get();
			?>
        </div>
    </div>
</div>