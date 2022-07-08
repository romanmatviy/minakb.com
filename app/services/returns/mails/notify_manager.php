<?php

// --- notify manager mail --- //

/* Вхідні дані
   data[
	id - номер замовлення
	user_name - ім'я користувача
	user_email - email користувача
	user_phone - телефон користувача
   ]
*/

$subject = 'Заявка на повернення №'.$data['id']. ' '.SITE_NAME;
$message = '<html><head><title>Заявка на повернення №'.$data['id']. ' '.SITE_NAME.'</title></head><body><p>Отримано заявку на повернення:</p>';

$message .= '<p><strong>Замовлення #'.$data['cart_id'].'</strong>: <br>';
$message .= '<strong>'.$data['product_article'].'</strong> '.$data['product_manufacturer'].' '.$data['product_name'].' - '.$data['quantity'].' од.</p>';
$message .= '<p>Причина повернення: <strong><i>'.$data['reason'].'</i></strong></p>';
$message .= '<p><a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'.$data['id'].'">Керувати поверненням</a></p> </body></html>';

?>