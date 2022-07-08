<?php

// --- user signup mail / EN --- //

/* Вхідні дані
   data[
	name - ім'я користувача
	email - email користувача
	auth_id - код підтвердження
   ]
*/

$subject = 'Registration of a new user on the site '.SITE_NAME;
$message = '<html><head><title>Registration of a new user on the site '.SITE_NAME.'</title></head><body><p>Hello <b>'.$data['name'].'</b>!</p><p>You have successfully registered on the site '.SITE_NAME.'. To complete the registration of your account, you need to follow this link <br><b><a href="'.SITE_URL.'signup/get_confirmed?email='.$data['email'].'&code='.$data['auth_id'].'">'.SITE_URL.'signup/get_confirmed?email='.$data['email'].'&code='.$data['auth_id'].'</a></b></p><p>Or log in with your email and password and enter the following verification code: <b>'.$data['auth_id'].'</b></p><p><p>An error?</p><p>Check the validity of the link. If you are still unable to complete the password recovery, please email us: '.SITE_EMAIL.' We will try to solve your problems in the near future.</p><p>Best regards, administration '.SITE_NAME.'</p></body></html>';

// echo $message;
// exit;

?>