<?php

/*

 	Service "Shop Showcase 3.3"
	for WhiteLion 1.3

*/

class shopshowcase extends Controller {

	private $groups = array();
	private $marketing = array();

    function __construct()
    {
        parent::__construct();

        $this->marketing = $this->get__wl_cooperation('marketing');
    }

    function _remap($method, $data = array())
    {
        if (method_exists($this, $method))
            return $this->$method($data);
        else
        	$this->index($method);
    }

    public function index($uri)
    {
    	$this->load->smodel('shop_model');

		if(count($this->data->url()) > 1)
		{
			$type = null;
			$this->shop_model->getBreadcrumbs = true;
			$product = $this->shop_model->routeURL($this->data->url(), $type);

			if($type == 'product' && $product)
			{
				$this->wl_alias_model->setContent($product->id);
				$_SESSION['alias']->name = $product->name;
				$_SESSION['alias']->breadcrumbs = $this->shop_model->breadcrumbs;

				if(isset($_GET['edit']) && $_SESSION['option']->userCanAdd)
				{
					if($this->userIs())
					{
						if($product->author_add == $_SESSION['user']->id || $this->userCan())
						{
							if($__init_before_addEditSave = $this->get__wl_cooperation('__init_before_addEditSave'))
								foreach ($__init_before_addEditSave as $aliasIdInit) {
									$this->load->function_in_alias($aliasIdInit, '__before_edit');
								}

							$_SESSION['alias']->breadcrumbs[$product->name] = $product->link;
							$_SESSION['alias']->title = $this->text('Редагувати').' '.$_SESSION['alias']->name;
							$_SESSION['alias']->name = $this->text('Редагувати');

							$groups = $options = false;
							if($_SESSION['option']->useGroups)
								$groups = $this->shop_model->getGroups(-1);
							if($_SESSION['option']->ProductMultiGroup)
								$options = $this->shop_model->getOptionsToGroup(0, false);
							else
								$options = $this->shop_model->getOptionsToGroup($product->group, false);

							$this->load->page_view('manager/edit_view', array('product' => $product, 'groups' => $groups, 'options' => $options));
						}
						else
							$this->load->notify_view(['errors' => "Тільки власник може редагувати товар"]);
						exit;
					}
					else
						$this->load->redirect('login?redirect='.$this->data->url(true));
				}

				if($product->active <= 0 && !$this->userCan())
					if(!$this->userIs() || ($this->userIs() && $product->author_add != $_SESSION['user']->id))
						$this->load->page_404(false);
				
				if($videos = $this->wl_alias_model->getVideosFromText())
				{
					$this->load->library('video');
					$this->video->setVideosToText($videos);
				}
				$this->setProductPrice($product);
				if(!empty($product->similarProducts))
				{
					if(is_array($product->similarProducts)) {
						foreach ($product->similarProducts as $folder => &$similarProducts) {
							$this->setProductsPrice($similarProducts);
						}
					}
					else
						$this->setProductsPrice($product->similarProducts);
				}

				if(!empty($_SESSION['alias']->images))
					foreach ($_SESSION['alias']->images[0] as $key => $path) {
						if($key == 'path')
							$product->photo = $path;
						else
						{
							$key = substr($key, 0, -4) .'photo';
							$product->$key = $path;
						}
					}

				$this->load->page_view('detal_view', array('product' => $product));
			}
			elseif($_SESSION['option']->useGroups && $type == 'group' && $product)
			{
				if($product->active == 0 && !$this->userCan())
					$this->load->page_404(false);
				$group = clone $product;
				unset($product);

				$this->wl_alias_model->setContent(-$group->id);
				$_SESSION['alias']->breadcrumbs = $this->shop_model->breadcrumbs;
				$subgroups = $products = $filters = $use_filter = $filter_minMaxPrices = false;

				if(count($_GET) > 1)
					foreach ($_GET as $key => $value) {
						if(!in_array($key, ['request', 'page', 'availability']))
						{
							$use_filter = true;
							break;
						}
					}

				if(!$use_filter && $group->haveChild)
				{
					$subgroups = $this->db->cache_get("subgroups".DIRSEP.$this->db->getCacheContentKey('group-', $group->id));
					if($subgroups === NULL)
					{
						$subgroups = $this->shop_model->getGroups($group->id);
						$this->db->cache_add("subgroups".DIRSEP.$this->db->getCacheContentKey('group-', $group->id), $subgroups);
					}
				}

				if($_SESSION['option']->showProductsParentsPages || !$subgroups)
				{
					if($use_filter)
						$products = $this->shop_model->getProducts($group->id);
					else
					{
						$cache_key = "products_in_group".DIRSEP.$this->db->getCacheContentKey('group-', $group->id);
						$cache_total = $cache_key .DIRSEP. 'total';
						if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
						{
							$page = 1;
							if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
								$page = $_GET['page'];
							$cache_key .= DIRSEP.'page-'.$page;
						}

						$products = $this->db->cache_get($cache_key);
						if($products === NULL)
						{
							$products = $this->shop_model->getProducts($group->id);
							$this->db->cache_add($cache_key, $products);
							$this->db->cache_add($cache_total, $_SESSION['option']->paginator_total);
						}
						elseif(!empty($products))
						{
							$this->shop_model->updateProductsPrice($products);
							$total = $this->db->cache_get($cache_total);
							if($total === NULL)
							{
								if(count($products) >= $_SESSION['option']->paginator_per_page)
									$_SESSION['option']->paginator_total = $this->shop_model->getProductsCountInGroup($group->id);
								else
									$_SESSION['option']->paginator_total = count($products);
								$this->db->cache_add($cache_total, $_SESSION['option']->paginator_total);
							}
							else
								$_SESSION['option']->paginator_total = $total;
						}
						else
							$_SESSION['option']->paginator_total = 0;
					}
					
					if($products)
					{
						$this->setProductsPrice($products);

						if($_SESSION['option']->showProductsParentsPages || !$subgroups || $use_filter)
						{
							$filters = $this->shop_model->getOptionsToGroup($group->id);
							$filter_minMaxPrices = $this->shop_model->getMinMaxPrices($group->id);
						}
					}
				}

				$this->load->page_view('group_view', array('group' => $group, 'subgroups' => $subgroups, 'use_filter' => $use_filter, 'products' => $products, 'filters' => $filters, 'filter_minMaxPrices' => $filter_minMaxPrices, 'catalogAllGroups' => $this->shop_model->getGroups(-1)));
			}
			else//if($this->userIs())
				$this->load->page_404(false);
			// else
			// 	$this->load->page_404();
		}
		else
		{
			$this->wl_alias_model->setContent();
			if($videos = $this->wl_alias_model->getVideosFromText())
			{
				$this->load->library('video');
				$this->video->setVideosToText($videos);
			}
			
			if($_SESSION['option']->useGroups)
			{
				if($groups = $this->shop_model->getGroups(-1))
					$this->load->page_view('index_view', array('catalogAllGroups' => $groups));
				else
				{
					if ($products = $this->shop_model->getProducts())
					{
						$filters = $this->shop_model->getOptionsToGroup();
						$filter_minMaxPrices = $this->shop_model->getMinMaxPrices();
						$this->setProductsPrice($products);

						if (count($products) >= $_SESSION['option']->paginator_per_page)
							$_SESSION['option']->paginator_total = $this->shop_model->getProductsCountInGroup();
						else
							$_SESSION['option']->paginator_total = count($products);
					}
					$this->load->page_view('group_view', array('products' => $products, 'use_filter' => true, 'filters' => $filters, 'filter_minMaxPrices' => $filter_minMaxPrices));
				}
			}
			else
			{
				$filters = $filter_minMaxPrices = false;
				if($products = $this->shop_model->getProducts())
				{
					$filters = $this->shop_model->getOptionsToGroup();
					$filter_minMaxPrices = $this->shop_model->getMinMaxPrices();
					$this->setProductsPrice($products);

					if (count($products) >= $_SESSION['option']->paginator_per_page)
						$_SESSION['option']->paginator_total = $this->shop_model->getProductsCountInGroup();
					else
						$_SESSION['option']->paginator_total = count($products);
				}
				$this->load->page_view('group_view', array('products' => $products, 'use_filter' => true, 'filters' => $filters, 'filter_minMaxPrices' => $filter_minMaxPrices));
			}
		}
    }

	public function search()
	{
		$this->wl_alias_model->setContent(0, 202);
		$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Пошук', 0);
		$this->load->smodel('shop_model');

		if(!empty($_GET['subgroup']))
		{
			$_GET['__group'] = $_GET['group'];
			$_GET['group'] = $_GET['subgroup'];
		}

		if($id = $this->data->get('id'))
		{
			if(is_numeric($id))
			{
				if($id == 0)
					$this->redirect($_SESSION['alias']->alias);
				elseif($id > 0)
				{
					if($product = $this->shop_model->getProduct($id, 'id', false))
						$this->redirect($product->link);
					else
						$this->load->notify_view(['errors' => $this->text("Товар з ід #{$id} не знайдено")]);
				}
				else
				{
					$this->shop_model->getBreadcrumbs = $this->shop_model->getGroupPhoto = false;
					if($group = $this->shop_model->getGroupByAlias(-$id, 0, 'id'))
						$this->redirect($group->link);
					
				}
			}
			else
				$this->load->notify_view(['errors' => $this->text("id must be numeric")]);
		}
		else if(!empty($_GET['group']))
		{
			if(!empty($_GET['name']) && $_SESSION['option']->ProductUseArticle)
			{
				$name = $this->data->get('name');
				$group = $_GET['group'];
				$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Пошук за артикулом', 0)." '{$name}'";

				unset($_GET['name'], $_GET['group']);
				if($products = $this->shop_model->getProducts('%'.$this->makeArticle($name)))
				{
					if($_SESSION['option']->searchHistory && !$this->userCan())
						$this->shop_model->searchHistory($this->makeArticle($name), count($products));

					if(count($products) == 1)
						$this->redirect($products[0]->link);

					$this->setProductsPrice($products);
					$this->load->page_view('search_view', array('products' => $products));
					exit;
				}

				$_GET['name'] = $name;
				$_GET['group'] = $group;
				$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Пошук по назві', 0)." '{$this->data->get('name')}'";
			}

			if(is_numeric($_GET['group']) && $_GET['group'] > 0)
			{
				$this->shop_model->getBreadcrumbs = $this->shop_model->getGroupPhoto = false;
				if($group = $this->shop_model->getGroupByAlias($_GET['group'], 0, 'id'))
				{
					$request = '';
					foreach ($_GET as $key => $value) {
						if(in_array($key, ['request', 'group']) || empty($value))
							continue;
						$request .= "{$key}={$value}&";
					}
					if(!empty($request))
						$this->redirect($group->link.'?'.substr($request, 0, -1));
					$this->redirect($group->link);
				}
				else
					$this->load->notify_view(['errors' => $this->text("Групу з ід #{$_GET['group']} не знайдено")]);
			}
			else if(is_array($_GET['group']))
			{
				$groups = [];
				foreach ($_GET['group'] as $group) {
					if(is_numeric($group) && $group > 0)
						$groups[] = $group;
				}
				if(!empty($_GET['__group']))
					$_GET['group'] = $_GET['__group'];
				if(!empty($groups))
				{
					if($products = $this->shop_model->getProducts($groups))
						$this->setProductsPrice($products);

					$this->load->page_view('group_view', array('products' => $products, 'use_filter' => true, 'filters' => $this->shop_model->getOptionsToGroup(), 'catalogAllGroups' => $this->shop_model->getGroups(-1)));
				}
				else
					$this->load->notify_view(['errors' => $this->text("Помилка пошуку")]);
			}
			else
				$this->load->notify_view(['errors' => $this->text("Групу з ід #{$_GET['group']} не знайдено")]);
		}
		else if(!empty($_GET['article']) && $_SESSION['option']->ProductUseArticle)
		{
			$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Пошук за артикулом', 0)." '{$this->data->get('article')}'";

			$article = $this->makeArticle($this->data->get('article'));
			$products = $this->shop_model->getProducts('%'.$article);

			if($_SESSION['option']->searchHistory && !$this->userCan())
				$this->shop_model->searchHistory($article, count($products));

			if(count($products) == 1)
				$this->redirect($products[0]->link);

			if($products)
				$this->setProductsPrice($products);

			$this->load->page_view('search_view', array('products' => $products));
		}
		else
		{
			if(!empty($_GET['name']) && $_SESSION['option']->ProductUseArticle)
			{
				$name = $this->data->get('name');
				$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Пошук за артикулом', 0)." '{$name}'";

				unset($_GET['name']);
				if($products = $this->shop_model->getProducts('%'.$this->makeArticle($name)))
				{
					if(count($products) == 1)
						$this->redirect($products[0]->link);

					$this->setProductsPrice($products);
					$this->load->page_view('search_view', array('products' => $products));
					exit;
				}

				$_GET['name'] = $name;
				$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Пошук по назві', 0)." '{$this->data->get('name')}'";
			}

			$author_add = NULL;
			if(!empty($_GET['author_add']) && is_numeric($_GET['author_add']) && $_GET['author_add'] > 0)
				$author_add = $this->db->getAllDataById('wl_users', $_GET['author_add']);
			if($author_alias = $this->data->get('author'))
				$author_add = $this->db->getAllDataById('wl_users', $author_alias, 'alias');

			if($author_add)
			{
				if($author_add->status == 1 && $author_add->type <= 3)
				{
					// $_SESSION['alias']->name = $_SESSION['alias']->title = $_SESSION['alias']->name .' від '.$author_add->name;
					$_SESSION['alias']->name = $_SESSION['alias']->title = 'Страви від '.$author_add->name;
					$_GET['author_add'] = $author_add->id;
				}
				else
					$author_add = false;
			}

			if($author_add === false)
			{
				$this->load->page_view('group_view', array('products' => false, 'use_filter' => false, 'filters' => false, 'catalogAllGroups' => $this->shop_model->getGroups(-1)));
				exit;
			}

			if($products = $this->shop_model->getProducts())
				$this->setProductsPrice($products);

			$this->load->page_view('group_view', array('products' => $products, 'use_filter' => true, 'filters' => $this->shop_model->getOptionsToGroup(), 'catalogAllGroups' => $this->shop_model->getGroups(-1)));
		}
	}

	public function ajaxGetProducts()
	{
		if(isset($_POST['params']))
			foreach ($_POST['params'] as $key => $value) {
				if(is_array($value))
				{
					foreach ($value as $secondValue) {
						$_GET[$key][] = $secondValue;
					}
				}
				else
					$_GET[$key] = $value;
			}

		$_GET['page'] = $this->data->post('page');
		$group = $this->data->post('group') > 0 ? $this->data->post('group') : '-1' ;

		$this->load->smodel('shop_model');
		$products = $this->shop_model->getProducts($group);
		$this->setProductsPrice($products);
		$this->load->json(array('products' => $products, 'page' => $_GET['page']+1, 'group' => $group));
	}

	public function ajaxUpdateProductPrice()
	{
		if ($product_id = $this->data->post('product')) {
			if (!empty($_POST['options']) && is_array($_POST['options'])) {
				$this->load->smodel('shop_model');
				$price = 0;
				$options = [];
				foreach ($_POST['options'] as $key => $value) {
					if(!is_numeric($key))
					{
						$k = substr($key, 0, 1);
						if($k == 'o')
							$key = substr($key, 1);
					}
					if(is_numeric($key) && is_numeric($value))
						$options[$key] = $value;
				}
				if(!empty($options))
					if($product = $this->shop_model->getProductPriceWithOptions($product_id, $options))
					{
						$this->setProductPrice($product);
						$price = $product->price;
					}
				$this->load->json(array('price' => $price, 'product' => $product_id));
			}
		}
	}

	public function ajaxGetGroups()
	{
		$parent_id = $this->data->post('parent_id');
		if(!is_numeric($parent_id) || $parent_id < 0)
			$parent_id = 0;

		$this->load->smodel('shop_model');
		$this->load->json(array('groups' => $this->shop_model->getGroups($parent_id, false)));
	}

	public function export_prom()
	{
		if(!empty($_SESSION['option']->export_ok) && isset($_GET['key']) && !empty($_SESSION['option']->exportKey) && $_SESSION['option']->exportKey == $_GET['key'])
		{
			ini_set('max_execution_time', 1800);
			ini_set('max_input_time', 1800);
			ini_set('memory_limit', '1024M');

			$this->load->library('ymlgenerator');
			$this->load->smodel('shop_model');
			$this->load->smodel('export_model');
			$this->export_model->init('prom');
			$products = $groups = array();

			if ($_SESSION['option']->useGroups)
			{
				$checkedGroups = -1;
				if (!empty($_GET['group']) && is_numeric($_GET['group']))
					$checkedGroups = $_GET['group'];

				if ($groups = $this->export_model->getGroups($checkedGroups))
				{
					$checkedGroups = array();
					foreach ($groups as $group) {
						$checkedGroups[] = $group->id;
					}

					$products = $this->export_model->getProducts($checkedGroups);
					$this->setProductsPrice($products);
					$this->ymlgenerator->createYml($products, $groups);
				}
				else
					echo "There are no active export groups";
			}
			else
			{
				$products = $this->export_model->getProducts();
				$this->setProductsPrice($products);
				$this->ymlgenerator->createYml($products, $groups);
			}
		}
		else
			echo '<img src="'.SERVER_URL.'style/images/access_denied.jpg" width="100%">';
		exit;
	}

	public function export_google()
	{
		if(!empty($_SESSION['option']->export_ok) && isset($_GET['key']) && !empty($_SESSION['option']->exportKey) && $_SESSION['option']->exportKey == $_GET['key'])
		{
			ini_set('max_execution_time', 1800);
			ini_set('max_input_time', 1800);
			ini_set('memory_limit', '1024M');

			$this->load->library('google_feed');
			$this->load->smodel('shop_model');
			$this->load->smodel('export_model');
			$this->export_model->init('google');
			$products = $groups = array();

			$checkedGroups = -1;
	        if(!empty($_GET['group']) && is_numeric($_GET['group']))
	            $checkedGroups = $_GET['group'];
        
	        if($groups = $this->export_model->getGroups($checkedGroups))
	        {
                $checkedGroups = array();
                foreach ($groups as $group) {
                    $checkedGroups[] = $group->id;
                }

                $products = $this->export_model->getProducts($checkedGroups);
		        $this->setProductsPrice($products);

				$this->google_feed->createXml($products, $groups);
	        }
	        else
	        	echo "There are no active export groups";
		}
		else
			echo '<img src="'.SERVER_URL.'style/images/access_denied.jpg" width="100%">';
		exit;
	}

	public function export_facebook()
	{
		if(!empty($_SESSION['option']->export_ok) && isset($_GET['key']) && !empty($_SESSION['option']->exportKey) && $_SESSION['option']->exportKey == $_GET['key'])
		{
			ini_set('max_execution_time', 1800);
			ini_set('max_input_time', 1800);
			ini_set('memory_limit', '1024M');

			$this->load->library('facebook_feed');
			$this->load->smodel('shop_model');
			$this->load->smodel('export_model');
			$this->export_model->init('facebook');
			$products = $groups = array();

			$checkedGroups = -1;
	        if(!empty($_GET['group']) && is_numeric($_GET['group']))
	            $checkedGroups = $_GET['group'];
        
	        if($groups = $this->export_model->getGroups($checkedGroups))
	        {
                $checkedGroups = array();
                foreach ($groups as $group) {
                    $checkedGroups[] = $group->id;
                }

                $products = $this->export_model->getProducts($checkedGroups);
		        $this->setProductsPrice($products);

				$this->facebook_feed->createXml($products, $groups);
	        }
	        else
	        	echo "There are no active export groups";
		}
		else
			echo '<img src="'.SERVER_URL.'style/images/access_denied.jpg" width="100%">';
		exit;
	}

	// public user manage mode
	public function my()
	{
		if($this->userIs())
		{
			if(empty($_SESSION['option']->userCanAdd))
				$this->load->notify_view(['errors' => "Згідно політики безпеки сайту, додавати товари може виключно адміністрація"]);

			if($_SESSION['user']->status != 1)
			{
				$user = $this->db->getAllDataById('wl_users', $_SESSION['user']->id);
				if($_SESSION['user']->status != $user->status)
				{
					$_SESSION['user']->status = $user->status;

					if($user->status == 1)
					{
						$_SESSION['notify'] = new stdClass();
						$_SESSION['notify']->title = $this->text('Вітаємо!');
						$_SESSION['notify']->success = $this->text('Ваш профіль успішно підтверджено');
					}
				}
			}
			
			$this->load->smodel('shop_model');
			$this->wl_alias_model->setContent();
			$_SESSION['alias']->name = $_SESSION['alias']->title = $this->text('Мої товари');

			$groups = false;
			if($_SESSION['option']->useGroups)
				$groups = $this->shop_model->getGroups();

			$_GET['author_add'] = $_SESSION['user']->id;
			$products = $this->shop_model->getProducts(-1, 0, false);
			$this->setProductsPrice($products);

			$this->load->profile_view('manager/index_view', array('groups' => $groups, 'products' => $products));
		}
		else
			$this->load->redirect('login?redirect='.$_SESSION['alias']->alias.'/my');
	}

	public function add()
	{
		if(empty($_SESSION['option']->userCanAdd))
			$this->load->notify_view(['errors' => "Згідно політики безпеки сайту, додавати товари може виключно адміністрація"]);

		if($this->userIs())
		{
			if($__init_before_addEditSave = $this->get__wl_cooperation('__init_before_addEditSave'))
				foreach ($__init_before_addEditSave as $aliasIdInit) {
					$this->load->function_in_alias($aliasIdInit, '__before_add');
				}

			$this->load->smodel('shop_model');
			$this->wl_alias_model->setContent();
			$_SESSION['alias']->name = $this->text('Додати товар');

			$groups = false;
			if($_SESSION['option']->useGroups)
				$groups = $this->shop_model->getGroups(-1);

			$options = $this->shop_model->getOptionsToGroup(0, false);

			$this->load->profile_view('manager/add_view', array('groups' => $groups, 'options' => $options));
		}
		else
			$this->load->redirect('login?redirect='.$_SESSION['alias']->alias.'/add');
	}

	public function save()
	{
		if(empty($_SESSION['option']->userCanAdd))
			$this->load->notify_view(['errors' => "Згідно політики безпеки сайту, керувати товарами може виключно адміністрація"]);

		if($this->userIs())
		{
			$_SESSION['notify'] = new stdClass();
			if(empty($_POST))
			{
				$_SESSION['notify']->errors = 'empty POST data';
				$this->redirect();
			}
			$this->load->smodel('products_model');
			if(empty($_POST['id']))
			{
				$_POST['active'] = -2; // користувач має надіслати на модерацію

				if($__init_before_addEditSave = $this->get__wl_cooperation('__init_before_addEditSave'))
					foreach ($__init_before_addEditSave as $aliasIdInit) {
						$this->load->function_in_alias($aliasIdInit, '__before_add_save');
					}

				$link = '';
				if($id = $this->products_model->add($link))
				{
					// $this->load->function_in_alias($_SESSION['alias']->alias, '__after_edit', $id, true);
					if(!empty($_FILES['photo']['name']))
						$this->load->function_in_alias($_SESSION['alias']->alias, '__savephoto', 
															['name_field' => 'photo', 'content' => $id, 'name' => $this->data->latterUAtoEN($this->data->post('name'))],
															true);
					$_SESSION['notify']->success = $this->text('Вітаємо! Товар створено');
					$_SESSION['notify']->success = $this->text('На даний момент є основна інформація, тепер додайте опис та додаткові характеристики');
					$this->redirect("{$_SESSION['alias']->alias}/{$link}?edit");
				}
				$_SESSION['notify']->errors = $this->text('Помилка додачі товару. Перевірте всі дані та спробуйте ще раз');
				$this->redirect();
			}
			else if(is_numeric($_POST['id']))
			{
				if($product = $this->products_model->getById($_POST['id']))
				{
					if($product->author_add == $_SESSION['user']->id || $this->userCan())
					{
						if($__init_before_addEditSave = $this->get__wl_cooperation('__init_before_addEditSave'))
								foreach ($__init_before_addEditSave as $aliasIdInit) {
									$this->load->function_in_alias($aliasIdInit, '__before_edit_save', $product);
								}

						if($product->active < 0 && isset($_POST['active']))
							unset($_POST['active']);
						$_POST['article_old'] = $product->article;
						$_POST['group_old'] = $product->group;
						$_POST['position_old'] = $product->position;
						$_POST['alias'] = $this->data->post('name');

						$link = $this->products_model->save($_POST['id']);
						$this->products_model->saveProductOptios($_POST['id']);
						$is_photo = false;
						if(is_array($_FILES['photo']['name']))
						{
							if(!empty($_FILES['photo']['name'][0]))
								$is_photo = true;
						} else if(!empty($_FILES['photo']['name']))
							$is_photo = true;
						if($is_photo)
							$this->load->function_in_alias($_SESSION['alias']->alias, '__savephoto', 
															['name_field' => 'photo', 'content' => $product->id, 'name' => $this->data->latterUAtoEN($this->data->post('name'))],
															true);
						if(!empty($_POST['video']))
						{
							$_POST['alias'] = $_SESSION['alias']->id;
							$_POST['content'] = $product->id;
							require APP_PATH.'controllers'.DIRSEP.'admin'.DIRSEP.'wl_video.php';
							$video = new wl_video_admin();
							$video->save(true);
						}
						$this->load->function_in_alias($_SESSION['alias']->alias, '__after_edit', $product->id, true);

						$_SESSION['notify'] = new stdClass();
						$_SESSION['notify']->success = 'Дані успішно оновлено!';
						$this->redirect($_SESSION['alias']->alias.'/'.$link.'?edit');
					}
					else
						$this->load->notify_view(['errors' => "Тільки власник може редагувати товар"]);
				}
				else
					$this->load->notify_view(['errors' => "Product with id #{$_POST['id']} not find"]);
			}
			else
				$this->load->notify_view(['errors' => "Product with id #{$_POST['id']} not find"]);
		}
		else
			$this->load->redirect('login');
	}

	public function delete_image()
	{
		if(empty($_SESSION['option']->userCanAdd))
			$this->load->notify_view(['errors' => "Згідно політики безпеки сайту, керувати товарами може виключно адміністрація"]);

		if($this->userIs())
		{
			if($id = $this->data->get('id'))
				if(is_numeric($id) && $id > 0)
					if($image = $this->db->getAllDataById('wl_images', $id))
						if($image->alias == $_SESSION['alias']->id)
							if($product = $this->db->getAllDataById($_SESSION['service']->table.'_products', $image->content))
								if($product->author_add == $_SESSION['user']->id || $this->userCan())
								{
									$path = IMG_PATH.$_SESSION['option']->folder.'/'.$image->content.'/';
				                    $path = substr($path, strlen(SITE_URL));
				                    $prefix = array('');
				                    if($sizes = $this->db->getAliasImageSizes($image->alias))
				                        foreach ($sizes as $resize) {
				                            $prefix[] = $resize->prefix.'_';
				                        }
				                    foreach ($prefix as $p) {
				                        $filename = $path.$p.$image->file_name;
				                        @unlink ($filename);
				                    }

				                    $this->db->deleteRow('wl_images', $image->id);
				                    if($_SESSION['language'])
				                        $this->db->deleteRow('wl_media_text', array('type' => 'photo', 'content' => $image->id));
				                    $this->db->executeQuery("UPDATE `wl_images` SET `position` = `position` - 1 WHERE `alias` = '{$image->alias}' AND `content` = '{$image->content}' AND `position` > '{$image->position}'");
				                    
				                    $this->load->function_in_alias($_SESSION['alias']->alias, '__after_edit', $product->id, true);
				                    $this->redirect();
								}
			$this->load->notify_view(['errors' => "Помилка ідентифікатору зображення або у Вас відсутній доступ до даного фото"]);
		}
		else
			$this->load->redirect('login');
	}

	public function delete_video()
	{
		if(empty($_SESSION['option']->userCanAdd))
			$this->load->notify_view(['errors' => "Згідно політики безпеки сайту, керувати товарами може виключно адміністрація"]);

		if($this->userIs())
		{
			if($id = $this->data->get('id'))
				if(is_numeric($id) && $id > 0)
					if($video = $this->db->getAllDataById('wl_video', $id))
						if($video->alias == $_SESSION['alias']->id)
							if($product = $this->db->getAllDataById($_SESSION['service']->table.'_products', $video->content))
								if($product->author_add == $_SESSION['user']->id || $this->userCan())
								{
				                    $this->db->deleteRow('wl_video', $video->id);
				                    if($_SESSION['language'])
				                        $this->db->deleteRow('wl_media_text', array('type' => 'video', 'content' => $video->id));
				                    
				                    $this->load->function_in_alias($_SESSION['alias']->alias, '__after_edit', $product->id, true);
				                    $this->redirect();
								}
			$this->load->notify_view(['errors' => "Помилка ідентифікатору зображення або у Вас відсутній доступ до даного фото"]);
		}
		else
			$this->load->redirect('login');
	}

	public function confirm()
	{
		if(empty($_SESSION['option']->userCanAdd))
			$this->load->notify_view(['errors' => "Згідно політики безпеки сайту, керувати товарами може виключно адміністрація"]);

		if($this->userIs())
		{
			if($id = $this->data->post('id'))
				if(is_numeric($id) && $id > 0)
					if($product = $this->db->getAllDataById($_SESSION['service']->table.'_products', $id))
						if($product->author_add == $_SESSION['user']->id || $this->userCan())
						{
		                    $this->db->updateRow($_SESSION['service']->table.'_products', ['active' => -1, 'date_edit' => time(), 'author_edit' => $_SESSION['user']->id], $id);
		                    
		                    $this->load->function_in_alias($_SESSION['alias']->alias, '__after_edit', $product->id, true);
		                    $this->redirect();
						}
			$this->load->notify_view(['errors' => "Помилка ідентифікатору товару або у Вас відсутній доступ"]);
		}
		else
			$this->load->redirect('login');
	}

    public function __get_Search($content)
    {
    	$this->load->smodel('shop_search_model');
    	return $this->shop_search_model->getByContent($content);
    }

    public function __get_SiteMap_Links()
    {
        $data = $row = array();
        $row['link'] = $_SESSION['alias']->alias;
        $row['alias'] = $_SESSION['alias']->id;
        $row['content'] = 0;
        // $row['code'] = 200;
        // $row['data'] = '';
        // $row['time'] = time();
        // $row['changefreq'] = 'daily';
        // $row['priority'] = 5;
        $data[] = $row;

        $this->load->smodel('shop_search_model');
        if($products = $this->shop_search_model->getProducts_SiteMap())
        	foreach ($products as $product)
            {
            	if(!$product->skip)
            	{
	            	$row['link'] = $product->link;
	            	$row['content'] = $product->id;
	            	$data[] = $row;
	            }
            }

       	if($_SESSION['option']->useGroups)
	        if($groups = $this->shop_search_model->getGroups_SiteMap())
	        	foreach ($groups as $group)
	            {
	            	$row['link'] = $group->link;
	            	$row['content'] = -$group->id;
	            	$data[] = $row;
	            }

        return $data;
    }
    
    // $id['key'] може мати будь-який ключ _products. Рекомендовано: id, article, alias.
	public function __get_Product($id = 0)
	{
		$key = 'id';
		$additionalFileds = $options = false;
		if(is_array($id))
		{
			if(isset($id['options']) && is_array($id['options']))
				$options = $id['options'];
			if(isset($id['additionalFileds']) && is_array($id['additionalFileds']))
				$additionalFileds = $id['additionalFileds'];
			if(isset($id['key'])) $key = $id['key'];
			if(isset($id['id'])) $id = $id['id'];
			else if(isset($id['article'])) $id = $id['article'];
		}

		$this->load->smodel('shop_model');
		$this->shop_model->getBreadcrumbs = false;
		if($product = $this->shop_model->getProduct($id, $key))
		{
			if($options)
				$product = $this->shop_model->getProductPriceWithOptions($product, $options);
			if($additionalFileds)
				foreach ($additionalFileds as $key => $value) {
					$product->$key = $value;
				}
			$this->setProductPrice($product);
			$product->useAvailability = $_SESSION['option']->useAvailability;
		}
		return $product;
	}

	public function __get_Products($data = array())
	{
		$paginator_per_page = $_SESSION['option']->paginator_per_page;
		$productOrder = $_SESSION['option']->productOrder;
		$get_sale = $this->data->get('sale');
		$get_availability = $this->data->get('availability');
		$group = -1;
		$noInclude = $_GET['sale'] = 0;
		unset($_GET['availability']);
		$active = true;
		$getProductOptions = $additionalFileds = false;
		if(isset($data['article']) && $data['article'] != '')
		{
			$article = (string) $data['article'];
			$article = trim($article);
			$article = mb_strtoupper($article);
			$data['article'] = str_replace([' ', '-', '.', ',', '/'], '', $article);
			$group = '%'.$data['article'];
		}
		elseif(isset($data['group']) && (is_numeric($data['group']) || is_array($data['group']))) $group = $data['group'];
		if(isset($data['limit']) && is_numeric($data['limit'])) $_SESSION['option']->paginator_per_page = $data['limit'];
		if(isset($data['sort']) && $data['sort'] != '') $_SESSION['option']->productOrder = $data['sort'];
		if(isset($data['sale']) && $data['sale'] == 1) $_GET['sale'] = 1;
		if(isset($data['availability'])) $_GET['availability'] = $data['availability'];
		if(isset($data['noInclude']) && $data['noInclude'] > 0) $noInclude = $data['noInclude'];
		if(isset($data['active']) && $data['active'] == false) $active = $data['active'];
		if(isset($data['getProductOptions']) && $data['getProductOptions'] == true) $getProductOptions = true;
		if(isset($data['additionalFileds']) && is_array($data['additionalFileds']))
			$additionalFileds = $data['additionalFileds'];

		$this->load->smodel('shop_model');
		$products = $this->shop_model->getProducts($group, $noInclude, $active, $getProductOptions);
		if($products)
		{
			if($additionalFileds)
				foreach ($products as $product)
					foreach ($additionalFileds as $key => $value) {
						$product->$key = $value;
					}
			$this->setProductsPrice($products);
		}

		$_SESSION['option']->paginator_per_page = $paginator_per_page;
		$_SESSION['option']->productOrder = $productOrder;
		$_GET['sale'] = $get_sale;
		$_GET['availability'] = $get_availability;
		
		return $products;
	}

	public function __get_Group($id = 0)
	{
		if(empty($id))
			return false;
		$this->load->smodel('shop_model');
		return $this->shop_model->getGroupByAlias($id, false, 'id');
	}

	public function __get_Groups($parent_id = 0)
	{
		if(!is_numeric($parent_id))
			$parent_id = 0;
		$this->load->smodel('shop_model');
		return $this->shop_model->getGroups($parent_id, false);
	}

	public function __get_OptionsToGroup($group_id)
	{
		if(!is_numeric($group_id))
			$group_id = 0;
		$this->load->smodel('shop_model');
		return $this->shop_model->getOptionsToGroup($group_id);
	}

	public function __get_Values_To_Option($id = 0)
	{
		if(empty($id))
			return false;
		$this->load->smodel('shop_model');
		$this->db->select($this->shop_model->table('_options').' as o', '*', -$id, 'group');
		$where = array('option' => '#o.id');
		if($_SESSION['language']) $where['language'] = $_SESSION['language'];
		$this->db->join($this->shop_model->table('_options_name'), 'name', $where);
		return $this->db->get('array');
	}

	public function __get_Option_Info($id = 0)
	{
		if(empty($id))
			return false;
		$this->load->smodel('shop_model');
		$this->db->select($this->shop_model->table('_options').' as o', '*', $id);
		$where = array('option' => '#o.id');
		if($_SESSION['language']) $where['language'] = $_SESSION['language'];
		$this->db->join($this->shop_model->table('_options_name'), 'name', $where);
		if($option = $this->db->get('single'))
		{
			$option->values = $this->__get_Values_To_Option($option->id);
			return $option;
		}
		return false;
	}

	public function __get_Price_With_options($info)
	{
		if (isset($info['product']) && isset($info['options']) && is_array($info['options'])) {
			$this->load->smodel('shop_model');
			if($product = $this->shop_model->getProductPriceWithOptions($info['product'], $info['options']))
			{
				$this->setProductPrice($product);
				return $product->price;
			}
		}
		return false;
	}

	public function __formatPrice($price)
	{
		$this->load->smodel('shop_model');
		return $this->shop_model->formatPrice($price);;
	}

	private function makeArticle($article)
	{
		$article = (string) $article;
		$article = trim($article);
		$article = strtoupper($article);
		$article = str_replace('-', '', $article);
		return str_replace(' ', '', $article);
	}

	private function setProductPrice(&$product)
	{
		$product->price_in = $product->price;
    	$product->old_price_in = $product->old_price;

		if($_SESSION['option']->useMarkUp > 0 && $product->markup)
		{
    		$product->price *= $product->markup;
    		$product->old_price *= $product->markup;
    	}

    	$product->price_before = $product->price;
    	if($product->old_price > $product->price)
    		$product->price_before = $product->old_price;
		if(!empty($this->marketing) && $product)
			foreach ($this->marketing as $marketingAliasId) {
				$product = $this->load->function_in_alias($marketingAliasId, '__update_Product', $product);
			}
		$product->discount = $product->price_before - $product->price;
		if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && isset($_SESSION['currency'][$product->currency]))
		{
			$product->price *= $_SESSION['currency'][$product->currency];
			$product->old_price *= $_SESSION['currency'][$product->currency];
			$product->discount *= $_SESSION['currency'][$product->currency];
		}
		
		$product->price = $this->shop_model->formatPrice($product->price, true);
		$product->old_price = $this->shop_model->formatPrice($product->old_price, true);
		$product->price_format = $this->shop_model->formatPrice($product->price);
		$product->old_price_format = $this->shop_model->formatPrice($product->old_price);
		if(!empty($product->quantity))
			$product->sum_format = $this->shop_model->formatPrice($product->price * $product->quantity);
	}

	public function setProductsPrice(&$products)
	{
		if($products)
		{
			foreach ($products as $product) {
				$product->price_in = $product->price;
		    	$product->old_price_in = $product->old_price;

				if($_SESSION['option']->useMarkUp > 0 && $product->markup)
				{
		    		$product->price *= $product->markup;
		    		$product->old_price *= $product->markup;
		    	}

		    	$product->price_before = $product->price;
		    	if($product->old_price > $product->price)
    				$product->price_before = $product->old_price;
			}
		
			if(!empty($this->marketing) && $products)
				foreach ($this->marketing as $marketingAliasId) {
					$products = $this->load->function_in_alias($marketingAliasId, '__update_Products', $products);
				}

			if($products)
				foreach ($products as $product) {
					$product->discount = $product->price_before - $product->price;
					if(!empty($_SESSION['currency']) && is_array($_SESSION['currency']) && isset($_SESSION['currency'][$product->currency]))
					{
						$product->price *= $_SESSION['currency'][$product->currency];
						$product->old_price *= $_SESSION['currency'][$product->currency];
						$product->discount *= $_SESSION['currency'][$product->currency];
					}
					
					$product->price = $this->shop_model->formatPrice($product->price, true);
					$product->old_price = $this->shop_model->formatPrice($product->old_price, true);
					$product->price_format = $this->shop_model->formatPrice($product->price);
					$product->old_price_format = $this->shop_model->formatPrice($product->old_price);
				}
		}
	}

	public function __setProduct_sPrice($products)
	{
		$this->load->smodel('shop_model');
		if(is_object($products))
			$this->setProductPrice($products);
		if(is_array($products))
			$this->setProductsPrice($products);
		return $products;
	}

}

?>