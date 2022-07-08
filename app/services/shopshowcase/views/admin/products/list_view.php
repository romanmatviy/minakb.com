<?php require_once APP_PATH.'services'.DIRSEP.$_SESSION['service']->name.DIRSEP.'views'.DIRSEP.'admin'.DIRSEP.'__search_subview.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(!empty($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1) { ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>

					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?><?=(!empty($group))?'/-'.$group->id:''?>" class="btn btn-info btn-xs">SEO</a>

                    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export_products<?=(!empty($group))?'?group='.$group->id:''?>" class="btn btn-xs btn-success right"><i class="fa fa-file-excel-o"></i> Експорт товарів</a>
                </div>
                <h4 class="panel-title"><?=(!empty($group))?$_SESSION['alias']->name .'. Список '.$_SESSION['admin_options']['word:products_to_all']:'Список всіх '.$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>
            <?php if(!empty($group)) { ?>
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
            	<?php if(!empty($products)) { 
            		require_once '__products-list.php';
                } elseif(!isset($search)) { ?>
					<div class="note note-info">
                        <h4>Увага! За даним пошуковим запитом <?=$_SESSION['admin_options']['word:products']?> відсутні. Уточніть запит</h4>
                    </div>
                <?php } else { ?>
                	<div class="note note-info">
                        <h4>Увага! Відсутні <?=$_SESSION['admin_options']['word:products']?></h4>
                        <p>
                            <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(!empty($group))?'?group='.$group->id:''?>" class="btn btn-warning"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    input[type="number"]{
        min-width: 50px;
    }
    td select {
        max-width: 200px;
    }
    td.move {
        width: 30px;
        cursor: move;
        text-align: center;
    }
</style>