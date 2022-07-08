<div class="row">
	<div class="row search-row">
		<?php if(!empty($__dashboard_subview)) { ?>
			<h2>Нові та активні Ваші замовлення</h2>
		<?php } ?>
        <form>
            <div class="col-sm-4 search-col">
                <input type="number" name="id" class="form-control" placeholder="№ Замовлення" value="<?=$this->data->get('id')?>">
            </div>
            <div class="col-sm-4 search-col">
                <input type="text" name="article" class="form-control" placeholder="Артикул товару у замовленні" value="<?=$this->data->get('article')?>">
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
                	<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add" class="btn btn-warning btn-xs"><i class="fa fa-plus"></i> Додати покупку</a>
                	<?php if(!empty($__dashboard_subview)) { ?>
                		<a href="<?=SITE_URL?>admin/cart" class="btn btn-xs btn-success"><i class="fa fa-list"></i> До всіх замовлень</a>
                	<?php } else { ?>
	                	<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/bonus" class="btn btn-success btn-xs"><i class="fa fa-ravelry"></i> Бонус-коди</a>
	                	<a href="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>/settings" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування</a>
	                <?php } ?>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <input type="date" form="cartFilter" name="day" class="form-control m-r-15 pull-left" value="<?=$this->data->get('day')?>" onchange="document.forms.cartFilter.submit()" style="max-width: 200px" title="Період від / Замовлення на дату">
                <?php if(!empty($_GET['day'])) { ?>
                	<input type="date" form="cartFilter" name="dayTo" class="form-control m-r-15 pull-left" value="<?=$this->data->get('dayTo')?>" onchange="document.forms.cartFilter.submit()" style="max-width: 200px" title="Період до">
                <?php }
                if(count($_GET) > 1)
                	echo '<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Скасувати всі фільтри</a>';
                ?>
                <div class="clearfix"></div>
            </div>
			<div class="panel-body">
                <div class="table-responsive">
                	<form id="cartFilter" action="<?= SITE_URL.'admin/'.$_SESSION['alias']->alias?>">
                		<input type="hidden" name="user" value="<?=$this->data->get('user')?>">
                		<?php if($article = $this->data->get('article'))
                			echo '<input type="hidden" name="article" value="'.$article.'">';
                		 ?>
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                            	<th style="max-width: 100px" title="ID / Менеджер">
                    				<select name="manager" class="form-control" onchange="document.forms.cartFilter.submit()">
	                            		<option value="-1">Всі</option>
	                            		<option value="0" <?=isset($_GET['manager']) && $_GET['manager'] == 0 ? 'selected' : ''?>>Відсутній</option>
										<?php $managers = $this->db->getAllDataByFieldInArray('wl_users', ['type' => '<3']);
										foreach ($managers as $manager) {
												$selected = !empty($_GET['manager']) && $_GET['manager'] == $manager->id ? 'selected' : '';
												echo('<option value="'.$manager->id.'" '.$selected);
												echo('>#'.$manager->id.' '.$manager->name.' ('.$manager->email.')</option>');
											} ?>
                            		</select>
                            	</th>
                            	<th>
                            		<select name="status" class="form-control" onchange="document.forms.cartFilter.submit()">
	                            		<option value="0">Всі статуси</option>
	                            		<?php if($s_cart_status = $this->cart_model->getStatuses())
	                            			foreach ($s_cart_status as $status) {
	                            				$selected = !empty($_GET['status']) && $_GET['status'] == $status->id ? 'selected' : '';
	                            				echo "<option value={$status->id} {$selected}>{$status->name}</option>";
	                            			}
	                            		 ?>
                            		</select></th>
                            	<th>Товар</th>
                            	<th>
                            		<?php $value = '';
                            		if($user = $this->data->get('user'))
                            			if(is_numeric($user) && $user > 0)
                            				if($user = $this->db->select('wl_users as u', 'id, name, email', $user)
                            					->join('wl_user_info', 'value as phone', array('field' => 'phone', 'user' => $user))
                            					->limit(1)
                            					->get() )
                            				{
                            					$value = "#{$user->id} {$user->name} {$user->phone} {$user->email}";
                            				}	?>
                            		<input type="text" class="form-control" placeholder="Покупець" value="<?=$value?>" id="clientName">
                            		<div id="clientsList"></div>
                            	</th>
								<th title="Загальна сума">
									<select name="pay" class="form-control" onchange="document.forms.cartFilter.submit()">
                        			<option value="all">Всі</option>
                        			<option value="null" <?=!empty($_GET['pay']) && $_GET['pay'] == 'null' ? 'selected' : ''?>>Не оплачено</option>
                        			<option value="part" <?=!empty($_GET['pay']) && $_GET['pay'] == 'part' ? 'selected' : ''?>>Часткова оплата</option>
                        			<option value="full" <?=!empty($_GET['pay']) && $_GET['pay'] == 'full' ? 'selected' : ''?>>Повна оплата</option>
                        		</select>
								</th>
                            </tr>
                        </thead>
                        <tbody>
						<?php if(!empty($carts)){ $activeDay = false;
							foreach($carts as $cart){ 
								$day = date('d.m.Y', $cart->date_add);
								if($activeDay != $day)
								{
									echo "<tr><th colspan=5>{$day}</th></tr>";
									$activeDay = $day;
								} ?>
						<tr class="<?=$cart->status_color?>">
							<td title="<?= date('d.m.Y H:i', $cart->date_add)?>">
								<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$cart->id?>" class="btn btn-<?=$cart->status_color?> btn-sm pull-left m-r-10">Замовлення #<?=$cart->id?></a>	<?= date('H:i', $cart->date_add)?> <br>
								<?= ($cart->manager) ? 'Менеджер: <a href="'.SITE_URL.'admin/wl_users/'.$cart->manager_email.'">#'.$cart->manager.'. '.$cart->manager_name.'</a>' : '' ?>
							</td>
							<td><strong><?= $cart->status_name?></strong> <?= $cart->date_edit > 0 ? '<br>'.date('d.m.Y H:i', $cart->date_edit) : '' ?></td>
							<td><?php if(!empty($cart->products))
                            foreach ($cart->products as $product) {
                            	if(empty($product->info))
                            		continue;
                            	if($product->info->photo && !empty($product->info->admin_photo)) { ?>
					    			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$cart->id?>" class="left">
					    				<img src="<?=IMG_PATH?><?=(isset($product->info->cart_photo)) ? $product->info->cart_photo : $product->info->admin_photo ?>" alt="<?=$this->text('Фото'). ' '. $product->info->name ?>" width="90">
					    			</a>
				    			<?php } if(!empty($product->info->article)) { ?>
					    			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$cart->id?>" target="_blank"><?= $product->info->article_show ?? $product->info->article ?></a> <br>
					    		<?php } 
	    						echo '<strong>'.$product->info->name.'</strong>';
	    						if(!empty($product->product_options))
								{
									$product->product_options = unserialize($product->product_options);
									$opttext = '';
									foreach ($product->product_options as $option) {
										$opttext .= "{$option->name}: <strong>{$option->value_name}</strong>, ";
									}
									$opttext = substr($opttext, 0, -2);
									echo "<p>{$opttext}</p>";
								}
								else
									echo "<br>";
	    						break;
	    					} if(!empty($cart->products) && count($cart->products) > 1) { ?>
	    						<p><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$cart->id?>" class="btn btn-<?=$cart->status_color?> btn-xs">+ <?=count($cart->products) - 1?> товар</a></p>
	    					<?php } ?>
							</td>
							<td>
								<strong><?= ($cart->user_name != '') ? '<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'?user='.$cart->user.'" title="Фільтр по клієнту">#'. $cart->user.' '.$cart->user_name.'</a> <a href="'.SITE_URL.'admin/wl_users/'.$cart->user_email.'" title="Про клієнта детально"><i class="fa fa-user"></i></a>' : 'Гість'?></strong>
								<br>
								<strong><?= $cart->user_phone?></strong> <?= $cart->user_email?>
							</td>
							<td><strong title="Загальна сума"><?= $cart->totalFormat?></strong> <br>
								<?php if($cart->total <= $cart->payed)
									echo "<span class='label label-success'>Оплачено</span>";
									 elseif($cart->total > $cart->payed && $cart->payed > 0)
									echo "<span class='label label-warning'>Часткова оплата</span>";
									 elseif(empty($cart->payed))
										echo "<span class='label label-danger'>Не оплачено</span>";
								 ?>
							</td>
						</tr>
						<?php } } else { ?>
							<tr>
								<td colspan="8">Замовлення відсутні</td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					</form>
				</div>
				<?php $this->load->library('paginator');
                echo $this->paginator->get();
                $this->load->js('js/'.$_SESSION['alias']->alias.'/admin_index.js');
                ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
    var CART_ADMIN_URL = '<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'?>';
  </script>
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
	.search-row .search-col .form-control {
	    border: 1px solid #16A085;
	    margin-bottom: 20px;
	}
	thead th { position: relative }
	#clientsList {
		position: absolute;
	    left: 0;
	    top: 100%;
	    background: #fff;
	    padding: 15px;
	    box-shadow: 5px 5px 7px 0px rgba(0, 0, 0, 0.6);
	    display: none
	}
	#clientsList p { cursor: pointer }
</style>
