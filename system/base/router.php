<?php if (!defined('SYS_PATH')) exit('Access denied');

/*
 * Шлях: SYS_PATH/base/router.php
 *
 * Шукає шлях до контроллеру і створює об'єкт
 */
 
class Router extends Loader {
	
	private $request;
	private $class;
	private $method;
	private $skip_alias_init = ['admin', 'app', 'assets', 'images', 'js', 'logout', 'save', 'style', 'css', 'assets', 'files', 'upload', 'api'];
	
	function __construct($req = null)
	{
		if($req != null)
		{
			$this->request = $req;
			$this->findRoute();
		}
	}
	
	/**
	 * Шукаємо шлях
	 */
	function findRoute()
	{
		parent::library('db', $this);
		$this->authorize();

		if(empty($_POST) && count($_GET) == 1 && preg_match('/[A-Z]/', $this->request))
		{
			header('HTTP/1.1 301 Moved Permanently');
		    header('Location: ' . SITE_URL.mb_strtolower($this->request));
		    exit();
		}

		$parts = explode('/', $this->request);
		$path = APP_PATH.'controllers'.DIRSEP;
		
		$_SESSION['amp'] = false;
		if(end($parts) == 'amp')
		{
			$_SESSION['amp'] = true;
			array_pop($parts);
		}
		
		if(empty($parts[0]))
			array_shift($parts);
		if(empty($parts))
			$parts[] = 'main';

		$userAdmin = false;
		if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0)
		{
			if($_SESSION['user']->admin)
				$userAdmin = true;
			if($_SESSION['user']->manager == 1 && (!isset($parts[1]) || isset($parts[1]) && in_array($parts[1], $_SESSION['user']->permissions)))
				$userAdmin = true;
		}

		$flag__skip_alias_init = false;
		if(in_array($parts[0], $this->skip_alias_init))
			$flag__skip_alias_init = true;

		if($this->request == 'wlLoadPage404')
			new Page404(!$flag__skip_alias_init);
		elseif($parts[0] == 'admin')
		{
			if(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0 && ($_SESSION['user']->admin || $_SESSION['user']->manager))
			{
				if($_SESSION['language'] && $_SESSION['language'] != $_SESSION['all_languages'][0])
					parent::redirect(SERVER_URL.$this->request, false);
				if(count($parts) == 1 || !$userAdmin)
				{
					$parts[] = 'admin';
					$_SESSION['alias'] = new stdClass();
					$_SESSION['alias']->id = 0;
        			$_SESSION['alias']->alias = 'admin';
					$_SESSION['alias']->service = false;
					$_SESSION['service'] = new stdClass();

					if(!$userAdmin)
						$parts = array('admin', 'admin', 'page_403');
				}
				elseif($userAdmin)
				{
					parent::model('wl_alias_model');
					$this->wl_alias_model->init($parts[1], $this->request);
					$this->wl_alias_model->admin_options();
				}
				else
					new Page404(false);
			}
			else
				parent::redirect('login?redirect='.$this->request);
		}
		elseif(!in_array($parts[0], $this->skip_alias_init))
		{
			parent::model('wl_alias_model');

			if(empty($_POST))
			{
				if(!$userAdmin)
				{
					parent::model('wl_statistic_model');
					if(!empty($this->wl_statistic_model->skip_ip_statistic))
					{
						parent::library('data', $this);
						if($ip = $this->data->userIP())
							if(!in_array($ip, $this->wl_statistic_model->skip_ip_statistic))
								$this->wl_statistic_model->set_views();
					}
					else
						$this->wl_statistic_model->set_views();
				}

				parent::model('wl_cache_model');
				if($this->wl_cache_model->init($this->request))
				{
					$this->wl_alias_model->initFromCache($this->wl_cache_model->page);

					if(!empty($_SESSION['option']->statictic_set_page) && !$userAdmin)
						$this->wl_statistic_model->set_page($this->wl_cache_model->page);
					$check_get = false;
					if(count($_GET) < 2)
						$check_get = true;
					else if(count($_GET) == 2 && isset($_GET['authorization']))
						$check_get = true;
					if($check_get || $this->wl_cache_model->page->code > 300)
						$this->wl_cache_model->get();
				}
				else
					$this->wl_alias_model->init($parts[0], $this->request);
			}
			else
				$this->wl_alias_model->init($parts[0], $this->request);
		}
		else
		{
			parent::model('wl_alias_model');
			$this->wl_alias_model->initEmptyAlias($parts[0], $this->request);
		}

		if($this->isService())
		{
			if($userAdmin && $parts[0] == 'admin')
			{
				$path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.$_SESSION['alias']->service.'_admin';
				$this->class .= '_admin';
				$this->method = (!isset($parts[2])) ? 'index' : $parts[2];
			}
			else
			{
				$path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.$_SESSION['alias']->service;
				$this->method = (!isset($parts[1])) ? 'index' : $parts[1];
			}
		}
		else
		{
			$_admin = false;
			if($userAdmin && $parts[0] == 'admin')
				$_admin = true;

			foreach($parts as $part)
			{
				if(is_dir($path.$part.DIRSEP))
				{
					$path .= $part.DIRSEP;
					array_shift($parts);
					continue;
				}
				
				if(is_file($path.$part.'.php'))
				{
					$this->class = $part;
					array_shift($parts);
					break;
				}
			}

			$path .= $this->class;
			if($_admin && $this->class != 'admin')
				$this->class .= '_admin';
			$this->method = (empty($parts)) ? 'index' : $parts[0];
		}

		if(is_readable($path.'.php'))
		{
			require $path.'.php';
			$this->callController();
		}
		else
			new Page404(!$flag__skip_alias_init);

		$_SESSION['_POST'] = $_SESSION['_GET'] = NULL;
		if((empty($_POST) && (isset($parts[0]) && !in_array($parts[0], $this->skip_alias_init) || $this->method == 'index')) && isset($this->wl_cache_model) && is_object($this->wl_cache_model))
		{
			if($this->wl_cache_model->page == false)
			{
				$this->wl_cache_model->page = $this->db->sitemap_add($_SESSION['alias']->content, $this->request);
				
				if($_SESSION['option']->statictic_set_page && !$userAdmin)
				{
					$this->wl_statistic_model->set_page($this->wl_cache_model->page);
					$this->wl_statistic_model->updatePageIndex();
				}
				$this->wl_cache_model->set();
			}
			else
			{
				if($_SESSION['option']->statictic_set_page && !$userAdmin)
				{
					$this->wl_statistic_model->set_page($this->wl_cache_model->page);
					$this->wl_statistic_model->updatePageIndex();
				}
				$this->wl_cache_model->set();

				if($sitemap = $this->wl_cache_model->SiteMap())
	            {
	                parent::library('SitemapGenerator');
	                foreach ($sitemap as $url) {
	                    if($url->link == 'main') $url->link = '';
	                    $this->sitemapgenerator->addUrl(SITE_URL.$url->link, date('c', $url->time), $url->changefreq, $url->priority/10);
	                }
	                try {
	                    // create sitemap
	                    $this->sitemapgenerator->createSitemap();
	                    // write sitemap as file
	                    $this->sitemapgenerator->writeSitemap();
	                    // update robots.txt file
	                    $this->sitemapgenerator->updateRobots();

	                    $this->db->updateRow('wl_options', array('value' => time()), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastgenerate'));

	                    if($_SESSION['option']->sitemap_autosent == 1)
	                    {
	                        // submit sitemaps to search engines
	                        // $result = $this->sitemapgenerator->submitSitemap("yahooAppId");
	                        $result = $this->sitemapgenerator->submitSitemap();
	                        $this->db->updateRow('wl_options', array('value' => time()), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastsent'));
	                    }
	                }
	                catch (Exception $exc) {
	                    $_SESSION['notify']->errors = $exc->getTraceAsString();
	                }
	            }
	        }
		}
	}
	
	/**
	 * Створюємо об'єкт і викликаємо метод
	 */	
	function callController()
	{
		$init_page = (!in_array($this->class, $this->skip_alias_init)) ? true : false;
		$controller = new $this->class($init_page);

		if(!empty($_SESSION['alias']->id))
		{
			$alias = new stdClass();
			$alias->id = $_SESSION['alias']->id;
			$alias->alias = $_SESSION['alias']->alias;
			$alias->table = $_SESSION['alias']->table ?? 0;
			$alias->service = $alias->service_name = $alias->service_table = false;
			if($_SESSION['alias']->service)
			{
				$alias->service = $_SESSION['service']->id;
				$alias->service_name = $_SESSION['service']->name;
				$alias->service_table = $_SESSION['service']->table;
			}
			$controller->addToWlAliasesCache($alias);
		}

		$method = $this->method;
		if(is_callable(array($controller, '_remap'))) {
			$controller->_remap($method);
		} else if(is_callable(array($controller, $method)) && $method != 'library' && $method != 'db') {
			$controller->$method();
		} else {
			$controller->load->page_404(!$init_page);
		}
	}
	
	private function isService()
	{
		if(isset($_SESSION['alias']->service) && $_SESSION['alias']->service)
		{
			$path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP;
			if(is_file($path.$_SESSION['alias']->service.'.php'))
			{
				$this->class = $_SESSION['alias']->service;
				return true;
			}
		}
		return false;
	}
	
}

class Page404 extends Controller {

	function __construct($update_SiteMap = true)
	{
		parent::__construct();
		$this->load->page_404($update_SiteMap);
	}

}

?>
