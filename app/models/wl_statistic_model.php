<?php

class wl_statistic_model {

	public $page_id = 0;
	public $skip_ip_statistic = ['::1'];

	public function set_views()
	{	
		if($this->searchBot())
			return true;

		$lastRow = $this->db->getQuery("SELECT id, day FROM `wl_statistic_views` WHERE ID = (SELECT MAX(ID) FROM `wl_statistic_views`)");
		$today = strtotime('today');

		if($lastRow && $today == $lastRow->day)
		{
			if(!isset($_COOKIE['statisticViews']))
				$update = ' `cookie` = `cookie` + 1, `unique` = `unique` + 1, `views` = `views` + 1 ';
			elseif(!isset($_SESSION['statistic']))
				$update = ' `unique` = `unique` + 1, `views` = `views` + 1 ';
			else
				$update = ' `views` = `views` + 1 ';
			
			$this->db->executeQuery("UPDATE `wl_statistic_views` SET {$update} WHERE `id` = {$lastRow->id}");
		}
		else
		{
			$data['day'] = $today;
			$data['cookie'] = isset($_COOKIE['statisticViews']) ? 0 : 1;
			$data['unique'] = 1;
			$data['views'] = 1;

			$this->db->insertRow('wl_statistic_views', $data);
		}

		setcookie('statisticViews', 'views', time() + 3600*24*31, '/');
	}

	public function set_page($page)
	{
		$today = strtotime('today');
		if(!isset($_SESSION['statistic']))
		{
			$_SESSION['statistic'] = new stdClass();
			$_SESSION['statistic']->pages = array();
		}

		if(!in_array($page->uniq_link, $_SESSION['statistic']->pages))
		{
			$_SESSION['statistic']->pages[] = $page->uniq_link;
			$this->updatePageViews($page, $today, true);
		}
		else
			$this->updatePageViews($page, $today);
	}

	private function updatePageViews($page, $today, $unique = false)
	{
		$where['alias'] = $page->alias;
		if($page->alias == 0)
			$where['content'] = $page->id;
		else
			$where['content'] = $page->content;
		if($_SESSION['language']) $where['language'] = $_SESSION['language'];
		$where['day'] = $today;

		$result = $this->db->getAllDataById('wl_statistic_pages', $where);
		if(!is_object($result))
		{
			$where['unique'] = 1;
			$where['views'] = 1;
			$this->db->insertRow('wl_statistic_pages', $where);
			$this->page_id = $this->db->getLastInsertedId();
		}
		else
		{
			$update = $unique == true ? ' `unique` = `unique` + 1, `views` = `views` + 1 ' : ' `views` = `views` + 1 ';
			$this->db->executeQuery("UPDATE `wl_statistic_pages` SET {$update} WHERE `id` = {$result->id}");
			$this->page_id = $result->id;
		}
	}

	public function updatePageIndex()
	{
		if($_SESSION['alias']->content !== NULL && $_SESSION['alias']->id > 0)
		{
			$page['alias'] = $_SESSION['alias']->id;
			$page['content'] = $_SESSION['alias']->content;
			$this->db->updateRow('wl_statistic_pages', $page, $this->page_id);
		}
	}

	public function searchBot()
	{
		if(isset($_SERVER['HTTP_USER_AGENT']))
		{
			$bots = array('Googlebot', 'Yahoo', 'Slurp', 'MSNBot', 'Teoma', 'Scooter', 'ia_archiver', 'Lycos', 'Yandex', 'StackRambler', 'Mail.Ru', 'Aport', 'WebAlta', 'bot', 'Google', 'YandexBot', 'Wget', 'AdsBot-Google-Mobile', 'adsbot');
			foreach ($bots as $bot) {
				if ( stristr($_SERVER['HTTP_USER_AGENT'], $bot) ) return true;
			}
		}
		return false;
	}
	
}

?>