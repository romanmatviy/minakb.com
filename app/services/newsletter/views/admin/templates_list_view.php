<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати шаблон</a>
                	<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/history" class="btn btn-success btn-xs"><i class="fa fa-ravelry"></i> Історія</a>
                </div>
                <h4 class="panel-title">Шаблони розсилки</h4>
            </div>
			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                        	<tr>
								<th></th>
								<th>Назва</th>
								<th>Від</th>
								<th>Остання розсилка</th>
								<th>Редаговано</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php if(!empty($templates)){ foreach($templates as $template){ ?>
								<tr>
									<td><?=$template->id?></td>
									<td><a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$template->id?>"><?=$template->name?></a></td>
									<td><?=$template->from?></td>
									<td><?=($template->last_do) ? date('d.m.Y H:i', $template->last_do) : 'Відсутня'?></td>
									<td><?=date('d.m.Y H:i', $template->date_edit)?></td>
									<td><a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$template->id?>">Перегляд & Розсилка</a></td>
									<td><a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/edit/<?=$template->id?>">Редагувати</a></td>
								</tr>
							<?php } } ?>
							<tr>
								<td class="text-center" colspan="7"><a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати шаблон</a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>