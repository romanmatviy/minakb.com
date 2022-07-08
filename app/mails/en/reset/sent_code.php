<?php

// --- user reset mail / EN --- //

/* Вхідні дані
   data[
	id - ід користувача
	name - ім'я користувача
	reset_key - код відновлення
	reset_expires - дата дії коду відновлення
   ]
*/

$from_name = 'Administration '.SITE_NAME;
// $from_mail = SITE_EMAIL;
$subject = 'Password recovery in the system '.SITE_NAME;
$message = '<html><head><title>Password recovery in the system '.SITE_NAME.'</title></head><body><p>Hello <b>'.$data['name'].'</b>!</p><p>At '.date("Y.n.d H:i:s").' You have received a password reset notification in your name. If you did not send us your data, just ignore this message, otherwise to reset your password and log in, follow the link below: </p><a href = "'.SITE_URL.'reset/go?id='.$data['id'].'&code='.$data['reset_key'].'">'.SITE_URL.'reset/go?id='.$data['id'].'&code='.$data['reset_key'].'</a>; The link is valid until <b><i>'.date("Y.n.d H:i:s", $data['reset_expires']).'.</i></b><p><p>An error?</p><p>Check the validity of the link. If you are still unable to complete the password recovery, please email us: '.SITE_EMAIL.' We will try to solve your problems in the near future.</p><p>Best regards, administration '.SITE_NAME.'</p></body></html>';


// echo $message;
// exit;
?>