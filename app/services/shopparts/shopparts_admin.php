<?php

/*

 	Service "Shop Parts 1.0"
	for WhiteLion 1.0

*/

class shopparts_admin extends Controller {
				
    function _remap($method, $data = array())
    {
    	$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index($uri)
    {
    	$this->load->smodel('shop_model');
    	$_SESSION['option']->paginator_per_page = 50;

    	if(count($this->data->url()) > 2)
		{
			$type = null;
			$url = $this->data->url();
			array_shift($url);
			$product = $this->shop_model->routeURL($url, $type, true);

			if($type == 'product' && $product)
			{
				$this->edit($product);
			}

			if($_SESSION['option']->useGroups && $type == 'group' && $product)
			{
				$group = clone $product;
				unset($product);

				$group->alias_name = $_SESSION['alias']->name;
				$group->parents = array();
				if($group->parent > 0)
				{
					$list = array();
		            $groups = $this->db->getAllData($this->shop_model->table('_groups'));
		            foreach ($groups as $Group) {
		            	$list[$Group->id] = clone $Group;
		            }
					$group->parents = $this->shop_model->makeParents($list, $group->parent, $group->parents);
				}
				$this->load->model('wl_ntkd_model');
				$this->wl_ntkd_model->setContent(($group->id * -1));

				$list = $this->shop_model->getGroups($group->id, false);
				if (empty($list) || $_SESSION['option']->ProductMultiGroup == 1) {
					$list = $this->shop_model->getProducts($group->id, 0, false);
					$this->load->admin_view('products/list_view', array('group' => $group, 'products' => $list));
				} else {
					$this->load->admin_view('index_view', array('group' => $group, 'groups' => $list));
				}
			}

			$this->load->page_404();
		}
		else
		{
			if($_SESSION['option']->useGroups)
			{
				$list = $this->shop_model->getGroups(0, false);
				if (empty($list) || $_SESSION['option']->ProductMultiGroup == 1) {
					$list = $this->shop_model->getProducts(-1, 0, false);
					$this->load->admin_view('products/list_view', array('products' => $list));
				} else {
					$this->load->admin_view('index_view', array('groups' => $list));
				}
			}
			else
			{
				$products = $this->shop_model->getProducts(-1, 0, false);
				$this->load->admin_view('products/list_view', array('products' => $products));
			}
		}
    }

	public function all()
	{
		$this->load->smodel('products_model');
		$products = $this->products_model->getProducts(-1, false);
		$this->load->admin_view('products/all_view', array('products' => $products));
	}

	public function search()
	{
		$this->load->smodel('shop_model');
		if($this->data->get('id'))
		{
			$product = $this->shop_model->getProduct($this->data->get('id'), 'id', false);
			if($product)
				$this->redirect('admin/'.$product->link);
			$this->load->admin_view('products/search_view', array('products' => false));
		}
		else
		{
			$mainSearch = true;
			$products = $this->shop_model->getProducts('%'.$this->makeArticle($this->data->get('article')), 0, false);
			if(empty($products))
			{
				$products = $this->shop_model->getProductAnalogs($this->makeArticle($this->data->get('article')));
				$mainSearch = false;
			}
			$this->load->admin_view('products/search_view', array('products' => $products, 'mainSearch' => $mainSearch));
		}
	}

	private function makeArticle($article)
	{
		$article = (string) $article;
		$article = trim($article);
		$article = strtoupper($article);
		$article = str_replace('-', '', $article);
		return str_replace(' ', '-', $article);
	}
	
	public function add()
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Додати новий товар' => '');
		$_SESSION['alias']->name .= '. Додати новий товар';
		$this->load->smodel('shop_model');
		$storages = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => $_SESSION['alias']->id, 'type' => 'storage'));
		$this->load->admin_view('products/add_view', array('manufacturers' => $this->shop_model->getManufactures(), 'storages' => $storages));
	}
	
	private function edit($product){
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Редагувати товар' => '');
		$_SESSION['alias']->name = $product->manufacturer_name.' '.$product->article.' '.$product->name;

		$groups = null;
		if($_SESSION['option']->useGroups)
		{
			$groups = $this->shop_model->getGroups();
			if($_SESSION['option']->ProductMultiGroup)
			{
				$activeGroups = $this->db->getAllDataByFieldInArray($this->shop_model->table('_product_group'), $product->id, 'product');
				$product->group = array();
				if($activeGroups)
				{
					foreach ($activeGroups as $ag) {
						$product->group[] = $ag->group;
					}
				}
			}
		}

		$this->load->admin_view('products/edit_view', array('product' => $product, 'groups' => $groups));
	}
	
	public function save()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('products_model');
			if($_POST['id'] == 0)
			{
				if($link = $this->products_model->add())
					$this->redirect("admin/{$_SESSION['alias']->alias}/{$link}#tab-main");
				$this->redirect();
			}
			else
			{
				$link = $this->products_model->save($_POST['id']);
				$this->products_model->saveProductOptios($_POST['id']);

				if(isset($_POST['to']) && $_POST['to'] == 'new') {
					$this->redirect("admin/{$_SESSION['alias']->alias}/add");
				} elseif(isset($_POST['to']) && $_POST['to'] == 'category') {
					$link = 'admin/'.$_SESSION['alias']->alias;
					$product = $this->products_model->getById($_POST['id']);
					$product->link = explode('/', $product->link);
					array_pop ($product->link);
					if(!empty($product->link))
					{
						$product->link = implode('/', $product->link);
						$link .= '/'.$product->link;
					}
					$this->redirect($link);
				}

				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->success = 'Дані успішно оновлено!';
				$this->redirect('admin/'.$_SESSION['alias']->alias.'/'.$link.'#tab-main');
			}
		}
	}

	public function saveOption()
	{
		$this->load->smodel('products_model');
		$name = $this->data->post('option');
		$_POST[$name] = $this->data->post('data');
		$this->products_model->saveProductOptios($_POST['id']);
	}
	
	public function delete()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('products_model');
			$link = $this->products_model->delete($_POST['id']);
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->success = $_SESSION['admin_options']['word:product_to_delete'].' успішно видалено!';
			$this->redirect("admin/{$_SESSION['alias']->alias}/{$link}");
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
			$_SESSION['alias']->name = 'Групи '.$_SESSION['admin_options']['word:products_to_all'];
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
			$groups = $this->groups_model->getGroups(-1);
			$_SESSION['alias']->name = 'Редагувати групу "'.$group->name.'"';
			$_SESSION['alias']->breadcrumb = array('Групи' => 'admin/'.$_SESSION['alias']->alias.'/groups', 'Редагувати групу' => '');
			$this->load->admin_view('groups/edit_view', array('group' => $group, 'groups' => $groups));
		}
		$this->load->page_404();
	}

	public function save_group(){
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('groups_model');
			$_SESSION['notify'] = new stdClass();

			$path = IMG_PATH.$_SESSION['option']->folder.'/groups/';
			$path = substr($path, strlen(SITE_URL));
			if(!is_dir($path)) {
				mkdir($path, 0777);
			}

			if($_POST['id'] == 0)
			{
				$alias = false;
				$id = $this->groups_model->add($alias);
				if($id)
				{
					if(!empty($_FILES['photo']['name'])) {
						$alias = $id .'-'. $alias;
						$ext = $this->savephoto('photo', $path, $alias);
						if($ext) $this->db->updateRow($this->groups_model->table(), array('photo' => $alias.'.'.$ext), $id);
					}
					$_SESSION['notify']->success = 'Групу успішно додано! Продовжіть наповнення сторінки.';
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/groups/'.$id);
				}
			}
			else
			{
				$alias = false;
				if($this->groups_model->save($_POST['id'], $alias))
				{
					if(!empty($_FILES['photo']['name'])) {
						$alias = $_POST['id'] .'-'. $alias;
						$ext = $this->savephoto('photo', $path, $alias);
						if($ext) $this->db->updateRow($this->groups_model->table(), array('photo' => $alias.'.'.$ext), $_POST['id']);
					}
					$_SESSION['notify']->success = 'Дані успішно оновлено!';
				} else {
					$_SESSION['notify']->errors = 'Сталася помилка, спробуйте ще раз!';
				}
				$this->redirect();
			}
		}
	}

	public function delete_group(){
		if(isset($_POST['id']) && is_numeric($_POST['id'])){
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
			
			$group = $this->db->getAllDataById($this->groups_model->table(), $_POST['id']);
			if($group) {
				$parent = $group->parent;
			}
			
			$this->wl_position_model->table = $this->groups_model->table();
			if($parent >= 0) {
				$this->wl_position_model->where = "`parent` = '{$parent}'";
			}
			if($this->wl_position_model->change($_POST['id'], $_POST['position'])) {
				$this->redirect();
			}
		}
		$this->load->page_404();
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
				$_SESSION['alias']->name = 'Редагувати властивість "'.$_SESSION['admin_options']['word:option'].'"';
				$_SESSION['alias']->breadcrumb = array('Властивості' => 'admin/'.$_SESSION['alias']->alias.'/options', 'Редагувати властивість' => '');
				$this->load->admin_view('options/edit_view', array('option' => $option));
			}
			else
			{
				$this->load->page404();
			}
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
				if($url){
					foreach ($url as $uri) {
						$group = $this->groups_model->getByAlias($uri, $parent);
						if($group){
							$parent = $group->id;
						} else $group = false;
					}
				}

				if($group){
					$group->alias_name = $_SESSION['alias']->name;
					$group->parents = array();
					if($group->parent > 0){
						$list = array();
			            $groups = $this->db->getAllData($this->groups_model->table());
			            foreach ($groups as $Group) {
			            	$list[$Group->id] = clone $Group;
			            }
						$group->parents = $this->groups_model->makeParents($list, $group->parent, $group->parents);
					}
					$this->load->model('wl_ntkd_model');
					$this->wl_ntkd_model->setContent(($group->id * -1));

					$groups = $this->groups_model->getGroups($group->id, false);
					$options = $this->options_model->getOptions($group->id, false);

					$_SESSION['alias']->name = $_SESSION['alias']->name .'. Керування властивостями ' . $_SESSION['admin_options']['word:products_to_all'];
					$_SESSION['alias']->breadcrumb = array('Властивості' => '');

					$this->load->admin_view('options/index_view', array('group' => $group, 'groups' => $groups, 'options' => $options));

				} else {
					$groups = $this->groups_model->getGroups(0, false);
					$options = $this->options_model->getOptions(0, false);

					$_SESSION['alias']->name = 'Керування властивостями ' . $_SESSION['admin_options']['word:products_to_all'];
					$_SESSION['alias']->breadcrumb = array('Властивості' => '');

					$this->load->admin_view('options/index_view', array('options' => $options, 'groups' => $groups));
				}
			} else {
				$options = $this->options_model->getOptions(0, false);

				$_SESSION['alias']->name = 'Керування властивостями ' . $_SESSION['admin_options']['word:products_to_all'];
				$_SESSION['alias']->breadcrumb = array('Властивості' => '');

				$this->load->admin_view('options/index_view', array('options' => $options));	
			}
		}
		$this->load->page_404();
	}

	public function add_option()
	{
		$_SESSION['alias']->name = $_SESSION['admin_options']['word:option_add'];
		$_SESSION['alias']->breadcrumb = array('Властивості' => 'admin/'.$_SESSION['alias']->alias.'/options', 'Додати властивість' => '');
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
			
			$option = $this->db->getAllDataById($this->options_model->table('_options'), $_POST['id']);
			if($option) {
				$parent = $option->group;
			}
			
			$this->wl_position_model->table = $this->options_model->table();
			if($parent >= 0) {
				$this->wl_position_model->where = "`group` = '{$parent}'";
			}
			if($this->wl_position_model->change($_POST['id'], $_POST['position'])) {
				$this->redirect();
			}
		}
		$this->load->page_404();
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

	private function savephoto($name_field, $path, $name, $array = false, $i = 0)
	{
		if(!empty($_FILES[$name_field]['name']))
		{
			$this->load->library('image');
			if($array) $this->image->uploadArray($name_field, $i, $path, $name);
			else $this->image->upload($name_field, $path, $name);
			$extension = $this->image->getExtension();
			$this->image->save();
			if($this->image->getErrors() == '')
			{
				$sizes = $this->db->getAllDataByFieldInArray('wl_images_sizes', $_SESSION['alias']->id, 'alias');
				if($sizes)
				{
					foreach ($sizes as $resize) if($resize->active == 1){
						$this->image->loadImage($path, $name, $extension);
						if($resize->type == 1) $this->image->resize($resize->width, $resize->height, 100);
						if($resize->type == 2) $this->image->preview($resize->width, $resize->height, 100);
						$this->image->save($path, $resize->prefix);
					}
				}
				return $this->image->getExtension();
			}
		}
		return false;
	}

	public function search_history()
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Історія пошуку' => '');
		$_SESSION['alias']->name .= '. Історія пошуку';

		$where = '';
		if(isset($_GET['day']))
		{
			$day = explode('-', $_GET['day']);
			if(count($day) == 3 && is_numeric($day[0]) && is_numeric($day[1]) && is_numeric($day[2]))
			{
				$_SESSION['alias']->name .= ' за '.$day[2].'.'.$day[1].'.'.$day[0];
				$day = mktime(0, 0, 0, $day[1], $day[2], $day[0]);
				if($day)
					$where = array('date' => $day);
			}
		}
		$this->db->select($_SESSION['service']->table.'_search_history as psh', '*', $where);
        $this->db->join($_SESSION['service']->table.'_products', 'article, name', '#psh.product_id');
        $this->db->join('wl_users', 'name as user_name, email as user_email', '#psh.user');
        $this->db->order('last_view DESC');

        if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
		{
			$start = 0;
			if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0) {
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			}
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1) {
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			}
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}

        $search_history = $this->db->get('array', false);
        $_SESSION['option']->paginator_total = $this->db->get('count');

        $this->load->admin_view('search_history_view', array('search_history' => $search_history));
	}

    public function clearDublicates()
    {
    	$BaseMem = memory_get_usage();
		$BaseTime = microtime(true);

        $products_articles = array();
        $dublicats = 0;
        $_SESSION['notify'] = new stdClass();

        $this->db->select('s_shopparts_products', 'id, article, manufacturer');
        $products = $this->db->get('array');
        if($products)
        {
        	$_SESSION['notify']->success = 'Товарів до перевірки в БД: '.count($products);
            foreach ($products as $product) {
                $article = $product->article;

				if(in_array(substr($article, -1), array('_', '.', ',')))
					$article = substr($article, 0, -1);

                $article = $article .'___'.$product->manufacturer;
                if(array_key_exists($article, $products_articles))
                {
                    $product_id = $products_articles[$article];
                    $this->db->deleteRow('s_shopparts_products', $product->id);
                    $this->db->deleteRow('s_shopparts_product_options', $product->id, 'product');
                    $this->db->updateRow('s_cart_products', array('product' => $product_id), array('product' => $product->id));
                    $this->db->updateRow('s_shopstorage_products', array('product' => $product_id), array('product' => $product->id));
                    $this->db->updateRow('products_update_history', array('product' => $product_id), array('product' => $product->id));
                    $this->db->updateRow('s_shopparts_search_history', array('product_id' => $product_id), array('product_id' => $product->id));
                    $dublicats++;
                }
                else
                    $products_articles[$article] = $product->id;
            }
        }
	        
        $_SESSION['notify']->success .= '<br>Віднайдено та очищено дублікатів: '.$dublicats;
        $_SESSION['notify']->success .= '<br>Товарів після очищення: '.count($products_articles);

        $CurrentMem = memory_get_usage();
        $memoty = round(($CurrentMem - $BaseMem)/1024, 2);
		if($memoty > 1024) $memoty = round($memoty / 1024, 2) . ' Мб';
		else $memoty .= ' Кб';
		$_SESSION['notify']->success .= '<br>Використано пам\'яті: '.$memoty.' <br>';
		$_SESSION['notify']->success .= 'Час: '.ceil(microtime(true) - $BaseTime).' сек';
        $this->load->redirect('admin/'.$_SESSION['alias']->alias.'/options');
    }
	
}

?>