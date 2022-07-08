<div class="row">
	<div class="row search-row">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/search">
            <div class="col-lg-8 col-sm-8 search-col">
                <input type="text" name="<?=($_SESSION['option']->ProductUseArticle) ? 'article' : 'id'?>" class="form-control" placeholder="<?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'ID'?>" value="<?=$this->data->get('article')?>" required="required">
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
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add<?=(isset($group))?'?group='.$group->id:''?>" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> <?=$_SESSION['admin_options']['word:product_add']?></a>
					
                    <?php if($_SESSION['option']->useGroups == 1) { ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					<?php } ?>

					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>

					<a href="<?=SITE_URL.'admin/wl_ntkd/'.$_SESSION['alias']->alias?><?=(isset($group))?'/-'.$group->id:''?>" class="btn btn-info btn-xs">SEO</a>
                </div>
                <h4 class="panel-title"><?=$_SESSION['alias']->name .'. Пошук '.$_SESSION['admin_options']['word:products_to_all']?></h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th><?=($_SESSION['option']->ProductUseArticle) ? 'Артикул' : 'Id'?></th>
								<th>Назва</th>
								<th>Ціна (у.о.)</th>
								<th>Наявність <?=($_SESSION['option']->useAvailability) ? '( од.)' : ''?></th>
								<?php if($_SESSION['option']->useAvailability == 0) { 
									$this->db->select($this->shop_model->table('_availability').' as a');
									$name = array('availability' => '#a.id');
									if($_SESSION['language']) $name['language'] = $_SESSION['language'];
									$this->db->join($this->shop_model->table('_availability_name'), 'name', $name);
									$availability = $this->db->get();
									?>
								<?php } if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 1) { ?>
									<th>Групи</th>
								<?php } ?>
								<th>Автор</th>
								<th>Редаговано</th>
								<th>Стан</th>
								<th>Змінити порядок</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
                        	if(!empty($products)) { 
                        		$max = count($products); 
                        		foreach($products as $a) { ?>
									<tr>
										<td><a href="<?=SITE_URL.'admin/'.$a->link?>"><?=($_SESSION['option']->ProductUseArticle) ? $a->article : $a->id?></a></td>
										<td>
											<a href="<?=SITE_URL.'admin/'.$a->link?>"><?=$a->name?></a> 
											<a href="<?=SITE_URL.$a->link?>"><i class="fa fa-eye"></i></a>
										</td>
										<td><?=$a->price?></td>
										<?php if($_SESSION['option']->useAvailability == 1) { ?>
											<td><input type="number" min="0" onchange="changeAvailability(this, <?=$a->id?>)" class="form-control" value="<?=$a->availability?>"></td>
										<?php } else { ?>
											<td>
												<select onchange="changeAvailability(this, <?=$a->id?>)" class="form-control">
													<?php if(!empty($availability)) foreach ($availability as $c) {
														echo('<option value="'.$c->id.'"');
														if($c->id == $a->availability) echo(' selected');
														echo('>'.$c->name.'</option>');
													} ?>
												</select>
											</td>
										<?php }
										if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 1) {
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
										<td>
											<form method="POST" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/changeposition">
												<input type="hidden" name="id" value="<?=$a->id?>">
												<input type="number" name="position" min="1" max="<?=$max?>" value="<?=$a->position?>" onchange="this.form.submit();" autocomplete="off" class="form-control">
											</form>
										</td>
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

<script type="text/javascript">
	function changeAvailability(e, id) {
		$.ajax({
			url: "<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/changeAvailability",
			type: 'POST',
			data: {
				availability :  e.value,
				id :  id,
				json : true
			},
			success: function(res){
				if(res['result'] == false){
					alert('Помилка! Спробуйте щераз');
				}
			}
		});
	}
</script>

<style type="text/css">
	input[type="number"]{
		min-width: 50px;
	}
	select {
		max-width: 200px;
	}

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