<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати питання</a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх питань</a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-success btn-xs">До керування групами</a>
					<?php } ?>
					
					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?>/edit" class="btn btn-info btn-xs"><i class="fa fa-id-card-o" aria-hidden="true"></i> Вміст сторінки</a>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Питання по групах</h4>
            </div>

			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Назва</th>
								<th>Адреса</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php foreach($groups as $group) { ?>
							<tr <?=($group->active == 1)?'':'class="danger" title="відключено"'?>>
								<td>
									<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$group->alias?>"><?=$group->name?></a>
									<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups/'.$group->alias?>" class="btn btn-warning btn-xs">Редагувати групу</a>
								</td>
								<td><?=$_SESSION['alias']->alias.'/'.$group->alias?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>