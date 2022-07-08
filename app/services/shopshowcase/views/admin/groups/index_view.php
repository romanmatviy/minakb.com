<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати групу</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups'?>" class="btn btn-success btn-xs">Список по групно</a>
                	<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Керування групами <?=$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>

            <?php
            if(isset($_SESSION['notify'])) { 
	        	require APP_PATH.'views/admin/notify_view.php';
	        }

	        if(!empty($groups)) {
            ?>

            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Id</th>
								<th>Група</th>
								<th>Адреса</th>
								<th>Востаннє редаговано</th>
								<th>Змінити порядок</th>
                            </tr>
                        </thead>
                        <tbody>
							<?php 
								$list = array();
								$emptyParentsList = array();
								$count_level_0 = 0;
								foreach ($groups as $g) {
									$list[$g->id] = $g;
									$list[$g->id]->child = array();
									if(isset($emptyParentsList[$g->id])){
										foreach ($emptyParentsList[$g->id] as $c) {
											$list[$g->id]->child[] = $c;
										}
									}
									if($g->parent > 0) {
										if(isset($list[$g->parent]->child)) $list[$g->parent]->child[] = $g->id;
										else {
											if(isset($emptyParentsList[$g->parent])) $emptyParentsList[$g->parent][] = $g->id;
											else $emptyParentsList[$g->parent] = array($g->id);
										}
									}
									if($g->parent == 0) $count_level_0++;
								}
								if(!empty($list)){
									function showList($all, $list, $count_childs, $parent = 0, $level = 0)
									{
										$pl = 15 * $level + 5;
										$ml = 10 * $level;
										foreach ($list as $g) if($g->parent == $parent) {
											$tr = '';
											if(!$g->active)
												$tr = 'class="danger" title="Група відключена"';
											elseif($g->hide)
												$tr = 'class="warning" title="Група скрита"';
											?>
											<tr <?=$tr?>>
												<td style="padding-left: <?=$pl?>px"><?=$g->id?></td>
												<td style="padding-left: <?=$pl?>px"><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/edit-<?=$g->id?>-<?=$g->alias?>"><?=($g->parent == 0) ? '<strong>'.$g->name.'</strong>' : $g->name?></a></td>
												<td><a href="<?=SITE_URL.$g->link?>">/<?=$g->link?></a></td>
												<td><?=date("d.m.Y H:i", $g->date_edit)?> <a href="<?=SITE_URL.'admin/wl_users/'.$g->author_edit?>"><?=$g->user_name?></a></td>
												<td style="padding: 1px 5px;">
													<form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/change_group_position">
														<input type="hidden" name="id" value="<?=$g->id?>">
														<input type="number" name="position" min="1" max="<?=$count_childs?>" value="<?=$g->position?>" onchange="this.form.submit();" autocomplete="off" style="height:35px; padding-left:5px; min-width:80px; margin-left: <?=$ml?>px">
													</form>
												</td>
											</tr>
										<?php
											if(!empty($g->child)) {
												$l = $level + 1;
												$childs = array();
												foreach ($g->child as $c) {
													$childs[] = $all[$c];
												}
												showList ($all, $childs, count($childs), $g->id, $l);
											}
										}
										return true;
									}
									showList($list, $list, $count_level_0);
								}
							?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } else { ?>
            	<div class="note note-info">
					<h4>Увага! В налаштуваннях адреси не створено жодної групи!</h4>
					<p>
					    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group" class="btn btn-warning"><i class="fa fa-plus"></i> Додати групу</a>
	                </p>
				</div>
			<?php } ?>
        </div>
    </div>
</div>