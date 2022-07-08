<div class="container content">
<?php

/**
* @author Sokil
* @copyright to service 30.03.2016
* @copyright 01.02.2015
*/

if(isset($pay) && $pay->amount > 0 && !empty($_SESSION['option']->merchant) && !empty($_SESSION['option']->password)) {
	$payament = "amt={$pay->amount}&ccy=UAH&details={$pay->details}&ext_details=&pay_way=privat24&order={$pay->id}&merchant={$_SESSION['option']->merchant}";
	$signature = sha1(md5($payament.$_SESSION['option']->password));
?>
	

	<h2 style="font-size: 43px;"><img src="<?=SERVER_URL?>app/services/privat24/views/logo_privat24.png" alt="Privat24"> Зачекайте хвильку..</h2>
    <h3 style="font-size: 27px;">проводиться перенаправлення на сервіс оплати</h3>
    
    <p class="reply__text">*Якщо перенаправлення не відбулось автоматично, будь ласка натисніть</p>

	<form action="https://api.privatbank.ua/p24api/ishop" method="post" id="pay_form" name="pay_form">
		<input type="hidden" name="amt" value="<?=$pay->amount?>"/>
		<input type="hidden" name="ccy" value="UAH" />
		<input type="hidden" name="merchant" value="<?=$_SESSION['option']->merchant?>" />
		<input type="hidden" name="order" value="<?=$pay->id?>" />
		<input type="hidden" name="details" value="<?=$pay->details?>" />
		<input type="hidden" name="ext_details" value="" />
		<input type="hidden" name="pay_way" value="privat24" />
		<input type="hidden" name="return_url" value="<?=SITE_URL.$pay->return_url?>" />
		<input type="hidden" name="server_url" value="<?=SERVER_URL.'privat24/validate/'.$pay->id ?>" />
		<input type="hidden" name="signature" value="<?=$signature?>" />
		<button type="submit" class="btn-u btn-u-sea-shop">Оплатити <?=number_format($pay->amount, 2)?> грн</button>
	</form>

	<script type="text/javascript">
	    window.onload = function() {
	        document.forms.pay_form.submit();
	    };
	</script>

<?php } else { ?>
	<p>Увага! Помилка налаштування сервісу</p>
<?php } ?>

</div>