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
                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Групи/підгрупи</h4>
            </div>
            <?php if(isset($group)){ ?>
                <div class="panel-heading">
	            	<h4 class="panel-title">
	            		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>"><?=$group->alias_name?></a> ->
						<?php if(!empty($group->parents)){
							$link = SITE_URL.'admin/'.$_SESSION['alias']->alias;
							foreach ($group->parents as $parent) { 
								$link .= '/'.$parent->link;
								echo '<a href="'.$link.'">'.$parent->name.'</a> -> ';
							}
							echo($_SESSION['alias']->name);
						} ?>
	            	</h4>
	            </div>
	        <?php } ?>
			<div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Назва</th>
								<th>Адреса</th>
								<th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php if(!empty($groups)){ $max = count($groups); foreach($groups as $g){ ?>
						<tr>
							<td><a href="<?=SITE_URL.'admin/'.$g->link?>"><?=$g->name?></a></td>
							<td><a href="<?=SITE_URL.$g->link?>">/<?=$_SESSION['alias']->alias.'/'.$g->link?>/*</a></td>
							<td style="backgroung-color:<?=($g->active == 1)?'green':'red'?>; color:white"><center><?=$g->active?></center></td>
						</tr>
						<?php } } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>