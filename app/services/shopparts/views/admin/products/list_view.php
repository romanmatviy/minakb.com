<div class="row">
	<div class="row search-row">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/search">
            <div class="col-lg-8 col-sm-8 search-col">
                <input type="text" name="article" class="form-control" placeholder="Артикул" value="<?=$this->data->get('article')?>" required="required">
            </div>
            <div class="col-lg-4 col-sm-4 search-col">
                <button class="btn btn-primary btn-search btn-block"><i class="fa fa-search"></i><strong> Знайти</strong></button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати товар</a>
					
                    <?php if($_SESSION['option']->useGroups == 1) { ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх товарів</a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх товарів</a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх товарів</a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?><?=(isset($group))?'/-'.$group->id:''?>" class="btn btn-info btn-xs">SEO</a>
                </div>
                <h4 class="panel-title"><?=(isset($group))?$_SESSION['alias']->name .'. Список товарів':'Список всіх товарів'?></h4>
            </div>
            <?php if(isset($group)) { ?>
                <div class="panel-heading">
	            		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><?=$group->alias_name?></a> 
						<?php if(!empty($group->parents)) {
							$link = SITE_URL.'admin/'.$_SESSION['alias']->alias;
							foreach ($group->parents as $parent) { 
								$link .= '/'.$parent->link;
								echo '<a href="'.$link.'" class="btn btn-info btn-xs">'.$parent->name.'</a> ';
							}
						} ?>
						<span class="btn btn-warning btn-xs"><?=$_SESSION['alias']->name?></span> 
	            </div>
	        <?php } ?>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Виробник</th>
                                <th>Артикул</th>
								<th>Назва</th>
								<th>Ціна (у.о.)</th>
								<?php if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 1) { ?>
									<th>Групи</th>
								<?php } ?>
								<th>Автор</th>
								<th>Редаговано</th>
								<th>Стан</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($products)) {
                        		foreach($products as $a) { ?>
									<tr>
										<td><?=$a->manufacturer_name?></td>
										<td><a href="<?=SITE_URL.'admin/'.$a->link?>"><?=$a->article?></a></td>
										<td>
											<a href="<?=SITE_URL.'admin/'.$a->link?>"><?=$a->name?></a> 
											<a href="<?=SITE_URL.$a->link?>"><i class="fa fa-eye"></i></a>
										</td>
										<td><?=$a->price?></td>
										<?php if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 1) {
											echo("<td>");
											if(!empty($a->group) && is_array($a->group)) {
                                                foreach ($a->group as $group) {
                                                    echo('<a href="'.SITE_URL.$_SESSION['alias']->alias.'/'.$group->link.'">'.$group->name.'</a> ');
                                                }
                                            } else {
                                                echo("Не визначено");
                                            }
                                            echo("</td>");
                                        	}
                                        ?>
										<td><a href="<?=SITE_URL.'admin/wl_users/'.$a->author_edit?>"><?=$a->user_name?></a></td>
										<td><?=date("d.m.Y H:i", $a->date_edit)?></td>
										<td style="background-color:<?=($a->active == 1)?'green':'red'?>;color:white"><?=($a->active == 1)?'активний':'відключено'?></td>
									</tr>
							<?php } } ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $this->load->library('paginator');
                echo $this->paginator->get();
                ?>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
	.search-row {
	    max-width: 800px;
	    margin-left: auto;
	    margin-right: auto;
	}
	.search-row .search-col {
	    padding: 0;
	    position: relative;
	}
	.search-row .search-col:first-child .form-control {
	    border: 1px solid #16A085;
	    border-radius: 3px 0 0 3px;
	    margin-bottom: 20px;
	}
</style>