<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/order.css'?>">

<h2><?=$this->text('Оплата замовлення')?> #<?= $cart->id?> <?=$this->text('від')?> <?= date('d.m.Y H:i', $cart->date_edit)?></h2>
<p><?=$this->text('Поточний статус')?>: <strong><?= $cart->status_name ?></strong>, <?=$this->text('До оплати')?>: <strong><?= $cart->toPayFormat ?></strong></p>

<a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$cart->id?>" class="btn btn-success"><i class="fas fa-undo"></i> <?=$this->text('До замовлення')?></a>

<h4><?=$this->text('Оберіть платіжний механізм')?>:</h4>

<form action="<?= SERVER_URL?>cart/pay" method="POST">
	<input type="hidden" name="cart" value="<?=$cart->id?>">
	<?php if(!$this->userIs() && $this->data->get('key'))
		echo '<input type="hidden" name="accessKey" value="'.$this->data->get('key').'">';

	if($payments) {
        foreach ($payments as $payment) { ?>
		    <button type="submit" name="method" value="<?=$payment->id?>" class="btn btn-success w50">
		    	<?=$payment->name?>
	    		<br>
	    		<?=htmlspecialchars_decode($payment->info)?>
	    	</button>
        <?php }
    } else 
    	echo "Платіжні сервіси не підключено. Зверніться до адміністратора"; ?>
</form>

<table class="products_list">
	<thead>
		<tr>
			<th></th>
			<th><?=$this->text('Товар')?></th>
			<th><?=$this->text('Ціна')?></th>
			<th><?=$this->text('Кількість')?></th>
			<th><?=$this->text('Сума')?></th>
		</tr>
	</thead>
	<tbody>
		<?php if($cart->products) foreach($cart->products as $i => $product) { ?>
			<tr>
				<td><?=$i+1?></td>
				<td>
					<?php if($product->info->photo) { ?>
						<a href="<?=SITE_URL.$product->info->link?>" class="left">
							<img src="<?=IMG_PATH?><?=(isset($product->info->cart_photo)) ? $product->info->cart_photo : $product->info->photo ?>" alt="<?= $product->info->name ?>">
						</a>
					<?php }
					echo "<div>";
					if(!empty($product->info->article)) { ?>
						<a href="<?=SITE_URL.$product->info->link?>" class="article"><?=$this->text('Артикул:')?> <strong><?=$product->info->article_show ?? $product->info->article ?></strong></a>
					<?php } ?>
					<a href="<?=SITE_URL.$product->info->link?>" class="name"><?=$product->info->name ?></a>
					<?php if(!empty($product->product_options))
					{
						$product->product_options = unserialize($product->product_options);
						foreach ($product->product_options as $option) {
							echo "<p>{$option->name}: <strong>{$option->value_name}</strong></p>";
						}
					}
					if(!empty($product->info->options))
					{
						$myInfo = ['1-manufacturer'];
						foreach ($myInfo as $info) {
							if(isset($product->info->options[$info]) && !empty($product->info->options[$info]->value))
								echo "<p>{$product->info->options[$info]->name}: <strong>{$product->info->options[$info]->value}</strong></p>";
						}
					}
					echo "</div>"; ?>
				</td>
				<td><?=$product->price_format?></td>
				<th><?=$product->quantity?></th>
				<th><?=$product->sum_format?></th>
			</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<?php if ($cart->subTotal != $cart->total) { ?>
			<tr>
				<td colspan="5"><?=$this->text('Сума')?>: <strong><?= $cart->subTotalFormat ?></strong></td>
			</tr>
		<?php } if ($cart->discount) { ?>
			<tr>
				<td colspan="5"><?=$this->text('Знижка')?>: <strong><?= $cart->discountFormat ?></strong></td>
			</tr>
		<?php } if ($cart->shippingPrice) { ?>
			<tr>
				<td colspan="5"><?=$this->text('Доставка')?>: <strong><?= $cart->shippingPriceFormat ?></strong></td>
			</tr>
		<?php } ?>
		<tr>
			<td colspan="5"><?=$this->text('Сума')?>: <strong><?= $cart->totalFormat ?></strong></td>
		</tr>
		<?php if (!empty($cart->toPay) && $cart->toPay != $cart->total) { ?>
			<tr>
				<td colspan="5"><?=$this->text('Оплачено')?>: <strong><?= $cart->payedFormat ?></strong></td>
			</tr>
			<tr>
				<td colspan="5"><?=$this->text('До оплати')?>: <strong><?= $cart->toPayFormat ?></strong></td>
			</tr>
		<?php } ?>
	</tfoot>
</table>