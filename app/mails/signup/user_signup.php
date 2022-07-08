<?php

// --- user signup mail --- //

/* Вхідні дані
   data[
	name - ім'я користувача
	email - email користувача
	auth_id - код підтвердження
   ]
*/

$subject = 'Реєстрація нового користувача у '.SITE_NAME;
$message = '<html><head><title>Реєстрація нового користувача на сайті '.SITE_NAME.'</title></head><body><p>Доброго дня <b>'.$data['name'].'</b>!</p><p>Ви успішно зареєструвалися  на сайті '.SITE_NAME.'. Для завершення реєстрації Вашого аккаунту необхідно перейти по даному посиланню <br><b><a href="'.SITE_URL.'signup/get_confirmed?email='.$data['email'].'&code='.$data['auth_id'].'">'.SITE_URL.'signup/get_confirmed?email='.$data['email'].'&code='.$data['auth_id'].'</a></b></p><p>Або увійдіть у систему за допомогою вашого email і паролю та введіть наступний код підтвердження: <b>'.$data['auth_id'].'</b></p><p><p>Виникла помилка?</p><p>Напишить нам: '.SITE_EMAIL.' Ми спробуємо зліквідувати Ваші проблеми найближчим часом.</p><p>З найкращими побажаннями, адміністрація '.SITE_NAME.'</p></body></html>';
?>