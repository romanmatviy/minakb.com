<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group<?=($group)?'?parent='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати групу</a>
                	<?php if($group) { ?>
				    	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/edit-<?=$group->id?>-<?=$group->alias?>" class="btn btn-info btn-xs"><i class="fa fa-edit"></i> Редагувати групу</a>
				    <?php } else if(!empty($_SESSION['option']->prom) && !isset($_GET['prom'])) { ?>
				    	<a href="?prom" class="btn btn-info btn-xs"><i class="fa fa-list"></i> Групи prom.ua</a>
				    <?php } ?>
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups?all'?>" class="btn btn-success btn-xs">Всі групи деревом</a></a>
                </div>
                <h4 class="panel-title"><?=($group) ? $group->name : 'Керування групами'?></h4>
            </div>
            <?php if($group) { ?>
                <div class="panel-heading">
        			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">Кореневі групи</a> 
					<?php if(!empty($group->parents))
						foreach ($group->parents as $parent) {
							echo '<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/groups/'.$parent->id.'-'.$parent->alias.'" class="btn btn-info btn-xs">'.$parent->name.'</a> ';
						} ?>
					<span class="btn btn-warning btn-xs"><?=$group->name?></span>

					<?php if(!empty($_SESSION['option']->prom) && !isset($_GET['prom'])) { ?>
						<a href="?prom" class="btn btn-info btn-xs pull-right"><i class="fa fa-list"></i> Групи prom.ua</a>
					<?php } ?>
	            </div>
	        <?php }
	        
	        if(!empty($groups)) { ?>

            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Id</th>
								<th>Група</th>
								<th>Востаннє редаговано</th>
								<?php if(!empty($_SESSION['option']->prom) && isset($_GET['prom'])) { ?>
									<th>Група prom.ua</th>
								<?php } ?>
								<th>Змінити порядок</th>
                            </tr>
                        </thead>
                        <tbody>
							<?php $good_position = 1; $max_position = count($groups);
							foreach ($groups as $g) { $tr = '';
							if(!$g->active)
								$tr = 'class="danger" title="Група відключена"';
							elseif($g->hide)
								$tr = 'class="warning" title="Група скрита"';
							if($g->position != $good_position)
							{
								$this->db->updateRow('s_shopshowcase_groups', ['position' => $good_position], $g->id);
								$g->position = $good_position;
							}
							$good_position++; ?>
							<tr <?=$tr?>>
								<td><?=$g->id?></td>
								<td>
									<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/<?=$g->id?>-<?=$g->alias?>"><?=$g->name?></a>
									<div class="dropdown pull-right">
										<button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
											Дія <span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											<li><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/edit-<?=$g->id?>-<?=$g->alias?>"><i class="fa fa-pencil"></i> Редагувати</a></li>
											<li><a href="<?=SITE_URL.$g->link?>"><i class="fa fa-eye"></i> Дивитися на сайті</a></li>
											<?php if(!empty($_SESSION['option']->exportKey)) { ?>
												<li role="separator" class="divider"></li>
												<li><a download href="<?=SERVER_URL.$_SESSION['alias']->alias?>/export_prom?group=<?=$g->id?>&key=<?=$_SESSION['option']->exportKey?>"><i class="fa fa-download"></i> Експорт групи <strong class="label label-success">prom.ua</strong></a></li>
												<li><a download href="<?=SERVER_URL.$_SESSION['alias']->alias?>/export_google?group=<?=$g->id?>&key=<?=$_SESSION['option']->exportKey?>"><i class="fa fa-download"></i> Експорт групи <strong class="label label-warning">Google Merchant Center</strong></a></li>
												<li><a download href="<?=SERVER_URL.$_SESSION['alias']->alias?>/export_facebook?group=<?=$g->id?>&key=<?=$_SESSION['option']->exportKey?>"><i class="fa fa-download"></i> Експорт групи <strong class="label label-info">Facebook</strong></a></li>
											<?php } ?>
										</ul>
									</div>
								</td>
								<td><?=date("d.m.Y H:i", $g->date_edit)?> <a href="<?=SITE_URL.'admin/wl_users/'.$g->author_edit?>"><?=$g->user_name?></a></td>
								<?php if(!empty($_SESSION['option']->prom) && isset($_GET['prom'])) { ?>
									<td><input onchange="changePromGroup(this, <?= $g->id?>)" type="number" value="<?= $g->prom_id?>" min="0" class="form-control"></td>
								<?php } ?>
								<td style="padding: 1px 5px;">
									<form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/change_group_position">
										<input type="hidden" name="id" value="<?=$g->id?>">
										<input type="number" name="position" min="1" max="<?=$max_position?>" value="<?=$g->position?>" onchange="this.form.submit();" autocomplete="off" style="height:35px; padding-left:5px; min-width:80px;">
									</form>
								</td>
							</tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } else { ?>
            	<div class="note note-info">
					<h4><?=($group)?'В даній групі відсутні підгрупи':'Увага! В магазині відсутні групи!'?></h4>
					<p>
					    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group<?=($group)?'?parent='.$group->id:''?>" class="btn btn-warning"><i class="fa fa-plus"></i> Додати <?=($group)?'під':''?>групу</a>
					    <?php if($group) { ?>
					    	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/edit-<?=$group->id?>-<?=$group->alias?>" class="btn btn-info"><i class="fa fa-edit"></i> Редагувати групу</a>
					    	<a href="<?=SITE_URL.'admin/'.$group->link?>" class="btn btn-success"><i class="fa fa-list"></i> До товарів групи</a>
					    <?php } ?>
	                </p>
				</div>
			<?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.onload = function() {
        $('a[download]').click(function () {
        	$('#saveing').addClass('show').delay( 30000 ).queue(function(){ $(this).removeClass('show').dequeue(); });
        })
    };
</script>

<?php if(!empty($_SESSION['option']->prom)) { ?>
<script>
	function changePromGroup(e, groupId) 
	{
		var promGroupId = parseInt(e.value);
		if(Number.isInteger(promGroupId))
		{
			$.ajax({
				url: "<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/changePromGroup",
				type: "POST",
				data: {
					groupId : groupId,
					promGroupId: promGroupId
				},
				success: function (res) {
					if(res['result'])
						$.gritter.add({text:"Дані успішно збережено!"});
					else
					{
						if(res.error)
							$.gritter.add({text:"Помилка! "+res.error});
						else
							$.gritter.add({text:"Помилка!"});
					}
				}
			})
		}
		else
			$.gritter.add({text:"Помилка! Має бути число"});
	}
</script>
<?php } ?>