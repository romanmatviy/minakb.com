<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:article_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:articles_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?>/main?>" class="btn btn-success btn-xs"><i class="fa fa-newspaper-o"></i> Головна сторінка текст</a>
                </div>
                <h4 class="panel-title"><?=(isset($group))?$_SESSION['alias']->name .'. Список '.$_SESSION['admin_options']['word:articles_to_all']:'Список всіх '.$_SESSION['admin_options']['word:articles_to_all']?></h4>
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
                                <th>Id</th>
								<th>Назва</th>
								<th>Адреса</th>
								<?php if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->articleMultiGroup){ ?>
									<th>Групи</th>
								<?php } ?>
								<th>Редаговано</th>
								<th>Змінити порядок</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($articles)) { 
                        		$max = count($articles); 
                        		foreach($articles as $a) { ?>
									<tr <?=($a->active == 1)?'':'class="danger" title="відключено"'?>>
										<td><?=$a->id?></td>
										<td><a href="<?=SITE_URL.'admin/'.$a->link?>"><?=$a->name?></a></td>
										<td><a href="<?=SITE_URL.$a->link?>"><?=$a->link?></a></td>
										<?php 
										if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->articleMultiGroup) {
											echo("<td>");
											if(!empty($a->group) && is_array($a->group)) {
                                                foreach ($a->group as $group) {
                                                    echo('<a href="'.SITE_URL.$_SESSION['alias']->alias.'/'.$group->alias.'">'.$group->name.'</a> ');
                                                }
                                            } else {
                                                echo("Не визначено");
                                            }
                                            echo("</td>");
                                        } ?>
										<td>
											<a href="<?=SITE_URL.'admin/wl_users/'.$a->author_edit?>"><?=$a->author_edit_name?></a>
											<br>
											<?=date("d.m.Y H:i", $a->date_edit)?>
										</td>
										<td style="padding:2px 5px">
											<form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/changeposition">
												<input type="hidden" name="id" value="<?=$a->id?>">
												<input type="number" name="position" min="1" max="<?=$max?>" value="<?=$a->position?>" onchange="this.form.submit();" autocomplete="off" class="form-control">
											</form>
										</td>
									</tr>
							<?php } } ?>
                        </tbody>
                    </table>
                    <?php
$this->load->library('paginator');
echo $this->paginator->get();
?>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
	input[type="number"] {
		min-width: 50px;
	}
</style>