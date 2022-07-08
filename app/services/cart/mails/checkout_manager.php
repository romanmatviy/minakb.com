<?php

// --- cart checkout manager mail --- //

/* Вхідні дані
   data[
	order_id - номер замовлення
	user_name - ім'я користувача
	user_email - email користувача
	user_phone - код підтвердження
   ]
*/

$subject = 'Нове Замовлення #'.$data['order_id']. ' '.SITE_NAME;
$message = '<html><head><title>Замовлення #'.$data['order_id']. ' '.SITE_NAME.'</title></head><body>
<center><img src="'.IMG_PATH.'logo.svg" width="250"></center>
<p>Доброго дня, <b>'.$data['date'].'</b> отримано замовлення від <b>'.$data['user_name'].'</b>!</p>';

$message .= '<h3><b>Покупець</b></h3>';
$message .= '<p><b>'.$data['user_name'].'</b><br> '.$data['user_email'].', '.$data['user_phone'].'</p>';

if(!empty($data['delivery']))
{
	$message .= '<h3><b>Доставка</b></h3>';
	$message .= '<p>'.$data['delivery'].'</p>';
}

if(!empty($data['payment']))
{
	$message .= '<br><h3>Платіжний механізм: <b>'.$data['payment']->name.'</b></h3>';
	if(!empty($data['payment']->info))
		$message .= '<p>'.nl2br($data['payment']->info).'</p><br>';
}

if(!empty($data['comment']))
{
	$message .= '<h3><b>Коментар</b></h3>';
	$message .= '<p>'.$data['comment'].'</p>';
}

$message .= '<h3><b>Замовлення</b></h3>';
$message .= '<table align="center" border="2" cellpadding="5" cellspacing="3" width="100%" style="border-collapse: collapse;">
                    <thead><tr><th></th><th>Артикул</th><th width="65%">Продукт</th><th width="10%">Ціна</th><th width="10%">К-сть</th><th width="10%">Разом</th></tr></thead><tbody>';

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
                    <td>'. $product->info->price_format .'</td>
                    <td>'. $product->quantity .'</td>
                    <td>'. $product->info->sum_format .'</td>
                </tr>';
    $i++;
}
if ($data['subTotal'] != $data['total'])
{
	$message .= '<tr><td colspan="6" align="right">Сума: '.$data['subTotalFormat'].'</td></tr>';
	if (!empty($data['discountTotal']))
		$message .= '<tr><td colspan="6" align="right">Знижка: '.$data['discountTotalFormat'].'</td></tr>';
	if (!empty($data['shippingPriceFormat']))
		$message .= '<tr><td colspan="6" align="right">Доставка: '.$data['shippingPriceFormat'].'</td></tr>';
}
$message .= '<tr><td colspan="6" align="right">До оплати: '.$data['totalFormat'].'</td></tr></tbody></table>';
$message .= '<p><a href="'.$data['admin_link'].'">Щоб керувати замовленням, перейдіть по посиланню.</a></p>';

$message .= '</body></html>';

// echo $message;
// exit;

?>