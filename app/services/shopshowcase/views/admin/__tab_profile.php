<div class="col-md-12">
    <div class="panel panel-inverse" data-sortable-id="profile-<?=$_SESSION['alias']->alias?>">
        <div class="panel-heading">
            <h4 class="panel-title"><?=$_SESSION['alias']->name?></h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered ">
	            <thead>
	                <tr>
						<th>Замовлення</th>
						<th>Статус</th>
						<th>Товар</th>
						<th>Сума</th>
						<th>Дата</th>
					</tr>
	            </thead>
				<tbody>
					<?php if($orders) foreach($orders as $order) { ?>
					<tr>
						<td>
							<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$order->id?>" class="btn btn-<?=$order->status_color?> btn-sm pull-left m-r-10">Замовлення #<?=$order->id?></a>
						</td>
						<td>
							<strong><?= $order->status_name?></strong> <?= $order->date_edit > 0 ? 'від '.date('d.m.Y H:i', $order->date_edit) : '' ?>
							<br>1c синхронізація: <strong><?= $order->date_1c > 0 ? date('d.m.Y H:i', $order->date_1c) : 'очікується' ?></strong>
						</td>
						<td><?php if(!empty($order->products))
                            foreach ($order->products as $product) {
                            	if(empty($product->info))
                            		continue;
                            	if($product->info->photo && !empty($product->info->admin_photo)) { ?>
					    			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$order->id?>" class="left">
					    				<img src="<?=IMG_PATH?><?=(isset($product->info->cart_photo)) ? $product->info->cart_photo : $product->info->admin_photo ?>" alt="<?=$this->text('Фото'). ' '. $product->info->name ?>" width="90">
					    			</a>
				    			<?php } if(!empty($product->info->article)) { ?>
					    			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$order->id?>" target="_blank"><?= $product->info->article_show ?? $product->info->article ?></a> <br>
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
	    					} if(!empty($order->products) && count($order->products) > 1) { ?>
	    						<p><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/<?=$order->id?>" class="btn btn-<?=$order->status_color?> btn-xs">+ <?=count($order->products) - 1?> товар</a></p>
	    					<?php } ?>
							</td>
						<td><?= $order->totalFormat ?> <br>
							<?php if($order->total <= $order->payed)
								echo "<span class='label label-success'>Оплачено</span>";
								 elseif($order->total > $order->payed && $order->payed > 0)
								echo "<span class='label label-warning'>Часткова оплата</span>";
								 elseif(empty($order->payed))
									echo "<span class='label label-danger'>Не оплачено</span>";
							 ?>
						</td>
						<td><?= date('d.m.Y H:i', $order->date_add) ?></td>
					</tr>
					<?php } ?>
	            </tbody>
			</table>
			<?php
			$this->load->library('paginator');
			echo $this->paginator->get();
			?>
        </div>
    </div>
</div>