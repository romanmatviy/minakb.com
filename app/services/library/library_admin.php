<?php

/*

 	Service "Library 2.7.1"
	for WhiteLion 1.0

*/

class library_admin extends Controller {
				
    function _remap($method, $data = array())
    {
    	if(isset($_SESSION['alias']->name))
    		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method))
            return $this->$method($data);
        else
        	$this->index($method);
    }

    public function index($uri)
    {
    	$this->load->smodel('library_model');
    	$_SESSION['option']->paginator_per_page = 50;

    	if(count($this->data->url()) > 2)
		{
			$type = null;
			$url = $this->data->url();
			array_shift($url);
			$article = $this->library_model->routeURL($url, $type, true);

			if($type == 'article' && $article)
				$this->edit($article);

			if($_SESSION['option']->useGroups && $type == 'group' && $article)
			{
				$group = clone $article;
				unset($article);

				$group->alias_name = $_SESSION['alias']->name;
				$group->parents = array();
				if($group->parent > 0)
				{
					$list = array();
		            $groups = $this->db->getAllData($this->library_model->table('_groups'));
		            foreach ($groups as $Group) {
		            	$list[$Group->id] = clone $Group;
		            }
					$group->parents = $this->library_model->makeParents($list, $group->parent, $group->parents);
				}
				$this->wl_alias_model->setContent(($group->id * -1));

				$list = $this->library_model->getGroups($group->id, false);
				if (empty($list) || $_SESSION['option']->articleMultiGroup == 1)
				{
					$list = $this->library_model->getArticles($group->id, 0, false);
					$this->load->admin_view('articles/list_view', array('group' => $group, 'articles' => $list));
				}
				else
					$this->load->admin_view('index_view', array('group' => $group, 'groups' => $list));
			}

			$this->load->page_404();
		}
		else
		{
			$this->wl_alias_model->setContent();
			if($_SESSION['option']->useGroups)
			{
				$list = $this->library_model->getGroups(0, false);
				if (empty($list) || $_SESSION['option']->articleMultiGroup == 1)
				{
					$list = $this->library_model->getArticles(-1, 0, false);
					$this->load->admin_view('articles/list_view', array('articles' => $list));
				}
				else
					$this->load->admin_view('index_view', array('groups' => $list));
			}
			else
			{
				$articles = $this->library_model->getArticles(-1, 0, false);
				$this->load->admin_view('articles/list_view', array('articles' => $articles));
			}
		}
    }

	public function all()
	{
		$this->load->smodel('articles_model');
		$articles = $this->articles_model->getArticles(-1, false);
		$this->load->admin_view('articles/all_view', array('articles' => $articles));
	}
	
	public function add()
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Додати новий запис' => '');
		$_SESSION['alias']->name .= '. Додати новий запис';
		$this->load->admin_view('articles/add_view');
	}
	
	private function edit($article){
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Редагувати запис' => '');
		$this->wl_alias_model->setContent($article->id);

		$groups = null;
		if($_SESSION['option']->useGroups)
		{
			$groups = $this->library_model->getGroups(-1);
			if($_SESSION['option']->articleMultiGroup)
			{
				$activeGroups = $this->db->getAllDataByFieldInArray($this->library_model->table('_article_group'), $article->id, 'article');
				$article->group = array();
				if($activeGroups)
				{
					foreach ($activeGroups as $ag) {
						$article->group[] = $ag->group;
					}
				}
			}
		}

		$this->load->admin_view('articles/edit_view', array('article' => $article, 'groups' => $groups));
	}
	
	public function save()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('articles_model');
			if($_POST['id'] == 0)
			{
				$link = $name = '';
				if($id = $this->articles_model->add($link, $name))
				{
					if(!empty($_FILES['photo']['name']))
						$this->savephoto('photo', $id, $this->data->latterUAtoEN($name), $name);
					$this->redirect("admin/{$_SESSION['alias']->alias}/{$link}");
				}
				$this->redirect();
			}
			else
			{
				$_SESSION['notify'] = new stdClass();

				$link = $this->articles_model->save($_POST['id']);
				if($_SESSION['option']->articleUseOptions)
					$this->articles_model->saveArticleOptios($_POST['id']);
				if(empty($_SESSION['notify']->errors))
				{
					if(isset($_POST['to']) && $_POST['to'] == 'new')
						$this->redirect("admin/{$_SESSION['alias']->alias}/add");
					elseif(isset($_POST['to']) && $_POST['to'] == 'category')
					{
						$link = 'admin/'.$_SESSION['alias']->alias;
						if($_SESSION['option']->useGroups)
						{
							$article = $this->articles_model->getById($_POST['id']);
							$article->link = explode('/', $article->link);
							array_pop ($article->link);
							if(!empty($article->link))
							{
								$article->link = implode('/', $article->link);
								$link .= '/'.$article->link;
							}
						}
						$this->redirect($link);
					}

					$_SESSION['notify']->success = 'Дані успішно оновлено!';
				}
				$this->redirect('admin/'.$_SESSION['alias']->alias.'/'.$link.'#tab-main');
			}
		}
	}
	
	public function delete()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('articles_model');
			$link = $this->articles_model->delete($_POST['id']);
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->success = $_SESSION['admin_options']['word:article_to_delete'].' успішно видалено!';
			$this->redirect("admin/{$_SESSION['alias']->alias}/{$link}");
		}
	}
	
	public function changeposition()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']) && is_numeric($_POST['position']))
		{
			$this->load->smodel('articles_model');
			$this->load->model('wl_position_model');
			$this->wl_position_model->where = 'wl_alias = '.$_SESSION['alias']->id;
			$this->wl_position_model->table = $this->articles_model->table();
			
			if($_SESSION['option']->useGroups > 0 && $_SESSION['option']->articleMultiGroup == 0)
			{
				if($article = $this->db->getAllDataById($this->articles_model->table(), $_POST['id']))
					$this->wl_position_model->where .= " AND `group` = '{$article->group}'";
			}
			
			if($this->wl_position_model->change($_POST['id'], $_POST['position']))
				$this->redirect();
		}
	}

	public function groups()
	{
		$this->load->smodel('groups_model');
		$id = $this->data->uri(3);
		$id = explode('-', $id);
		if(is_numeric($id[0]))
		{
			$this->edit_group($id[0]);
		}
		else
		{
			$groups = $this->groups_model->getGroups(-1, false);
			$_SESSION['alias']->name = 'Групи '.$_SESSION['admin_options']['word:articles_to_all'];
			$_SESSION['alias']->breadcrumb = array('Групи' => '');
			$this->load->admin_view('groups/index_view', array('groups' => $groups));
		}
	}

	public function add_group()
	{
		$this->load->smodel('groups_model');
		$groups = $this->groups_model->getGroups(-1);
		$_SESSION['alias']->name = $_SESSION['admin_options']['word:group_add'];
		$_SESSION['alias']->breadcrumb = array('Групи' => 'admin/'.$_SESSION['alias']->alias.'/groups', $_SESSION['admin_options']['word:group_add'] => '');
		$this->load->admin_view('groups/add_view', array('groups' => $groups));
	}

	private function edit_group($id)
	{
		$group = $this->groups_model->getById($id, false);
		if($group)
		{
			$this->wl_alias_model->setContent(($group->id * -1));
			$groups = $this->groups_model->getGroups(-1);
			$_SESSION['alias']->breadcrumb = array('Групи' => 'admin/'.$_SESSION['alias']->alias.'/groups', 'Редагувати групу' => '');
			$this->load->admin_view('groups/edit_view', array('group' => $group, 'groups' => $groups));
		}
		$this->load->page_404(false);
	}

	public function save_group()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('groups_model');
			$_SESSION['notify'] = new stdClass();

			if($_POST['id'] == 0)
			{
				$alias = $title = false;
				if($id = $this->groups_model->add($alias, $title))
				{
					if(!empty($_FILES['photo']['name']) && $alias)
						$this->savephoto('photo', -$id, $alias, $title);
					$_SESSION['notify']->success = 'Групу успішно додано! Продовжіть наповнення сторінки.';
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/groups/'.$id);
				}
			}
			else
			{
				if($this->groups_model->save($_POST['id']))
					$_SESSION['notify']->success = 'Дані успішно оновлено!';
				else
					$_SESSION['notify']->errors = 'Сталася помилка, спробуйте ще раз!';
				$this->redirect('#tab-main');
			}
		}
	}

	public function delete_group()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('groups_model');
			$this->groups_model->delete($_POST['id']);
			$this->redirect("admin/{$_SESSION['alias']->alias}/groups");
		}
	}

	public function change_group_position()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']) && is_numeric($_POST['position']))
		{
			$this->load->smodel('groups_model');
			$this->load->model('wl_position_model');
			
			$this->wl_position_model->table = $this->groups_model->table();
			$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id;
			if($group = $this->db->getAllDataById($this->groups_model->table(), $_POST['id']))
				$this->wl_position_model->where .= " AND `parent` = '{$group->parent}'";

			if($this->wl_position_model->change($_POST['id'], $_POST['position']))
				$this->redirect();
		}
		$this->load->page_404(false);
	}

	public function options()
	{
		$this->load->smodel('groups_model');
		$this->load->smodel('options_model');

		$url = $this->data->url();
		$id = end($url);
		$id = explode('-', $id);
		$id = $id[0];

		if(is_numeric($id))
		{
			$option = $this->db->getAllDataById($this->options_model->table(), $id);
			if($option)
			{
				$_SESSION['alias']->name = 'Редагувати налаштування';
				$_SESSION['alias']->breadcrumb = array('Властивості' => 'admin/'.$_SESSION['alias']->alias.'/options', 'Редагувати налаштування' => '');
				$this->load->admin_view('options/edit_view', array('option' => $option));
			}
			else
				$this->load->page404();
		}
		elseif($id != '' && $id != $_SESSION['alias']->alias)
		{
			if($_SESSION['option']->useGroups)
			{
				$group = false;
				$parent = 0;
				array_shift($url);
				array_shift($url);
				array_shift($url);
				if($url)
					foreach ($url as $uri) {
						$group = $this->groups_model->getByAlias($uri, $parent);
						if($group)
							$parent = $group->id;
						else
							$group = false;
					}

				if($group)
				{
					$group->alias_name = $_SESSION['alias']->name;
					$group->parents = array();
					if($group->parent > 0)
					{
						$list = array();
			            $groups = $this->db->getAllData($this->groups_model->table());
			            foreach ($groups as $Group) {
			            	$list[$Group->id] = clone $Group;
			            }
						$group->parents = $this->groups_model->makeParents($list, $group->parent, $group->parents);
					}
					$this->wl_alias_model->setContent(($group->id * -1));
					$group->group_name = $_SESSION['alias']->name;

					$groups = $this->groups_model->getGroups($group->id, false);
					$options = $this->options_model->getOptions($group->id, false);

					$_SESSION['alias']->name = $_SESSION['alias']->name .'. Керування налаштуваннями';
					$_SESSION['alias']->breadcrumb = array('Налаштування' => '');

					$this->load->admin_view('options/index_view', array('group' => $group, 'groups' => $groups, 'options' => $options));
				}
				else
				{
					$groups = $this->groups_model->getGroups(0, false);
					$options = $this->options_model->getOptions(0, false);

					$_SESSION['alias']->name = 'Керування налаштуваннями';
					$_SESSION['alias']->breadcrumb = array('Налаштування' => '');

					$this->load->admin_view('options/index_view', array('options' => $options, 'groups' => $groups));
				}
			}
			else
			{
				$options = $this->options_model->getOptions(0, false);

				$_SESSION['alias']->name = 'Керування налаштуваннями';
				$_SESSION['alias']->breadcrumb = array('Налаштування' => '');

				$this->load->admin_view('options/index_view', array('options' => $options));	
			}
		}
		$this->load->page_404();
	}

	public function add_option()
	{
		$_SESSION['alias']->name = 'Додати налаштування';
		$_SESSION['alias']->breadcrumb = array('Властивості' => 'admin/'.$_SESSION['alias']->alias.'/options', 'Додати налаштування' => '');
		$this->load->admin_view('options/add_view');
	}

	public function save_option()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$_SESSION['notify'] = new stdClass();
			$this->load->smodel('options_model');
			if($_POST['id'] == 0){
				$id = $this->options_model->add_option();
				if($id){
					$_SESSION['notify']->success = 'Властивість успішно додано!';
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/options/'.$id);
				}
			} else {
				if($this->options_model->saveOption($_POST['id'])){
					$_SESSION['notify']->success = 'Властивість успішно оновлено!';
					$this->redirect();
				}
			}
		}
	}

	public function delete_option()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0)
		{
			$this->load->smodel('options_model');
			if($this->options_model->deleteOption($_POST['id'])){
				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->success = 'Властивість успішно видалено!';
				$this->redirect('admin/'.$_SESSION['alias']->alias.'/options');
			}
		}
	}

	public function change_option_position()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']) && is_numeric($_POST['position']))
		{
			$this->load->smodel('options_model');
			$this->load->model('wl_position_model');

			$this->wl_position_model->table = $this->options_model->table();
			$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id;
			if($option = $this->db->getAllDataById($this->options_model->table('_options'), $_POST['id']))
				$this->wl_position_model->where .= " AND `group` = '{$option->group}'";
			
			if($this->wl_position_model->change($_POST['id'], $_POST['position']))
				$this->redirect();
		}
		$this->load->page_404(false);
	}

	public function deleteOptionProperty()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('options_model');
			if($this->db->deleteRow($this->options_model->table(), $_POST['id']) && $this->db->deleteRow($this->options_model->table('_options_name'), $_POST['id'], 'option'))
			{
				if(isset($_POST['json']) && $_POST['json']){
					$this->load->json(array('result' => true));
				} else {
					$this->redirect();
				}
			}
		}
	}

	private function savephoto($name_field, $content, $name, $title = '')
	{
		if(!empty($_FILES[$name_field]['name']) && $_SESSION['option']->folder)
		{
			$path = IMG_PATH;
            $path = substr($path, strlen(SITE_URL));
            $path = substr($path, 0, -1);
            if(!is_dir($path))
            	mkdir($path, 0777);
            $path .= '/'.$_SESSION['option']->folder;
            if(!is_dir($path))
            	mkdir($path, 0777);
			$path .= '/'.$content;
            if(!is_dir($path))
            	mkdir($path, 0777);
            $path .= '/';

            $data['alias'] = $_SESSION['alias']->id;
            $data['content'] = $content;
            $data['file_name'] = '';
            $data['title'] = $title;
            $data['author'] = $_SESSION['user']->id;
            $data['date_add'] = time();
            $data['position'] = 1;
            $this->db->insertRow('wl_images', $data);
            $photo_id = $this->db->getLastInsertedId();
            $name .= '-' . $photo_id;

            $this->load->library('image');
			$this->image->upload($name_field, $path, $name);
			$extension = $this->image->getExtension();
			$this->image->save();
			if($extension && $this->image->getErrors() == '')
			{
				if($sizes = $this->db->getAliasImageSizes())
				{
					foreach ($sizes as $resize) {
                        if($resize->prefix == '')
                        {
                            if($this->image->loadImage($path, $name, $extension))
                            {
                                if(in_array($resize->type, array(1, 11, 12)))
                                    $this->image->resize($resize->width, $resize->height, $resize->quality, $resize->type);
                                if(in_array($resize->type, array(2, 21, 22)))
                                    $this->image->preview($resize->width, $resize->height, $resize->quality, $resize->type);
                                $this->image->save($resize->prefix);
                            }
                        }
                    }
				}
				$name .= '.'.$extension;
                $this->db->updateRow('wl_images', array('file_name' => $name), $photo_id);
                return $name;
			}			
		}
		return false;
	}

	public function __get_Search($content)
    {
    	$this->load->smodel('library_search_model');
    	return $this->library_search_model->getByContent($content, true);
    }

    public function __getRobotKeyWords($content = 0)
    {
    	$words = array();
    	$this->load->smodel('library_model');
    	if($content > 0)
    	{
    		$this->db->select($this->library_model->table('_articles'), 'id', $_SESSION['alias']->id, 'wl_alias');
    		$this->db->limit(1);
    		if($article = $this->db->get())
    		{
	    		if($article = $this->library_model->getArticle($article->id, 'id'))
	    		{
	    			foreach ($article as $key => $value) {
	    				if(!is_object($value) && !is_array($value))
		    				$words[] = '{article.'.$key.'}';
	    			}
	    		}
	    	}
    		else
    			$words = array('{article.id}', '{article.name}', '{article.wl_alias}', '{article.alias}', '{article.group}', '{article.active}', '{article.position}', '{article.author_add}', '{article.date_add}', '{article.author_edit}', '{article.date_edit}', '{article.author_add_name}', '{article.author_edit_name}');
    	}
    	elseif($content < 0)
    	{
    		$this->db->select($this->library_model->table('_groups'), 'alias', $_SESSION['alias']->id, 'wl_alias');
    		$this->db->limit(1);
    		if($group = $this->db->get())
    		{
	    		if($group = $this->library_model->getGroupByAlias($group->alias))
	    		{
	    			foreach ($group as $key => $value) {
	    				if(!is_object($value) && !is_array($value))
		    				$words[] = '{group.'.$key.'}';
	    			}
	    		}
	    	}
    		else
    			$words = array('{group.id}', '{group.name}', '{group.wl_alias}', '{group.parent}', '{group.alias}', '{group.active}', '{group.position}', '{group.author_add}', '{group.date_add}', '{group.author_edit}', '{group.date_edit}', '{group.user_name}');
    	}
    	return $words;
    }
	
}

?>