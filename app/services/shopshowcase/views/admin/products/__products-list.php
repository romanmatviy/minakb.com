<?php $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />

<?php $productOrder = false;
if(isset($_SESSION['option']->productOrder) && empty($_GET['sort']))
{
	$_SESSION['option']->productOrder = trim($_SESSION['option']->productOrder);
	$order = explode(' ', $_SESSION['option']->productOrder);
	if((count($order) == 2 && $order[0] == 'position' && in_array($order[1], array('asc', 'ASC', 'desc', 'DESC'))) || (count($order) == 1 && $order[0] == 'position'))
		$productOrder = true;
}
?>
<div class="table-responsive">
    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
        <thead>
            <tr>
            	<?php if(!isset($search) && $productOrder) echo "<th><input type=\"checkbox\" id=\"selectAllProducts\"></th>"; ?>
				<th><?=($_SESSION['option']->ProductUseArticle) ? 'Артикул /' : ''?> Назва</th>
				<th><div class="btn-group">
					<?php $sort = array('' => 'Ціна авто', 'price_down' => 'Від дешевих до дорогих ↑', 'price_up' => 'Від дорогих до дешевих ↓'); ?>
					<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
						<?=(isset($_GET['sort']) && isset($sort[$_GET['sort']])) ? $sort[$_GET['sort']] : 'Ціна'?> <?=(!empty($_SESSION['currency'])) ? '' : '(y.o.)'?> <span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<?php foreach ($sort as $key => $value) { ?>
							<li><a href="<?=$this->data->get_link('sort', $key)?>"><?=$value?></a></li>
						<?php } ?>
					</ul>
				</div></th>
				<th>Наявність <?=($_SESSION['option']->useAvailability) ? '( од.)' : ''?></th>
				<?php if($_SESSION['option']->useAvailability == 0) { 
					$this->db->select($this->shop_model->table('_availability').' as a');
					$name = array('availability' => '#a.id');
					if($_SESSION['language']) $name['language'] = $_SESSION['language'];
					$this->db->join($this->shop_model->table('_availability_name'), 'name', $name);
					$availability = $this->db->get();
				} if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 1) { ?>
					<th>Групи</th>
				<?php } ?>
				<th>Автор / Редаговано</th>
				<?php if(!isset($search)) { ?>
					<th><div class="btn-group">
						<?php $sort = array('' => 'Авто', 'active_on' => 'Активні згори ↑', 'active_off' => 'Активні знизу ↓'); ?>
						<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
							<?=(isset($_GET['sort']) && isset($sort[$_GET['sort']])) ? $sort[$_GET['sort']] : 'Стан'?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<?php foreach ($sort as $key => $value) { ?>
								<li><a href="<?=$this->data->get_link('sort', $key)?>"><?=$value?></a></li>
							<?php } ?>
						</ul>
					</div></th>
				<?php } ?>
            </tr>
        </thead>
        <tbody>
        	<?php foreach($products as $a) { ?>
				<tr id="<?=($_SESSION['option']->ProductMultiGroup && isset($a->position_id)) ? $a->position_id : $a->id?>">
					<?php if(!isset($search) && $productOrder) { ?>
						<td class="move sortablehandle"><i class="fa fa-sort"></i>
							<input type="checkbox" name="selectedProducts[]" value="<?=$a->id?>">
						</td>
					<?php } ?>
					<td>
						<?php if(!empty($a->admin_photo)) {?>
						<a href="<?=SITE_URL.'admin/'.$a->link?>"><img src="<?= IMG_PATH.$a->admin_photo?>" width="90" class="pull-left p-r-10" alt=""></a>
						<?php } ?>
						<a href="<?=SITE_URL.'admin/'.$a->link?>" class="product_name">
							<?=($_SESSION['option']->ProductUseArticle) ? '<strong>'.mb_strtoupper($a->article_show).'</strong>' : ''?> 
							<?=empty($a->name)?$a->id : $a->name?></a>

						<div class="dropdown pull-right">
						  <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						    Дія <span class="caret"></span>
						  </button>
						  <ul class="dropdown-menu">
						    <li><a href="<?=SITE_URL.'admin/'.$a->link?>?edit"><i class="fa fa-pencil"></i> Редагувати</a></li>
						    <li><a href="<?=SITE_URL.$a->link?>"><i class="fa fa-eye"></i> Дивитися на сайті</a></li>
						    <li role="separator" class="divider"></li>
						    <li><a href="#deleteProduct" class="text-danger" data-toggle="modal" data-pid="<?=$a->id?>" data-name="<?=$a->name?>"><i class="fa fa-trash"></i> Видалити</a></li>
						  </ul>
						</div>
					</td>
					<td>
						<?=(!empty($a->currency)) ? $a->price.' '.$a->currency : $this->shop_model->formatPrice($a->price) ?>
						<?php if($a->old_price) {
							echo "<br><del title='Стара ціна'>";
							echo (!empty($a->currency)) ? $a->old_price.' '.$a->currency : $this->shop_model->formatPrice($a->old_price) ;
							echo "</del>";
						} ?>
					</td>
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
						$active = 0;
						if(!empty($a->group) && is_array($a->group)) {
							$allG = count($a->group); $iG = 0;
                            foreach ($a->group as $g) {
                                echo('<a href="'.SITE_URL.'admin/'.$g->link.'">'.$g->name.'</a>');
                                if(++$iG < $allG)
                                	echo ", ";
                                if($g->active)
                                    $active++;
                            }
                        }
                         else {
                            echo("Не визначено");
                        }
                        echo("</td>");
                    	}
                    ?>
					<td><?=$a->author_add ? '<a href="'.SITE_URL.'admin/wl_users/'.$a->author_add.'">'.$a->user_name.'</a> <br>' : ''?> <?=date("d.m.Y H:i", $a->date_add)?> / <?=date("d.m.Y H:i", $a->date_edit)?></td>
					
					<?php if(!isset($search)) {
						if($productOrder || isset($_GET['sort'])) { ?>
							<td>
								<input type="checkbox" data-render="switchery" <?=($a->active == 1) ? 'checked' : ''?> value="1" onchange="changeActive(this, <?=$a->id?>, <?=(isset($group)) ? $group->id : 0 ?>)" />
							</td>
						<?php } else {
							$color = 'success';
	                        $color_text = 'активний';
							if($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup && !empty($a->group) && is_array($a->group))
	                        {
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
	                        elseif(!$_SESSION['option']->ProductMultiGroup)
	                        {
	                        	if($a->active == 0)
	                            {
	                                $color = 'danger';
	                                $color_text = 'відключено';
	                            }
	                            elseif($a->active < 0)
	                            {
	                                $color = 'warning';
	                                $color_text = 'Очікує підтвердження';
	                            }
	                        }
						?>
							<td class="<?=$color?>"><?=$color_text?></td>
					<?php } } ?>
				</tr>
			<?php } ?>
        </tbody>
    </table>
</div>
<div class="pull-right">Товарів у групі: <strong><?=$_SESSION['option']->paginator_total?></strong>. <?php if(!isset($search)){?>Активних товарів: <strong><?=$_SESSION['option']->paginator_total_active?></strong><?php } ?></div>
<?php if(!isset($search) && $productOrder) { ?>
<div class="dropdown">
	<button class="btn btn-info btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
		Дія із відміченими <span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
		<?php if(!empty($group) && !empty($allGroups)) { ?>
		<li><a href="#multi-changeGroup" data-toggle="modal"><i class="fa fa-list"></i> Перемістити в іншу групу</a></li>
		<?php } ?>
		<li role="separator" class="divider"></li>
		<li><a href=":javascript" class="text-success" onclick="return multi_editProducts('active', 1)"><i class="fa fa-eye"></i> <strong>Включити</strong> / активувати</a></li>
		<li><a href=":javascript" class="text-warning" onclick="return multi_editProducts('active', 0)"><i class="fa fa-eye"></i> <strong>Відключити</strong> / не доступні</a></li>
		<?php if($_SESSION['option']->useAvailability == 0 && !empty($availability)) foreach ($availability as $c) { ?>
			<li><a href="::javascript" onclick="return multi_editProducts('availability', <?=$c->id?>)"><i class="fa fa-cubes"></i> Доступність: <strong><?=$c->name?></strong></a></li>
		<?php } ?>
		<li role="separator" class="divider"></li>
		<li><a href="#multi-deleteProducts" class="text-danger" data-toggle="modal"><i class="fa fa-trash"></i> Видалити</a></li>
	</ul>
</div>
<?php }
$this->load->library('paginator');
echo $this->paginator->get();

if(!isset($search) && $productOrder) {
	if(!empty($group) && !empty($allGroups)) { ?>
	<div class="modal fade" tabindex="-1" role="dialog" id="multi-changeGroup">
	  	<div class="modal-dialog" role="document">
	    	<div class="modal-content">
	      		<div class="modal-header">
	        		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        		<h4 class="modal-title text-warning">Перемістити в іншу групу</h4>
	      		</div>
	      		<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/multi_changeGroup" method="POST">
		      		<div class="modal-body">
			        	<div class="form-group row">
			        		<label class="col-md-3 control-label">Оберіть групу</label>
			        		<div class="col-md-9">
								<select name="group" class="form-control">
									<option value="0">Немає</option>
									<?php
									$list = $emptyChildsList = array();
								    foreach ($allGroups as $g) {
								        $g->parent = (int) $g->parent;
								        $list[$g->id] = $g;
								        $list[$g->id]->child = array();
								        if(isset($emptyChildsList[$g->id]))
								            foreach ($emptyChildsList[$g->id] as $c) {
								                $list[$g->id]->child[] = $c;
								            }
								        if($g->parent > 0)
								        {
								            if(isset($list[$g->parent]->child))
								                $list[$g->parent]->child[] = $g->id;
								            else
								            {
								                if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
								                else $emptyChildsList[$g->parent] = array($g->id);
								            }
								        }
								    }
									function showList($product_group, $all, $list, $parent = 0, $level = 0)
									{
										$prefix = '';
										for ($i=0; $i < $level; $i++) {
											$prefix .= '- ';
										}
										foreach ($list as $g) if($g->parent == $parent) {
											if(empty($g->child)) {
												$selected = '';
												if($product_group == $g->id) $selected = 'selected';
												echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
											} else {
												echo('<optgroup label="'.$prefix.$g->name.'">');
												$l = $level + 1;
												$childs = array();
												foreach ($g->child as $c) {
													$childs[] = $all[$c];
												}
												showList ($product_group, $all, $childs, $g->id, $l);
												echo('</optgroup>');
											}
										}
										return true;
									}
									showList($group->id, $list, $list); ?>
								</select>
							</div>
						</div>
		      		</div>
		      		<div class="modal-footer">
		      			<input type="hidden" name="old_group" value="<?=$group->id?>">
		      			<input type="hidden" name="products">
				        <button type="button" class="btn btn-default" data-dismiss="modal">Скасувати</button>
				        <button type="submit" class="btn btn-warning"><i class="fa fa-sign-in"></i> Перемістити</button>
		      		</div>
	      		</form>
			</div>
	  	</div>
	</div>
	<?php } ?>
	<div class="modal fade" tabindex="-1" role="dialog" id="multi-deleteProducts">
	  	<div class="modal-dialog" role="document">
	    	<div class="modal-content">
	      		<div class="modal-header">
	        		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        		<h4 class="modal-title text-danger"><i class="fa fa-trash"></i> Видалити відмічені <?=$_SESSION['admin_options']['word:products']?>?</h4>
	      		</div>
	      		<div class="modal-body text-danger">
		          	<ul></ul>
	      		</div>
	      		<div class="modal-footer">
	      			<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/multi_deleteProducts" method="POST">
			      		<input type="hidden" name="products">
				        <button type="button" class="btn btn-default" data-dismiss="modal">Скасувати</button>
				        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Видалити</button>
	        		</form>
	      		</div>
			</div>
	  	</div>
	</div>
	<form id="multi_editProducts" action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/multi_editProducts" method="POST" class="hide">
  		<input type="hidden" name="products">
  		<input type="hidden" name="field">
  		<input type="hidden" name="value">
	</form>
<?php } ?>
<div class="modal fade" tabindex="-1" role="dialog" id="deleteProduct">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
      		<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        		<h4 class="modal-title text-danger">Видалити <strong></strong>?</h4>
      		</div>
      		<div class="modal-body text-danger">
	        	<i class="fa fa-trash fa-2x pull-left"></i>
	          	<p>Ви впевнені що бажаєте видалити <?=$_SESSION['admin_options']['word:product']?>?</p>
      		</div>
      		<div class="modal-footer">
      			<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
		      		<input type="hidden" name="id" value="0">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Скасувати</button>
			        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Видалити</button>
        		</form>
      		</div>
		</div>
  	</div>
</div>

<?php $_SESSION['alias']->js_load[] = "js/{$_SESSION['alias']->alias}/admin-products-list.js"; ?>