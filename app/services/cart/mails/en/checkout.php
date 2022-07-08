<?php

// --- cart checkout mail --- //

/* Вхідні дані
   data[
	order_id - номер замовлення
	user_name - ім'я користувача
	user_email - email користувача
	user_phone - код підтвердження
   ]
*/

$subject = 'Order #'.$data['order_id']. ' '.SITE_NAME;
$message = '<html><head><title>Order #'.$data['order_id']. ' '.SITE_NAME.'</title></head><body>
<center><img src="'.IMG_PATH.'logo.svg" width="250px"></center>
<p>Hello <b>'.$data['user_name'].'</b>!</p><p>Thank you for buying in our store. The information on your order is presented below.</p>';
$message .= '<p><a href="'.$data['link'].'">To view the order, follow this link.</a></p>';

$message .= '<h3><b>Shopper</b></h3>';
$message .= '<p><b>'.$data['user_name'].'</b><br> '.$data['user_email'].', '.$data['user_phone'].'</p>';
if($data['new_user'] && !empty($data['password']))
	$message .= '<p><u>Warning! Your password to the personal account: <b>'.$data['password'].'</b></u></p>';

if(!empty($data['delivery']))
{
	$message .= '<h3><b>Delivery</b></h3>';
	$message .= '<p>'.$data['delivery'].'</p>';
}

if(!empty($data['payment']))
{
	$message .= '<h3>Payment mechanism: <b>'.$data['payment']->name.'</b></h3>';
	if(!empty($data['payment']->info))
		$message .= '<p>'.nl2br($data['payment']->info).'</p>';
}

if(!empty($data['comment']))
{
	$message .= '<h3><b>Comment</b></h3>';
	$message .= '<p>'.$data['comment'].'</p>';
}

$message .= '<h3><b>Order:</b></h3>';
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
if ($data['subTotal'] != $data['total'])
{
	$message .= '<tr><td colspan="6" align="right">Sub total: '.$data['subTotalFormat'].'</td></tr>';
	if (!empty($data['discountTotal']))
		$message .= '<tr><td colspan="6" align="right">Discount: '.$data['discountTotalFormat'].'</td></tr>';
	if (!empty($data['shippingPriceFormat']))
		$message .= '<tr><td colspan="6" align="right">Shipping: '.$data['shippingPriceFormat'].'</td></tr>';
}
$message .= '<tr><td colspan="6" align="right">Total: '.$data['totalFormat'].'</td></tr></tbody></table>';
$message .= '<p>Best regards, administration '.SITE_NAME.'</p></body></html>';


// echo $message;
// exit;
?>