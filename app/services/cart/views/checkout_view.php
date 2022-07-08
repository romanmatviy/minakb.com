<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/cart.css'?>">
<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/checkout.css'?>">
<link rel="stylesheet" href="<?=SERVER_URL?>assets/jquery-ui/1.12.1/jquery-ui.min.css">
<?php // $this->load->js('js/'.$_SESSION['alias']->alias.'/cities.js');
$this->load->js(['assets/jquery-ui/1.12.1/jquery-ui.min.js', 'assets/sticky.min.js', 'assets/jquery.mask.min.js', 'js/'.$_SESSION['alias']->alias.'/cart.js', 'js/'.$_SESSION['alias']->alias.'/checkout.js']);

if(!isset($shippingPayAction)) {
	$shippingPayAction = 'hide';
	$shippingPriceFormat = '';
} ?>

<main id="cart-checkout" data-sticky-container>
	<a href="<?=SITE_URL.$_SESSION['alias']->alias?>" class="pull-right"><i class="fas fa-undo"></i> <?=$this->text('Редагувати замовлення')?></a>

	<h1><?=$_SESSION['alias']->name?></h1>

	<div id="cart_notify" class="alert alert-danger <?=(empty($_SESSION['notify']->errors)) ? 'hide' : ''?>">
		<span class="close"><i class="fas fa-times"></i></span>
		<p><?=$_SESSION['notify']->errors ?? ''?></p>
	</div>

	<?php if(!empty($_SESSION['notify']->success)) { ?>
		<div class="alert alert-success">
		    <span class="close"><i class="fas fa-times"></i></span>
		    <h4><?=$_SESSION['notify']->success?></h4>
		</div>
	<?php } unset($_SESSION['notify']); ?>

	<div class="d-flex w100">
		<div class="w30-5 m100">
			<?php /* ?>
			<div id="percents" data-margin-top="0"><div class="active"></div><div class="text">15%</div></div>
			<div class="info" data-margin-top="27"><?=$this->text('Статус заповнення інформації')?></div>

			<?php */ if(!$this->userIs()) { ?>
				<div id="cart-signup" class="flex">
					<div data-tab="new-buyer" class="w50 <?=(empty($_SESSION['notify']->errors)) ? 'active' : ''?>"><?=$this->text('Я новий покупець')?></div>
					<div data-tab="regular-buyer" class="w50 <?=(empty($_SESSION['notify']->errors) && !isset($_SESSION['_POST']['password'])) ? '' : 'active'?>"><?=$this->text('Я постійний покупець')?></div>
				</div>

				<div id="new-buyer" <?=(empty($_SESSION['notify']->errors)) ? '' : 'class="hide"'?>>
					<h4><?=$this->text('Покупець')?></h4>
					<div class="d-flex">
					    <div class="w50-5">
					        <input type="text" id="name" class="form-control" placeholder="<?=$this->text('Ім\'я')?>" title="<?=$this->text('Ім\'я')?>" value="<?= $this->data->re_post('name') ?>" required>
					    </div>
					    <div class="w50-5">
					        <input type="text" id="surname" class="form-control" placeholder="<?=$this->text('Прізвище')?>" title="<?=$this->text('Прізвище')?>" value="<?= $this->data->re_post('surname') ?>" required>
					    </div>
					</div>
					<div class="d-flex">
					    <div class="w50-5">
					        <input type="email" id="email" class="form-control" placeholder="email*" value="<?= $this->data->re_post('email') ?>" required>
					    </div>
					    <div class="w50-5">
					        <input type="text" id="phone" class="form-control" placeholder="<?=$this->text('Телефон', 0)?>*" value="<?=$this->data->re_post('phone')?>">
					    </div>
					</div>					
				</div>

				<div id="regular-buyer" <?=(empty($_SESSION['notify']->errors) && !isset($_SESSION['_POST']['password'])) ? 'class="hide"' : ''?>>
					<h4><?=$this->text('Вже купували?')?></h4>
		 			<p><?=$this->text('Увійдіть - це заощадить Ваш час')?></p>
					<form action="<?=SITE_URL.$_SESSION['alias']->alias?>/login" method="POST" id="formLogin">
						<p class="message"></p>
						<input type="text" name="email_phone" placeholder="<?=$this->text('email або телефон')?>*" value="<?=$this->data->re_post('email')?>">
						<input type="password" name="password" placeholder="<?=$this->text('Пароль')?>*">
						<div>
							<a href="<?=SITE_URL?>reset" class="right"><?=$this->text('Забув пароль?')?></a>
							<?php $this->load->library('recaptcha');
		                    if($this->recaptcha->public_v3) {
		                        $this->recaptcha->form_v3($this->text('Увійти'), 'formLogin');
		                    } else { 
		                        $this->recaptcha->form(); ?>
		                        <button type="submit"><?=$this->text('Увійти', 4)?></button>
		                    <?php } ?>
						</div>
					</form>
				</div>

				<?php $this->load->library('facebook'); 
				if($_SESSION['option']->facebook_initialise){ ?>
					<div class="facebook">
						<p><?=$this->text('Швидкий вхід:')?></p>
						<button class="facebookSignUp" onclick="return facebookSignUp()">Facebook <i class="fab fa-facebook"></i></button>
					</div>

					<script>
						window.fbAsyncInit = function() {
							
						    FB.init({
						      appId      : '<?=$this->facebook->getAppId()?>',
						      cookie     : true,
						      xfbml      : true,
						      version    : 'v2.6'
						    });
						};

						(function(d, s, id){
						    var js, fjs = d.getElementsByTagName(s)[0];
						    if (d.getElementById(id)) {return;}
						    js = d.createElement(s); js.id = id;
						    js.src = "//connect.facebook.net/en_US/sdk.js";
						    fjs.parentNode.insertBefore(js, fjs);
						}(document, 'script', 'facebook-jssdk'));
					</script>
				<?php }
			} ?>

			<form id="confirm" action="<?=SITE_URL.$_SESSION['alias']->alias?>/confirm" method="POST">
				<?php if(!$this->userIs()) { ?>
					<input type="text" name="email" value="<?=$this->data->re_post('email')?>" class="hide" required>
					<input type="text" name="phone" value="<?=$this->data->re_post('phone')?>" class="hide" required>
					<input type="text" name="name" value="<?=$this->data->re_post('name')?>" class="hide" required>
				<?php } else { ?>
					<div id="buyer">
						<h4><?=$this->text('Покупець')?></h4>
			 			<p>
				 			<?php echo $_SESSION['user']->name .'<br>'.$_SESSION['user']->email;
				 			if(empty($_SESSION['user']->phone)) { ?>
				 				</p><p><input type="text" id="phone" name="phone" value="<?=$this->data->re_post('phone')?>" placeholder="<?=$this->text('+380********* (Контактний номер)')?>" required>
				 			<?php } else echo '<br>'.$this->data->formatPhone($_SESSION['user']->phone); ?>
			 			</p>
					</div>
				<?php } if($shippings)
					require_once '__shippings_subview.php';
				if($payments) { ?>
					<h4 class="checkout-payment <?=($shippingPayAction != 'by_manager') ? '':'hide'?>"><?=$this->text('Оплата')?></h4>
					<input type="radio" name="payment_method" value="0" <?=($shippingPayAction == 'by_manager') ? 'checked':''?> class="hide">
					<div id="payments" class="checkout-payment <?=($shippingPayAction != 'by_manager') ? '':'hide'?>">
				    	<?php foreach ($payments as $payment) {
							$checked = (count($payments) == 1) ? 'checked' : '';
							// $checked = ($payments[0]->id == $payment->id && $shippingPayAction != 'by_manager') ? 'checked' : '';
							if(!empty($userShipping))
							{
								if($userShipping->payment_alias == 0 && $payment->id == $userShipping->payment_id)
									$checked = 'checked';
								else if($userShipping->payment_alias > 0 && $userShipping->payment_alias == $payment->wl_alias)
									$checked = 'checked';
							} ?>
				    		<label <?=$checked ? 'class="active"' : ''?>>
					            <input type="radio" name="payment_method" value="<?=$payment->id?>" <?=$checked?> required>
					            <?=$payment->name?>
					        </label>
					        <div class="payment-info <?=$checked ? '' : 'hide'?>" id="payment-<?=$payment->id?>">
				                <p><?=htmlspecialchars_decode($payment->info)?></p>
				            </div>
				    	<?php } ?>
					</div>
				<?php } ?>

				<h4><?=$this->text('Коментар')?></h4>
				<textarea name="comment" class="form-control" placeholder="<?=$this->text('Побажання до замовлення, наприклад щодо доставки')?>" rows="5"><?=$this->data->re_post('comment')?></textarea>

				<div class="price-box" data-margin-top="110">

					<p class="cart-subtotal <?=($discountTotal || ($bonusCodes && !empty($bonusCodes->info)) || ($shippingPayAction != 'hide')) ? '':'hide'?>">
						<?=$this->text('Сума')?>
						<strong class="right"><?=$subTotalFormat?></strong>
					</p>
					
					<?php if($bonusCodes && !empty($bonusCodes->info))
						foreach ($bonusCodes->info as $key => $discount) { ?>
						<p class="bonusCode">
							<?=$this->text('Бонус-код').': '.$key?>
							<strong class="right"><?=$discount?></strong>
						</p>
			        <?php } ?>

		        	<p class="cart-shipping <?=($shippingPayAction != 'hide') ? '':'hide'?>">
						<?=$this->text('Доставка')?>
						<strong class="right"><?=$shippingPriceFormat?></strong>
					</p>

					<p class="price">
						<?=$this->text('До оплати')?>
						<strong class="right"><?=$totalFormat?></strong>
					</p>

					<?php if($discountTotal) { ?>
						<p class="discount"><?=$this->text('Ви економите')?> <strong class="right"><?=$discountTotalFormat ?></strong></p>
					<?php } ?>

					<?php if(!empty($_SESSION['option']->dogovirOfertiLink)) { ?>
						<label id="oferta">
							<input type="checkbox" name="oferta" <?=$this->userIs() ? 'checked' : ''?> required>
							<i class="<?=$this->userIs() ? 'fas fa-check-square' : 'far fa-square'?>"></i>
							<div><?=$this->text('Я погоджуюся з')?> <a href="<?=SERVER_URL.$_SESSION['option']->dogovirOfertiLink?>" target="_blank"><?=$this->text('Договором оферти')?></a></div>
						</label>
					<?php } ?>

					<button type="submit" class="checkout" disabled><?=$this->text('Оформити замовлення')?></button>
				</div>
			</form>
		</div>

		<div class="w70-5 m-hide">
			<?php if ($bonusCodes && $bonusCodes->showForm) { ?>
				<form action="<?=SITE_URL.$_SESSION['alias']->alias?>/coupon" method="POST" class="coupon-form flex">
					<input type="text" name="code" class="w75" placeholder="<?=$this->text('Маєте купон на знижку? Введіть код купону сюди')?>" required>
					<button class="w25"><?=$this->text('Застосувати купон')?></button>
				</form>
			<?php } $actions = false;
			require_once '__cart_products_list2.php'; ?>
		</div>
	</div>
</main>