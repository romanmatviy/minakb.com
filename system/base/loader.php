<?php if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/base/loader.php
 *
 * Завантажуємо сторонні класи, бібліотеки тощо...
 */
 
class Loader {
	
	/**
	 * Якшо в конфігу задана секція "autoload" то завантажуємо ці бібліотеки.
	 * Якщо виклик із сервісу, то передається назва сервісу
	 */
	function __construct()
	{
		if($this->config('autoload'))
			$this->autoload( $this->config('autoload') );
		$this->model('wl_alias_model');
	}
	
	/**
	 * Завантажуємо конфіг
	 *
	 * @params $key назва індексу масива
	 *
	 * @return значення
	 */
	function config($key)
	{
		require APP_PATH.'config.php';
		if(array_key_exists($key, $config))
			return $config[$key];
		else
			return null;
	}
	
	/**
	 * Завантажуємо бібліотеки за замовчуванням
	 *
	 * @params масив назв бібліотек
	 */
	function autoload($arr)
	{
		foreach($arr as $class) {
			$class = strtolower($class);
			if($this->config($class))
				$this->$class = $this->register($class, $this->config($class));
			else
				$this->$class = $this->register($class);
		}
	}

	function authorize()
    {
        if(isset($_COOKIE['auth_id']) && empty($_SESSION['user']->id))
        {
            $this->model('wl_auth_model');
            $this->wl_auth_model->authByCookies($_COOKIE['auth_id']);
        }
    }
	
	/**
	 * Завантажуємо подання
	 *
	 * @params $view назва подання
	 * @params $data параметри
	 */	
	function view($view, $data = null, $setContentRobot = true)
	{
		if($data)
			foreach($data as $key => $value) {
				$$key = $value;
			}
		if(isset($data['value']))
    		$value = $data['value'];

		$this->init__wl_cooperation('__page_before_view');
		if(isset($_SESSION['alias']->content) && $setContentRobot && !isset($_SESSION['alias']->setContentRobot) || isset($_SESSION['alias']->setContentRobot) && $_SESSION['alias']->setContentRobot == true)
		{
			unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);
			$this->wl_alias_model->setContentRobot($data);
		}

		$view_path = APP_PATH.'views'.DIRSEP;
		if($_SESSION['amp'])
			$view_path .= 'amp'.DIRSEP;
		if($_SESSION['alias']->service)
		{
			if(isset($_SESSION['option']->uniqueDesign) && $_SESSION['option']->uniqueDesign)
				$view_path .= $_SESSION['alias']->alias.DIRSEP.$view.'.php';
			else
			{
				if($_SESSION['amp'])
					$view_path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'views'.DIRSEP.'amp'.DIRSEP.$view.'.php';
				else
					$view_path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'views'.DIRSEP.$view.'.php';
			}
		}
		else
			$view_path .= $view.'.php';
		if(file_exists($view_path))
			require $view_path;
	}
	
	/**
	 * Завантажуємо подання головної розмітки (layout)
		*
	 * @params $view_file назва подання
	 * @params $data параметри
	 */	
	function page_view($view_file = false, $data = null)
	{
		// unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);
		if($data)
			foreach($data as $key => $value) {
				$$key = $value;
			}
		if(isset($data['value']))
    		$value = $data['value'];

		$this->init__wl_cooperation('__page_before_view');
		if(!isset($_SESSION['alias']->setContentRobot) || $_SESSION['alias']->setContentRobot == true)
			$this->wl_alias_model->setContentRobot($data);
		
		$view_path = APP_PATH.'views'.DIRSEP.'page_view.php';
		if($_SESSION['amp'])
			$view_path = APP_PATH.'views'.DIRSEP.'amp'.DIRSEP.'page_view.php';
		if($_SESSION['alias']->service && $view_file)
		{
			if(isset($_SESSION['option']->uniqueDesign) && $_SESSION['option']->uniqueDesign > 0 && $view_file)
			{
				if($_SESSION['amp'])
					$view_file = APP_PATH.'views'.DIRSEP.'amp'.DIRSEP.$_SESSION['alias']->alias.DIRSEP.$view_file;
				else
					$view_file = APP_PATH.'views'.DIRSEP.$_SESSION['alias']->alias.DIRSEP.$view_file;
			}
			else
			{
				if($_SESSION['amp'])
					$view_file = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'views'.DIRSEP.'amp'.DIRSEP.$view_file;
				else
					$view_file = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'views'.DIRSEP.$view_file;
			}
		}
		if(file_exists($view_path))
			require $view_path;
	}
	
	/**
	 * Завантажуємо подання повідомлення з головною розміткою (layout)
	 *
	 * @params $data параметри
	 */	
	function notify_view($data = null)
	{
		if($data)
			foreach($data as $key => $value) {
				$$key = $value;
			}
		if(isset($data['value']))
    		$value = $data['value'];
		$this->init__wl_cooperation('__page_before_view');

		$view_path = APP_PATH.'views'.DIRSEP.'page_view.php';
		$view_file = 'notify_view';
		if(file_exists($view_path))
		{
			require $view_path;
			exit();
		}
	}

	function profile_view($sub_page = false, $data = null, $user_id = 0)
	{
		if(is_array($data))
			foreach($data as $key => $value) {
				$$key = $value;
			}
		if(isset($data['value']))
    		$value = $data['value'];
		if($sub_page)
			$sub_page .= '.php';
		if($_SESSION['alias']->service)
		{
			if (isset($_SESSION['option']->uniqueDesign) && $_SESSION['option']->uniqueDesign > 0 && $sub_page) {
				if ($_SESSION['amp'])
					$sub_page = APP_PATH . 'views' . DIRSEP . 'amp' . DIRSEP . $_SESSION['alias']->alias . DIRSEP . $sub_page;
				else
					$sub_page = APP_PATH . 'views' . DIRSEP . $_SESSION['alias']->alias . DIRSEP . $sub_page;
			}
			else {
				$sub_page = APP_PATH . 'services' . DIRSEP . $_SESSION['alias']->service . DIRSEP . 'views' . DIRSEP . $sub_page;
			}
		}
		$view_path = APP_PATH.'views'.DIRSEP.'page_view.php';
		$view_file = 'profile/index_view';
		if(empty($user))
		{
			$this->model('wl_user_model');
			$user = $this->wl_user_model->getInfo($user_id, false);
		}
		if(file_exists($view_path))
		{
			require $view_path;
			exit();
		}
	}

	function page_404($update_SiteMap = true)
	{
		$_SESSION['alias']->code = 404;
		if($update_SiteMap)
		{
			$this->library('db');
			if($_SESSION['alias']->content === NULL)
			{
				$page = $this->db->sitemap_add($_SESSION['alias']->content, $_SESSION['alias']->link, 404);
				$referer = array();
				$referer['sitemap'] = $page->id;
				$referer['from'] = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'direct link';
				$referer['date'] = time();
				$this->db->insertRow('wl_sitemap_from', $referer);
			}
			else
				$this->db->sitemap_update($_SESSION['alias']->content, 'code', 404);
		}
		header('HTTP/1.0 404 Not Found');
		if($page = $this->db->cache_get('page_404', 'html', false))
			echo $page;

		$view_path = APP_PATH.'views'.DIRSEP.'page_view.php';
		$view_file = '404_view';
		if(file_exists($view_path))
		{
			ob_start();
			require $view_path;
			$content = (string) ob_get_contents();
			$this->db->cache_add('page_404', $content, 'html', false);
			ob_end_flush();
			exit();
		}
	}

	/**
	 * Завантажуємо подання розмітки панелі керування сайтом
		*
	 * @params $view_file назва подання
	 * @params $data параметри
	 */	
	function admin_view($view_file = false, $data = null)
	{
		unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);
		if($data)
			foreach($data as $key => $value) {
				$$key = $value;
			}
		if(isset($data['value']))
    		$value = $data['value'];
		$view_path = APP_PATH.'views'.DIRSEP.'admin/admin_view.php';
		if($_SESSION['alias']->service && $view_file)
			$view_file = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'views'.DIRSEP.'admin'.DIRSEP.$view_file;
		if(file_exists($view_path))
		{
			require $view_path;
			$_SESSION['_POST'] = $_SESSION['_GET'] = NULL;
			exit();
		}
	}
	
	/**
	 * Завантажуємо моделі
	 *
	 * @params $model назва моделі
	 */	
	function model($model)
	{
		if(isset($this->$model) && is_object($this->$model))
			return true;
		$model_path = APP_PATH.'models'.DIRSEP.$model.'.php';
		if(file_exists($model_path))
		{
			require_once $model_path;
			$this->$model = new $model();
			if(is_object($this->db))
				$this->$model->db = $this->db;
			if(isset($this->data) && is_object($this->data))
				$this->$model->data = $this->data;
		}
	}
	
	
	/**
	 * Завантажуємо моделі
	 *
	 * @params $model назва моделі
	 */	
	function smodel($model)
	{
		if($_SESSION['service']->name)
		{
			if(isset($this->$model) && is_object($this->$model))
				return true;
			$model_path = APP_PATH.'services'.DIRSEP.$_SESSION['service']->name.DIRSEP.'models'.DIRSEP.$model.'.php';
			if(file_exists($model_path))
			{
				require_once $model_path;
				$this->$model = new $model();
				if(is_object($this->db))
				{
					$this->$model->db = $this->db;
					$this->$model->data = $this->data;
				}
			}
		}
		return false;
	}

	/**
	 * Завантажуємо функцію у контролері сайту або контролері сервісу згідно назви сторінки
	 *
	 * @params $alias адреса
	 * @params $method назва функції, яку викликаємо у контролері
	 * @params $data дані, що передаємо функції
	 * @params $admin позначка що відповідає за режим доступу та контролер панелі керування
	 */	
	private $wl_aliases = array();
	public function addToWlAliasesCache($alias)
	{
		if(is_object($alias))
		{
			$this->wl_aliases[$alias->id] = clone $alias;
			$this->wl_aliases[$alias->alias] = clone $alias;
		}
	}
	public function function_in_alias($alias, $method = '', $data = array(), $admin = false)
	{
		$rezult = NULL; $init__wl_alias_model = true;
		$old_alias = $_SESSION['alias']->id;
		$this->library('db');

		if(isset($this->wl_aliases[$alias]))
			$alias = $this->wl_aliases[$alias];
		else
		{
			$fromDB = true;
			$this->wl_aliases[$alias] = false;
			$key = 'alias';
			if(is_numeric($alias))
				$key = 'id';
			else
			{
				if($cache = $this->db->cache_get($alias, 'wl_aliases'))
					if(isset($cache->alias))
					{
						$fromDB = false;
	                    $alias = $cache->alias;
					}
			}
			if($fromDB)
			{
				$alias = $this->db->select('wl_aliases as a', '*', $alias, $key)
									->join('wl_services as s', 'name as service_name, table as service_table', '#a.service')
									->get('single');
			}
		}

		if(empty($_SESSION['alias-cache'][$_SESSION['alias']->id]))
			$_SESSION['alias-cache'][$_SESSION['alias']->id] = new stdClass();
		$_SESSION['alias-cache'][$_SESSION['alias']->id]->alias = clone $_SESSION['alias'];
		if(isset($_SESSION['option']))
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->options = clone $_SESSION['option'];
		else
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->options = null;
		if(isset($_SESSION['service']))
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->service = clone $_SESSION['service'];
		else
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->service = null;

		if(is_object($alias))
		{
			if($_SESSION['alias']->id == $alias->id || $_SESSION['alias']->alias == $alias->alias)
			{
				$service = $alias->alias;
				if($alias->service)
					$service = $_SESSION['alias']->service;
				if($admin)
					$service .= '_admin';
				if(get_class($this) == $service)
				{
					if(is_callable(array($this, '__construct')))
						$this->__construct();
					$rezult = false;
					if(is_callable(array($this, '_remap')))
						$rezult = $this->_remap($method, $data);
					else if(is_callable(array($this, $method)))
						$rezult = $this->$method($data);
					if(!empty($_SESSION['alias-cache'][$old_alias]->alias))
						$_SESSION['alias'] = clone $_SESSION['alias-cache'][$old_alias]->alias;
					return $rezult;
				}
			}

			if(empty($this->wl_aliases[$alias->id]) || empty($this->wl_aliases[$alias->alias]))
			{
				$this->wl_aliases[$alias->id] = clone $alias;
				$this->wl_aliases[$alias->alias] = clone $alias;
			}

			if(isset($_SESSION['alias-cache'][$alias->id]))
			{
				if($alias->id != $_SESSION['alias']->id)
				{
					$_SESSION['alias'] = $_SESSION['alias-cache'][$alias->id]->alias;
					$_SESSION['option'] = $_SESSION['alias-cache'][$alias->id]->options;
					$_SESSION['service'] = $_SESSION['alias-cache'][$alias->id]->service;
					$init__wl_alias_model = false;
				}
				
				$service = $alias->alias;
				if($alias->service)
					$service = $_SESSION['alias']->service;
				if($admin)
					$service .= '_admin';
				if(isset($this->$service) && is_object($this->$service))
				{
					$rezult = false;
					$_SESSION['alias']->alias_from = $old_alias;
					if(is_callable(array($this->$service, '__construct')))
						$this->$service->__construct();
					if(is_callable(array($this->$service, '_remap')))
						$rezult = $this->$service->_remap($method, $data);
					else if(is_callable(array($this->$service, $method)))
						$rezult = $this->$service->$method($data);
					unset($_SESSION['alias']->alias_from);
				}
			}
			else
			{
				$_SESSION['alias'] = $alias;
				$_SESSION['alias-cache'][$alias->id] = new stdClass();
				$_SESSION['alias-cache'][$alias->id]->alias = clone $alias;
				$_SESSION['alias-cache'][$alias->id]->options = null;
				$_SESSION['alias-cache'][$alias->id]->service = null;
			}

			if($rezult === NULL)
			{
				if($init__wl_alias_model)
				{
					$this->model('wl_alias_model');
					$this->wl_alias_model->init($alias->alias);
				}
				$service = $alias->alias;
				$model_path = APP_PATH.'controllers'.DIRSEP.$service.'.php';
				if($admin)
					$model_path = APP_PATH.'controllers'.DIRSEP.'admin'.DIRSEP.$service.'.php';
				if($alias->service > 0)
				{
					$service = $_SESSION['alias']->service;
					$model_path = APP_PATH.'services'.DIRSEP.$service.DIRSEP.$service.'.php';
					if($admin)
						$model_path = APP_PATH.'services'.DIRSEP.$service.DIRSEP.$service.'_admin.php';
				}
				if($admin)
					$service .= '_admin';
				
				$_SESSION['alias-cache'][$alias->id]->alias = clone $_SESSION['alias'];
				if(isset($_SESSION['option']))
					$_SESSION['alias-cache'][$alias->id]->options = clone $_SESSION['option'];
				if(isset($_SESSION['service']))
					$_SESSION['alias-cache'][$alias->id]->service = clone $_SESSION['service'];

				if(file_exists($model_path))
				{
					require_once $model_path;
					if($method != '')
					{
						$_SESSION['alias']->alias_from = $old_alias;
						$this->$service = new $service();
						if($method != '__after_edit' && is_callable(array($service, '_remap')))
							$rezult = $this->$service->_remap($method, $data);
						else if(is_callable(array($service, $method)))
							$rezult = $this->$service->$method($data);
						if($admin)
							unset($this->$service);
						unset($_SESSION['alias']->alias_from);
					}
				}
			}
			
			if($old_alias != $alias->id && isset($_SESSION['alias-cache'][$old_alias]))
			{
				$_SESSION['option'] = $_SESSION['service'] = NULL;
				$_SESSION['alias'] = clone $_SESSION['alias-cache'][$old_alias]->alias;
				if(isset($_SESSION['alias-cache'][$old_alias]->options))
					$_SESSION['option'] = clone $_SESSION['alias-cache'][$old_alias]->options;
				if(isset($_SESSION['alias-cache'][$old_alias]->service))
					$_SESSION['service'] = clone $_SESSION['alias-cache'][$old_alias]->service;
			}
		}
		return $rezult;
	}
	
	/**
	 * Завантажуємо бібліотеки
	 *
	 * @params $class назва класу/файла
	 * @params $ref посилання на обєкт
	 */
	function library($class, $ref)
	{
		if(empty($class))
			return false;
		$class = strtolower($class);
		if($this->config($class))
			$this->$class = $this->register($class, $this->config($class));
		else
			$this->$class = $this->register($class);
	}

	/**
	 * Здійснюємо перенаправлення на вказану адресу
	 *
	 * @params $link адреса перенаправлення. Якщо відсутня, то на сторінку звідки прийшов користувач
	 * @params $use_SITE_URL чи використовувати префікс адреси сайту до адреси перенаправлення
	 */
	function redirect($link = '', $use_SITE_URL = true)
	{
		if($link == '' || $link[0] == '#')
		{
			if($_SERVER['HTTP_REFERER'])
				$link = $_SERVER['HTTP_REFERER'] . $link;
			else
				$link = SITE_URL;
		}
		elseif($link == '/')
			$link = SITE_URL;
		elseif($use_SITE_URL)
			$link = SITE_URL . $link;
		header ('HTTP/1.1 303 See Other');
		header("Location: {$link}");
		exit();
	}

	function json($value = '')
	{
		header('Content-type: application/json');
		echo json_encode($value);
		$_SESSION['_POST'] = $_SESSION['_GET'] = NULL;
		exit();
	}

	function text($word = '', $alias = -1)
	{
		if($word != '')
		{
			$this->model('wl_language_model');
			return $this->wl_language_model->get($word, $alias);
		}
		return $word;
	}

	public function js($link='')
	{
		if(is_array($link))
			foreach ($link as $js) {
				if($js != '')
				{
					if(isset($_SESSION['alias']->alias_from) && $_SESSION['alias']->alias_from != $_SESSION['alias']->id)
					{
						if(!in_array($js, $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load))
							$_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load[] = $js;
					}
					else
					{
						if(!in_array($js, $_SESSION['alias']->js_load))
							$_SESSION['alias']->js_load[] = $js;
					}
				}
				
			}
		else if($link != '')
		{
			if(isset($_SESSION['alias']->alias_from) && $_SESSION['alias']->alias_from != $_SESSION['alias']->id)
			{
				if(!in_array($link, $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load))
					$_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load[] = $link;
			}
			else
			{
				if(!in_array($link, $_SESSION['alias']->js_load))
					$_SESSION['alias']->js_load[] = $link;
			}
		}
	}

	public function js_init($link='')
	{
		if(is_array($link))
			foreach ($link as $js) {
				if($js != '')
				{
					if(isset($_SESSION['alias']->alias_from) && $_SESSION['alias']->alias_from != $_SESSION['alias']->id)
					{
						if(!in_array($js, $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_init))
							$_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_init[] = $js;
					}
					else
					{
						if(!in_array($js, $_SESSION['alias']->js_init))
							$_SESSION['alias']->js_init[] = $js;
					}
				}
				
			}
		else if($link != '')
		{
			if(isset($_SESSION['alias']->alias_from) && $_SESSION['alias']->alias_from != $_SESSION['alias']->id)
			{
				if(!in_array($link, $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_init))
					$_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_init[] = $link;
			}
			else
			{
				if(!in_array($link, $_SESSION['alias']->js_init))
					$_SESSION['alias']->js_init[] = $link;
			}
		}
	}

	/**
	 * Створюємо об'єкти і зберігаємо в реєстрі
	 *
	 * @param $class назва класу
	 *
	 * @return створений об'єкт
	 */
	function register($class, $params = null)
	{
		$registry = Registry::singleton();
		if($registry->get($class) !== null)
			return $registry->get($class);
		
		$class_path = SYS_PATH.'libraries'.DIRSEP.$class.'.php';
		if(file_exists($class_path))
		{
			require $class_path;
			$obj = new $class($params);
			if(is_object($obj))
			{
				$registry->set($class, $obj);
				return $obj;
			}
		}
		return null;
	}
	
}

?>