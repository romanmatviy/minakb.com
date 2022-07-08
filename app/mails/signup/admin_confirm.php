<?php

// --- user manager confirm mail --- //

/* Вхідні дані
   data[
	name - ім'я користувача
	email - email користувача
	auth_id - код підтвердження
   ]
*/
$user = $data;

$subject = "Підтвердження реєстрації {$user->name} на сайті ".SITE_NAME;
$message = '<html><head><title>Підтвердження реєстрації '.$user->name.' на сайті  '.SITE_NAME.'</title></head><body><p>Доброго дня <b>'.$user->name.'</b>!</p><p>Ваш профіль <strong>підтверджено</strong>. Дякуємо за реєстрацію!</p><p>З найкращими побажаннями, адміністрація '.SITE_NAME.'</p></body></html>';
?>