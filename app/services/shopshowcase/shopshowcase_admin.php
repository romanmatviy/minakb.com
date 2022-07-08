<?php

/*

 	Service "Shop Showcase 3.3"
	for WhiteLion 1.3

*/

class shopshowcase_admin extends Controller {
				
    function _remap($method, $data = array())
    {
    	$this->wl_alias_model->setContent();

    	if(isset($_SESSION['alias']->name))
    		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => '');
        if (method_exists($this, $method))
            return $this->$method($data);
        else
        	$this->index($method);
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
				$this->edit($product);

			if($_SESSION['option']->useGroups && $type == 'group' && $product)
			{
				$group = clone $product;
				unset($product);

				$group->alias_name = $_SESSION['alias']->name;
				$group->parents = $this->shop_model->makeParents($group->parent, array());

				$this->wl_alias_model->setContent(($group->id * -1));

				$groups = $this->shop_model->getGroups($group->id, false);
				$products = $this->shop_model->getProducts($group->id, 0, false);
				if (empty($groups))
				{
					$allGroups = false;
					if(!$_SESSION['option']->ProductMultiGroup)
						$allGroups = $this->shop_model->allGroups;
					$this->load->admin_view('products/list_view', array('group' => $group, 'products' => $products, 'allGroups' => $allGroups));
				}
				else
					$this->load->admin_view('index_view', array('group' => $group, 'groups' => $groups, 'products' => $products));
			}

			$this->load->page_404(false);
		}
		else
		{
			$this->wl_alias_model->setContent();
			
			if($_SESSION['option']->useGroups)
			{
				$groups = $this->shop_model->getGroups(0, false);
				if (empty($groups))
				{
					$products = $this->shop_model->getProducts(-1, 0, false);
					$this->load->admin_view('products/list_view', array('products' => $products));
				}
				else
				{
					$products = $this->shop_model->getProducts(0, 0, false);
					$this->load->admin_view('index_view', array('groups' => $groups, 'products' => $products));
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
		$search = -1; $group = false;
		if($this->data->get('id'))
		{
			if($product = $this->shop_model->getProduct($this->data->get('id'), 'id', false))
				$this->redirect('admin/'.$product->link);
			$search = '%'.$this->data->get('id');
		}
		if($this->data->get('article'))
			$search = '%'.$this->shop_model->prepareArticleKey($this->data->get('article'));

		if($this->data->get('group'))
			if($group = $this->shop_model->getGroupByAlias($this->data->get('group'), 0, 'id'))
			{
				$group->alias_name = $_SESSION['alias']->name;
				$group->parents = array();
				if($group->parent > 0)
					$group->parents = $this->shop_model->makeParents($group->parent, $group->parents);
				$this->wl_alias_model->setContent(($group->id * -1));

				$search = $group->id;
			}

		if($products = $this->shop_model->getProducts($search, 0, false))
		{
			if(count($products) == 1)
				$this->redirect('admin/'.$products[0]->link);
			if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias1' => $_SESSION['alias']->id, 'type' => 'storage')))
				$this->load->admin_view('products/search_view', array('products' => $products, 'cooperation' => $cooperation, 'group' => $group));
			else
				$this->load->admin_view('products/list_view', array('products' => $products, 'search' => true, 'group' => $group));
		}
		else
			$this->load->admin_view('products/list_view', array('products' => false));
	}
	
	public function add()
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Додати новий запис' => '');
		$_SESSION['alias']->name .= '. Додати новий запис';
		$this->load->admin_view('products/add_view');
	}

	public function _get_groupsTree()
	{
		if($product_id = $this->data->get('product'))
		{
			$this->load->smodel('shop_model');
			$groups = $this->shop_model->getGroups(-1);
			if($_SESSION['option']->ProductMultiGroup)
			{
				$product_groups = array();
				if($activeGroups = $this->db->getAllDataByFieldInArray($this->shop_model->table('_product_group'), $product_id, 'product'))
					foreach ($activeGroups as $ag) {
						$product_groups[] = $ag->group;
					}
			}
			$this->load->view('admin/products/_groupsTree', array('groups' => $groups, 'product_groups' => $product_groups));
		}
	}
	
	private function edit($product)
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias);
		$this->wl_alias_model->setContent($product->id);
		if(isset($_GET['edit']))
			$_SESSION['alias']->breadcrumb['Редагувати '.$_SESSION['alias']->name] = '';
		else
			$_SESSION['alias']->breadcrumb[$_SESSION['alias']->name] = '';
		if(!empty($product->article_show))
		{
			$_SESSION['alias']->title = $product->article_show." ".$_SESSION['alias']->name;
			$_SESSION['alias']->name = "<strong>{$product->article_show}</strong> ".$_SESSION['alias']->name;
		}

		$groups = null;
		if($_SESSION['option']->useGroups)
		{
			$groups = $this->shop_model->getGroups(-1);
			if($_SESSION['option']->ProductMultiGroup)
			{
				$product->group = array();
				if($activeGroups = $this->db->getAllDataByFieldInArray($this->shop_model->table('_product_group'), $product->id, 'product'))
					foreach ($activeGroups as $ag) {
						$product->group[] = $ag->group;
						foreach ($groups as $group) {
							if($group->id == $ag->group)
							{
								$group->product_active = $ag->active;
								$group->product_position = $ag->position;
								if(empty($_GET['edit']))
									$group->product_position_max = $this->db->getCount($this->shop_model->table('_product_group'), $group->id, 'group');
								break;
							}
						}
					}
			}
		}
		if(isset($_GET['edit']))
			$this->load->admin_view('products/edit_view', array('product' => $product, 'groups' => $groups));
		else
			$this->load->admin_view('products/info_view', array('product' => $product, 'groups' => $groups));
	}
	
	public function save()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('products_model');
			if($_POST['id'] == 0)
			{
				$link = '';
				if($id = $this->products_model->add($link))
				{
					$this->__after_edit($id);
					if(!empty($_FILES['photo']['name']))
						$this->savephoto('photo', $id, $this->data->latterUAtoEN($name));
					$this->redirect("admin/{$_SESSION['alias']->alias}/{$link}?edit");
				}
				$this->redirect();
			}
			else
			{
				if($__init_before_addEditSave = $this->get__wl_cooperation('__init_before_addEditSave'))
				{
					$product = $this->db->getAllDataById($_SESSION['service']->table.'_products', $_POST['id']);

					if(empty($product))
						$this->redirect();
					
					foreach ($__init_before_addEditSave as $aliasIdInit) {
						$this->load->function_in_alias($aliasIdInit, '__before_edit_save', $product);
					}
				}

				$link = $this->products_model->save($_POST['id']);
				$this->products_model->saveProductOptios($_POST['id']);
				if($_SESSION['option']->ProductMultiGroup == 0)
				{
					$position = explode(' ', $_SESSION['option']->productOrder);
					if($position[0] == 'position' && $_POST['position_old'] != $this->data->post('position') && $_POST['group'] == $_POST['group_old'])
					{
						$this->load->model('wl_position_model');
						$this->wl_position_model->table = $this->products_model->table();
						$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id;
						if($_SESSION['option']->useGroups > 0)
							$this->wl_position_model->where .= " AND `group` = '{$_POST['group']}'";
						$this->wl_position_model->change($_POST['id'], $_POST['position']);
					}
				}
				elseif(!empty($this->products_model->multigroup_new_position))
				{
					$this->load->model('wl_position_model');
					$this->wl_position_model->table = $this->products_model->table('_product_group');
					foreach ($this->products_model->multigroup_new_position as $key) {
						$this->wl_position_model->where = "`group` = '{$key->group}'";
						$this->wl_position_model->change($key->id, $key->position);
					}
				}

				$this->__after_edit($_POST['id']);
				$this->init__wl_cooperation('__after_product_edit', $_POST['id']);

				if(isset($_POST['to']) && $_POST['to'] == 'new')
					$this->redirect("admin/{$_SESSION['alias']->alias}/add");
				elseif(isset($_POST['to']) && $_POST['to'] == 'category')
				{
					$link = 'admin/'.$_SESSION['alias']->alias.'/'.$link;
					$link = explode('/', $link);
					array_pop ($link);
					$link = implode('/', $link);
					$this->redirect($link);
				}

				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->success = 'Дані успішно оновлено!';
				if(isset($_POST['to']) && $_POST['to'] == 'info')
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/'.$link);
				else
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/'.$link.'?edit#tab-main');
			}
		}
	}

	public function confirmProduct()
	{
		if($product_id = $this->data->get('id'))
			if(is_numeric($product_id) && $product_id > 0)
			{
				$this->db->updateRow($_SESSION['service']->table.'_products', ['active' => 1, 'date_edit' => time(), 'author_edit' => $_SESSION['user']->id], $product_id);
				$this->__after_edit($product_id);
				$this->init__wl_cooperation('__after_product_edit', $product_id);
			}
		$this->redirect();
	}

	public function markup()
	{
		$markups = $this->db->getAllData('s_shopshowcase_markup');
		$this->load->admin_view('products/markup_view', array('markups' => $markups));
	}

	public function markup_save()
	{
		if($_POST)
		{
			foreach ($_POST as $key => $value) {
				$data = array();
				$data['from'] = $value['from'];
				$data['to'] = $value['to'];
				$data['value'] = $value['value'];
				$this->db->updateRow('s_shopshowcase_markup', $data, $key);
			}
			$this->db->cache_delete_all('product');
			$this->db->cache_delete_all('products');
			$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
		}

		$this->redirect();
	}

	public function markup_add()
	{
		if($_POST)
		{
			$data = array();
			$data['from'] = $this->data->post('from');
			$data['to'] = $this->data->post('to');
			$data['value'] = $this->data->post('value');
			$this->db->insertRow('s_shopshowcase_markup', $data);

			$this->db->cache_delete_all('product');
			$this->db->cache_delete_all('products');
			$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');

			$this->redirect('admin/'.$_SESSION['alias']->alias.'/markup');
		}
		else 
			$this->load->admin_view('products/markup_add_view');
	}

	public function markup_delete()
	{
		$res = array('result' => false);
		if($this->db->deleteRow('s_shopshowcase_markup', $this->data->post('id')))
		{
			$this->db->cache_delete_all('product');
			$this->db->cache_delete_all('products');
			$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
			$res['result'] = true;
		}

		$this->json($res);
	}

	public function export()
	{
		if(isset($_GET['active']) && empty($_SESSION['option']->exportKey))
		{
			$password = bin2hex(openssl_random_pseudo_bytes(4));
            $password = sha1($_SESSION['alias']->alias . md5($password) . SYS_PASSWORD);
            $option = array();
            $option['service'] = $_SESSION['service']->id;
            $option['alias'] = $_SESSION['alias']->id;
            $option['name'] = 'exportKey';
			if($row = $this->db->getAllDataById('wl_options', $option))
				$this->db->updateRow('wl_options', ['value' => $password], $row->id);
			else
			{
				$option['value'] = $password;
				$this->db->insertRow('wl_options', $option);
			}
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->success = "Ключ безпеки для експорту товарів: <strong>{$password}</strong>";

			$this->db->cache_delete($_SESSION['alias']->alias, 'wl_aliases');
			if (isset($_SESSION['alias-cache'][$_SESSION['alias']->id]))
				unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);
            $this->redirect();
		}
		$_SESSION['alias']->name = 'Експорт товарів';
		if(isset($_GET['groups']) && isset($_GET['type']) && !empty($_SESSION['option']->exportKey))
		{
			if(in_array($_GET['type'], ['prom', 'google', 'facebook']))
			{
				$this->load->smodel('groups_model');
				$this->load->admin_view('export/groups_view', array('groups' => $this->groups_model->getGroups(-1)));
			}
			else
				$this->redirect('admin/'.$_SESSION['alias']->alias.'/export');
		}
		else
		{
			$this->load->smodel('shop_model');
			$groups = $this->shop_model->getGroups(-1);
			$options = $this->shop_model->getOptionsToGroup();
			$this->load->admin_view('export/index_view', compact('groups', 'options'));
		}
	}

	public function export_xlsx()
	{
		$this->load->smodel('shop_model');
		$options = $this->shop_model->getOptionsToGroup();
		if (empty($_POST))
		{
			$this->redirect('admin/' . $_SESSION['alias']->alias . '/export');
			// $_SESSION['alias']->name = 'Експорт товарів';
			// $groups = $this->shop_model->getGroups(-1);
			// $this->load->admin_view('export/products_view', compact('groups', 'options'));
		}
		else
		{
			// echo "<pre>";
			// print_r($_POST);
			// echo "</pre>";
			// exit;
			$groups = -1;
			if ($product_groups = $this->data->post('product_groups'))
			$groups = explode(',', $product_groups);
			$active = true;
			if ($this->data->post('active') == "-1")
			$active = false;

			$_SESSION['option']->paginator_per_page = 0;
			$_SESSION['option']->productOrder = '`group`, position';
			if ($products = $this->shop_model->getProducts($groups, 0, $active))
			{
				$a = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
				$goodfields = array('id' => 'ID', 'article_show' => 'Артикул', 'name' => "Назва", 'price' => 'Ціна', 'availability' => 'Наявність', 'active' => 'Стан', 'text' => 'Опис', 'link' => 'Посилання', 'photo' => 'Основне зображення', 'group' => 'ID групи', 'group_name' => 'Назва групи', 'date_add' => "Додано", 'date_edit' => "Редаговано");
				if (!empty($options))
				foreach ($options as $option)
				{
					if ($option->main)
					{
						$alias = explode('-', $option->alias);
						$alias = $alias[1] ?? $alias[0];
						$goodfields[$alias] = $option->name;
					}
				}
				// echo "<pre>";
				// print_r($goodfields);
				// print_r($products[0]);
				// echo "</pre>";
				// exit;
				$this->load->library('PHPExcel');

				// Set document properties
				$this->phpexcel->getProperties()->setCreator(SITE_NAME)
					->setLastModifiedBy(SITE_NAME)
					->setTitle("Products from " . SITE_NAME);

				$this->phpexcel->setActiveSheetIndex(0);
				$this->phpexcel->getActiveSheet()->setTitle('Products');

				$x = 0;
				foreach ($_POST['fields'] as $field)
				{
					$alias = explode('-', $field);
					if (count($alias) == 2 && is_numeric($alias[0]))
					$field = $alias[1];
					if (array_key_exists($field, $goodfields))
					{
						$y = 1;
						$xy = $a[$x] . $y++;
						$this->phpexcel->getActiveSheet()->setCellValue($xy, $goodfields[$field]);
						foreach ($products as $product)
						{
							$xy = $a[$x] . $y++;
							if (!isset($products[0]->$field))
								continue;
							if ($field == 'availability')
							$field = 'availability_name';
							if ($field == 'active')
								$product->active = $product->active ? 'Активний' : 'Не активний';
							if ($field == 'link')
								$product->$field = SITE_URL . $product->$field;
							if ($field == 'photo')
								$product->$field = IMG_PATH . $product->$field;
							if ($field == 'date_add' || $field == 'date_edit')
								$product->$field = date('d.m.Y H:i', $product->$field);
							$this->phpexcel->getActiveSheet()->setCellValue($xy, $product->$field);
						}
						$x++;
					}
				}

				// Set active sheet index to the first sheet, so Excel opens this as the first sheet
				$this->phpexcel->setActiveSheetIndex(0);

				header('Cache-Control: max-age=0');
				// If you're serving to IE over SSL, then the following may be needed
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
				header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header('Pragma: public'); // HTTP/1.0

				$date = date('dmY');
				if ($_POST['file'] == 'xlsx')
				{
					// Redirect output to a client’s web browser (Excel2007)
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="' . SITE_NAME . '-products-' . $date . '.xlsx"');

					$objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel2007');
					$objWriter->save('php://output');
				}
				elseif ($_POST['file'] == 'xls')
				{
					// Redirect output to a client’s web browser (Excel5)
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . SITE_NAME . '-products-' . $date . '.xls"');

					$objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
					$objWriter->save('php://output');
				}
				elseif ($_POST['file'] == 'csv')
				{
					// Redirect output to a client’s web browser (Excel5)
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . SITE_NAME . '-products-' . $date . '.csv"');

					$objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'CSV')->setDelimiter(',')
						->setEnclosure('"')
						->setSheetIndex(0);
					$objWriter->save('php://output');
				}
			}
			else
				echo "Відсутні товари";
		}
	}

	public function save_export_groups()
	{
		$res = ['result' => false];
		if($to = $this->data->post('export'))
		{
			if(in_array($to, ['prom', 'google', 'facebook']))
			{
				$key = 'export_'.$to;
				$active = 0;
				if($this->data->post('active') == 1)
					$active = 1;

				$update_id = [];
				foreach ($_POST['groups'] as $post_group) {
					if(is_numeric($post_group))
						$update_id[] = $post_group;
				}
				if(!empty($update_id))
					$this->db->updateRow('s_shopshowcase_groups', [$key => $active], ['id' => $update_id]);
				$res['result'] = true;
			}
		}
		$this->load->json($res);
	}

    public function import()
    {
    	$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Імпорт' => '');
    	$_SESSION['alias']->name = 'Імпорт товарів';
    	if(!isset($_SESSION['notify']))
    		$_SESSION['notify'] = new stdClass();
		// require(SYS_PATH.'libraries'.DIRSEP.'SpreadsheetReader/php-excel-reader/excel_reader2.php');
		require(SYS_PATH.'libraries'.DIRSEP.'SpreadsheetReader/SpreadsheetReader.php');

		$path = 'upload';
		if(!is_dir($path))
        {
            if(mkdir($path, 0777) == false)
            {
                $_SESSION['notify']->errors = 'Error create dir ' . $path;
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
            } 
        }

    	if($this->data->uri(3) == 'prepare')
    	{
    		if(!empty($_FILES['file']['name']))
			{
				$ext = explode('.', $_FILES['file']['name']);
				$ext = end($ext);
				if($ext == 'xlsx')
				{
					$path .= '/'.$_SESSION['alias']->alias.'.'.$ext;
					move_uploaded_file($_FILES['file']['tmp_name'], $path);
				}
				else
					$path = false;
			}
			else
			{
				if($path = $this->data->get('path'))
				{
					if(!is_readable($path))
						$path = false;
				}
			}
			if(empty($path))
			{
				$_SESSION['notify']->errors = 'File *.xlsx required!';
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
			}

			try
			{
				@$file = new SpreadsheetReader($path);
				if(!$file->valid())
				{
					$_SESSION['notify']->errors = 'Помилка розпізнавання файлу. Спробуйте перезберегти файл використовуючи Microsoft Word';
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
				}

				$_SESSION['alias']->breadcrumb['Імпорт'] = 'admin/'.$_SESSION['alias']->alias.'/import';
				$_SESSION['alias']->breadcrumb['Аналіз файлу'] = '';
				$_SESSION['alias']->name = 'Імпорт товарів. Аналіз та налаштування колонок';
    			$this->load->admin_view('import/prepare_view', compact('file', 'path'));
    			exit;
			}
			catch (Exception $E)
			{
				$_SESSION['notify']->errors = $E -> getMessage();
				$this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
			}
    	}
    	else if($this->data->uri(3) == 'analyze')
    	{
    		$path = $this->data->post('path');
    		if(is_readable($path))
    		{
    			$errors = '';
    			$col_id = $col_price = $row_start = -1;
    			foreach (['col_id' => 'Колонка з ID', 'col_price' => 'Колонка з ціною', 'row_start' => 'Перший рядок'] as $field => $title) {
    				$$field = $this->data->post($field);
    				if($$field < 0)
    					$errors .= "Відсутнє значення <strong>{$title}</strong><br>";
    			}
    			if(!empty($errors))
    			{
    				$_SESSION['notify']->errors = $errors;
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/import/prepare?path='.$path);
    			}

    			try
				{
					$file = new SpreadsheetReader($path);

					$_SESSION['alias']->breadcrumb['Імпорт'] = 'admin/'.$_SESSION['alias']->alias.'/import';
					$_SESSION['alias']->breadcrumb['Аналіз файлу'] = '';
					$_SESSION['alias']->name = 'Імпорт товарів. Перевірка перед імпортом';
	    			$this->load->admin_view('import/analyze_view', compact('file', 'path', 'col_id', 'col_price', 'row_start'));
	    			exit;
				}
				catch (Exception $E)
				{
					$_SESSION['notify']->errors = $E -> getMessage();
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
				}
    		}
    		else
			{
				$_SESSION['notify']->errors = 'Import error! File '.$path.' not found';
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
			}
    	}
    	else if($this->data->uri(3) == 'go')
    	{
    		$path = $this->data->post('path');
    		if(is_readable($path))
    		{
    			$errors = '';
    			$col_id = $col_price = $row_start = -1;
    			foreach(['path', 'col_id', 'col_price', 'row_start'] as $field) {
    				$$field = $this->data->post($field);
    				if($$field < 0)
    					$errors .= "Відсутнє значення <strong>{$title}</strong><br>";
    			}
    			if(!empty($errors))
    			{
    				$_SESSION['notify']->errors = $errors;
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/import/prepare?path='.$path);
    			}

    			try
				{
					$file = new SpreadsheetReader($path);

					$ids = [];
					foreach ($file as $rowIndex => $row) 
					{
						if($rowIndex < $row_start)
							continue;
						$id = (int) $row[$col_id];
						if(!empty($id) && $id > 0)
							$ids[] = $id;
					}
					if(!empty($ids))
					{
						$where_ntkd = ['alias' => '#p.wl_alias', 'content' => '#p.id'];
						if($_SESSION['language'])
							$where['language'] = $_SESSION['language'];
						$products = $this->db->select('s_shopshowcase_products as p', 'id, article_show, price', $ids)
											->join('wl_ntkd', 'name', $where_ntkd)
											->get('array');

						$updated = 0;
						foreach ($file as $rowIndex => $row) 
						{
							if($rowIndex < $row_start)
								continue;

							$id = (int) $row[$col_id];
							$price_out = (double) $row[$col_price];

							foreach ($products as $product) {
								if($product->id == $id)
								{
									if($product->price != $price_out)
									{
										$this->db->updateRow('s_shopshowcase_products', ['price' => $price_out], $id);
										$updated++;
									}
									break;
								}
							}
						}

						$_SESSION['alias']->breadcrumb['Імпорт'] = 'admin/'.$_SESSION['alias']->alias.'/import';
						$_SESSION['alias']->breadcrumb['Рузультат імпорту'] = '';
    					$_SESSION['alias']->name = 'Імпорт товарів';
						$_SESSION['notify']->success = "Оновлено <strong>{$updated}</strong> товарів";
						$this->load->admin_view('import/analyze_view', compact('file', 'col_id', 'col_price', 'row_start', 'products'));
	    				exit;
					}
					else
					{
						$_SESSION['notify']->errors = 'Товари не ідентифіковано! Перевірте налаштування колонок імпорту';
						$this->redirect('admin/'.$_SESSION['alias']->alias.'/import/prepare?path='.$path);
					}
				}
				catch (Exception $E)
				{
					$_SESSION['notify']->errors = $E -> getMessage();
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
				}
    		}
    		else
			{
				$_SESSION['notify']->errors = 'Import error! File '.$path.' not found';
                $this->redirect('admin/'.$_SESSION['alias']->alias.'/import');
			}
    	}

    	$this->load->admin_view('import/index_view');
    }

	public function saveOption()
	{
		$this->load->smodel('products_model');
		$name = $this->data->post('option');
		$_POST[$name] = $this->data->post('data');
		$this->products_model->saveProductOptios($_POST['id'], false);
		$this->__after_edit($_POST['id']);
	}

	public function saveChangePrice()
	{
		$this->load->smodel('products_model');
		$this->products_model->saveChangePrice($_POST['id']);
		$this->__after_edit($_POST['id']);
		$this->redirect('#tab-changePrice');
	}
	
	public function delete()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('products_model');
			$this->__after_edit($_POST['id']);
			$link = $this->products_model->delete($_POST['id']);
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->success = $_SESSION['admin_options']['word:product_to_delete'].' успішно видалено!';
			$this->redirect("admin/".$link);
		}
	}
	
	public function change_position()
	{
		$res = array('result' => false);
		if(isset($_POST['id']) && is_numeric($_POST['id']) && is_numeric($_POST['position']))
		{
			$this->load->smodel('products_model');
			$this->load->model('wl_position_model');

			$this->wl_position_model->table = $this->products_model->table();
			$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id;
			$newposition = $_POST['position'] + 1;

			$order = 'ASC';
			if($_SESSION['option']->productOrder == 'position DESC')
				$order = 'DESC';
			
			if($_SESSION['option']->useGroups > 0)
			{
				if($_SESSION['option']->ProductMultiGroup)
				{
					if($position = $this->db->getAllDataById($this->products_model->table('_product_group'), $_POST['id']))
					{
						$this->wl_position_model->table = $this->products_model->table('_product_group');
						$this->wl_position_model->where = "`group` = '{$position->group}'";
						if($order == 'DESC')
						{
							$all = $this->db->getCount($this->products_model->table('_product_group'), $position->group, 'group');
							if($all > 0)
								$newposition = $all + 1 - $newposition;
						}
					}
					else
						$this->wl_position_model->table = '';
				}
				else
				{
					if($product = $this->db->getAllDataById($this->products_model->table(), $_POST['id']))
					{
						$this->wl_position_model->where .= " AND `group` = '{$product->group}'";
						if($order == 'DESC')
						{
							$all = $this->db->getCount($this->products_model->table(), $product->group, 'group');
							if($all > 0)
								$newposition = $all + 1 - $newposition;
						}
					}
					else
						$this->wl_position_model->table = '';
				}
			}
			
			if($this->wl_position_model->change($_POST['id'], $newposition))
			{
				$this->__after_edit($_POST['id']);
				$res['result'] = true;
			}
		}
		$this->load->json($res);
	}

	public function changeAvailability()
	{
		$res = array('result' => false);
		if(isset($_POST['availability']) && is_numeric($_POST['availability']) && isset($_POST['id']) && is_numeric($_POST['id']))
		{
			if($this->db->updateRow($_SESSION['service']->table.'_products', array('availability' => $_POST['availability']), $_POST['id']))
			{
				$this->__after_edit($_POST['id']);
				$res['result'] = true;
			}
		}
		$this->load->json($res);
	}

	public function changeActive()
	{
		$res = array('result' => false);
		if(isset($_POST['active']) && is_numeric($_POST['active']) && isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$table = $_SESSION['service']->table.'_products';
			$where = array('id' => $_POST['id']);
			if($_SESSION['option']->useGroups == 1 && $_SESSION['option']->ProductMultiGroup == 1 && isset($_POST['group']) && is_numeric($_POST['group']) && $_POST['group'] > 0) {
				$table = $_SESSION['service']->table.'_product_group';
				$where = array('product' => $_POST['id'], 'group' => $_POST['group']);
			}
			if($this->db->updateRow($table, array('active' => $_POST['active']), $where))
			{
				$this->__after_edit($_POST['id']);
				$res['result'] = true;
			}
		}
		$this->load->json($res);
	}

    public function multi_changeGroup()
    {
    	$_SESSION['notify'] = new stdClass();
    	if(!$_SESSION['option']->ProductMultiGroup)
	    	if($group_id = $this->data->post('group'))
	    		if(is_numeric($group_id))
			    	if($old_group = $this->data->post('old_group'))
			    		if(is_numeric($old_group))
			    			if($group_id != $old_group)
					    		if($products = $this->data->post('products'))
					    		{
					    			$products = explode(',', substr($products, 0, -1));
					    			$this->load->smodel('shop_model');
					    			$count = $this->db->getCount($this->shop_model->table('_products'), $group_id, 'group') + 1;
					    			$this->db->updateRow($this->shop_model->table('_products'), ['group' => $group_id, 'position' => $count, 'date_edit' =>time(), 'author_edit' => $_SESSION['user']->id], ['id' => $products]);
					    			$this->shop_model->rePositionProductsInGroup($old_group);
					    			$this->shop_model->rePositionProductsInGroup($group_id);
					    			foreach ($products as $product_id) {
					    				$this->__after_edit($product_id);
					    			}
					    			$group = $this->shop_model->getGroupByAlias($group_id, 0, 'id');
					    			$_SESSION['notify']->success = 'Товари переміщено у <strong>'.$group->name.'</strong>';
					    			$this->redirect('/admin/'.$group->link);
					    		}
		$_SESSION['notify']->errors = 'Помилка переміщення!';
		$this->redirect();
    }

    public function multi_editProducts()
    {
    	$_SESSION['notify'] = new stdClass();
    	$field = $this->data->post('field');
    	$value = $this->data->post('value');
    	if(in_array($field, ['active', 'availability']) && is_numeric($value))
    		if($products = $this->data->post('products'))
    		{
    			$products = explode(',', substr($products, 0, -1));
    			$this->load->smodel('shop_model');
    			$this->db->updateRow($this->shop_model->table('_products'), [$field => $value, 'date_edit' =>time(), 'author_edit' => $_SESSION['user']->id], ['id' => $products]);
    			foreach ($products as $product_id) {
    				$this->__after_edit($product_id);
    			}
    			$_SESSION['notify']->success = 'Товари <strong>оновлено</strong>';
    		}
		$this->redirect();
    }

    public function multi_deleteProducts()
    {
    	$_SESSION['notify'] = new stdClass();
		if($products = $this->data->post('products'))
		{
			$products = explode(',', substr($products, 0, -1));
			if(!empty($products))
			{
				$this->load->smodel('products_model');
				foreach ($products as $product_id) {
					$this->products_model->delete($product_id);
					$this->__after_edit($product_id);
				}
				$_SESSION['notify']->success = 'Товари <strong>видалено</strong>';
			}
		}
		else
			$_SESSION['notify']->errors = 'Помилка видалення!';
		$this->redirect();
    }

	public function groups()
	{
		$this->load->smodel('groups_model');
		$id = $this->data->uri(3);
		$id = explode('-', $id);
		if($id[0] == 'edit' && is_numeric($id[1]))
			$this->edit_group($id[1]);
		else
		{
			$_SESSION['alias']->name = 'Групи '.$_SESSION['admin_options']['word:products_to_all'];
			$_SESSION['alias']->breadcrumb = array('Групи' => '');

			if(isset($_GET['all']))
			{
				$groups = $this->groups_model->getGroups(-1, false);
				$this->load->admin_view('groups/index_view', array('groups' => $groups));
			}
			else
			{
				$group = false;
				if(is_numeric($id[0]))
				{
					$groups = $this->groups_model->getGroups($id[0], false);
					$group = $this->groups_model->getById($id[0]);
				}
				else
					$groups = $this->groups_model->getGroups(0, false);
				$this->load->admin_view('groups/list_view', array('groups' => $groups, 'group' => $group));
			}
		}
	}

	public function add_group()
	{
		$this->load->smodel('groups_model');
		$groups = $this->groups_model->getGroups(-1);
		$_SESSION['alias']->name = 'Групи';
		$_SESSION['alias']->breadcrumb = array('Групи' => 'admin/'.$_SESSION['alias']->alias.'/groups', 'Додати групу' => '');
		$this->load->admin_view('groups/add_view', array('groups' => $groups));
	}

	private function edit_group($id)
	{
		if($group = $this->groups_model->getById($id, false))
		{
			$this->wl_alias_model->setContent(($group->id * -1));
			$groups = $this->groups_model->getGroups(-1);
			$_SESSION['alias']->breadcrumb = array('Групи' => 'admin/'.$_SESSION['alias']->alias.'/groups', 'Редагувати групу' => '');
			$this->load->admin_view('groups/edit_view', array('group' => $group, 'groups' => $groups));
		}
		else
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
				$alias = '';
				if($id = $this->groups_model->add($alias))
				{
					$this->__after_edit(-$id);
					if(!empty($_FILES['photo']['name']) && $alias)
						$this->savephoto('photo', -$id, $alias);
					$_SESSION['notify']->success = 'Групу успішно додано! Продовжіть наповнення сторінки.';
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/groups/'.$id);
				}
			}
			else
			{
				if($this->groups_model->save($_POST['id']))
				{
					$this->__after_edit(-$_POST['id']);
					$_SESSION['notify']->success = 'Дані успішно оновлено!';
				}
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
			$this->__after_edit(-$_POST['id']);
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
			$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id;
			if($parent >= 0) {
				$this->wl_position_model->where .= " AND `parent` = '{$parent}'";
			}
			if($this->wl_position_model->change($_POST['id'], $_POST['position']))
			{
				$this->__after_edit(-$_POST['id']);
				$this->redirect();
			}
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
			if($option = $this->db->getAllDataById($this->options_model->table(), $id))
			{
				$_SESSION['alias']->name = 'Редагувати властивість "'.$_SESSION['admin_options']['word:option'].'"';
				$_SESSION['alias']->breadcrumb = array('Властивості' => 'admin/'.$_SESSION['alias']->alias.'/options', 'Редагувати властивість' => '');
				$this->load->admin_view('options/edit_view', array('option' => $option));
			}
			else
				$this->load->page_404(false);
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
				{
					foreach ($url as $uri) {
						$group = $this->groups_model->getByAlias($uri, $parent);
						if($group)
							$parent = $group->id;
						else
							$group = false;
					}
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
						$group->parents = $this->groups_model->makeParents($group->parent, $group->parents);
					}
					$this->wl_alias_model->setContent(($group->id * -1));
					$group->group_name = $_SESSION['alias']->name;

					$groups = $this->groups_model->getGroups($group->id, false);
					$options = $this->options_model->getOptions($group->id, false);

					$_SESSION['alias']->name = $_SESSION['alias']->name .'. Керування властивостями ' . $_SESSION['admin_options']['word:products_to_all'];
					$_SESSION['alias']->breadcrumb = array('Властивості' => '');

					$this->load->admin_view('options/index_view', array('group' => $group, 'groups' => $groups, 'options' => $options));
				}
				else
				{
					$groups = $this->groups_model->getGroups(0, false);
					$options = $this->options_model->getOptions(0, false);

					$_SESSION['alias']->name = 'Керування властивостями ' . $_SESSION['admin_options']['word:products_to_all'];
					$_SESSION['alias']->breadcrumb = array('Властивості' => '');

					$this->load->admin_view('options/index_view', array('options' => $options, 'groups' => $groups));
				}
			}
			else
			{
				$options = $this->options_model->getOptions(0, false);

				$_SESSION['alias']->name = 'Керування властивостями ' . $_SESSION['admin_options']['word:products_to_all'];
				$_SESSION['alias']->breadcrumb = array('Властивості' => '');

				$this->load->admin_view('options/index_view', array('options' => $options));	
			}
		}
		$this->load->page_404(false);
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
			if($_POST['id'] == 0)
			{
				if($id = $this->options_model->add_option())
				{
					$_SESSION['notify']->success = 'Властивість успішно додано!';
					$this->redirect('admin/'.$_SESSION['alias']->alias.'/options/'.$id);
				}
			}
			else
			{
				if($this->options_model->saveOption($_POST['id']))
				{
					if(!empty($_POST['savePositionToManual']))
						$_SESSION['optionsavePositionToManual'] = true;
					$_SESSION['notify']->success = 'Властивість успішно оновлено!';

					if(!empty($_FILES['photo']['name']))
					{
						foreach ($_FILES['photo']['name'] as $key => $value) {
							if(!empty($value))
							{
								$path = IMG_PATH;
					            $path = substr($path, strlen(SITE_URL));
					            $path = substr($path, 0, -1);
					            if(!is_dir($path))
					            	mkdir($path, 0777);
					            $path .= '/'.$_SESSION['option']->folder;
					            if(!is_dir($path))
					            	mkdir($path, 0777);
					            $path .= '/options';
					            if(!is_dir($path))
					            	mkdir($path, 0777);
								$path .= '/'.$this->data->post('id').'-'.$this->data->post('alias');
					            if(!is_dir($path))
					            	mkdir($path, 0777);
					            $path .= '/';

					            $fileName = $this->db->select('s_shopshowcase_options', 'alias', $key)->get();

					            $this->load->library('image');
								$this->image->uploadArray('photo', $key, $path, $key.'_'.$fileName->alias);
								$fileName = $key.'_'.$fileName->alias.'.'.$this->image->getExtension();

								$this->db->updateRow('s_shopshowcase_options', array('photo' => $fileName), $key);
				        	}
						}
					}

					$this->db->cache_delete_all();
					$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
				}
			}
		}
		$this->redirect();
	}

	public function delete_option()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0)
		{
			$this->load->smodel('options_model');
			if($this->options_model->deleteOption($_POST['id']))
			{
				$this->db->cache_delete_all();
				$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
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
			$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id;
			if($parent >= 0) {
				$this->wl_position_model->where .= " AND `group` = '{$parent}'";
			}
			if($this->wl_position_model->change($_POST['id'], $_POST['position'])) {
				$this->db->cache_delete_all();
				$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
				$this->redirect();
			}
		}
		$this->load->page_404(false);
	}
	public function change_suboption_position()
	{
		$res = array('result' => false);
		if(isset($_POST['id']) && is_numeric($_POST['position']))
		{
			$id = explode('_', $_POST['id']);
			if(count($id) == 2 && $id[0] == 'option' && is_numeric($id[1]))
			{
				$this->load->smodel('options_model');
				$this->load->model('wl_position_model');
				
				if($option = $this->db->getAllDataById($this->options_model->table('_options'), $id[1]))
				{
					$this->wl_position_model->table = $this->options_model->table();
					$this->wl_position_model->where = '`wl_alias` = '.$_SESSION['alias']->id." AND `group` = {$option->group}";
				
					$position = ($_SESSION['language']) ? $_POST['position'] - 1 : $_POST['position'];
					if($this->wl_position_model->change($id[1], $position))
					{
						$this->db->cache_delete_all();
						$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
						$res['result'] = true;
					}
				}
			}
		}
		$this->load->json($res);
	}

	public function activeOptionProperty()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('options_model');
			if($optionElement = $this->db->getAllDataById($this->options_model->table(), $_POST['id']))
			{
				$active = $optionElement->active == 1 ? 0 : 1;
				if($this->db->updateRow($this->options_model->table(), ['active' => $active], $_POST['id']))
				{
					$this->db->cache_delete_all();
					$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
					if(isset($_POST['json']) && $_POST['json']){
						$this->load->json(array('result' => true));
					} else {
						$this->redirect();
					}
				}
			}
		}
	}

	public function deleteOptionProperty()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('options_model');
			if($this->db->deleteRow($this->options_model->table(), $_POST['id']) && $this->db->deleteRow($this->options_model->table('_options_name'), $_POST['id'], 'option'))
			{
				$this->db->cache_delete_all();
				$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
				if(isset($_POST['json']) && $_POST['json']){
					$this->load->json(array('result' => true));
				} else {
					$this->redirect();
				}
			}
		}
	}

	public function deletePropertyPhoto()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$id = $this->data->post('id');
			$path = $this->data->post('path');

			$path = IMG_PATH.$path;
            $path = substr($path, strlen(SITE_URL));
            
			@unlink($path);
			$this->db->updateRow('s_shopshowcase_options', array('photo' => 0), $id);
			$this->db->cache_delete_all();
			$this->db->cache_delete_all($_SESSION['alias']->alias, 'html');
		}
	}

	private function savephoto($name_field, $content, $name)
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

            $data = [];
            $data['alias'] = $_SESSION['alias']->id;
            $data['content'] = $content;
            $data['file_name'] = $data['title'] = '';
            $data['author'] = $_SESSION['user']->id;
            $data['date_add'] = time();

            $next_position = 1;
            if($last_position = $this->db->select('wl_images', 'position', ['alias' => $_SESSION['alias']->id, 'content' => $content])
            					->order('position DESC')
            					->limit(1)
            					->get())
            	$next_position = $last_position->position + 1;

            $this->load->library('image');
            $sizes = $this->db->getAliasImageSizes();
            if(is_array($_FILES[$name_field]['name']))
            {
            	$file_names = [];
            	foreach ($_FILES[$name_field]['name'] as $i => $value) {
            		$data['position'] = $next_position++;
		            $photo_id = $this->db->insertRow('wl_images', $data);
		            $photo_name = $name . '-' . $photo_id;

					if($this->image->uploadArray($name_field, $i, $path, $photo_name))
					{
						$extension = $this->image->getExtension();
						$photo_name .= '.'.$extension;
		                $this->db->updateRow('wl_images', array('file_name' => $photo_name), $photo_id);
		                $file_names[] = $photo_name;

		                if($sizes)
							foreach ($sizes as $resize) {
		                        if($resize->prefix == '')
		                        {
	                                if(in_array($resize->type, array(1, 11, 12)))
	                                    $this->image->resize($resize->width, $resize->height, $resize->quality, $resize->type);
	                                if(in_array($resize->type, array(2, 21, 22)))
	                                    $this->image->preview($resize->width, $resize->height, $resize->quality, $resize->type);
	                                $this->image->save($resize->prefix);
	                                break;
		                        }
		                    }
					}
				}
				if(empty($file_names))
					return false;
	            return $file_names;
			}
			else
			{
				$data['position'] = $next_position;
	            $photo_id = $this->db->insertRow('wl_images', $data);
	            $name .= '-' . $photo_id;

				if($this->image->upload($name_field, $path, $name))
				{
					$extension = $this->image->getExtension();
					$name .= '.'.$extension;
	                $this->db->updateRow('wl_images', array('file_name' => $name), $photo_id);

	                if($sizes)
						foreach ($sizes as $resize) {
	                        if($resize->prefix == '')
	                        {
                                if(in_array($resize->type, array(1, 11, 12)))
                                    $this->image->resize($resize->width, $resize->height, $resize->quality, $resize->type);
                                if(in_array($resize->type, array(2, 21, 22)))
                                    $this->image->preview($resize->width, $resize->height, $resize->quality, $resize->type);
                                $this->image->save($resize->prefix);
                                break;
	                        }
	                    }
	                return $name;
				}
			}	
		}
		return false;
	}

	public function __savephoto($data)
	{
		if(empty($data) || !is_array($data))
			return false;
		$keys = ['name_field', 'content', 'name'];
		foreach ($keys as $key) {
			if(empty($data[$key]))
				return false;
			$$key = $data[$key];
		}
		return $this->savephoto($name_field, $content, $name);
	}

	public function search_history()
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Історія пошуку' => '');
		$_SESSION['alias']->name .= '. Історія пошуку';

		$this->db->select('s_shopshowcase_search_history as psh');
        $this->db->join('wl_users', 'name as user_name, email as user_email', '#psh.user');
        $this->db->order('last_view DESC');

        if(isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
		{
			$start = 0;
			if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}

        $search_history = $this->db->get('array', false);
        $_SESSION['option']->paginator_total = $this->db->get('count');

        $this->load->admin_view('search_history_view', array('search_history' => $search_history));
	}

	public function __get_Search($content)
    {
    	$this->load->smodel('shop_search_model');
    	return $this->shop_search_model->getByContent($content, true);
    }

    public function similarFolders()
    {
    	$key1 = '';
    	$similarFolders = [];
    	foreach($_POST as $key => $value)
    	{
    		$key = explode('-', $key);
    		if($key[0] == 'key' && !empty($key[1]))
    		{
    			array_shift($key);
    			$key = implode('-', $key);
    			$key1 = $this->data->latterUAtoEN(trim($value));
    			if(!empty($key1))
    				$similarFolders[$key1] = $this->data->post('name-'.$key);
    			if($key != $key1 && $key != 'new')
    			{
    				if(empty($key1))
    					$this->db->executeQuery("UPDATE `s_shopshowcase_products_similar` SET `folder` = NULL WHERE `folder` = '{$key}'");
    				else
    					$this->db->executeQuery("UPDATE `s_shopshowcase_products_similar` SET `folder` = '{$key1}' WHERE `folder` = '{$key}'");
    			}
    		}
    	}
    	$where = ['service' => $_SESSION['service']->id, 'alias' => $_SESSION['alias']->id, 'name' => 'similarFolders'];
    	if($wl_options = $this->db->getAllDataById('wl_options', $where))
    	{
    		$this->db->updateRow('wl_options', ['value' => serialize($similarFolders)], $wl_options->id);
    		if(empty($_SESSION['option']->similarFolders) && !empty($key1))
				$this->db->executeQuery("UPDATE `s_shopshowcase_products_similar` SET `folder`='{$key1}' WHERE `folder` IS NULL");
    	}
    	else
    	{
    		$where['value'] = serialize($similarFolders);
			$this->db->insertRow('wl_options', $where);
			if(!empty($key1))
				$this->db->executeQuery("UPDATE `s_shopshowcase_products_similar` SET `folder`='{$key1}' WHERE `folder` IS NULL");
    	}
		$_SESSION['option']->similarFolders = $similarFolders;
		$_SESSION['notify'] = new stdClass();
		$_SESSION['notify']->success = 'Групи подібності оновлено';
		$this->db->cache_delete($_SESSION['alias']->alias, 'wl_aliases');
		$this->db->cache_delete_all('product');
    	$this->redirect('#tab-similar');
    }

    public function addSimilarProduct()
    {
    	$_SESSION['notify'] = new stdClass();
        $key = 'id';
        if($article_id_value = $this->data->post('article'))
        {
	        $product_1_id = $this->data->post('product');

	        if($_SESSION['option']->ProductUseArticle)
	        {
	        	$key = 'article';
	        	$this->load->smodel('shop_model');
	        	$article_id_value = $this->shop_model->prepareArticleKey($article_id_value);
	        }

	        $product_2 =  $this->db->select('s_shopshowcase_products', 'id', array($key => $article_id_value))->get();

	        if($product_2 && $product_1_id != $product_2->id)
	        {
	        	$where = ['product' => $product_1_id];
	        	if($folder = $this->data->post('folder'))
	        		$where['folder'] = $folder;

	        	$group = 0;
		        if($similar_1 = $this->db->getAllDataById('s_shopshowcase_products_similar', $where))
		        	$group = $similar_1->group;

		        $where['product'] = $product_2->id;
	        	if($similar_2 = $this->db->getAllDataById('s_shopshowcase_products_similar', $where))
	        	{
	        		if($group != $similar_2->group)
	        		{
		        		if($group)
		        			$this->db->updateRow('s_shopshowcase_products_similar', ['group' => $group], $similar_2->id);
		        		else
		        		{
		        			$where['product'] = $product_1_id;
		        			$where['group'] = $similar_2->group;
		        			$this->db->insertRow('s_shopshowcase_products_similar', $where);
		        		}
		        	}
	        	}
	        	else
	        	{
	        		if($group == 0)
	        		{
		        		$group = 1;
		        		if($next = $this->db->getQuery('SELECT MAX(`group`) as nextGroup FROM `s_shopshowcase_products_similar`'))
		        			$group = $next->nextGroup + 1;

		        		$where['product'] = $product_1_id;
	        			$where['group'] = $group;
	        			$this->db->insertRow('s_shopshowcase_products_similar', $where);
		        	}
		        	$where['product'] = $product_2->id;
        			$where['group'] = $group;
        			$this->db->insertRow('s_shopshowcase_products_similar', $where);
	        	}
	        	if($products_similar = $this->db->getAllDataById('s_shopshowcase_products_similar', $group, 'group'))
	        	{
	        		foreach($products_similar as $p)
	        			$this->__after_edit($p->product);
	        	}
	        	else
	        	{
	        		$this->__after_edit($product_1_id);
	        		$this->__after_edit($product_2->id);
	        	}
	        }
	        else
	    	{
	    		if($_SESSION['option']->ProductUseArticle)
	    			$_SESSION['notify']->errors = 'Невірний артикул товару';
	    		else
	    			$_SESSION['notify']->errors = 'Невірний #ID товару';
	    	}
	    }
	    else
    	{
    		if($_SESSION['option']->ProductUseArticle)
    			$_SESSION['notify']->errors = 'Невірний артикул товару';
    		else
    			$_SESSION['notify']->errors = 'Невірний #ID товару';
    	}
        $this->redirect("#tab-similar");
    }
	
	public function deleteSimilarProduct()
	{
		if($similar = $this->db->getAllDataById('s_shopshowcase_products_similar', $this->data->post('id')))
		{
			$similars = $this->db->getAllDataByFieldInArray('s_shopshowcase_products_similar', $similar->group, 'group');
			foreach ($similars as $s) {
				$this->__after_edit($s->product);
			}
			if(count($similars) <= 2)
				$this->db->deleteRow('s_shopshowcase_products_similar', $similar->group, 'group');
			else
				$this->db->deleteRow('s_shopshowcase_products_similar', $similar->id);
		}
	}

	public function saveSimilarText()
	{
		$group = $this->data->post('group');

		if($group != 0)
		{
			$text = htmlentities($_POST['text'], ENT_QUOTES, 'utf-8');

			$products = $this->db->select('s_shopshowcase_products', 'id, wl_alias', array('group' => $group))->get('array');

			if($products)
			{
				foreach($products as $product)
				{
					$where_ntkd = array();
					$where_ntkd['alias'] = $product->wl_alias; 
					$where_ntkd['content'] = $product->id; 
					if($_SESSION['language'] && $_POST['language']) $where_ntkd['language'] = $this->data->post('language');
					if(!isset($_POST['all'])) $where_ntkd['text'] = '';
					$this->db->updateRow("wl_ntkd", array('text' => $text), $where_ntkd);
				}
				
			}
			
		}
		
		$this->redirect();
	}

	public function similarProductsInvoices()
	{
		if($product_id = $this->data->post('product_id'))
		{
			$storages = array();
			if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias1'))
			    foreach ($cooperation as $c) {
			        if($c->type == 'storage') $storages[] = $c->alias2;
			    }
			if(!empty($storages)) {
				$this->load->smodel('shop_model');
				if($product = $this->shop_model->getProduct($product_id, 'id'))
					if(!empty($product->similarProducts))
					{
						echo "<h3>Аналоги / подібні</h3>";
						foreach($product->similarProducts as $similarProduct) {
							echo "<h4><a href=\"/admin/{$similarProduct->link}\">{$similarProduct->manufacturer} <strong>{$similarProduct->article_show}</strong> {$similarProduct->name}</a></h4>";
							$this->load->view('admin/products/edit_tabs/tab-storages', ['invoice_to_product' => $similarProduct, 'storages' => $storages]);
						}
					}
			}
		}
	}

	public function __getRobotKeyWords($content = 0)
    {
    	$words = array();
    	$this->load->smodel('shop_model');
    	if($content > 0)
    	{
    		$this->db->select($this->shop_model->table('_products'), 'id', $_SESSION['alias']->id, 'wl_alias');
    		$this->db->limit(1);
    		if($product = $this->db->get())
    		{
	    		if($product = $this->shop_model->getProduct($product->id, 'id'))
	    		{
	    			foreach ($product as $key => $value) {
	    				if(!is_object($value) && !is_array($value))
		    				$words[] = '{product.'.$key.'}';
	    			}
	    		}
	    	}
    		else
    			$words = array('{product.id}', '{product.name}', '{product.wl_alias}', '{product.article}', '{product.alias}', '{product.group}', '{product.price}', '{product.currency}', '{product.availability}', '{product.active}', '{product.position}', '{product.author_add}', '{product.date_add}', '{product.author_edit}', '{product.date_edit}', '{product.author_add_name}', '{product.author_edit_name}');
    	}
    	elseif($content < 0)
    	{
    		$this->db->select($this->shop_model->table('_groups'), 'alias', $_SESSION['alias']->id, 'wl_alias');
    		$this->db->limit(1);
    		if($group = $this->db->get())
    		{
	    		if($group = $this->shop_model->getGroupByAlias($group->alias))
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

    public function changePromGroup()
	{
		$res = array('result' => false);
		if(!empty($_SESSION['option']->export_ok))
		{
			$groupId = $this->data->post('groupId');
			$promGroupId = $this->data->post('promGroupId');

			if($groupId)
			{
				$this->db->updateRow('s_shopshowcase_groups', array('prom_id' => $promGroupId), $groupId);
				$res['result'] = true;
			}
		}
		else
			$res['error'] = 'Active `prom` => 1 option';

		$this->json($res);
	}

	public function promo()
	{
		$uri = $this->data->uri(3);
		if($uri == 'add')
		{
			$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Акції' => 'admin/'.$_SESSION['alias']->alias.'/promo', 'Додати' => '');
			$_SESSION['alias']->title = 'Додати акцію';
			$_SESSION['alias']->name = '<i class="fa fa-tasks" aria-hidden="true"></i> Додати акцію';

			$this->load->admin_view('promo/add_view');
		}
		else if(is_numeric($uri) && $uri > 0)
		{
			$this->load->smodel('shop_promo_model');
			if($promo = $this->shop_promo_model->get($uri))
			{
				$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Акції' => 'admin/'.$_SESSION['alias']->alias.'/promo', '#'.$promo->id => '');
				$_SESSION['alias']->title = 'Акція #'.$promo->id;
				$_SESSION['alias']->name = '<i class="fa fa-tasks" aria-hidden="true"></i> Акція #'.$promo->id;

				$promo->productsCount = $this->shop_promo_model->getProducts($promo->id, 'count');

				$this->load->smodel('shop_model');
				$this->shop_model->init();
				$this->load->admin_view('promo/edit_view', ['promo' => $promo, 'groups' => $this->shop_model->allGroups]);
			}
			else
				$this->load->page_404(false);
		}
		else if(empty($uri))
		{
			$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Акції' => '');
			$_SESSION['alias']->title = 'Керування акціями';
			$_SESSION['alias']->name = '<i class="fa fa-tasks" aria-hidden="true"></i> Керування акціями';
			$this->load->smodel('shop_promo_model');
			$this->load->admin_view('promo/index_view', ['promotions' => $this->shop_promo_model->get()]);
		}
		else
			$this->load->page_404(false);
	}

	public function save_promo()
	{
		$this->load->smodel('shop_promo_model');
		if($promoId = $this->shop_promo_model->save())
		{
			if($products = $this->shop_promo_model->getProducts($promoId))
				foreach ($products as $product)
				{
					$this->__after_edit($product->id);
				}
			$this->redirect('admin/'.$_SESSION['alias']->alias.'/promo/'.$promoId);
		}
		else
			$this->redirect();
	}

	public function promo_getProducts()
	{
		$res = '';
		if($promoId = $this->data->post('promo'))
			if(is_numeric($promoId) && $promoId > 0)
			{
				$this->load->smodel('shop_promo_model');
				$res = '<h4>Товари, які беруть участь в акції #'.$promoId.'</h4>';
				if($products = $this->shop_promo_model->getProducts($promoId))
					foreach ($products as $product) {
						$attr = 'checked';
						$title = '';
						if($product->old_price > $product->price)
						{
							$title = 'title="Власна акційна ціна" class="text-success"';
							$attr = 'disabled';
							$product->name .= ' ('.$product->old_price.' '.$product->currency.' => '.$product->price.')';
						}
						$res .= '<label '.$title.'><input type="checkbox" value="'.$product->id.'" '.$attr.'> <strong>'.$product->article_show.'</strong> '.$product->name.' <a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/search?id='.$product->id.'"><small>Детальніше</small></a></label>';
					}
			}
		$this->load->json($res);
	}

	public function promo_getGroupProducts()
	{
		$res = '';
		if($groupId = $this->data->post('group'))
			if(is_numeric($groupId) && $groupId > 0)
				if($promoId = $this->data->post('promo'))
					if(is_numeric($promoId) && $promoId > 0)
					{
						$this->load->smodel('shop_model');
						$this->shop_model->init();
						if(isset($this->shop_model->allGroups[$groupId]))
						{
							$res = '<h4>'.$this->shop_model->allGroups[$groupId]->name.'</h4>';
							$_SESSION['option']->paginator_per_page = 0;
							if($products = $this->shop_model->getProducts($groupId))
								foreach ($products as $product) {
									$attr = $title = '';
									if($product->promo > 0)
									{
										if($product->promo == $promoId)
											$attr = 'checked';
										else
										{
											$title = 'title="Інша акція" class="text-danger"';
											$attr = 'disabled';
											$product->name .= ' (<a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/promo/'.$product->promo.'" class="btn btn-xs btn-info">Акція #'.$product->promo.'</a>';
										}
									}
									else if($product->old_price > $product->price)
									{
										$title = 'title="Власна акційна ціна" class="text-success"';
										$attr = 'disabled';
										$product->name .= ' ('.$product->old_price.' '.$product->currency.' => '.$product->price.')';
									}
									$res .= '<label '.$title.'><input type="checkbox" value="'.$product->id.'" '.$attr.'> <strong>'.$product->article_show.'</strong> '.$product->name.' <a href="'.SITE_URL.'admin/'.$_SESSION['alias']->alias.'/search?id='.$product->id.'"><small>Детальніше</small></a></label>';
								}
						}
					}
		$this->load->json($res);
	}

	public function promo_saveProduct()
	{
		$res = ['save' => false, 'count' => 0];
		if($productId = $this->data->post('product'))
			if(is_numeric($productId) && $productId > 0)
				if($promoId = $this->data->post('promo'))
					if(is_numeric($promoId) && $promoId > 0 && isset($_POST['active']))
					{
						$active = $_POST['active'] == 1 ? $promoId : 0;
						$this->load->smodel('shop_promo_model');
						$res['save'] = $this->shop_promo_model->saveProduct($productId, $promoId, $active);
						$res['count'] = $this->shop_promo_model->getProducts($promoId, 'count');
						$this->__after_edit($productId);
					}
		$this->load->json($res);
	}
    
    public function save_price_format()
    {
        if($_SESSION['user']->type == 1 && $this->data->post('service'))
        {
            $price_format = array('before' => '', 'after' => '', 'round' => 2);
            $price_format['before'] = htmlspecialchars($_POST['before']);
            $price_format['after'] = htmlspecialchars($_POST['after']);
            $price_format['round'] = $this->data->post('round');
            $price_format['penny'] = $this->data->post('penny');
            $value = serialize($price_format);

            $where = array('alias' => $_SESSION['alias']->id, 'name' => 'price_format');
            $where['service'] = $this->data->post('service');
            if($option = $this->db->getAllDataById('wl_options', $where))
            {
                if($option->value != $value)
                    $this->db->updateRow('wl_options', array('value' => $value), $option->id);
            }
            else
            {
                $where['value'] = $value;
                $this->db->insertRow('wl_options', $where);
            }

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Формат виводу ціни оновлено';
            unset($_SESSION['alias-cache'][$_SESSION['alias']->id]);
            $this->db->cache_delete($_SESSION['alias']->alias, 'wl_aliases');
        }
        $this->redirect();
    }

    public function clear_cache()
    {
    	if($id = $this->data->get('id'))
    		if(is_numeric($id))
    			$this->__after_edit($id);
    	$this->redirect();
    }

    public function __set_rating($product_id)
	{
		$rating = $this->db->getQuery("SELECT SUM(`rating`) as rating, count(`rating`) as votes FROM `wl_comments` WHERE `alias` = {$_SESSION['alias']->id} AND `content` = {$product_id} AND `parent` = 0 AND `status` < 3");
		if(!empty($rating->votes))
		{
			$this->db->updateRow('s_shopshowcase_products', ['rating' => round($rating->rating / $rating->votes, 3), 'rating_votes' => $rating->votes], $product_id);
			$this->__after_edit($product_id);
		}
	}

    public function __after_edit($content)
    {
    	if($_SESSION['cache'])
    		$this->db->cache_delete($this->db->getHTMLCacheKey($content));

		// $this->db->updateRow('wl_ntkd', ['get_sivafc' => NULL], ['alias' => $_SESSION['alias']->id, 'content' => $content]);

    	$this->load->smodel('shop_model');
    	$parent_to = 0;
    	if($content > 0)
    	{
    		if($product = $this->shop_model->getProduct($content, 'id'))
    		{
    			$this->db->cache_delete('product'.DIRSEP.$this->db->getCacheContentKey('product_', $product->id, 2));
    			$this->db->cache_delete('product'.DIRSEP.$this->db->getCacheContentKey('product_', $product->id, 2)."-all_info");
    			if(!empty($product->group))
    			{
	    			if(is_numeric($product->group))
	    				$parent_to = $product->group;
	    			else if(is_object($product->group))
	    				$parent_to = $product->group->id;
	    			elseif(is_array($product->group))
	    				foreach($product->group as $g)
	    					$this->p___after_editGroup($g->id, $content);
	    		}
    			if(!empty($product->similarProducts))
				{
    				if(!empty($_SESSION['option']->similarFolders)) {
    					if(!is_array($_SESSION['option']->similarFolders))
    						$_SESSION['option']->similarFolders = unserialize($_SESSION['option']->similarFolders);
    					foreach($_SESSION['option']->similarFolders as $similarFolderKey => $similarFolderName) {
    						if(!empty($product->similarProducts[$similarFolderKey])) foreach($product->similarProducts[$similarFolderKey] as $similarProduct) {
	    						$this->db->cache_delete('product'.DIRSEP.$this->db->getCacheContentKey('product_', $similarProduct->id, 2));
    							$this->db->cache_delete('product'.DIRSEP.$this->db->getCacheContentKey('product_', $similarProduct->id, 2)."-all_info");
    						}
    					}
    				}
    				else
    					foreach($product->similarProducts as $similarProduct) {
    						$this->db->cache_delete('product'.DIRSEP.$this->db->getCacheContentKey('product_', $similarProduct->id, 2));
							$this->db->cache_delete('product'.DIRSEP.$this->db->getCacheContentKey('product_', $similarProduct->id, 2)."-all_info");
    					}
    			}
    		}
    		else
	    		return false;
    	}
    	elseif ($content < 0)
    		$parent_to = -$content;
    	if($parent_to)
	    	$this->p___after_editGroup($parent_to, $content);
    	return true;
    }

    private function p___after_editGroup($parent_to, $content = 0)
    {
    	if(empty($this->shop_model->allGroups))
    		$this->shop_model->init();
		while ($parent_to > 0) {
    		$this->db->cache_delete("subgroups".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to));
    		$this->db->cache_delete("products".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to));
    		$this->db->cache_delete("optionsToGroup".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to)."+filter");
    		$this->db->cache_delete_all("products_in_group".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to));
    		if(isset($this->shop_model->allGroups[$parent_to]))
    			$parent_to = $this->shop_model->allGroups[$parent_to]->parent;
    		else
    			$parent_to = 0;
    	}
    	
    	if ($content < 0)
			$this->db->cache_delete('allGroups');
		$this->shop_model->allGroups = false;
		$this->shop_model->init();
		while ($parent_to > 0) {
    		$this->db->cache_delete("subgroups".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to));
    		$this->db->cache_delete("products".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to));
    		$this->db->cache_delete("optionsToGroup".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to)."+filter");
    		$this->db->cache_delete_all("products_in_group".DIRSEP.$this->db->getCacheContentKey('group-', $parent_to));
    		if(isset($this->shop_model->allGroups[$parent_to]))
    			$parent_to = $this->shop_model->allGroups[$parent_to]->parent;
    		else
    			return false;
    	}
    }
    



    public function __dashboard_subview()
    {   
        if(empty($_SESSION['option']->userCanAdd))
        	return false;

        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('shop_model');
        ob_start();
        $products = $this->shop_model->getProducts(-1, 0, -1);
        $this->load->view('admin/__dashboard_subview', array('products' => $products, 'searchForm' => true));
        $subview = ob_get_contents();
        ob_end_clean();
        return $subview;
    }

    public function __tab_profile($user_id = 0)
    {   
        if(empty($_SESSION['option']->userCanAdd) || empty($user_id))
        	return false;

        if(!isset($_SESSION['option']->paginator_per_page) || $_SESSION['option']->paginator_per_page < 5)
            $_SESSION['option']->paginator_per_page = 20;
        $this->load->smodel('shop_model');
        ob_start();
        $_GET['author_add'] = $user_id;
        $products = $this->shop_model->getProducts(-1, 0, false);
        $this->load->view('admin/__dashboard_subview', array('products' => $products, 'searchForm' => false));
        $tab = new stdClass();
        $tab->key = $_SESSION['alias']->alias;
        $tab->name = 'Товари автора';
        $tab->content = ob_get_contents();
        ob_end_clean();
        return $tab;
    }


    public function __reset_products_position()
	{
		$products = $this->db->select('s_shopshowcase_products as p', 'id, `group`, position')
								->order('group')
								->get();
		$group = $products[0]->group;
		$position = 1;
		$updated = 0;
		foreach ($products as $product) {
			if($group != $product->group)
			{
				$group = $product->group;
				$position = 1;
			}
			if($product->position != $position)
			{
				$this->db->updateRow('s_shopshowcase_products', ['position' => $position], $product->id);
				$updated++;
			}
			$position++;
		}
		echo "updated: ".$updated;
		// echo "<pre>";
		// print_r($products);
		// echo "</pre>";
		// exit;
	}
}

?>