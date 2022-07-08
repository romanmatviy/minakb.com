<?php

class wl_alias_model
{
	public $service = false;

	public function initEmptyAlias($alias, $link = '')
	{
		$_SESSION['alias'] = new stdClass();
		$_SESSION['option'] = new stdClass();
		$_SESSION['service'] = new stdClass();

		$_SESSION['alias']->alias = $alias;
		$_SESSION['alias']->link = $this->db->sanitizeString($link);
		$_SESSION['alias']->id = 0;
		$_SESSION['alias']->content = NULL;
		$_SESSION['alias']->code = 202;
		$_SESSION['alias']->service = false;
		$_SESSION['alias']->section = [];
		$_SESSION['alias']->name = $_SESSION['alias']->title = $_SESSION['alias']->breadcrumb_name = $alias;
		$_SESSION['alias']->description = $_SESSION['alias']->keywords = $_SESSION['alias']->text = $_SESSION['alias']->list = $_SESSION['alias']->meta = $_SESSION['option']->global_MetaTags = '';
		$_SESSION['alias']->files = $_SESSION['alias']->audios = $_SESSION['alias']->image = $_SESSION['alias']->images = $_SESSION['alias']->videos = false;
		$_SESSION['alias']->js_plugins = $_SESSION['alias']->js_load = $_SESSION['alias']->js_init = $_SESSION['alias']->breadcrumbs = array();

		$wl_options_0 = $this->db->cache_get('wl_options_0', 'wl_aliases');
		if($wl_options_0 === NULL)
		{
			$options_where['service'] = $options_where['alias'] = array(0);
			if($options = $this->db->getAllDataByFieldInArray('wl_options', $options_where, 'service, alias'))
				foreach($options as $opt) {
					$key = $opt->name;
					$_SESSION['option']->$key = $opt->value;
				}
			$this->db->cache_add('wl_options_0', $_SESSION['option'], 'wl_aliases');
		}
		elseif(!empty($wl_options_0))
			$_SESSION['option'] = $wl_options_0;
	}

    public function init($alias, $link = '')
    {
		$alias = $this->db->sanitizeString($alias);
		$this->initEmptyAlias($alias, $link);

		$options_where['service'] = $options_where['alias'] = array(0);

		$wl_alias = $this->db->cache_get($alias, 'wl_aliases');
		if($wl_alias === NULL)
		{
			$wl_alias = new stdClass();
			$wl_alias->key = $alias;
			$wl_alias->alias = NULL;
			$wl_alias->options = [];

			$this->db->select('wl_aliases as a', '*', $alias, 'alias');
			$this->db->join('wl_services', 'name as service_name, table as service_table', '#a.service');
			if($alias = $this->db->get('single'))
			{
				$wl_alias->alias = $alias;
				$_SESSION['alias']->id = $alias->id;
				$_SESSION['alias']->table = $alias->table;
				$_SESSION['alias']->code = 200;
				if($alias->service > 0)
				{
					$options_where['service'][] = $alias->service;
					$_SESSION['alias']->service = $alias->service_name;
					$_SESSION['service']->id = $alias->service;
					$_SESSION['service']->name = $alias->service_name;
					$_SESSION['service']->table = $alias->service_table;
				}
				$options_where['alias'][] = $alias->id;

				if(isset($_SESSION['alias-cache'][$alias->id]))
					$_SESSION['alias-cache'][$alias->id]->alias->js_load = $_SESSION['alias-cache'][$alias->id]->alias->js_init  = array();
			}

			if($options = $this->db->getAllDataByFieldInArray('wl_options', $options_where, 'service, alias'))
				foreach($options as $opt) {
					$key = $opt->name;
					$_SESSION['option']->$key = $opt->value;
					$wl_alias->options[$key] = $opt->value;
				}

			if($wl_alias->alias)
				$this->db->cache_add($wl_alias->key, $wl_alias, 'wl_aliases');
		}
		else
		{
			if($wl_alias->alias)
			{
				$_SESSION['alias']->id = $wl_alias->alias->id;
				$_SESSION['alias']->table = $wl_alias->alias->table;
				$_SESSION['alias']->code = 200;
				if($wl_alias->alias->service > 0)
				{
					$_SESSION['alias']->service = $wl_alias->alias->service_name;
					$_SESSION['service']->id = $wl_alias->alias->service;
					$_SESSION['service']->name = $wl_alias->alias->service_name;
					$_SESSION['service']->table = $wl_alias->alias->service_table;
				}

				if(isset($_SESSION['alias-cache'][$wl_alias->alias->id]))
					$_SESSION['alias-cache'][$wl_alias->alias->id]->alias->js_load = $_SESSION['alias-cache'][$wl_alias->alias->id]->alias->js_init  = array();
			}
			if($wl_alias->options)
				foreach($wl_alias->options as $key => $value) {
					$_SESSION['option']->$key = $value;
				}
		}

		if($_SESSION['alias']->id)
		{
			if(empty($_SESSION['alias-cache'][$_SESSION['alias']->id]))
				$_SESSION['alias-cache'][$_SESSION['alias']->id] = new stdClass();
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->alias = clone $_SESSION['alias'];
			if(isset($_SESSION['service']))
				$_SESSION['alias-cache'][$_SESSION['alias']->id]->service = clone $_SESSION['service'];
			else
				$_SESSION['alias-cache'][$_SESSION['alias']->id]->service = null;
			if(isset($_SESSION['option']))
				$_SESSION['alias-cache'][$_SESSION['alias']->id]->options = clone $_SESSION['option'];
			else
				$_SESSION['alias-cache'][$_SESSION['alias']->id]->options = null;
		}
		return true;
    }

    public function initFromCache($page)
    {
		$_SESSION['alias'] = new stdClass();
		$_SESSION['option'] = new stdClass();
		$_SESSION['service'] = new stdClass();

		$_SESSION['alias']->alias = $page->alias_link;
		$_SESSION['alias']->link = $page->link;
		$_SESSION['alias']->id = $page->alias;
		$_SESSION['alias']->content = $page->content;
		$_SESSION['alias']->code = $page->code;
		$_SESSION['alias']->table = $page->alias_table;
		if($page->service)
		{
			$_SESSION['alias']->service = $page->service_name;
			$_SESSION['service']->id = $page->service;
			$_SESSION['service']->name = $page->service_name;
			$_SESSION['service']->table = $page->service_table;
		}
		else
			$_SESSION['alias']->service = false;
		$_SESSION['alias']->section = [];
		$_SESSION['alias']->name = $_SESSION['alias']->title = $_SESSION['alias']->breadcrumb_name = $page->link;
		$_SESSION['alias']->description = $_SESSION['alias']->keywords = $_SESSION['alias']->text = $_SESSION['alias']->list = $_SESSION['alias']->meta = $_SESSION['alias']->global_MetaTags = '';
		$_SESSION['alias']->files = $_SESSION['alias']->audios = $_SESSION['alias']->image = $_SESSION['alias']->images = $_SESSION['alias']->videos = false;
		$_SESSION['alias']->js_plugins = $_SESSION['alias']->js_load = $_SESSION['alias']->js_init = $_SESSION['alias']->breadcrumbs = array();
		if(isset($_SESSION['alias-cache'][$_SESSION['alias']->id]))
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->alias->js_load = $_SESSION['alias-cache'][$_SESSION['alias']->id]->alias->js_init  = array();

		if(empty($_SESSION['alias-cache'][$_SESSION['alias']->id]))
			$_SESSION['alias-cache'][$_SESSION['alias']->id] = new stdClass();
		$_SESSION['alias-cache'][$_SESSION['alias']->id]->alias = clone $_SESSION['alias'];
		if(isset($_SESSION['service']))
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->service = clone $_SESSION['service'];
		else
			$_SESSION['alias-cache'][$_SESSION['alias']->id]->service = null;

		if($_SESSION['alias']->id)
		{
			if(!isset($_SESSION['alias-cache'][$page->alias]->options))
			{
				if($wl_alias = $this->db->cache_get($page->alias_link, 'wl_aliases'))
				{
					if($wl_alias->options)
						foreach($wl_alias->options as $key => $value) {
							$_SESSION['option']->$key = $value;
						}
				}
				else
				{
					$options_where['service'] = $options_where['alias'] = array(0);
					if($page->service > 0)
						$options_where['service'][] = $page->service;
					$options_where['alias'][] = $page->alias;
					if($options = $this->db->getAllDataByFieldInArray('wl_options', $options_where, 'service, alias'))
						foreach($options as $opt) {
							$key = $opt->name;
							$_SESSION['option']->$key = $opt->value;
						}
				}
				
				if(!empty($_SESSION['option']))
					$_SESSION['alias-cache'][$_SESSION['alias']->id]->options = clone $_SESSION['option'];
				else
					$_SESSION['alias-cache'][$_SESSION['alias']->id]->options = null;
			}
			else
				$_SESSION['option'] = $_SESSION['alias-cache'][$page->alias]->options;
		}
		else
		{
			if($wl_alias = $this->db->cache_get('alias_0-options', 'wl_aliases'))
			{
				if($wl_alias->options)
					foreach($wl_alias->options as $key => $value) {
						$_SESSION['option']->$key = $value;
					}
			}
			else
			{
				$wl_alias = new stdClass();
				$wl_alias->key = $wl_alias->alias = NULL;
				$wl_alias->options = [];
				$options_where['service'] = $options_where['alias'] = 0;
				$wl_alias->options = $this->db->getAllDataByFieldInArray('wl_options', $options_where, 'service, alias');
				if($wl_alias->options)
					foreach($wl_alias->options as $opt) {
						$key = $opt->name;
						$_SESSION['option']->$key = $opt->value;
						$wl_alias->options[$key] = $opt->value;
					}
				$this->db->cache_add('alias_0-options', $wl_alias, 'wl_aliases');
			}
		}
		return true;
    }

    public function setContent($content = 0, $code = 200)
    {
		if(!is_numeric($content))
			return false;
		$_SESSION['alias']->content = $content;
		$_SESSION['alias']->code = $code;

		$where = array();
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = $content;

		$get_sivafc = NULL;

		if($_SESSION['language'])
			$where['language'] = $_SESSION['language'];
		if(empty($_SESSION['alias']->breadcrumbs) && $content != 0)
		{
			$where['content'] = [$content, 0];
			if($wl_ntkd = $this->db->getAllDataByFieldInArray('wl_ntkd', $where))
				foreach ($wl_ntkd as $data) {
					if($data->content == $content)
					{
						$get_sivafc = $data->get_sivafc;

						$_SESSION['alias']->name = html_entity_decode($data->name, ENT_QUOTES);
						$_SESSION['alias']->title = html_entity_decode($data->title, ENT_QUOTES);
						$_SESSION['alias']->description = html_entity_decode($data->description, ENT_QUOTES);
						$_SESSION['alias']->keywords = html_entity_decode($data->keywords, ENT_QUOTES);
						$_SESSION['alias']->text = html_entity_decode($data->text, ENT_QUOTES);
						$_SESSION['alias']->list = html_entity_decode($data->list, ENT_QUOTES);
						$_SESSION['alias']->meta = html_entity_decode($data->meta, ENT_QUOTES);

						if($_SESSION['alias']->images)
							foreach ($_SESSION['alias']->images as $photo) {
								if($photo->title == '')
									$photo->title = $data->name;
							}
					}
					else if($data->content == 0 && empty($_SESSION['alias']->breadcrumbs))
					{
						$_SESSION['alias']->breadcrumb_name = html_entity_decode($data->name, ENT_QUOTES);
						$_SESSION['alias']->breadcrumbs = array($_SESSION['alias']->breadcrumb_name => '');
					}
				}
		}
		else
		{
			if($data = $this->db->getAllDataById('wl_ntkd', $where))
			{
				$get_sivafc = $data->get_sivafc;

				$_SESSION['alias']->name = html_entity_decode($data->name, ENT_QUOTES);
				$_SESSION['alias']->title = html_entity_decode($data->title, ENT_QUOTES);
				$_SESSION['alias']->description = html_entity_decode($data->description, ENT_QUOTES);
				$_SESSION['alias']->keywords = html_entity_decode($data->keywords, ENT_QUOTES);
				$_SESSION['alias']->text = html_entity_decode($data->text, ENT_QUOTES);
				$_SESSION['alias']->list = html_entity_decode($data->list, ENT_QUOTES);
				$_SESSION['alias']->meta = html_entity_decode($data->meta, ENT_QUOTES);

				if(!empty($_SESSION['alias']->images))
					foreach ($_SESSION['alias']->images as $photo) {
						if($photo->title == '')
							$photo->title = $data->name;
					}
			}
			if(empty($_SESSION['alias']->breadcrumbs))
			{
				if($content == 0)
				{
					$_SESSION['alias']->breadcrumb_name = $_SESSION['alias']->name;
					$_SESSION['alias']->breadcrumbs = array($_SESSION['alias']->name => '');
				}
				else
				{
					$where['content'] = 0;
					if($data = $this->db->getAllDataById('wl_ntkd', $where))
					{
						$_SESSION['alias']->breadcrumb_name = $data->name;
						$_SESSION['alias']->breadcrumbs = array($data->name => $_SESSION['alias']->alias);
					}
				}
			}
		}

		unset($where['language']);

		$get_sections = $get_images = $get_videos = $get_audios = $get_files = $get_comments = false;
		if($get_sivafc !== NULL)
		{
			if(!empty($get_sivafc))
				for ($i=0; $i < strlen($get_sivafc); $i++) { 
					switch ($get_sivafc[$i]) {
						case 's':
							$get_sections = true;
							break;
						case 'i':
							$get_images = true;
							break;
						case 'v':
							$get_videos = true;
							break;
						case 'a':
							$get_audios = true;
							break;
						case 'f':
							$get_files = true;
							break;
						case 'c':
							$get_comments = true;
							break;
					}
				}
		}
		else
			$get_sections = $get_images = $get_videos = $get_audios = $get_files = $get_comments = true;

		// тимчасово. Поки wl_comments ще не підкдлючено до _SESSION['alias']->comments
		$get_comments = false;

		if(empty($_SESSION['option']->folder))
			$get_images = false;

		if($get_images)
		{
			$this->db->select('wl_images as i', '*', $where);
			if($_SESSION['language'])
				$this->db->join('wl_media_text', 'text as title', array('type' => 'photo', 'content' => '#i.id', 'language' => $_SESSION['language']));
			$this->db->join('wl_users', 'name as user_name', '#author');
			$this->db->order('section_id, position ASC');
			$_SESSION['alias']->images = $this->db->get('array');
			if(!empty($_SESSION['alias']->images))
			{
				$sizes = $this->db->getAliasImageSizes();
				foreach ($_SESSION['alias']->images as $photo) {
					if($sizes)
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix.'_path';
							$photo->$resize_name = $_SESSION['option']->folder.'/'.$_SESSION['alias']->content.'/'.$resize->prefix.'_'.$photo->file_name;
						}
					$photo->path = $_SESSION['option']->folder.'/'.$_SESSION['alias']->content.'/'.$photo->file_name;
				}
				if(isset($_SESSION['alias']->images[0]->header_path))
					$_SESSION['alias']->image = $_SESSION['alias']->images[0]->header_path;
				else
					$_SESSION['alias']->image = $_SESSION['alias']->images[0]->path;
			}
			else
				$get_images = false;
		}

		if($get_videos)
		{
			$this->db->select('wl_video', '*', $where);
			$this->db->join('wl_users', 'name as user_name', '#author');
			$_SESSION['alias']->videos = $this->db->get('array');
			if(empty($_SESSION['alias']->videos))
				$get_videos = false;
		}

		if(!empty($_SESSION['option']->folder))
		{
			if($get_audios)
			{
				$this->db->select('wl_audio', '*', $where);
				$this->db->join('wl_users', 'name as user_name', '#author');
				$this->db->order('position ASC');
				$_SESSION['alias']->audios = $this->db->get('array');
				if(empty($_SESSION['alias']->audios))
					$get_audios = false;
			}

			if($get_files)
			{
				$this->db->select('wl_files', '*', $where);
				$this->db->join('wl_users', 'name as user_name', '#author');
				$this->db->order('position ASC');
				$_SESSION['alias']->files = $this->db->get('array');
				if(empty($_SESSION['alias']->files))
					$get_files = false;
			}
		}

		if($get_sections)
		{
			if($wl_sections = $this->db->getAllDataByFieldInArray('wl_sections', ['alias_id' => $_SESSION['alias']->id, 'content_id' => $content], 'position'))
			{
				foreach($wl_sections as $section) {
					if(($section->access == 'login' || $section->access == 'manager') && !(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0))
						continue;
					if($section->access == 'manager' && !(isset($_SESSION['user']->id) && $_SESSION['user']->id > 0 && ($_SESSION['user']->admin || $_SESSION['user']->manager)))
						continue;

					if ($_SESSION['language'] && in_array($section->type, ['text_multi', 'textarea_multi', 'images']))
					{
						$t = @unserialize($section->title);
						$v = @unserialize($section->value);
						foreach ($_SESSION['all_languages'] as $lang) {
							$section->{'title_' . $lang} = $t[$lang] ?? '';
							$section->{'value_' . $lang} = $v[$lang] ?? '';
						}
						$section->title = $t[ $_SESSION['language'] ];
						$section->value = $v[ $_SESSION['language'] ];
					}
					if($section->type == 'images')
					{
						$section->images = [];
						if(!empty($_SESSION['alias']->images))
							foreach ($_SESSION['alias']->images as $image) {
								if($image->section_id == $section->id)
									$section->images[] = clone $image;
							}
					}
					if($section->type == 'videos')
					{
						$section->videos = [];
						if(!empty($_SESSION['alias']->videos))
							foreach ($_SESSION['alias']->videos as $video) {
								if($video->section_id == $section->id)
									$section->videos[] = clone $video;
							}
					}

					if(empty($section->name))
						$section->name = 'section_'.$section->id;
					$_SESSION['alias']->section[$section->name] = clone $section;
				}
			}
			else
				$get_sections = false;
		}
		
		$url = $this->data->url();
		if(!empty($_SESSION['alias']->images) && (empty($url) || $url[0] != 'admin'))
		{
			foreach ($_SESSION['alias']->images as $i => $image) {
				if($image->section_id > 0)
					unset($_SESSION['alias']->images[$i]);
			}
			if(!empty($_SESSION['alias']->videos))
				foreach ($_SESSION['alias']->videos as $i => $video) {
					if($video->section_id > 0)
						unset($_SESSION['alias']->videos[$i]);
				}
		}

		if($get_sivafc === NULL)
		{
			$get_sivafc = '';
			if($get_sections)
				$get_sivafc .= 's';
			if($get_images)
				$get_sivafc .= 'i';
			if($get_videos)
				$get_sivafc .= 'v';
			if($get_audios)
				$get_sivafc .= 'a';
			if($get_files)
				$get_sivafc .= 'f';
			if($get_comments)
				$get_sivafc .= 'c';
			$this->db->updateRow('wl_ntkd', ['get_sivafc' => $get_sivafc], $where);
		}

		return true;
	}

	public function getVideosFromText()
	{
		$video = false;
		if(preg_match_all("#\{video-[0-9]+\}#is", $_SESSION['alias']->text, $video) > 0)
		{
			$videos = array();
			$videos_id = array();
			foreach ($video[0] as $v) {
				$id = substr($v, 7);
				$id = substr($id, 0, -1);
				$videos_id[$id] = $v;
			}
			foreach ($videos_id as $id => $text) {
				$video = $this->db->getAllDataById('wl_video', $id);
				if($video) {
					$video->replace_text = $text;
					$videos[] = $video;
				}
			}
			return $videos;
		}
		return false;
	}

    public function admin_options()
    {
		$_SESSION['admin_options'] = array();
		$admin_options = $this->db->getAllDataByFieldInArray('wl_options', array('alias' => -$_SESSION['alias']->id));
		if($admin_options)
			foreach ($admin_options as $ao) {
				if($ao->name != 'sub-menu')
					$_SESSION['admin_options'][$ao->name] = $ao->value;
			}
		return true;
    }

    public function setContentRobot($data = array())
    {
    	if(!is_numeric($_SESSION['alias']->content))
    		return false;
    	
    	$ntkd = $where = array();
    	$keys = array('title', 'description', 'keywords', 'text', 'list', 'meta');
    	if($_SESSION['language'])
    		$where['language'] = $_SESSION['language'];

    	if($_SESSION['alias']->id > 0)
    	{
    		$where['alias'] = array(0, $_SESSION['alias']->id);
    		$keyCacheContent = 'ntkd_robot__1'; // -1;
    		if($_SESSION['alias']->content > 0)
    		{
    			$where['content'] = array(0, 1);
    			$keyCacheContent = 'ntkd_robot_1'; // +1;
    		}
    		else
    			$where['content'] = array(0, -1);
    		if($_SESSION['language'])
    			$keyCacheContent .= '_'.$_SESSION['language'];
    		$all = false;
    		if(isset($_SESSION['alias-cache'][$_SESSION['alias']->id]->$keyCacheContent))
    			$all = $_SESSION['alias-cache'][$_SESSION['alias']->id]->$keyCacheContent;
    		else
    			$all = $this->db->getAllDataByFieldInArray('wl_ntkd_robot', $where, 'alias DESC');
    		if($all)
	    		foreach ($all as $row) {
	    			foreach ($row as $key => $value) {
		    			if(in_array($key, $keys) && $value != '')
		    				$ntkd[$key] = htmlspecialchars_decode($value);
		    		}
	    		}
	    	if(isset($_SESSION['alias-cache'][$_SESSION['alias']->id]))
    			$_SESSION['alias-cache'][$_SESSION['alias']->id]->$keyCacheContent = $all;
    	}
    	else
    	{
	    	$where['alias'] = $where['content'] = 0;
	    	$keyCacheContent = 'ntkd_robot_0'; // 0;
	    	if($_SESSION['language'])
    			$keyCacheContent .= '_'.$_SESSION['language'];
	    	$all = false;
    		if(isset($_SESSION['alias-cache'][$_SESSION['alias']->id]->$keyCacheContent))
    			$all = $_SESSION['alias-cache'][$_SESSION['alias']->id]->$keyCacheContent;
    		else
    			$all = $this->db->getAllDataById('wl_ntkd_robot', $where);
	    	if($all)
	    		foreach ($all as $key => $value) {
	    			if(in_array($key, $keys) && $value != '')
	    				$ntkd[$key] = htmlspecialchars_decode($value);
	    		}
	    	if(isset($_SESSION['alias-cache'][$_SESSION['alias']->id]))
    			$_SESSION['alias-cache'][$_SESSION['alias']->id]->$keyCacheContent = $all;
    	}
    	if(!empty($ntkd))
    	{
	    	$keys = array();
	    	if(!empty($data))
	    		foreach ($data as $key => $value) {
	    			$name = '{';
	    			if(is_object($value))
	    			{
	    				$name .= $key.'.';
	    				foreach ($value as $keyO => $valueO) {
	    					if(!is_object($valueO) && !is_array($valueO))
	    						$keys[$name.$keyO.'}'] = $valueO;
	    				}
	    			}
	    		}
	    	$keys['{name}'] = $_SESSION['alias']->name;
	    	$keys['{SITE_URL}'] = SITE_URL;
	    	$keys['{IMG_PATH}'] = IMG_PATH;
	    	foreach ($ntkd as $key => $value) {
	    		if($_SESSION['alias']->$key == '')
	    		{
	    			foreach ($keys as $keyR => $valueR) {
	    				$value = str_replace($keyR, $valueR, $value);
	    			}
	    			$_SESSION['alias']->$key = $value;
	    		}
	    	}
	    }
	    if($_SESSION['alias']->title == '')
			$_SESSION['alias']->title = $_SESSION['alias']->name;
		if($_SESSION['alias']->description == '')
			$_SESSION['alias']->description = $this->getShortText($_SESSION['alias']->list);
    }

    private function getShortText($text, $len = 230)
    {
        $text = strip_tags(html_entity_decode($text));
        if(mb_strlen($text, 'UTF-8') > $len)
        {
            $pos = mb_strpos($text, ' ', $len, 'UTF-8');
			if($pos)
				return mb_substr($text, 0, $pos, 'UTF-8');
			else
			{
				$pos = mb_strpos($text, ' ', $len - 10, 'UTF-8');
				if($pos)
					return mb_substr($text, 0, $pos, 'UTF-8');
			}
        }
        return $text;
    }

}

?>
