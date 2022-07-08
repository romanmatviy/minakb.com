<?php

// --- Create account by admin from admin panel. Sent random ganarate password to user --- //

/* Вхідні дані
   data[
	name - ім'я користувача
	email - email користувача
	password - пароль відновлення
	registered - дата реєстрації в UNIX
   ]
*/

$from_name = 'Адміністрація '.SITE_NAME;
$subject = 'Реєстрація у '.SITE_NAME;
$message = '<html><head><title>Реєстрація на сайті '.SITE_NAME.'</title></head><body><p>Доброго дня <b>'.$data['name'].'</b>!</p><p>О '.date("Y.n.d H:i:s", $data['registered']).' на Ваш email '.$data['email'].' створено акаунт на сайті '.SITE_URL.'. Пароль: <b>'.$data['password'].'</b></p><p>Для входу в панель керування використовуйте наступне посилання: <a href="'.SITE_URL.'admin">'.SITE_URL.'admin</a></p><br><p>З найкращими побажаннями, адміністрація '.SITE_NAME.'</p></body></html>';


?>