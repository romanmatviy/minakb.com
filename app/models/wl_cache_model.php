<?php 

class wl_cache_model extends Loader
{

	public $page = false;
	
	public function init($link)
	{
		if($page = $this->db->select('wl_sitemap as s', '*', sha1($link), 'link_sha1')
							->join('wl_aliases as a', 'alias as alias_link, service, table as alias_table', '#s.alias')
							->join('wl_services', 'name as service_name, table as service_table', '#a.service')
							->get())
		{
			$this->page = $page;
			$this->page->uniq_link = $link;
			if($_SESSION['language'])
				$this->page->uniq_link = $_SESSION['language'].'/'.$link;
			return true;
		}
		return false;
	}

	public function get()
	{
		switch ($this->page->code) {
			case 200:
				$key = $this->db->getHTMLCacheKey($this->page->content, $this->page->alias_link);
				if($page = $this->db->cache_get($key, 'html', false))
				{
					echo $page;
                    if($_SESSION['option']->showTimeSiteGenerate)
                        $this->showTime('load from cache');
                    exit();
                }
				break;

			case 201:
				if(empty($_SESSION['user']->id))
				{
					$key = $this->db->getHTMLCacheKey($this->page->content, $this->page->alias_link);
					if($page = $this->db->cache_get($key, 'html', false))
					{
						echo $page;
	                    if($_SESSION['option']->showTimeSiteGenerate)
	                        $this->showTime('load from cache');
	                    exit();
	                }
	            }
				break;
			
			case 301:
				$referer = array();
				$referer['sitemap'] = $this->page->id;
				$referer['from'] = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'direct link';
				$referer['date'] = time();
				$this->db->insertRow('wl_sitemap_from', $referer);

				header ('HTTP/1.1 301 Moved Permanently');
				header("Location: ".SITE_URL.$this->page->data);
				exit();
				break;

			case 404:
				$referer = array();
				$referer['sitemap'] = $this->page->id;
				$referer['from'] = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : 'direct link';
				$referer['date'] = time();
				$this->db->insertRow('wl_sitemap_from', $referer);
				
				new Page404(false);
				break;
		}
		if($_SESSION['cache'] && ($this->page->code == 200 || ($this->page->code == 201 && empty($_SESSION['user']->id))))
			ob_start();
	}

	public function set()
	{
		$cache = array();

		if($_SESSION['alias']->content !== NULL && $this->page->alias != $_SESSION['alias']->id)
		{
			$cache['alias'] = $_SESSION['alias']->id;
			$cache['content'] = $_SESSION['alias']->content;
		}

		if($_SESSION['alias']->code != $this->page->code)
			$cache['code'] = $_SESSION['alias']->code;

		$check_get = false;
		if(count($_GET) < 2)
			$check_get = true;
		else if(count($_GET) == 2 && isset($_GET['authorization']))
			$check_get = true;
		if($_SESSION['cache'] && ($_SESSION['alias']->code == 200 || ($_SESSION['alias']->code == 201 && empty($_SESSION['user']->id))) && $check_get)
		{
			$content = (string) ob_get_contents();

			$key = $this->db->getHTMLCacheKey($this->page->content, $this->page->alias_link);
			$this->db->cache_add($key, $content, 'html', false);

			ob_end_flush();
		}

		if(!empty($cache))
		{
			$cache['time'] = time();
			$this->db->updateRow('wl_sitemap', $cache, $this->page->id);
		}

		if($_SESSION['option']->showTimeSiteGenerate)
			$this->showTime();
		exit;
	}

	private function showTime()
	{
		$mem_end = memory_get_usage();
		$time_end = microtime(true);
		$time = $time_end - $GLOBALS['time_start'];
		$mem = $mem_end - $GLOBALS['mem_start'];
		$mem = round($mem/1024, 5);
		if($mem > 1024)
		{
			$mem = round($mem/1024, 5);
			$mem = (string) $mem . ' Мб';
		}
		else
			$mem = (string) $mem . ' Кб';

		$after = ($_SESSION['cache']) ? 'Cache активний' : 'Cache відключено';
		echo '<hr><center>Час виконання: '.round($time, 5).' сек. Використанок памяті: '.$mem.'. Запитів до БД: '.$this->db->count_db_queries.'. '.$after.'</center>';
	}

	public function SiteMap($force = false)
	{
		$update = $_SESSION['option']->sitemap_active;
		if(!$force && $update)
		{
			if($_SESSION['option']->sitemap_lastedit + 7200 < time())
				$update = false;
			if($update && $_SESSION['option']->sitemap_lastgenerate > $_SESSION['option']->sitemap_lastedit)
				$update = false;
			$lastedit_day = mktime (1, 0, 0, date("n", $_SESSION['option']->sitemap_lastedit), date("j", $_SESSION['option']->sitemap_lastedit), date("Y", $_SESSION['option']->sitemap_lastedit));
			if($lastedit_day + 3600 * 24 < time())
				$update = false;
		}
		if($update || $force)
		{
			$where = array();
			$where['alias'] = '>0';
			$where['code'] = '!301';
			$where['+code'] = '!404';
			$where['priority'] = '>=0';
			$this->db->select('wl_sitemap', 'link, time, changefreq, priority', $where);
			return $this->db->get();
		}
		return false;
	}

}

?>