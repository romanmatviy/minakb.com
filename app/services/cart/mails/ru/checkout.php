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

$subject = 'Заказ #'.$data['order_id']. ' '.SITE_NAME;
$message = '<html><head><title>Заказ #'.$data['order_id']. ' '.SITE_NAME.'</title></head><body>
<center><img src="'.IMG_PATH.'logo.svg" width="250px"></center>
<p>Здравствуйте <b>'.$data['user_name'].'</b>!</p><p>Спасибо за покупку в нашем магазине. Информацию по Вашему заказу представлено ниже.</p>';
$message .= '<p><a href="'.$data['link'].'">Чтобы посмотреть заказ, перейдите по этой ссылке.</a></p>';

$message .= '<h3><b>Покупатель</b></h3>';
$message .= '<p><b>'.$data['user_name'].'</b><br> '.$data['user_email'].', '.$data['user_phone'].'</p>';
if($data['new_user'] && !empty($data['password']))
	$message .= '<p><u>Внимание! Пароль к персональному кабинету: <b>'.$data['password'].'</b></u></p>';

if(!empty($data['delivery']))
{
	$message .= '<h3><b>Доставка</b></h3>';
	$message .= '<p>'.$data['delivery'].'</p>';
}

if(!empty($data['payment']))
{
	$message .= '<h3>Платежный механизм: <b>'.$data['payment']->name.'</b></h3>';
	if(!empty($data['payment']->info))
		$message .= '<p>'.nl2br($data['payment']->info).'</p>';
}

if(!empty($data['comment']))
{
	$message .= '<h3><b>Комментарий</b></h3>';
	$message .= '<p>'.$data['comment'].'</p>';
}

$message .= '<h3><b>Товары</b></h3>';
$message .= '<table align="center" border="2" cellpadding="5" cellspacing="3" width="100%" style="border-collapse: collapse;">
                    <thead><tr><th></th><th width="10%">Артикул</th><th width="65%">Товар</th><th width="10%">Цeна</th><th width="8%">К-ство</th><th width="10%">Сумма</th></tr></thead><tbody>';

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
	$message .= '<tr><td colspan="6" align="right">Сумма: '.$data['subTotalFormat'].'</td></tr>';
	if (!empty($data['discountTotal']))
		$message .= '<tr><td colspan="6" align="right">Скидка: '.$data['discountTotalFormat'].'</td></tr>';
	if (!empty($data['shippingPriceFormat']))
		$message .= '<tr><td colspan="6" align="right">Доставка: '.$data['shippingPriceFormat'].'</td></tr>';
}
$message .= '<tr><td colspan="6" align="right">К оплате: '.$data['totalFormat'].'</td></tr></tbody></table>';
$message .= '<p>С наилучшими пожеланиями, администрация '.SITE_NAME.'</p></body></html>';


// echo $message;
// exit;
?>