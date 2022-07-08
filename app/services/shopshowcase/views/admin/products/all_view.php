<?php require_once APP_PATH.'services'.DIRSEP.$_SESSION['service']->name.DIRSEP.'views'.DIRSEP.'admin'.DIRSEP.'__search_subview.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <h4 class="panel-title"><?='Список всіх '.$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>
            <div class="panel-body">
                <?php if(!empty($products)) { ?>
                    <div class="table-responsive">
                        <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                            <thead>
                                <tr>
                                    <th>Id</th>
    								<th>Назва</th>
                                    <th>Ціна <?=(!empty($_SESSION['currency'])) ? '' : '(y.o.)'?></th>
    								<?php if($_SESSION['option']->useGroups == 1) { ?>
    									<th>Група</th>
    								<?php } ?>
    								<th>Редаговано</th>
    								<th>Стан</th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php $max = count($products); 
                            		foreach($products as $a) { ?>
    									<tr>
    										<td><?=$a->id?></td>
    										<td>
                                                <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$a->link?>"><?=$a->name?></a>
                                                <a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$a->link?>"><i class="fa fa-eye"></i></a>
                                            </td>
                                            <td>
                                                <?=$a->price?> <?=(!empty($a->currency)) ? $a->currency : 'y.o.'?>
                                                <?php if($a->old_price) {
                                                    echo "<br><del title='Стара ціна'>{$a->old_price} ";
                                                    echo (!empty($a->currency)) ? $a->currency : 'y.o.';
                                                    echo "</del>";
                                                } ?>
                                                
                                            </td>
    										<?php
                                            if($_SESSION['option']->useGroups == 1) {
                                                echo("<td>"); $active = 0;
                                                if($_SESSION['option']->ProductMultiGroup) {
                                                    if(!empty($a->group) && is_array($a->group)) {
                                                        foreach ($a->group as $group) {
                                                            $class = ($group->active) ? '' : 'class="label label-warning"';
                                                            echo('<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$group->link.'" target="_blank" '.$class.'>'.$group->name.'</a> ');
                                                            if($group->active)
                                                                $active++;
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
    										<td><a href="<?=SITE_URL.'admin/wl_users/'.$a->author_edit?>"><?=$a->author_edit_name?></a> <?=date("d.m.Y H:i", $a->date_edit)?></td>
                                            <?php $color = ($a->active == 1) ? 'success':'danger';
                                            $color_text = ($a->active == 1) ? 'активний':'відключено';
                                            if($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup && !empty($a->group) && is_array($a->group))
                                            {
                                                $color = 'success';
                                                $color_text = 'активний';
                                                if($active == 0)
                                                {
                                                    $color = 'danger';
                                                    $color_text = 'відключено';
                                                }
                                                elseif($active < count($a->group))
                                                {
                                                    $color = 'warning';
                                                    $color_text = 'частково активний';
                                                }
                                            }
                                        ?>
                                            <td class="<?=$color?>"><?=$color_text?></td>
    									</tr>
    							<?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />
                <?php 
                    $_SESSION['alias']->js_load[] = 'assets/DataTables/js/jquery.dataTables.js';  
                    $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colReorder.js'; 
                    $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colVis.js'; 
                    $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.responsive.js'; 
                    $_SESSION['alias']->js_load[] = 'assets/color-admin/table-list.js';
                    $_SESSION['alias']->js_init[] = 'TableManageCombine.init();'; 
                } else {
                ?>
                    <div class="note note-info">
                        <h4>Увага! Відсутні <?=$_SESSION['admin_options']['word:products']?></h4>
                        <p>
                            <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>