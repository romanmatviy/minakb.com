<?php

//--- CMS White Lion 1.4 ---//

$https = true;
if ($https) {
    if (stripos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        $https = false;
    }
    if ($https && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
        $request = '/';
        if (isset($_SERVER['REQUEST_URI'])) {
            $request = $_SERVER['REQUEST_URI'];
        }
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $request;
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}

if (false && empty($_COOKIE['security_close'])) {
    $authenticated = false;
    $Authenticate = ['user' => 'test', 'password' => 'dinmark'];
    if (isset($_GET['authorization'])) {
        if (preg_match('/^Basic\s+(.*)$/i', $_GET['authorization'], $user_pass)) {
            list($user, $pass) = explode(':', base64_decode($user_pass[1]));
            if ($user == $Authenticate['user'] && $pass == $Authenticate['password']) {
                setcookie('security_close', 'test_dinmark', time() + 3600 * 24 * 31, '/');
                $authenticated = true;
            }
        }
    }

    if (!$authenticated) {
        header('WWW-Authenticate: Basic realm="security close"');
        header('HTTP/1.1 401 Unauthorized');
        exit('Access denied: bad login or password');
    }
}

$time_start = microtime(true);
$mem_start = memory_get_usage();

session_start();

error_reporting(E_ALL);

//Після інсталяції НЕ ЗМІНЮВАТИ!
define('WL_VERSION', '1.4');
define('SITE_EMAIL', 'info@minakb.localhost'); // Від даної пошти сайт відправляє листи
define('SYS_PASSWORD', 'f5b53bb61917'); // Сіль для кешування критичних даних (паролі)
$useWWW = false; // Автовиправлення ОСНОВНОЇ адреси (не мультомовної якщо використовується піддомен)
$multilanguage_type = 'main domain'; // Якщо false то сайт НЕ мультимовний! може бути: false, "*.domain.com.ua" (адреса по головному домену, існування піддоменів мов на роботу не впливає), 'main domain' (мультимовність site.com/en/link..)
$_SESSION['all_languages'] = ['uk', 'en']; // Список всіх  мов в масиві, перша мова - мова по замовчуванню
$_SESSION['cache'] = false; // використання кешованих даних
$images_folder = 'images';
// define('IMG_PATH', 'https://domain.com/images/'); // якщо фото потрібно брати з робочого домену

//задаєм системні константи
define('DIRSEP', DIRECTORY_SEPARATOR);
define('SYS_PATH', getcwd() . DIRSEP . 'system' . DIRSEP);
define('APP_PATH', getcwd() . DIRSEP . 'app' . DIRSEP);
define('CACHE_PATH', getcwd() . DIRSEP . 'cache' . DIRSEP);

require SYS_PATH . 'base' . DIRSEP . 'framework.php';
