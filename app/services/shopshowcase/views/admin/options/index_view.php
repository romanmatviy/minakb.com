<div class="row">
	<div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_option<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:option_add']?></a>
                </div>
                <h4 class="panel-title">Поточні налаштування</h4>
            </div>
            <?php
            if(isset($_SESSION['notify'])) { 
	        	require APP_PATH.'views/admin/notify_view.php';
	        }
	        if(isset($group)) { ?>
            	<div class="panel-heading">
            		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options"><?=$group->alias_name?></a> -> 
            		<?php 
            		if(!empty($group->parents)) {
						$link = SITE_URL.'admin/'.$_SESSION['alias']->alias.'/options';
						foreach ($group->parents as $parent) { $link .= '/'.$parent->alias; ?>
							<a href="<?=$link?>"><?=$parent->name?></a> -> 
					<?php } 
					} 
					echo($group->group_name); 
					?>
            	</div>
            <?php } ?>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Id</th>
								<th>Властивість</th>
								<th>Група</th>
								<th>Тип</th>
								<th>Єлемент фільтру</th>
								<th>До корзини</th>
								<th>Основна</th>
								<th>Стан</th>
								<th>Змінити порядок</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php if(!empty($options)) { $max = count($options); foreach($options as $a) { ?>
							<tr>
								<td><?=$a->id?></td>
								<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options/<?=$a->alias?>"><?=$a->name?></a></td>
								<td><?=$a->group_name?></td>
								<td><?=$a->type_name?></td>
								<td><?=($a->filter == 1)?'так':'ні'?></td>
								<td><?php if($a->toCart == 1) { echo 'так';
									if($a->changePrice) echo '. Впливає на ціну';
								} else echo	'ні'; ?></td>
								<td><?=($a->main == 1)?'так':'ні'?></td>
								<td style="background-color:<?=($a->active == 1)?'green':'red'?>;color:white"><?=($a->active == 1)?'активний':'відключено'?></td>
								<td><form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/change_option_position"><input type="hidden" name="id" value="<?=$a->id?>"><input type="number" name="position" min="1" max="<?=$max?>" value="<?=$a->position?>" onchange="this.form.submit();" autocomplete="off" class='form-control'></form></td>
							</tr>
							<?php } } else { ?>
							<tr>
								<td colspan="9" class="text-center">
									<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_option<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:option_add']?></a>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if(!empty($groups)) { ?>
	<div class="row">
		<div class="col-md-12">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <h4 class="panel-title">Перейти до детальніших налаштувань групи</h4>
	            </div>
	            <div class="panel-body">
	                <div class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
	                        <thead>
	                            <tr>
									<th>Група</th>
									<th>До товарів</th>
									<th>Стан</th>
	                            </tr>
	                        </thead>
	                        <tbody>
								<?php foreach($groups as $g) { ?>
									<tr>
										<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options/<?=$g->link?>"><?=$g->name?></a></td>
										<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$g->link?>">admin/<?=$_SESSION['alias']->alias.'/'.$g->link?>/*</a></td>
										<td style="background-color:<?=($g->active == 1)?'green':'red'?>;color:white"><?=($g->active == 1)?'активний':'відключено'?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>

<style type="text/css">
	input[type="number"] {
		height:35px;
		padding-left:5px;
		min-width: 50px;
	}
	select {
		width: 250px;
	}
	.panel-heading a {
		color: #fff;
	}
</style>