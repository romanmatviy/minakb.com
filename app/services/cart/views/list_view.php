<main>
	<h1><?=$this->text('Мої замовлення')?></h1>
	<table class="cart_list">
		<thead>
			<tr>
				<?php /*<th></th>*/ ?>
				<td><?=$this->text('Замовлення')?></td>
				<td><?=$this->text('Статус')?></td>
				<td><?=$this->text('Сума')?></td>
				<td><?=$this->text('Оплата')?></td>
				<td><?=$this->text('Доставка')?></td>
			</tr>
		</thead>
		<tbody>
			<?php if($orders) { foreach($orders as $order) { ?>
			<tr>
				<?php /*<td>
					<a class="btn" href="<?= SITE_URL.'cart/'.$order->id ?>"><i class="far fa-list-alt"></i> <?=$this->text('Перегляд')?></a>
					<?php if(false && $order->status == 1 && $showPayment) { ?>
						<a href="<?= SITE_URL.'cart/'.$order->id ?>/pay" class="btn btn-warning"><i class="fas fa-credit-card"></i> <?=$this->text('Оплатити')?></a>
					<?php } ?>
				</td>*/ ?>
				<td><a class="btn" href="<?= SITE_URL.'cart/'.$order->id ?>"><i class="far fa-list-alt"></i> #<strong><?= $order->id .'</strong> від '. date('d.m.Y H:i', $order->date_add)?></a></td>
				<td><?= $order->status_name ?></td>
				<td><?= $order->total_format ?></td>
				<td><?php if ($order->payed == 0) {
						echo "Не оплачено";
					} elseif ($order->payed >= $order->total) {
						echo $this->text("Оплачено");
					} else {
						echo $this->text("Часткова оплата")." <u>{$order->payed} грн</u>";
					} ?></td>
				<td><?=$order->shipping_name ?><?=!empty($order->ttn)?'. ТТН '.$order->ttn:''?></td>
			</tr>
			<?php } } else { ?>
				<tr>
					<td colspan="5"><?=$this->text('Замовлення відсутні')?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php $this->load->library('paginator');
        echo $this->paginator->get(); ?>
</main>

<style>
	table.cart_list { width: 100%; max-width: 100%; border-collapse: collapse; }
	table.cart_list td { background: #fff; color: #000; padding: 10px; border: none }
	table.cart_list tr {
	    border: 3px solid #f2f2f2 !important;
    	border-radius: 2px;
	}
	table.cart_list .btn { display: inline-block }
</style>