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

<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
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
							<td><a href="<?=SITE_URL.$g->link?>">/<?=$g->link?>/*</a></td>
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