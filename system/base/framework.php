<?php  if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/base/framework.php
 *
 * Підключаємо всі необхідні файли і створюєм обєкт route
 */

if(empty($_SESSION['user'])) 
{
	$_SESSION['user'] = new stdClass();
}
$_SESSION['option'] = null;

if($_SERVER['REQUEST_URI'] == "/index.php") {
	header("Location: /", TRUE, 301);
	exit();
}

$protocol = ($https) ? 'https://' : 'http://';
$request = (empty($_GET['request'])) ? '' : $_GET['request'];
$request = trim($request, '/\\');

if($_SERVER["SERVER_NAME"] == 'localhost')
{
	$REQUEST_URI = explode('/', $_SERVER["REQUEST_URI"]);
	if(isset($REQUEST_URI[1]))
	{
		define('SERVER_URL', 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/');
		define('SITE_NAME', $REQUEST_URI[1]);

		if($multilanguage_type)
		{
			if(isset($REQUEST_URI[2]) && in_array($REQUEST_URI[2], $_SESSION['all_languages']) && $REQUEST_URI[2] != $_SESSION['all_languages'][0])
			{
				$_SESSION['language'] = $REQUEST_URI[2];
				define('SITE_URL', 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/'.$REQUEST_URI[2].'/');
				define('SITE_URL_MAIN', 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/'.$REQUEST_URI[2]);
				$request = explode('/', $request);
				if($request[0] == $REQUEST_URI[2])
					array_shift($request);
				$request = implode('/', $request);
			}
			else
			{
				$_SESSION['language'] = $_SESSION['all_languages'][0];
				define('SITE_URL', 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/');
				define('SITE_URL_MAIN', 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1]);
			}
			
			if($request != '')
				$request = '/'.$request;
			for ($i = 1; $i < count($_SESSION['all_languages']); $i++) {
				define('SITE_URL_'.strtoupper($_SESSION['all_languages'][$i]), 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/'.$_SESSION['all_languages'][$i].$request);
			}
			define('SITE_URL_'.strtoupper($_SESSION['all_languages'][0]), 'http://'.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].$request);
		}
		else
		{
			define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/');
			define('SITE_URL_MAIN', $protocol.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/');
			$_SESSION['language'] = false;
		}
	}
}
else
{
	$uri = explode('.', $_SERVER["SERVER_NAME"]);

	if($multilanguage_type)
	{
		if($multilanguage_type == 'main domain')
		{
			$REQUEST_URI = explode('/', $_SERVER["REQUEST_URI"]);
			if(isset($REQUEST_URI[1]))
			{
				$r_uri = explode('?', $REQUEST_URI[1]);
				$REQUEST_URI[1] = $r_uri[0];
			}
			if(isset($REQUEST_URI[1]))
			{
				$last = substr($REQUEST_URI[1], -1, 1);
				if($last == '?')
					$REQUEST_URI[1] = substr($REQUEST_URI[1], 0, -1);
			}
			if(isset($REQUEST_URI[1]) && in_array($REQUEST_URI[1], $_SESSION['all_languages']) && $REQUEST_URI[1] != $_SESSION['all_languages'][0])
			{
				$_SESSION['language'] = $REQUEST_URI[1];
				define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1].'/');
				define('SITE_URL_MAIN', $protocol.$_SERVER["SERVER_NAME"].'/'.$REQUEST_URI[1]);
				$request = explode('/', $request);
				if($request[0] == $REQUEST_URI[1])
					array_shift($request);
				$request = implode('/', $request);
			}
			elseif(!$useWWW && $uri[0] == 'www')
			{
				array_shift($uri);
				$uri = implode(".", $uri);
				$request = '/';
				if(isset($_GET['request'])) $request .= $_GET['request'];
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: '. $protocol . $uri . $request);
				exit();
			}
			elseif($useWWW && $uri[0] != 'www')
			{
				$uri = implode(".", $uri);
				$request = '/';
				if(isset($_GET['request'])) $request .= $_GET['request'];
				header ('HTTP/1.1 301 Moved Permanently');
				header ('Location: '. $protocol . $uri . $request);
				exit();
			}
			else
			{
				$_SESSION['language'] = $_SESSION['all_languages'][0];
				define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
				define('SITE_URL_MAIN', $protocol.$_SERVER["SERVER_NAME"].'/');
			}

			define('SITE_NAME', $_SERVER["SERVER_NAME"]);
			define('SERVER_URL', $protocol.$_SERVER["SERVER_NAME"].'/');

			if(defined('SITE_NAME'))
			{
				if($request != '')
					$request = '/'.$request;
				for ($i = 1; $i < count($_SESSION['all_languages']); $i++) {
					define('SITE_URL_'.strtoupper($_SESSION['all_languages'][$i]), $protocol.SITE_NAME.'/'.$_SESSION['all_languages'][$i].$request);
				}
				define('SITE_URL_'.strtoupper($_SESSION['all_languages'][0]), $protocol.SITE_NAME.$request);
			}
		}
		elseif($multilanguage_type != '')
		{
			$multilanguage_type = explode('.', $multilanguage_type);
			if($multilanguage_type[0] == '*')
			{
				if(in_array($uri[0], $_SESSION['all_languages']) && $uri[0] != $_SESSION['all_languages'][0])
				{
					$_SESSION['language'] = $uri[0];
					define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SITE_URL_MAIN', $protocol.$_SERVER["SERVER_NAME"].'/');
					array_shift($uri);
					$uri = implode(".", $uri);
					define('SERVER_URL', $protocol.$uri.'/');
					define('SITE_NAME', $uri);
				}
				elseif($uri[0] == $_SESSION['all_languages'][0] || (!$useWWW && $uri[0] == 'www'))
				{
					array_shift($uri);
					$uri = implode(".", $uri);
					$request = '/';
					if(isset($_GET['request'])) $request .= $_GET['request'];
					header ('HTTP/1.1 301 Moved Permanently');
					header ('Location: '. $protocol . $uri . $request);
					exit();
				}
				elseif($useWWW && $uri[0] != 'www')
				{
					array_unshift($uri, 'www');
					$uri = implode(".", $uri);
					$request = '/';
					if(isset($_GET['request'])) $request .= $_GET['request'];
					header ('HTTP/1.1 301 Moved Permanently');
					header ('Location: '. $protocol . $uri . $request);
					exit();
				}
				elseif($useWWW && $uri[0] == 'www' && $uri[1] == $multilanguage_type[1])
				{
					array_shift($multilanguage_type);
					$_SESSION['language'] = $_SESSION['all_languages'][0];
					define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SITE_URL_MAIN', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SERVER_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SITE_NAME', implode('.', $multilanguage_type));
				}
				elseif(!$useWWW && $uri[0] == $multilanguage_type[1])
				{
					$_SESSION['language'] = $_SESSION['all_languages'][0];
					define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SITE_URL_MAIN', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SERVER_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
					define('SITE_NAME', $_SERVER["SERVER_NAME"]);
				}
				else
					$request = 'wlLoadPage404';
			}
			else
				exit("Невірне налаштування мультимовності 'multilanguage_type' у index.php");

			if(defined('SITE_NAME'))
			{
				if($request != '')
					$request = '/'.$request;
				for ($i = 1; $i < count($_SESSION['all_languages']); $i++) {
					define('SITE_URL_'.strtoupper($_SESSION['all_languages'][$i]), $protocol.$_SESSION['all_languages'][$i].'.'.SITE_NAME.$request);
				}
				define('SITE_URL_'.strtoupper($_SESSION['all_languages'][0]), $protocol.SITE_NAME.$request);
			}
		}
	}
	else
	{
		define('SITE_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
		define('SERVER_URL', $protocol.$_SERVER["SERVER_NAME"].'/');
		define('SITE_NAME', $_SERVER["SERVER_NAME"]);
		$_SESSION['language'] = false;
	}
}

$url = $_SERVER["REQUEST_URI"];
if(empty($_POST) && isset($url[0]) && is_int(stripos($url, '//')))
{
	$url = preg_replace("#(?<!^http:)/{2,}#i", "/", $url);
	$last = substr($url, -1, 1);
	if($last == '/')
		$url = substr($url, 0, -1);
	if($url[0] == '/')
		$url = substr($url, 1);
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '. SITE_URL.$url);
	exit;
}
if(isset($_GET['request']))
{
	$last = substr($_GET['request'], -1, 1);
	if($last == '/')
	{
		header ('HTTP/1.1 301 Moved Permanently');
		if($request != '')
		{
			if($request[0] == '/')
				$request = substr($request, 1);
			header ('Location: '. SITE_URL . $request);
		}
		else
			header ('Location: '. substr(SITE_URL, 0, -1));
		exit();
	}
}

if(!defined('IMG_PATH'))
	define('IMG_PATH', SERVER_URL.$images_folder.'/');

$request = ($request == '') ? 'main' : $request;
if($request[0] == '/')
	$request = substr($request, 1);
start_route($request);

function start_route($request)
{
	require 'registry.php';
	require 'loader.php';
	require 'controller.php';
	require 'router.php';
	
	$route = new Router($request);
}

?>