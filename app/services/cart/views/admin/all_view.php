<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
            </div>
			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                            	<th>ID</th>
                            	<th></th>
                            	<th>Покупець</th>
                            	<th>Контактний номер</th>
								<th>Статус</th>
								<th>Загальна сума</th>
								<th>Дата заявки</th>
								<th>Дата обробки</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php if(!empty($carts)){ foreach($carts as $cart){ 
							$color = 'default';
							switch ($cart->status) {
								case 1:
								case 4:
									$color = 'warning';
									break;
								case 2:
									$color = 'success';
									break;
								case 3:
									$color = 'primary';
									break; 
								case 5:
									$color = 'danger';
									break;
							}
							?>
						<tr>
							<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$cart->id?>"><?=$cart->id?></a></td>
							<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$cart->id?>" class="btn btn-<?=$color?> btn-xs">Детальніше</a></td>
							<td><?= $cart->user_name?></td>
							<td><?= $cart->user_phone .' '. $cart->user_phone2 ?></td>
							<td><?= $cart->status_name?></td>
							<td>$<?= $cart->total?></td>
							<td><?= date('d.m.Y H:i', $cart->date_add)?></td>
							<td><?= $cart->date_edit > 0 ? date('d.m.Y H:i', $cart->date_edit) : '' ?></td>
						</tr>
						<?php } } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/jquery.dataTables.js';  
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colReorder.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colVis.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.responsive.js'; 
  $_SESSION['alias']->js_load[] = 'js/admin/table-list.js';
  $_SESSION['alias']->js_init[] = 'TableManageCombine.init();'; 
?>
<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />