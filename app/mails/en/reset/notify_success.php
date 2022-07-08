<?php

// --- user info reset mail / EN --- //

/* Вхідні дані
   data[
	name - ім'я користувача
   ]
*/

$from_name = 'Administration '.SITE_NAME;
$subject = 'Password recovery in the system '.SITE_NAME;
$message = '<html><head><title>Password recovery in the system '.SITE_NAME.'</title></head><body><p>Hello <b>'.$data['name'].'</b>!</p><p>At '.date("Y.n.d H:i:s").' the password to your user profile on the site '.SITE_NAME.' has been changed. </p><p>This is an informational message for the security of your data. If you have not changed your password on the site, contact the administration by mail as soon as possible '.SITE_EMAIL.'. Otherwise, just ignore this letter.</p><p>Best regards, administration '.SITE_NAME.'</p></body></html>';


?>