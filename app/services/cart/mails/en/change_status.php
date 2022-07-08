<?php

// --- cart manager change status. Info mail to client --- //

/* Вхідні дані
   data[
	id - номер замовлення
	user_name - ім'я користувача
	user_email - email користувача
	user_phone - код підтвердження
   ]
*/


$subject = $data['status_name'].': Order # '.$data['id'].' from '.$data['date'] . ' '.SITE_NAME;
$message = '<html><head><title>Order #'.$data['id']. ' '.SITE_NAME.'</title></head><body>
<center><img src="'.IMG_PATH.'logo.svg" width="250"></center>
<p>Hello <b>'.$data['user_name'].'</b>!</p><h1>Order #'.$data['id'].' from '.$data['date'].'</h1><p>Updated information on your order is presented below:</p>';
$message .= '<p><a href="'.$data['link'].'">To view the order, follow this link.</a></p><br>';

	$message .= '---------------------------------------------------------------';
$message .= '<h3><b>Status: </b>'.$data['status_name'].'</h3>';
if($data["comment"] != '')
	$message .= "<p><strong>{$data["comment"]}</strong></p>";
$message .= '---------------------------------------------------------------';
// $message .= '<br>';

$message .= '<h3><b>Shopper</b></h3>';
$message .= '<p><b>'.$data['user_name'].'</b><br> '.$data['user_email'].', '.$data['user_phone'].'</p>';

if(!empty($data['delivery']))
{
	$message .= '<h3><b>Delivery</b></h3>';
	$message .= '<p>'.$data['delivery'].'</p>';
}

if(!empty($data['payment']))
{
	$message .= '<h3>Payment mechanism: <b>'.$data['payment']->name.'</b></h3>';
	if(!empty($data['payment']->info) && $data['payed'] > 0)
		$message .= '<p>'.nl2br($data['payment']->info).'</p>';
	else if(!empty($data['payment']->tomail))
		$message .= '<p><strong>'.nl2br($data['payment']->tomail).'</strong></p>';
}

if(!empty($data['info']))
{
	$message .= '<h3><b>Comment</b></h3>';
	$message .= '<p>'.$data['info'].'</p>';
}

$message .= '<h3><b>Order</b></h3>';
$message .= '<table align="center" border="2" cellpadding="5" cellspacing="3" width="100%" style="border-collapse: collapse;">
                    <thead><tr><th></th><th width="10%">Sku</th><th width="65%">Product</th><th width="10%">Price</th><th width="8%">Amount</th><th width="10%">Sum</th></tr></thead><tbody>';

$i = 1;
foreach($data['products'] as $product){
    $message .=  '<tr>
                    <td>'. $i .'</td>
                    <td>'. $product->info->article_show .'</td>
                    <td>'. $product->info->name;
					if(!empty($product->product_options))
					{
						if(!is_array($product->product_options))
							$product->product_options = unserialize($product->product_options);
						foreach ($product->product_options as $option) {
							$message .= "<br>{$option->name}: <strong>{$option->value_name}</strong>";
						}
					}
    $message .= '</td>
                    <td>'. $product->price_format .'</td>
                    <td>'. $product->quantity .'</td>
                    <td>'. $product->sum_format .'</td>
                </tr>';
    $i++;
}

if (!empty($data['discount']) || !empty($data['shippingPrice'])){
	$message .= '<tr><td colspan="6" align="right">Sub total: <strong>'.$data['subTotalFormat'].'</strong></td></tr>';
	if (!empty($data['discount']))
		$message .= '<tr><td colspan="6" align="right">Discount: <strong>'.$data['discountFormat'].'</strong></td></tr>';
	if (!empty($data['shippingPrice']))
		$message .= '<tr><td colspan="6" align="right">Shipping: <strong>'.$data['shippingPriceFormat'].'</strong></td></tr>';
}
$message .= '<tr><td colspan="6" align="right">Total: <strong>'.$data['total_formatted'].'</strong></td></tr></tbody></table>';

if($data['action'] == 'new')
	$message .= "<p>For online payment through Liqpay (PrivatBank) go <a href=\"{$data['pay_link']}\">{$data['pay_link']}</a></p>";

$message .= '<p>Best regards, administration '.SITE_NAME.'</p></body></html>';

// echo $message;
// exit;
?>