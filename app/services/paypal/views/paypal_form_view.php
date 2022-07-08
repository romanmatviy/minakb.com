<div class="container content">

<?php

/**
* @author Ostap Matskiv
* @copyright 04.06.2018
*/

if(isset($pay) && $pay->amount > 0 && !empty($_SESSION['option']->receiverEmail)) {

?>

<h2 style="font-size: 43px;"><i class="fab fa-cc-visa"></i> Attends une minute..</h2>
    <h3 style="font-size: 27px;">redirige vers le service de paiement</h3>
    
    <p class="reply__text">*Si la redirection ne s'est pas effectuée automatiquement, veuillez cliquer sur</p>

<form action="<?php echo $pay->formLink; ?>" method="post" id="pay_form" name="pay_form">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="<?php echo $_SESSION['option']->receiverEmail; ?>">
	<input type="hidden" name="item_number" value="<?php echo $pay->id; ?>">
	<input type="hidden" name="item_name" value="<?php echo $pay->details; ?>">
	<input type="hidden" name="quantity" value="1">
	<input type="hidden" name="amount" value="<?php echo $pay->amount;?>">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="return" value="<?php echo SITE_URL.$pay->return_url; ?>">
	<input type="hidden" name="notify_url" value="<?php echo SERVER_URL.$_SESSION['alias']->alias.'/validate'; ?>">
	<input type="hidden" name="currency_code" value="<?php echo $_SESSION['option']->currency_code; ?>">
	<input type="hidden" name="lc" value="US">
	<input type="hidden" name="bn" value="PP-BuyNowBF">

	<button type="submit" class="btn-u btn-u-sea-shop">
	    <i class="fab fa-paypal"></i> Pour payer <?=number_format($pay->amount, 2)?> €
	</button>
</form>

	<script type="text/javascript">
	    window.onload = function() {
	        document.forms.pay_form.submit();
	    };
	</script>

<?php } else { ?>
	<p>Attention! Erreur</p>
<?php } ?>

</div>