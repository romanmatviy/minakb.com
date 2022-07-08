<section class="mainContent clearfix">
	<div class="container" id="pb_payparts">
		<h1><?=$_SESSION['alias']->name?></h1>
		<div class="row">
			<div class="col-md-6">
				<form action="<?=SITE_URL.$_SESSION['alias']->alias?>/init" method="POST" onsubmit="start_pb_init()">
					<input type="hidden" name="cart_id" value="<?=$cart->wl_alias.'-'.$cart->id?>">

					<p><?=$this->text('Загальна сума замовлення')?>: <strong><?=$cart->total?> грн</strong></p>
					<?php if($cart->payed > 0 && $cart->payed < $cart->total) { ?>
						<p><?=$this->text('Оплачено на даний момент')?>: <strong><?=$cart->payed?> грн</strong></p>
						<p><?=$this->text('Залишок до оплати')?>: <strong><?=$cart->total - $cart->payed?> грн</strong></p>
					<?php } ?>

					<p><?=$this->text('На скільки платежів бажаєте розбити оплату?')?></p>
					<select name="parts_count" class="form-control">
						<?php for ($i=2; $i < 12; $i++) { 
							echo "<option value='{$i}'>{$i}</option>";
						} ?>
					</select>

					<button type="submit" class="btn btn-success"><?=$this->text('Перейти на сторону банку для продовження оплати')?></button>
				</form>
			</div>
		</div>
	</div>
</section>

<script>
	function start_pb_init(btn) {
		$('#pb_payparts select').attr('readonly', 'true');
		$('#pb_payparts button').html('<img src="/style/images/icon-loading.gif" width="75">');
	}
</script>

<style>
	#pb_payparts p {
		margin: 15px;
		font-size: 17px;
	}
</style>