<?php

/*

 	Service "Library 2.7.1"
	for WhiteLion 1.0

*/

class library extends Controller {
				
    function _remap($method, $data = array())
    {
        if (method_exists($this, $method))
            return $this->$method($data);
        else
        	$this->index($method);
    }

    public function index($uri)
    {
    	$this->load->smodel('library_model');
		
		if(count($this->data->url()) > 1)
		{
			$type = null;
			$article = $this->library_model->routeURL($this->data->url(), $type);

			if($type == 'article' && $article)
			{
				if($article->active == 0 && !$this->userCan())
					$this->load->page_404(false);
				$this->wl_alias_model->setContent($article->id);
				if($videos = $this->wl_alias_model->getVideosFromText())
				{
					$this->load->library('video');
					$this->video->setVideosToText($videos);
				}
				$this->load->page_view('detal_view', array('article' => $article));
			}
			elseif($_SESSION['option']->useGroups && $type == 'group' && $article)
			{
				if($article->active == 0 && !$this->userCan())
					$this->load->page_404(false);
				$group = clone $article;
				unset($article);

				$group->parents = array();
				if($group->parent > 0)
				{
					$list = array();
		            $groups = $this->db->getAllDataByFieldInArray($this->library_model->table('_groups'), $_SESSION['alias']->id, 'wl_alias');
		            foreach ($groups as $Group) {
		            	$list[$Group->id] = clone $Group;
		            }
					$group->parents = $this->library_model->makeParents($list, $group->parent, $group->parents);
					$link = $_SESSION['alias']->alias;
					foreach ($group->parents as $parent) {
						$link .= '/'.$parent->alias;
						$_SESSION['alias']->breadcrumbs[$parent->name] = $link;
					}
					$group->link = $link;
				}

				$this->wl_alias_model->setContent(($group->id * -1));
				if($videos = $this->wl_alias_model->getVideosFromText())
				{
					$this->load->library('video');
					$this->video->setVideosToText($videos);
				}
				$_SESSION['alias']->breadcrumbs[$_SESSION['alias']->name] = '';

				$subgroups = $this->library_model->getGroups($group->id);
				$articles = $this->library_model->getArticles($group->id);
				$this->load->page_view('group_view', array('group' => $group, 'subgroups' => $subgroups, 'articles' => $articles));
			}
			else
				$this->load->page_404();
		}
		else
		{
			$this->wl_alias_model->setContent();
			if($videos = $this->wl_alias_model->getVideosFromText())
			{
				$this->load->library('video');
				$this->video->setVideosToText($videos);
			}
			
			$articles = $this->library_model->getArticles();
			if($_SESSION['option']->useGroups)
			{
				$groups = $this->library_model->getGroups();
				$this->load->page_view('index_view', array('groups' => $groups, 'articles' => $articles));
			}
			else
				$this->load->page_view('index_view', array('articles' => $articles));
		}
    }

	public function search()
	{
		if(isset($_GET['name']) || isset($_GET['group']))
		{
			if(isset($_GET['name']) && is_numeric($_GET['name']))
				$this->redirect($_SESSION['alias']->alias.'/'.$_GET['name']);

			$search_group = new stdClass();
			$search_group->alias = 'search';
			$search_group->alias_name = $_SESSION['alias']->name;
			$search_group->parents = array();

			$this->load->smodel('library_model');
			$_SESSION['alias']->name = 'Пошук по назві';
			$_SESSION['alias']->title = 'Пошук по назві';

			if(isset($_GET['name']))
			{
				$_SESSION['alias']->name = "Пошук по назві \"{$this->data->get('name')}\"";
				$_SESSION['alias']->title = "Пошук по назві \"{$this->data->get('name')}\"";
			}

			$group = 0;
			if(isset($_GET['group']))
			{
				$language = '';
				if($_SESSION['language']) $language = "AND n.language = '{$_SESSION['language']}'";
				$group = $this->db->getQuery("SELECT g.*, n.name, n.title FROM `{$this->library_model->table('_groups')}` as g LEFT JOIN `wl_ntkd` as n ON n.alias = {$_SESSION['alias']->id} AND n.content = -g.id {$language} WHERE g.link = '{$this->data->get('group')}'");
				if($group)
				{
					$_SESSION['alias']->name = 'Пошук '.$group->name;
					$_SESSION['alias']->title = 'Пошук '.$group->title;
					$group = $group->id;
				}
			}

			$articles = $this->library_model->getArticles($group);
			$this->load->page_view('group_view', array('articles' => $articles, 'group' => $search_group));
		}
	}

    public function __get_Search($content)
    {
    	$this->load->smodel('library_search_model');
    	return $this->library_search_model->getByContent($content);
    }

    public function __get_SiteMap_Links()
    {
        $data = $row = array();
        $row['link'] = $_SESSION['alias']->alias;
        $row['alias'] = $_SESSION['alias']->id;
        $row['content'] = 0;
        $data[] = $row;

        $this->load->smodel('library_model');
        $articles = $this->library_model->getArticles();
        if(!empty($articles))
        	foreach ($articles as $article)
            {
            	$row['link'] = $article->link;
            	$row['content'] = $article->id;
            	$data[] = $row;
            }

        if($_SESSION['option']->useGroups)
        {
	        $groups = $this->library_model->getGroups();
	        if(!empty($groups))
	        	foreach ($groups as $group)
	            {
	            	$row['link'] = $group->link;
	            	$row['content'] = -$group->id;
	            	$data[] = $row;
	            }
        }

        return $data;
    }

	public function __get_Article($id = 0)
	{
		if($id > 0)
		{
			$this->load->smodel('library_model');
			if($article = $this->library_model->getArticle($id, 'id'))
			{
            	$article->link = $_SESSION['alias']->alias.'/'.$article->alias;
            	$article->photo = null;
            	// $article->video = $this->db->getAllDataByFieldInArray('wl_video', array('alias' => $_SESSION['alias']->id, 'content' => $article->id));

            	if($photo = $this->library_model->getArticlePhoto($article->id))
            	{
					if($sizes = $this->db->getAliasImageSizes())
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix.'_photo';
							$article->$resize_name = $_SESSION['option']->folder.'/'.$article->id.'/'.$resize->prefix.'_'.$photo->file_name;
						}
					$article->photo = $_SESSION['option']->folder.'/'.$article->id.'/'.$photo->file_name;
            	}
            	return $article;
			}
		}
		return false;
	}
	
	public function __get_Articles($data = array())
	{
		$group = -1;
		if(isset($data['group']) && is_numeric($data['group'])) $group = $data['group'];
		if(isset($data['limit']) && is_numeric($data['limit'])) $_SESSION['option']->paginator_per_page = $data['limit'];

		$this->load->smodel('library_model');
		return $this->library_model->getArticles($group);
	}

	public function __get_Groups($parent)
	{
		if(empty($parent))
			$parent = 0;
		$this->load->smodel('library_model');
		return $this->library_model->getGroups($parent, false);
	}
	
}

?>