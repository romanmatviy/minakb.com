<?php

// --- notify client mail --- //

$subject = $data['title']. ' '.SITE_NAME;
$message = '<html><head><title>'.$data['title']. ' '.SITE_NAME.'</title></head><body><p>Отримано заявку на повернення:</p>';

$message .= '<p><strong>Замовлення #'.$data['cart_id'].'</strong> </p>';
$message .= '<p>'.$data['text'].'</p>';
if(!empty($data['info']))
	$message .= '<p>'.$data['info'].'</p>';
$message .= '<p><a href="'.SITE_URL.$_SESSION['alias']->alias.'">Керувати поверненням</a></p> </body></html>';

?>