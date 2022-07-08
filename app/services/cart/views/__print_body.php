<center><img src="<?=IMG_PATH?>logo.png" style="width: 150px"></center>
<div class="pull-right" style="text-align: right;">
	<p><strong>вул. Любінська 92</strong></p>
	<p><strong>м. Львів, Україна, 79054</strong></p>
</div>
<p><strong><?=SITE_NAME?></strong></p>
<p><strong><?=SITE_EMAIL?>, +38 067 3141471</strong></p>

<h1>Замовлення #<?= $cart->id?> від <?= date('d.m.Y H:i', $cart->date_edit)?></h1>

<?php /* <div class="pull-right" style="text-align: right;">
	<p>Статус замовлення: <strong><?= $cart->status_name ?></strong></p>
</div> */ ?>

<table class="cartUserinfo">
	<tr>
		<td>Покупець:</td>
		<td>
			<strong><?= $cart->user_name ." (#$cart->user)" ?></strong>
			<br><?= $cart->user_email .", " . $cart->user_phone ?>
		</td>
	</tr>
	<?php if($cart->shipping_id && !empty($cart->shipping->name)) { ?>
		<tr>
			<td>Доставка: </td>
			<td><strong><?= $cart->shipping->name ?></strong>
				<?php if(!empty($cart->shipping->text))
	            		echo "<p>{$cart->shipping->text}</p>";
			        if(!empty($cart->shipping_info['recipientName']))
			        {
			            echo "<p>Отримувач: <b>{$cart->shipping_info['recipientName']}</b>";
				        if(!empty($cart->shipping_info['recipientPhone']))
				            echo ", <b>{$cart->shipping_info['recipientPhone']}</b>";
				        echo " </p>"; 
				    } ?>
			</td>
		</tr>
	<?php } ?>
</table>

<div class="table-responsive" >
	<table class="table table-striped table-bordered nowrap" width="100%">
		<thead>
			<tr>
				<th>#</th>
				<?php if(!empty($cart->products[0]->info->article)) { ?>
	    			<th>Артикул</th>
	    		<?php } ?>
				<?php /* <th>Виробник</th> */ ?>
				<th>Товар</th>
				<?php if($cart->products && $cart->products[0]->storage_invoice) { ?>
					<th><?=$this->text('Склад')?></th>
				<?php } ?>
				<th width="80px">Ціна</th>
				<th width="60px">К-сть</th>
				<th width="80px">Сума</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1; if($cart->products) foreach($cart->products as $product) {?>
			<tr>
				<td><?=$i++?></td>
				<?php if(!empty($product->info->article)) { ?>
					<td><?= $product->info->article_show ?></td>
				<?php } ?>
				<?php /* <td><?=$product->info->options['1-manufacturer']->value->name?></td> */ ?>
				<td><?php if(!empty($product->info))
				echo '<strong>'.$product->info->name.'</strong>';
    			if(!empty($product->product_options))
				{
					echo "<hr style='margin:2px'>";
					$i = 0;
					$product->product_options = unserialize($product->product_options);
					foreach ($product->product_options as $key => $value) {
						if($i++ > 0)
							echo "<br>";
						echo "{$key}: <strong>{$value}</strong>";
					}
				} ?></td>
				<?php if($product->storage_invoice) { ?>
					<td><?=$product->storage->storage_name?></td>
				<?php } ?>
				<td><?= $product->price_format ?></td>
				<td><?= $product->quantity ?></td>
				<td><?= $product->sum_format ?></td>
			</tr>
			<?php } 
			$cols = 7;
			if(!empty($cart->products[0]->info->article))
				$cols++;
			?>
			<?php if ($cart->subTotal != $cart->total) { ?>
				<tr>
					<td colspan="<?=$cols?>" style="text-align: right;">
						<?=$this->text('Сума')?>: <strong><?= $cart->subTotalFormat ?></strong>
					</td>
				</tr>
			<?php } if ($cart->discount) { ?>
				<tr>
					<td colspan="<?=$cols?>" style="text-align: right;">
						<?=$this->text('Знижка')?>: <strong><?= $cart->discountFormat ?></strong>
					</td>
				</tr>
			<?php } if ($cart->shippingPrice) { ?>
				<tr>
					<td colspan="<?=$cols?>" style="text-align: right;">
						<?=$this->text('Доставка')?>: <strong><?= $cart->shippingPriceFormat ?></strong>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="<?=$cols?>" style="text-align: right;">
					<?=$this->text('До сплати')?>: <strong><?= $cart->totalFormat ?></strong>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<style>
	h1 { margin: 30px 0 20px; font-size: 30px; }
	p { margin: 0 0 5px 0;}
	table.cartUserinfo tr td { padding: 5px }
	table.table tr td:nth-child(6),
	table.table tr td:nth-child(8) { text-align: right }
	table.table tr td:nth-child(7) { text-align: center }
</style>