<?php

// --- user signup mail / RU --- //

/* Вхідні дані
   data[
	name - ім'я користувача
	email - email користувача
	auth_id - код підтвердження
   ]
*/

$subject = 'Регистрация нового пользователя на сайте '.SITE_NAME;
$message = '<html><head><title>Регистрация нового пользователя на сайте '.SITE_NAME.'</title></head><body><p>Здравствуйте <b>'.$data['name'].'</b>!</p><p>Вы успешно зарегистрировались на сайте '.SITE_NAME.'. Для завершения регистрации Вашего аккаунта необходимо перейти по данной ссылке <br><b><a href="'.SITE_URL.'signup/get_confirmed?email='.$data['email'].'&code='.$data['auth_id'].'">'.SITE_URL.'signup/get_confirmed?email='.$data['email'].'&code='.$data['auth_id'].'</a></b></p><p>Или войдите в систему с помощью вашего email и пароля и введите следующий код подтверждения: <b>'.$data['auth_id'].'</b></p><p><p>Возникла ошибка?</p><p>Напишите нам: '.SITE_EMAIL.' Мы попробуем ликвидировать Ваши проблемы в ближайшее время.</p><p>С наилучшими пожеланиями, администрация '.SITE_NAME.'</p></body></html>';
?>