<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати групу</a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>
                </div>
                <h4 class="panel-title">Керування групами <?=$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>

            <?php
            if(isset($_SESSION['notify'])){ 
	        	require APP_PATH.'views/admin/notify_view.php';
	        }
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
								<th>Автор</th>
								<th>Стан</th>
								<th>Змінити порядок</th>
                            </tr>
                        </thead>
                        <tbody>
							<?php if(!empty($groups)){
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
										$pl = 15 * $level;
										$ml = 10 * $level;
										foreach ($list as $g) if($g->parent == $parent) { ?>
											<tr>
												<td style="padding-left: <?=$pl?>px"><?=$g->id?></td>
												<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/<?=$g->id?>-<?=$g->alias?>"><?=$g->name?></a></td>
												<td><a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$g->link?>">/<?=$_SESSION['alias']->alias.'/'.$g->link?></a></td>
												<td><?=date("d.m.Y H:i", $g->date_edit)?></td>
												<td><a href="<?=SITE_URL.'admin/wl_users/'.$g->author_edit?>"><?=$g->user_name?></a></td>
												<td style="background-color:<?=($g->active == 1)?'green':'red'?>;color:white"><?=($g->active == 1)?'активний':'відключено'?></td>
												<td style="padding: 1px 5px;">
													<form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/change_group_position">
														<input type="hidden" name="id" value="<?=$g->id?>">
														<input type="number" name="position" min="1" max="<?=$count_childs?>" value="<?=$g->position?>" onchange="this.form.submit();" autocomplete="off" style="margin-left: <?=$ml?>px">
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
							} ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>