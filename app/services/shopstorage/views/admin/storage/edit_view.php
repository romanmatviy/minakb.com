<?php if(isset($_SESSION['notify'])){ 
require APP_PATH.'views/admin/notify_view.php';
} ?>
      
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити накладну</button>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$product->id?>" class="btn btn-info btn-xs">До всіх накладних</a>
            	</div>
                <h4 class="panel-title">Прихідна накладна #<?=$product->id?></h4>
            </div>
            <div class="panel-body">
	            <div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
					<i class="fa fa-trash fa-2x pull-left"></i>
					<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
						<p>Ви впевнені що бажаєте видалити прихідну накладну?</p>
						<input type="hidden" name="id" value="<?=$product->id?>">
						<input type="submit" value="Видалити" class="btn btn-danger">
						<button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
					</form>
				</div>
                <div class="table-responsive">
	                <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="id" value="<?=$product->id?>">
		                <div class="table-responsive">
		                    <table class="table table-striped table-bordered nowrap" width="100%">
		                    	<?php if(isset($product->info->manufacturer_name)) { ?>
			                    	<tr>
										<th>Виробник</th>
										<th><?=$product->info->manufacturer_name?></th>
									</tr>
		                    	<?php } if($_SESSION['option']->productUseArticle) { ?>
		                    		<tr>
										<th>Артикул товару</th>
										<th><?=$product->info->article_show?></th>
									</tr>
								<?php } else { ?>
									<tr>
										<th>ID товару</th>
										<th><?=$product->info->id?></th>
									</tr>
								<?php } ?>
								<tr>
									<th>Ціна прихідна <u><?=$product->currency?></u></th>
									<td><input type="number" name="price_in" id="price_in" value="<?=$product->price_in?>" min="0" onchange="setPrice(this.value)" step="0.01" class="form-control" required></td>
								</tr>
								<tr>
									<th>Загальна наявність</th>
									<td><input type="number" name="amount" value="<?=$product->amount?>" min="0" class="form-control" required></td>
								</tr>
								<tr>
									<th>Резервовано</th>
									<td><input type="number" name="amount_reserved" value="<?=$product->amount_reserved?>" min="0" class="form-control" required></td>
								</tr>
								<tr>
									<th colspan="2"><center>Ціна вихідна <u><?=$product->currency?></u></center></th>
								</tr>
								<tr>
									<th>Режим націнки</th>
									<td>
										<label><input type="radio" name="priceMode" onchange="setPriceMode(this)" value="0" <?=(empty($product->price_out)) ? 'checked' : ''?>> Загальний (автоматично)</label>
										<label><input type="radio" name="priceMode" onchange="setPriceMode(this)" value="1" <?=(empty($product->price_out)) ? '' : 'checked'?>> Індивідуальний</label>
									</td>
								</tr>
								<?php if($_SESSION['option']->markUpByUserTypes) {
									$groups = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
									$price_out = 0;
									if(empty($product->price_out))
									{
										echo('<imput type="hidden" name="price_out" value="0">');
										$storage = $this->storage_model->getStorage();
										$price_out = array();
										foreach ($storage->markup as $user_type => $markup) {
											$price_out[$user_type] = round($product->price_in * ($markup + 100) / 100, 2);
										}
										foreach($groups as $group) if($group->id > 1) { ?>
											<tr>
												<td><?=$group->title?> <?=(isset($storage->markup[$group->id])) ? '('.$storage->markup[$group->id].'%)' : ''?></td>
												<td><?=(isset($price_out[$group->id])) ? $price_out[$group->id] : 0?></td>
											</tr>
										<?php }
									}
									else
									{
										if(is_numeric($product->price_out)) $price_out = $product->price_out;
										else $product->price_out = unserialize($product->price_out);
										foreach($groups as $group) if($group->id > 1) { ?>
											<tr>
												<td><?=$group->title?> (Націнка <?=(isset($storage->markup[$group->id]))?$storage->markup[$group->id] : 0?>%)</td>
												<td>
													<input type="number" name="price_out-<?=$group->id?>" id="price_out-<?=$group->id?>" value="<?=(isset($product->price_out[$group->id])) ? $product->price_out[$group->id] : $price_out?>" min="0" step="0.01" class="form-control price_out">
												</td>
											</tr>
										<?php }
									} 
								} else { $readonly = '';
									if(empty($product->price_out))
									{
										$readonly = 'readonly';
										$product->price_out = $product->price_in;
										if(!empty($storage->markup))
											$product->price_out *= (1 + $storage->markup / 100);
									} ?>
									<tr>
										<th>Ціна вихідна <u title="Зміна по замовчуванню"><?=(isset($storage->markup))?$storage->markup.'%' : ''?></u></th>
										<td>
											<input type="number" name="price_out" id="price_out" value="<?=$product->price_out?>" min="0" step="0.01" class="form-control" required <?=$readonly?>>
											<input type="hidden" id="markup" value="<?=$storage->markup ?? 0?>">
										</td>
									</tr>
								<?php } if(!$_SESSION['option']->deleteIfZero) { ?>
									<tr>
										<th>Дата приходу</th>
										<td><input type="text" name="date_in" value="<?=date('d.m.Y', $product->date_in)?>" class="form-control" required></td>
									</tr>
								<?php } ?>
								<tr>
									<th>Дата останньої операції</th>
									<td><?=($product->date_out > 0) ? date("d.m.Y H:i", $product->date_out) : 'Відсутня'?></td>
								</tr>
								<tr>
									<th>Накладну додано</th>
									<td><?=date('d.m.Y H:i', $product->date_add)?> by <?=($product->manager_add)?$product->manager_add_name:'auto 1c'?></td>
								</tr>
								<tr>
									<th>Накладну редаговано</th>
									<td><?=date('d.m.Y H:i', $product->date_edit)?> by <?=($product->manager_edit)?$product->manager_edit_name:'auto 1c'?></td>
								</tr>
								<tr>
									<td>
										Після збереження:
									</td>
									<td id="after_save">
										<input type="radio" name="to" value="edit" id="to_edit" checked="checked"><label for="to_edit">проглянути накладну</label>
										<input type="radio" name="to" value="new" id="to_new"><label for="to_new">додати нову накладну</label>
									</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<input id="submit" type="submit" class="btn btn-sm btn-success" value="Зберегти">
									</td>
								</tr>
		                    </table>
		                </div>
		            </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$product->info->link?>" class="btn btn-info btn-xs">До товару</a>
            	</div>
                <h4 class="panel-title">Інформація про товар</h4>
            </div>
            <div class="panel-body" id="product">
	                <div id="product-info" class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
	                    	<?php /* <tr>
								<th>Виробник</th>
								<td><?=$product->info->options['1-manufacturer']->value?></td>
							</tr>
	                    	<?php */ ?>
                    		<tr>
								<th>Товар</th>
								<td>
									<a href="<?=SITE_URL.'admin/'.$product->info->link?>">
										<strong><?=$_SESSION['option']->productUseArticle ? $product->info->article_show : '#'.$product->info->id?></strong> <?=$product->info->name?>
									</a>
								</td>
							</tr>
							<tr>
								<th>Стандартна ціна</th>
								<td><?=$product->info->price.' '.$product->info->currency?></td>
							</tr>
							<?php /* ?>
							<tr>
								<th>Аналоги</th>
								<td>
								<?php 
								if(empty($product->info->analogs))
									echo "Не вказано";
								else
									foreach (explode(',', $product->info->analogs) as $analog) {
										echo "<span class=\"label label-info\">{$analog}</span> ";
									}
								?>
								</td>
							</tr>
							<?php */ if(!empty($product->info->group_name)) { ?>
								<tr>
									<th>Група</th>
									<td><?=$product->info->group_name?></td>
								</tr>
							<?php } if(!empty($product->info->group) && is_array($product->info->group)) { ?>
								<tr>
									<th>Групи</th>
									<td>
										<?php
										$groups = array();
										foreach ($product->info->group as $group) {
											$groups[] = '<a href="'.SITE_URL.'admin/'.$group->link.'">'.$group->name.'</a>';
										}
										echo(implode(', ', $groups));
										?>
									</td>
								</tr>
							<?php } if(!empty($product->info->options)) { foreach($product->info->options as $option) { ?>
								<tr>
									<th><?=$option->name?></th>
									<td><?=$option->value?> <?=$option->sufix?></td>
								</tr>
							<?php } } ?>
	                    </table>	                    
	                </div>
	            </form>
            </div>
        </div>
    </div>
</div>

<?php if(count($product->history) > 1 ) { ?>
	<div class="row">
	    <div class="col-md-12">
	        <div class="panel panel-inverse">
	            <div class="panel-heading">
	                <div class="panel-heading-btn">
	                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати накладну</a>
	                </div>
	                <h4 class="panel-title">Список всіх накладних по товару <?=($_SESSION['option']->productUseArticle) ? $product->info->article : $product->info->id?> <?=$product->info->name?></h4>
	            </div>
	            <div class="panel-body">
	                <div class="table-responsive">
	                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
	                        <thead>
	                            <tr>
	                                <th>Накладна №</th>
	                                <th></th>
									<th>Ціна прихідна</th>
									<th>Кількість / Залишок</th>
									<?php if($_SESSION['option']->markUpByUserTypes == 1) {
										foreach($groups as $group){
										?>
										<th>Ціна для <?=$group->title?></th>
									<?php } } else { ?>
										<th>Ціна вихідна</th>
									<?php } ?>
									<th>Остання операція</th>
									<th>Дата приходу</th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        	<?php
	                        	if(!empty($product->history)) { 
	                        		foreach($product->history as $history) if($history->id != $product->id) { ?>
										<tr>
											<td><?=$history->id?></td>
											<td><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$history->id?>" class="btn btn-info btn-xs">Детальніше</a></td>
											<td><?=$history->price_in?></td>
											<td><?=$history->amount?></td>
											<?php if($_SESSION['option']->markUpByUserTypes == 1) {
												$price_out = 0;
												if(is_numeric($history->price_out)) $price_out = $history->price_out;
												else $history->price_out = unserialize($history->price_out);
												foreach($groups as $group){ ?>
													<td><?=(isset($history->price_out[$group->id])) ? $history->price_out[$group->id] : $price_out?></td>
											<?php } } else {
												echo("<td>{$history->price_out}</td>");
	                                        	}
	                                        ?>
											<td><?=($history->date_out > 0) ? date("d.m.Y H:i", $history->date_out) : 'Відсутня'?></td>
											<td><?=date("d.m.Y H:i", $history->date_in)?></td>
										</tr>
								<?php } } ?>
	                        </tbody>
	                    </table>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
<?php } ?>

<script type="text/javascript">
function setPrice(price) {
	<?php if($_SESSION['option']->markUpByUserTypes) { foreach($groups as $group){ ?>
		$('#price_out-<?=$group->id?>').val(<?=(isset($storage->markup[$group->id]))?$storage->markup[$group->id] : 0?> * price / 100 + Math.floor(price));
	<?php } ?>
		$('#price_out-0').val((<?=(isset($storage->markup[0]))?$storage->markup[0] : 0?> * price / 100 + Math.floor(price)).toFixed(2));
	<?php } else { ?>
		$('#price_out').val(<?=(isset($storage->markup))?$storage->markup : 0?> * price / 100 + Math.floor(price));
	<?php } ?>
}
function setPriceMode(e) {
	if(e.value == 1)
	{
	<?php if($_SESSION['option']->markUpByUserTypes) { foreach($groups as $group){ ?>
		$('#price_out-<?=$group->id?>').attr('readonly', false);
	<?php } ?>
		$('#price_out-0').attr('readonly', false);
	<?php } else { ?>
		$('#price_out').attr('readonly', false);
	<?php } ?>
	}
	else
	{
	<?php if($_SESSION['option']->markUpByUserTypes) { foreach($groups as $group){ ?>
		$('#price_out-<?=$group->id?>').attr('readonly', true);
	<?php } ?>
		$('#price_out-0').attr('readonly', true);
	<?php } else { ?>
		$('#price_out').attr('readonly', true);
	<?php } ?>
	}
}
function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}
</script>