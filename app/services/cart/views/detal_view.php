<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/order.css'?>">
<?php if(!empty($echoContainer)) echo "<div class='container content'>"; ?>

<?php if(!empty($_SESSION['notify']->errors)) { ?>
   <div class="alert alert-danger">
        <span class="close" data-dismiss="alert">×</span>
        <h4><i class="fas fa-exclamation-triangle"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Помилка!', 0)?></h4>
        <p><?=$_SESSION['notify']->errors?></p>
    </div>
<?php } elseif(!empty($_SESSION['notify']->success)) { ?>
    <div class="alert alert-success">
        <span class="close" data-dismiss="alert">×</span>
        <h4><i class="fas fa-check"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Успіх!', 0)?></h4>
        <p><?=$_SESSION['notify']->success?></p>
    </div>
<?php } unset($_SESSION['notify']); 

$accessKey = '';
if(!$this->userIs() && $this->data->get('key'))
	$accessKey = '?key='.$this->data->get('key');
?>

<main id="cart">
<h1><?=$this->text('Замовлення')?> #<?= $cart->id?> <?=$this->text('від')?> <?= date('d.m.Y H:i', $cart->date_edit)?></h1>
<p><?=$this->text('Статус замовлення')?>: <strong><?= $cart->status_name ?></strong>. <?=$this->text('Статус оплати')?>: <strong>
					<?php if ($cart->payed == 0) {
						echo "Не оплачено";
					} elseif ($cart->payed >= $cart->total) {
						echo $this->text("Оплачено");
					} else {
						echo $this->text("Часткова оплата")." <u>{$cart->payedFormat}</u>";
					} ?>
				</strong></p>

<a href="<?=SITE_URL.$_SESSION['alias']->alias?>/my" class="btn btn-success"><i class="fas fa-undo"></i> <?=$this->text('До всіх замовлень')?></a>
<a href="<?=SITE_URL?>cart/<?= $cart->id?>/print<?=$accessKey?>" class="btn btn-danger" target="_blank"><i class="fas fa-print"></i> <?=$this->text('Друкувати')?></a>
<?php if($showPayment) { ?>
	<a href="<?=SITE_URL?>cart/<?= $cart->id?>/pay<?=$accessKey?>" class="btn btn-warning"><i class="fas fa-credit-card"></i> <?=$this->text('Оплатити')?></a>
<?php } 
if(!$showPayment && $cart->action == 'closed') {
	$lastday = time() - 86400*14;
	if($cart->date_edit > $lastday)
		if($returns = $this->db->select('wl_aliases_cooperation as c', '', ['alias1' => $_SESSION['alias']->id, 'type' => 'returns'])
									->join('wl_aliases', 'alias, admin_ico as ico', '#c.alias2')
									->limit(1)->get()) { ?>
	    	<a href="<?=SITE_URL.$returns->alias.'?cart='.$cart->id?>" class="btn btn-info"><i class="fas <?=$returns->ico?>"></i> <?=$this->text('Повернення')?></a>
<?php } } ?>

<div class="table__cart_products_list2 w100">
	<div class="thead">
		<div class="th photo m-hide"><?=$this->text('Фото')?></div>
		<div class="th name"><?=$this->text('Назва')?></div>
		<div class="th price"><?=$this->text('Ціна')?></div>
		<div class="th amount"><?=$this->text('К-сть')?></div>
		<div class="th sum"><?=$this->text('Сума')?></div>
	</div>
	<?php if($cart->products) foreach($cart->products as $i => $product) { ?>
	<div class="tr">
		<div class="td photo m-hide"><a href="<?=SITE_URL.$product->info->link?>">
			<?php if($product->info->photo) { ?>
				<img src="<?=IMG_PATH?><?=$product->info->cart_photo ?? $product->info->admin_photo ?>" alt="<?=$product->info->name ?>">
			<?php } else
						echo '<img src="/style/images/no_image2.png">'; ?>
			</a>
		</div>
		<div class="td name">
			<a href="<?=SITE_URL.$product->info->link?>"><?=$product->info->name ?></a>
			<p>Артикул: <strong><?=$product->info->article_show ?></strong></p>
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
						echo "<p>{$product->info->options[$info]->name}: <strong>{$product->info->options[$info]->value->name}</strong></p>";
				}
			} ?>
		</div>
		<div class="td price"><?=$product->price_format?></div>
		<div class="td amount">
			<?=$product->quantity?>
			<?php if(!empty($product->quantity_returned))
				echo "<br>Повернено: {$product->quantity_returned} од."; ?>
		</div>
		<div class="td sum">
			<?=!empty($product->sum_before_format) ? '<del>'.$product->sum_before_format.'</del><br>':''?>	
			<?=$product->sum_format?>	
		</div>
	</div>
	<?php } ?>
	<div class="tfoot">
		<?php if ($cart->subTotal != $cart->total) { ?>
			<p><?=$this->text('Сума')?>: <strong><?= $cart->subTotalFormat ?></strong></p>
		<?php } if ($cart->discount) { ?>
			<p><?=$this->text('Знижка')?>: <strong><?= $cart->discountFormat ?></strong></p>
		<?php } if ($cart->shippingPrice) { ?>
			<p><?=$this->text('Доставка')?>: <strong><?= $cart->shippingPriceFormat ?></strong></p>
		<?php } ?>
		<p class="total"><?=$this->text('До сплати')?>: <strong><?= $cart->totalFormat ?></strong></p>
	</div>
</div>

<?php /*
<table class="products_list">
	<thead>
		<tr>
			<th></th>
			<th><?=$this->text('Товар')?></th>
			<?php if($cart->products[0]->storage_invoice) { ?>
				<th><?=$this->text('Склад / Термін')?></th>
			<?php } ?>
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
				<?php if($product->storage_invoice) { ?>
					<td><?=$product->storage->storage_name?><?=!empty($product->storage->storage_time) ? ' / '.$product->storage->storage_time : ''?></td>
				<?php } ?>
				<td><?=$product->price_format?></td>
				<th><?=$product->quantity?>
					<?php if(!empty($product->quantity_returned))
	    				echo "<br>Повернено: {$product->quantity_returned} од."; ?>
				</th>
				<th>
					<?=!empty($product->sum_before_format) ? '<del>'.$product->sum_before_format.'</del><br>':''?>	
					<?=$product->sum_format?>	
				</th>
			</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<?php if ($cart->subTotal != $cart->total) { ?>
			<tr>
				<td colspan="6"><?=$this->text('Сума')?>: <strong><?= $cart->subTotalFormat ?></strong></td>
			</tr>
		<?php } if ($cart->discount) { ?>
			<tr>
				<td colspan="6"><?=$this->text('Знижка')?>: <strong><?= $cart->discountFormat ?></strong></td>
			</tr>
		<?php } if ($cart->shippingPrice) { ?>
			<tr>
				<td colspan="6"><?=$this->text('Доставка')?>: <strong><?= $cart->shippingPriceFormat ?></strong></td>
			</tr>
		<?php } if (!empty($cart->toPay) && $cart->toPay != $cart->total) { ?>
			<tr>
				<td colspan="5"><?=$this->text('Оплачено')?>: <strong><?= $cart->payedFormat ?></strong></td>
			</tr>
			<tr>
				<td colspan="5"><?=$this->text('До оплати')?>: <strong><?= $cart->toPayFormat ?></strong></td>
			</tr>
		<?php } ?>
	</tfoot>
</table>

<?php */ 
if($cart->shipping_id && $this->data->uri(2) != 'edit-shipping')
{
	if($cart->action == 'new')
		echo "<h4>{$this->text('Доставка')} <small style='font-size: small'><a href='/{$_SESSION['alias']->alias}/{$cart->id}/edit-shipping{$accessKey}' class='btn btn-primary'><i class=\"fas fa-pencil-alt\"></i> {$this->text('редагувати')}</a></small></h4>";
	else
		echo "<h4>{$this->text('Доставка')}</h4>";
	if(!empty($cart->shipping->name))
	{
		echo "<p><strong>{$cart->shipping->name}</strong> <br>";
		if(!empty($cart->shipping->text))
	    	echo "{$cart->shipping->text}</p>";
	    else
	    	echo "{$cart->shipping->info}</p>";
	}
	if(!empty($cart->shipping_info['recipientName']))
	{
	    echo "<p>{$this->text('Отримувач')}: <strong>{$cart->shipping_info['recipientName']}";
	    if(!empty($cart->shipping_info['recipientPhone']))
	    	echo ", {$cart->shipping_info['recipientPhone']}</strong>";
		echo "</strong></p>";
	}
	if(!empty($cart->ttn))
        echo "<p>{$this->text('ТТН')}: <strong>{$cart->ttn}</strong> </p>";
}
elseif($cart->action == 'new' && (empty($cart->shipping_id) || $this->data->uri(2) == 'edit-shipping'))
{
	if($shippings = $this->cart_model->getShippings(array('active' => 1)))
	{
		if(empty($cart->shipping_id)) { ?>
		<div class="alert alert-warning">
	        <h4 style="margin: 0"><i class="fas fa-exclamation-triangle "></i> <?=$this->text('Увага! Вкажіть доставку')?></h4>
	    </div>
	    <?php }

	    if(empty($cart->shipping_id))
			$userShipping = $this->cart_model->getUserShipping($cart->user);
		else
		{
			$userShipping = new stdClass();
			$userShipping->method_id = $cart->shipping_id;
			$userShipping->info = $cart->shipping_info;
			$userShipping->country = $userShipping->city = $userShipping->department = $userShipping->address = '';
			if(!empty($userShipping->info))
				foreach ($userShipping->info as $key => $value) {
					$userShipping->$key = $value;
				}
		}
		$shippingTypeFields = [];
		$shippingWlAlias = 0;
		if($userShipping->method_id)
		{
			$shipping = $this->cart_model->getShippings(['id' => $userShipping->method_id]);
			$shippingTypeFields = $shipping->type_fields;
            $shippingWlAlias = $shipping->wl_alias;
		}
	    echo '<form action="'.SITE_URL.$_SESSION['alias']->alias.'/set__shippingToOrder" method="post" class="w50 m100" id="cart-checkout">';
	    echo '<input type="hidden" name="order_id" value="'.$cart->id.'">';
	    if(!$this->userIs() && $this->data->get('key'))
	    	echo '<input type="hidden" name="accessKey" value="'.$this->data->get('key').'">';
	    require_once '__shippings_subview.php';
	    echo "<div class='d-flex'>";
	    echo '<button type="submit" class="btn btn-success w50-5">'.$this->text('Зберегти', 0).'</button>';
	    echo '<a href="'.SITE_URL.$_SESSION['alias']->alias.'/'.$cart->id.$accessKey.'" class="btn btn-danger w50-5">'.$this->text('Скасувати', 0).'</a>';
		echo "</div> </form>";

		echo '<link rel="stylesheet" type="text/css" href="'.SERVER_URL.'style/'.$_SESSION['alias']->alias.'/cart.css">';
		echo '<link rel="stylesheet" type="text/css" href="'.SERVER_URL.'style/'.$_SESSION['alias']->alias.'/checkout.css">';
		echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">';
		$this->load->js(['assets/jquery-ui/ui/minified/jquery-ui.min.js', 'assets/jquery.mask.min.js', 'js/'.$_SESSION['alias']->alias.'/checkout.js']);
	}
}

if(!empty($cart->payment)) {
	echo "<h4>{$this->text('Оплата')}</h4>";
	echo "<p><strong>{$cart->payment->name}</strong></p>";
	echo "<p>{$cart->payment->info}</p>";
} ?>

<h4><?=$this->text('Історія замовлення')?></h4>
<table class="history">
    <thead>
    	<tr>
    		<td><?=$this->text('Дата')?></td>
	    	<td><?=$this->text('Статус')?></td>
	    	<td><?=$this->text('Додатково')?></td>
    	</tr>
    </thead>
    <tbody>
    	<tr>
            <td><?= date('d.m.Y H:i',$cart->date_add)?></td>
            <td><?=$this->text('Нове замовлення')?></td>
            <td></td>
        </tr>
    	<?php if($cart->history) foreach($cart->history as $history) if($history->show) { ?>
    	<tr>
            <td><?= date('d.m.Y H:i',$history->date)?></td>
            <td><?= $history->status_name?></td>
            <td><?= $history->comment?></td>
    	</tr>
    	<?php } ?>
    </tbody>
</table>

<!-- <pre><?php //print_r($cart) ?></pre> -->
</main>

<?php if(!empty($echoContainer)) echo "</div>"; ?>