<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:article_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
					<?php } ?>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <h4 class="panel-title"><?='Список всіх '.$_SESSION['admin_options']['word:articles_to_all']?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Id</th>
								<th>Назва</th>
								<th>Адреса</th>
								<?php if($_SESSION['option']->useGroups == 1) { ?>
									<th>Група</th>
								<?php } ?>
								<th>Автор</th>
								<th>Редаговано</th>
								<th>Стан</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($articles)){ 
                        		$max = count($articles); 
                        		foreach($articles as $a){ ?>
									<tr>
										<td><?=$a->id?></td>
										<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$a->link?>"><?=$a->name?></a></td>
										<td><a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$a->link?>">/<?=$a->alias?></a></td>
										<?php
                                        if($_SESSION['option']->useGroups == 1) {
                                            echo("<td>");
                                            if($_SESSION['option']->articleMultiGroup) {
                                                if(!empty($a->group) && is_array($a->group)) {
                                                    foreach ($a->group as $group) {
                                                        echo('<a href="'.SITE_URL.$_SESSION['alias']->alias.'/'.$group->alias.'">'.$group->name.'</a> ');
                                                    }
                                                } else {
                                                    echo("Не визначено");
                                                }
                                            } else {
                                                echo($a->group_name);
                                            }
                                            echo("</td>");
                                        }
                                        ?>
										<td><a href="<?=SITE_URL.'admin/wl_users/'.$a->author_edit?>"><?=$a->author_edit_name?></a></td>
										<td><?=date("d.m.Y H:i", $a->date_edit)?></td>
										<td style="background-color:<?=($a->active == 1)?'green':'red'?>;color:white"><?=($a->active == 1)?'активний':'відключено'?></td>
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
  $_SESSION['alias']->js_load[] = 'assets/color-admin/table-list.js';
  $_SESSION['alias']->js_init[] = 'TableManageCombine.init();'; 
?>
<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />