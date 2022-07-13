<?php
/**
 * Список бібліотек які будуть завантаження за замовчуванням
 */
$config['autoload'] = ['db', 'data'];
$config['recaptcha'] = [
    'public' => '',
    'secret' => '',
    'public_v3' => 'RECAPTCHA_PUBLIC_KEY',
    'secret_v3' => 'RECAPTCHA_SECRET_KEY'
];
$config['facebook'] = ['appId' => 'FACEBOOK_APP_ID', 'secret' => 'FACEBOOK_SECRET_KEY'];
$config['googlesignin'] = ['clientId' => 'GOOGLE_CLIENT_ID', 'secret' => 'GOOGLE_API_SECRET'];

/**
 * Параметри для з'єднання до БД
 */
$config['db'] = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'root',
    'database' => 'minakb.com.ua'
];

$config['mail'] = [
    'host' => '$MAILHOST',
    'user' => '$MAILUSER',
    'password' => '$MAILPASSWORD',
    'port' => '$MAILPORT'
];

$config['Paginator'] = [
    'ul' => 'pagination nomargin'
];
$config['video'] = [
    'width' => 737
];
