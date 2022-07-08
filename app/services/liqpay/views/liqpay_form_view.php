<div class="container content">
<?php

/**
* @author Ostap Matskiv
* @copyright 20.06.2017
*/

if(isset($pay) && $pay->amount > 0 && !empty($_SESSION['option']->public_key) && !empty($_SESSION['option']->private_key)) {

?>
	
	<h2 style="font-size: 43px;"><i class="fab fa-cc-visa"></i> Зачекайте хвильку..</h2>
    <h3 style="font-size: 27px;">проводиться перенаправлення на сервіс оплати</h3>
    
    <p class="reply__text">*Якщо перенаправлення не відбулось автоматично, будь ласка натисніть</p>

	<form method="POST" action="https://www.liqpay.ua/api/checkout" accept-charset="utf-8" id="pay_form" name="pay_form">
		<?php
	    	$payment = array();
	        $payment['version'] = 3;
	        $payment['public_key'] = $_SESSION['option']->public_key;
	        $payment['amount'] = $pay->amount;
	        $payment['currency'] = 'UAH';
	        $payment['description'] = $pay->details;
	        $payment['order_id'] = $pay->id;
	        $payment['action'] = 'pay';
	        $payment['language'] = 'uk';
	        if($_SESSION['option']->testPay)
	        	$payment['sandbox'] = 1;
	        $payment['server_url'] = SERVER_URL.$_SESSION['alias']->alias.'/validate/'.$pay->id;
	        $payment['result_url'] = SITE_URL.$pay->return_url;

	        $data = base64_encode( json_encode($payment) );
	        $signature = base64_encode(sha1($_SESSION['option']->private_key . $data . $_SESSION['option']->private_key, 1));

	        echo('<input type="hidden" name="data" value="'.$data.'"/>');
	        echo('<input type="hidden" name="signature" value="'.$signature.'"/>');
	    ?>
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