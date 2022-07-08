<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group)) ? '?group='.$group->id : ''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати питання</a>
					
                    <?php if($_SESSION['option']->useGroups){ ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх питань</a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-success btn-xs">До всіх груп</a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?>/edit" class="btn btn-info btn-xs"><i class="fa fa-id-card-o" aria-hidden="true"></i> Вміст сторінки</a>

                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <?php if(isset($group)){ ?>
	            	<h4 class="panel-title">
	            		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="left"><?=$group->alias_name?></a> ->
						<?=$group->name?>
	            	</h4>
		        <?php } else { ?>
                	<h4 class="panel-title">Перелік питань</h4>
                <?php } ?>
            </div>
            
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Id</th>
								<th>Питання</th>
								<?php if($_SESSION['option']->useGroups) { ?>
									<th>Група</th>
								<?php } ?>
								<th>Автор Редаговано</th>
								<?php if($this->data->uri(2) != 'all') { ?>
									<th>Порядок</th>
								<?php } ?>
                            </tr>
                        </thead>
                        <tbody>
	                        <?php if($faqs) {
		                        $max = count($faqs);
		                        foreach($faqs as $faq) { ?>
		                        	<tr <?=($faq->active)?'':'class="danger" title="Відключено"'?>>
		                        		<td><?=$faq->id?></td>
		                        		<td><a href="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/<?=$faq->link?>"><?=$faq->question?></a></td>
		                        		<?php if($_SESSION['option']->useGroups){ ?>
											<td><?=$faq->group_name?></td>
										<?php } ?>
		                        		<td><?=$faq->author_edit_name?> <?=date('d.m.Y H:i', $faq->date_edit)?></td>
		                        		<?php if($this->data->uri(2) != 'all') { ?>
			                        		<td>
												<form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/change_question_position">
													<input type="hidden" name="id" value="<?=$faq->id?>">
													<input type="hidden" name="group" value="<?=$faq->group?>">
													<input type="number" name="position" min="1" max="<?=$max?>" value="<?=$faq->position?>" onchange="this.form.submit();" autocomplete="off" class="form-control">
												</form>
											</td>
										<?php } ?>
		                        	</tr>
	                        <?php } }
	                        if($this->data->uri(2) != 'all') { ?>
	                        	<tr>
	                                <td class="text-center" colspan="6">
	                                    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group)) ? '?group='.$group->id : ''?>" class="btn btn-warning btn-xs m-r-10"><i class="fa fa-plus"></i> Додати питання</a>
	                                    <a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?>/edit" class="btn btn-info btn-xs"><i class="fa fa-id-card-o" aria-hidden="true"></i> Вміст сторінки</a>
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

<?php if($this->data->uri(2) == 'all') {
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/jquery.dataTables.js';  
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colReorder.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colVis.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.responsive.js'; 
  $_SESSION['alias']->js_load[] = 'js/admin/table-list.js'; 
  $_SESSION['alias']->js_init[] = 'TableManageCombine.init();'; 
?>
<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />

<style type="text/css">
	input[type="number"] {
		min-width: 50px;
	}
</style>
<?php } ?>