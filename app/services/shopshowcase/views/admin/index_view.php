<?php require_once '__search_subview.php'; ?>

<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(!empty($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>

					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?>/edit" class="btn btn-success btn-xs">SEO головна</a> 
					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?>/seo_robot" class="btn btn-success btn-xs">SEO робот</a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Групи/підгрупи</h4>
            </div>
            <?php if(isset($group)) { ?>
                <div class="panel-heading">
            		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><?=$group->alias_name?></a> 
					<?php if(!empty($group->parents)) {
						$link = SITE_URL.'admin/'.$_SESSION['alias']->alias;
						foreach ($group->parents as $parent) { 
							$link .= '/'.$parent->alias;
							echo '<a href="'.$link.'" class="btn btn-info btn-xs">'.$parent->name.'</a> ';
						}
					} ?>
					<span class="btn btn-warning btn-xs"><?=$_SESSION['alias']->name?></span> 
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups/edit-'.$group->id.'-'.$group->alias?>" class="btn btn-xs btn-primary right"><i class="fa fa-edit"></i> Редагувати групу</a>
	            </div>
	        <?php } ?>
			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Назва</th>
								<th>Адреса</th>
								<th>Стан</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php if(!empty($groups)){ $max = count($groups); foreach($groups as $g){ ?>
						<tr>
							<td><a href="<?=SITE_URL.'admin/'.$g->link?>"><?=$g->name?></a></td>
							<td><a href="<?=SITE_URL.$g->link?>">/<?=$g->link?>/*</a></td>
							<td class="<?=($g->active == 1)?'success':'danger'?>"><?=($g->active == 1)?'активна':'відключено'?></td>
						</tr>
						<?php } } ?>
						</tbody>
					</table>
				</div>

				<?php if(!empty($products))
				{
					echo '<h4 title="Перенесіть товари в кінцеву групу">Увага! Товари не в кінцевій групі!</h4>';
					$search = true;
					require_once 'products/__products-list.php';
				}
				?>
			</div>
		</div>
	</div>
</div>