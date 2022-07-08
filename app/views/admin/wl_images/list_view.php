<div class="row">
	<div class="col-md-12">
	    <div class="panel panel-inverse">
	        <div class="panel-heading">
	        	<div class="panel-heading-btn">
	            	<a href="<?=SITE_URL?>admin/wl_images/<?=$alias->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати зміну розміру</a>
	            </div>
	            <h4 class="panel-title">Розміри зображень</h4>
	        </div>
	        <div class="panel-body">
		        <form action="<?=SITE_URL?>admin/wl_images/save" method="POST">
		        	<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>Назва</th>
								<th>Префікс</th>
								<th>Тип*</th>
								<th>Ширина</th>
								<th>Висота</th>
								<th>Стан</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						    <?php 
						    $wl_images_sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $alias->id, 'alias');
						    if($wl_images_sizes) 
						    	foreach ($wl_images_sizes as $pic) { ?>
								<tr>
									<td><?=$pic->name?></td>
									<td><?=$pic->prefix?></td>
									<td><?=(in_array($pic->type, array(1, 11, 12))) ? 'resize' : 'preview'?></td>
									<td><?=$pic->width?></td>
									<td><?=$pic->height?></td>
									<td><?=$pic->active?></td>
									<td><a href="<?=SITE_URL?>admin/wl_images/<?=$alias->alias?>/<?=$pic->id?>" class="btn btn-success btn-xs">Редагувати</a></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</form>
				*Тип <b>resize</b> - створення мініатюри зі збереженням пропорцій по довшому полю висота/ширина зображення<br>
				Тип <b>preview</b> - створення мініатюри строго заданого розміру згідно висоти/ширини з максимальним збереженням інформації
	        </div>
	    </div>
	</div>
</div>