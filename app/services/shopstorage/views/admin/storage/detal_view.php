<?php if(isset($_SESSION['notify'])){ 
require APP_PATH.'views/admin/notify_view.php';
} ?>
      
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/edit/'.$product->id?>" class="btn btn-warning btn-xs">Редагувати</a> 
					<button onClick="showUninstalForm()" class="btn btn-danger btn-xs">Видалити накладну товару</button>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">До всіх товарів складу</a>
            	</div>
                <h4 class="panel-title">Прихідна накладна товару #<?=$product->id?></h4>
            </div>
            <div class="panel-body">
                <div id="uninstall-form" class="alert alert-danger fade in" style="display: none;">
					<i class="fa fa-trash fa-2x pull-left"></i>
					<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
						<p>Ви впевнені що бажаєте видалити прихідну накладну товару?</p>
						<input type="hidden" name="id" value="<?=$product->id?>">
						<input type="submit" value="Видалити" class="btn btn-danger">
						<button type="button" style="margin-left:25px" onClick="showUninstalForm()" class="btn btn-info">Скасувати</button>
					</form>
				</div>
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
							<th>Ціна прихідна</th>
							<td><?=$product->price_in.' '.$product->currency?></td>
						</tr>
						<tr>
							<th>Загальна наявність</th>
							<td><?=$product->amount?></td>
						</tr>
						<tr>
							<th>Резервовано</th>
							<td><?=($product->amount_reserved == '') ? 0 : $product->amount_reserved?></td>
						</tr>
						<tr>
							<th>Доступно</th>
							<td><strong><?=$product->amount_free?></strong></td>
						</tr>
						<?php if($_SESSION['option']->markUpByUserTypes) { ?>
							<tr>
								<th colspan="2"><center>Ціна вихідна</center></th>
							</tr>
							<?php
							$groups = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
							$price_out = 0;
							
							if(empty($product->price_out))
							{
								echo('<tr><th>Режим націнки</th><td>Загальний (автоматично)</td></tr>');
								$storage = $this->storage_model->getStorage();
								$product->price_out = array();
								foreach ($storage->markup as $user_type => $markup) {
									$product->price_out[$user_type] = round($product->price_in * ($markup + 100) / 100, 2);
								}
							}
							else
							{
								echo('<tr><th>Режим націнки</th><td>Індивідуальний</td></tr>');
								if(is_numeric($product->price_out))
									$price_out = $product->price_out;
								else
									$product->price_out = unserialize($product->price_out);
							}

							foreach($groups as $group) if($group->id > 1) { ?>
								<tr>
									<td><?=$group->title?> <?=(isset($storage->markup[$group->id])) ? '('.$storage->markup[$group->id].'%)' : ''?></td>
									<td><?=(isset($product->price_out[$group->id])) ? $product->price_out[$group->id] : $price_out?></td>
								</tr>
							<?php }
						} else { ?>
							<tr>
								<th>Ціна вихідна
								<?php if(empty($product->price_out))
								{
									$product->price_out = $product->price_in;
									if(!empty($product->markup))
									{
										echo ' <u title="Зміна по замовчуванню">+'.$product->markup.'%</u>';
										$product->price_out *= (1 + $product->markup / 100);
									}
								} ?>
								</th>
								<th><?=$product->price_out?> <?=$product->currency?></th>
							</tr>
						<?php } if(!$_SESSION['option']->deleteIfZero) { ?>
							<tr>
								<th>Дата приходу</th>
								<td><?=date('d.m.Y', $product->date_in)?></td>
							</tr>
						<?php } ?>
						<tr>
							<th>Дата останньої операції</th>
							<td><?=($product->date_out > 0) ? date("d.m.Y H:i", $product->date_out) : 'Відсутня'?></td>
						</tr>
						<tr>
							<th>Квитанцію додано</th>
							<td><?=date('d.m.Y H:i', $product->date_add)?> by <?=($product->manager_add)?$product->manager_add_name:'auto 1c'?></td>
						</tr>
						<tr>
							<th>Квитанцію редаговано</th>
							<td><?=date('d.m.Y H:i', $product->date_edit)?> by <?=($product->manager_edit)?$product->manager_edit_name:'auto 1c'?></td>
						</tr>
                    </table>
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
	                <h4 class="panel-title"><?=$_SESSION['alias']->name?>. Список всіх накладних по товару <?=($_SESSION['option']->productUseArticle) ? $product->info->article : $product->info->id?> <?=$product->info->name?></h4>
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
	function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}
</script>