<?php if($_SESSION['cart']->initJsStyle) {
	$_SESSION['alias']->js_load[] = 'assets/sticky.min.js';
	$_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/cart.js';
	echo '<link rel="stylesheet" type="text/css" href="'.SITE_URL.'style/'.$_SESSION['alias']->alias.'/cart.css">';
	$_SESSION['cart']->initJsStyle = false;
} ?>

<main id="cart" class="d-flex w100 m-column" data-sticky-container>
	<div class="w75-5 m100">
		<h1><?=$_SESSION['alias']->name?></h1>

		<div id="cart_notify" class="alert alert-danger hide">
			<p><?=$this->text('Корзина пуста')?></p>
		</div>

		<?php if(!empty($_SESSION['notify']->errors)) { ?>
			<div class="col-md-12">
			   <div class="alert alert-success">
			        <span class="close"><i class="fas fa-times"></i></span>
			        <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Помилка!')?></h4>
			        <p><?=$_SESSION['notify']->errors?></p>
			    </div>
			</div>
		<?php }
		if(!empty($_SESSION['notify']->success)) { ?>
			<div class="alert alert-success">
			    <span class="close"><i class="fas fa-times"></i></span>
			    <p><?=$_SESSION['notify']->success?></p>
			</div>
		<?php } unset($_SESSION['notify']); ?>

		<?php $actions = true; if($products) require_once '__cart_products_list2.php'; ?>

		<div class="alert alert-warning <?=$products ? 'hide' : ''?> emptyCart">
			<p><?=$this->text('Корзина пуста')?></p>
		</div>
	</div>
	<div class="w25 m100">
		<?php if(!$this->userIs()) { ?>
 			<h4><?=$this->text('Вже купували?')?></h4>
 			<p><?=$this->text('Увійдіть - це заощадить Ваш час')?></p>
			<form action="<?=SITE_URL?>login" method="POST" id="formLogin">
				<p class="message"></p>
				<input type="text" name="email_phone" placeholder="<?=$this->text('email або телефон')?>*" value="<?=$this->data->re_post('email_phone')?>">
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
				<?php } ?>
			</form>
		<?php } ?>
		<div class="price-box">
			<p class="price">
				<?=$this->text('Попередня сума')?>
				<strong class="right"><?=$products ? $subTotalFormat : '0.00'?></strong>
			</p>
			<?php if($products && $discountTotal) { ?>
				<p class="discount"><?=$this->text('Ви економите')?> <strong class="right"><?=$discountTotal ?></strong></p>
			<?php } ?>
			<a href="<?=SITE_URL.$_SESSION['alias']->alias?>/checkout" class="checkout <?=$products ? 'active' : ''?>"><?=$this->text('Оформити замовлення')?></a>
		</div>
	</div>
</main>

<div id="cart-product-delete" class="modal">
	<i class="fas fa-times right"></i>
	<h4><?=$this->text('Видалити товар з корзини?')?></h4>
	<div class="d-flex">
		<div class="product-img w33-5"></div>
		<div class="w66-5">
			<h5 class="product-name"></h5>
			<div class="product-options"></div>
			<p class="product-price"></p>
			<p> <?=$this->text('Увага! Ви завжди можете відкласти товар,')?>
				<br> <?=$this->text('замовивши його пізніше')?></p>
		</div>
	</div>
	<input type="hidden" id="action-product-key">
	<div class="d-flex actions">
		<button class="close"><?=$this->text('Скасувати')?></button>
		<button class="postpone"><i class="fas fa-history"></i> <?=$this->text('Відкласти товар')?></button>
		<button class="delete"><i class="fas fa-times"></i> <?=$this->text('Видалити з корзини')?></button>
	</div>
</div>