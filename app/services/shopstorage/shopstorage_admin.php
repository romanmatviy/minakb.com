<?php

/*

 	Service "Shop Storage 1.2"
	for WhiteLion 1.0

*/

class shopstorage_admin extends Controller {
				
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
    	$this->load->smodel('storage_model');
    	if(is_numeric($uri))
    	{
    		if($product = $this->storage_model->getInvoice($uri, -1))
    		{
    			$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Накладна #'.$uri => '');
				$_SESSION['alias']->name .= '. Товарна накладна #'.$uri;

    			$product->info = $this->getProduct('id', $product->product);
    			$product->history = $this->storage_model->getProducts($product->product, true);
    			$this->load->admin_view('storage/detal_view', array('product' => $product));
    		}
    		$this->load->page_404(false);
    	}
    	else
    	{
    		$invoices = false;
    		$_SESSION['option']->paginator_per_page = 50;
    		if(isset($_GET['article']))
    		{
    			$products = $this->getProduct('article', $this->data->get('article'));
    			if($products) 
    			{
    				foreach ($products as $product) {
				    	$invoicesProduct = $this->storage_model->getProducts($product->id, -1);
				    	if($invoicesProduct)
				    	{
				    		foreach ($invoicesProduct as $invoice) {
				    			$invoice->info = $product;
				    			$invoices[] = clone $invoice;
				    		}
				    	}
				    }
    			}
    		}
    		elseif(isset($_GET['id']))
    		{
    			$product = $this->getProduct('id', $this->data->get('id'));
    			if($product) 
    			{
			    	$invoices = $this->storage_model->getProducts($product->id, -1);
			    	if($invoices)
			    	{
			    		foreach ($invoices as $invoice) {
			    			$invoice->info = $product;
			    		}
			    	}
    			}
    		}
    		else
    		{
    			$invoices = $this->storage_model->getProducts(0, -1);
		    	if($invoices)
		    	{
		    		foreach ($invoices as $product) {
		    			$product->info = $this->getProduct('id', $product->product);
		    		}
		    	}
    		}
	    	$this->load->admin_view('storage/list_view', array('invoices' => $invoices));
    	}
    }
	
	public function add()
	{
		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Додати накладну' => '');
		$_SESSION['alias']->name .= '. Додати накладну';

		$this->load->smodel('storage_model');
		$storage = $this->storage_model->getStorage();

		$this->load->admin_view('storage/add_view', array('storage' => $storage));
	}
	
	public function edit()
	{
		$id = $this->data->uri(3);
    	if(is_numeric($id))
    	{
    		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Редагувати накладну' => '');
			$_SESSION['alias']->name = 'Редагувати товарну накладну #'.$id;

    		$this->load->smodel('storage_model');
    		$product = $this->storage_model->getInvoice($id, -1);
    		if($product)
    		{
    			$product->info = $this->getProduct('id', $product->product);
    			$product->history = $this->storage_model->getProducts($product->product);
    			$storage = $this->storage_model->getStorage();
    			$this->load->admin_view('storage/edit_view', array('product' => $product, 'storage' => $storage));
    		}
    		$this->load->page_404();
    	}
	}
	
	public function save()
	{
		
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('storage_model');
			$id = 0;
			$_SESSION['notify'] = new stdClass();

			if($_POST['id'] == 0)
			{
				if($id = $this->storage_model->save())
					$_SESSION['notify']->success = 'Накладну успішно додано!';
			}
			else
			{
				if($this->storage_model->save($_POST['id']))
				{
					$id = $_POST['id'];
					$_SESSION['notify']->success = 'Дані успішно оновлено!';
				}
			}
			if(isset($_POST['to']) && $_POST['to'] == 'new' || $id == 0)
				$this->redirect("admin/{$_SESSION['alias']->alias}/add");
			$this->redirect('admin/'.$_SESSION['alias']->alias.'/'.$id);
		}
	}
	
	public function delete()
	{
		if(isset($_POST['id']) && is_numeric($_POST['id']))
		{
			$this->load->smodel('storage_model');
			if($this->storage_model->delete($_POST['id']))
			{
				$_SESSION['notify'] = new stdClass();
				$_SESSION['notify']->success = 'Накладну успішно видалено!';
				$this->redirect("admin/{$_SESSION['alias']->alias}");
			}
		}
	}

	public function options()
	{
		$this->load->smodel('storage_model');

		$storage = $this->storage_model->getStorage();

		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Налаштування' => '');
		$_SESSION['alias']->name = 'Керування ' . $storage->name;

		$options = null;

        $this->db->executeQuery("SELECT * FROM wl_options WHERE service = '{$_SESSION['service']->id}' AND alias = '0'");
        if($this->db->numRows() > 0){
            $options_all = $this->db->getRows('array');
            foreach ($options_all as $option) {
                $options[$option->name] = new stdClass();
                $options[$option->name]->name = $option->name;
                $options[$option->name]->value = $option->value;
                $options[$option->name]->type = 'text';
                $options[$option->name]->title = $option->name;
            }
        } 
        $this->db->executeQuery("SELECT * FROM wl_options WHERE service = '{$_SESSION['service']->id}' AND alias = '{$_SESSION['alias']->id}'");
        if($this->db->numRows() > 0){
            $options_all = $this->db->getRows('array');
            foreach ($options_all as $option)
            {
                if(isset($options[$option->name])) $options[$option->name]->value = $option->value;
                else
                {
                    $options[$option->name] = new stdClass();
                    $options[$option->name]->name = $option->name;
                    $options[$option->name]->value = $option->value;
                    $options[$option->name]->type = 'text';
                    $options[$option->name]->title = $option->name;
                }
            }
        }

        $path = APP_PATH.'services'.DIRSEP.$_SESSION['alias']->service.DIRSEP.'models/install_model.php';
        if(file_exists($path)){
            require_once($path);
            $install = new install();

            if(!empty($install->options) && !empty($options))
            {
                foreach ($install->options as $key => $value) {
                    if(isset($install->options_type[$key]) && isset($options[$key])) $options[$key]->type = $install->options_type[$key];
                    if(isset($install->options_title[$key]) && isset($options[$key])) $options[$key]->title = $install->options_title[$key];
                }
            }
        }

		$this->load->admin_view('options/index_view', array('storage' => $storage, 'options' => $options));
	}

	public function options_save()
	{
		$data = array();
		$data['active'] = $this->data->post('active');
		$data['name'] = $this->data->post('name');
		$data['markup'] = $this->data->post('markup');
		$data['currency'] = $this->data->post('currency');
		$this->db->updateRow($_SESSION['service']->table, $data, $_SESSION['alias']->id);
		
		$storage = $this->db->getAllDataById('wl_ntkd', array('alias' => $_SESSION['alias']->id, 'content' => 0));
		if($storage)
		{
			$data = array();
			$data['name'] = $this->data->post('name');
			$data['list'] = $this->data->post('time');
			$this->db->updateRow('wl_ntkd', $data, $storage->id);
		}

		if($_POST['active'] != $_POST['active_old'])
		{
			if($storages = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', array('alias2' => $_SESSION['alias']->id)))
			{
				foreach ($storages as $storage) {
					if($storage->type == 'storage' && $this->data->post('active') == 0)
						$this->db->updateRow('wl_aliases_cooperation', array('type' => 'storage-0'), $storage->id);
					elseif($storage->type == 'storage-0' && $this->data->post('active') == 1)
						$this->db->updateRow('wl_aliases_cooperation', array('type' => 'storage'), $storage->id);
				}
			}
		}

		$_SESSION['notify'] = new stdClass();
		$_SESSION['notify']->success = 'Інформацію успішно оновлено!';
		$this->redirect();
	}

	public function markup_save()
	{
		$table = $_SESSION['service']->table.'_markup';
		$data = array();
		$data['storage'] = $_SESSION['alias']->id;
		foreach ($_POST as $key => $value) {
			$key = explode('-', $key);
			if($key[0] == 'markup' && isset($key[1]))
			{
				$data['user_type'] = $key[1];
				$markup = $this->db->getAllDataById($table, $data);
				if($markup)
				{
					if($markup->markup != $value) $this->db->updateRow($table, array('markup' => $value), $markup->id);
				}
				else
				{
					$data['markup'] = $value;
					$this->db->insertRow($table, $data);
					unset($data['markup']);
				}
			}
		}
		$_SESSION['notify'] = new stdClass();
		$_SESSION['notify']->success = 'Інформацію успішно оновлено!';
		$this->redirect();
	}

	public function update()
	{
		$storage = $this->db->getAllDataById('s_shopstorage', $_SESSION['alias']->id);
		if(empty($storage->updateRows) || empty($storage->updateCols))
		{
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->errors = 'Увага! Файл імпорту (структуру файлу) не налаштовано!';
			$this->redirect("admin/{$_SESSION['alias']->alias}/optionsimport");
		}

		$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Оновити прайс' => '');
		$_SESSION['alias']->name = 'Оновити прайс ' . $_SESSION['alias']->name;

		$this->db->select('s_shopstorage_updates as s', '*', $_SESSION['alias']->id, 'storage');
		$this->db->join('wl_users', 'name, email', '#s.manager');
		$this->db->order('date DESC');
		$this->db->limit(20);
		$history = $this->db->get('array');

		$this->load->admin_view('update/index_view', array('history' => $history));
	}

	public function updateStart()
	{
		$_SESSION['notify'] = new stdClass();
		require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
		require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/SpreadsheetReader.php');

		try
		{
			$ext = explode('.', $this->data->post('file'));
			$ext = end($ext);
			$from = 'upload/'.$_SESSION['alias']->alias.'_prepare.'.$ext;
			$path = 'upload/'.$_SESSION['alias']->alias.'.'.$ext;
			if(is_readable($path))
				unlink($path);
			if(rename ($from, $path))
			{
				$BaseMem = memory_get_usage();
				$Time = microtime(true);
				$Spreadsheet = new SpreadsheetReader($path);

				$this->load->smodel('import_model');

				if($this->import_model->checkRows($Spreadsheet, true))
				{
					$CurrentMem = memory_get_usage();
					$_SESSION['notify']->success = 'Інформацію успішно імпортовано!';
					$memoty = round(($CurrentMem - $BaseMem)/1024, 2);
					if($memoty > 1024) $memoty = round($memoty / 1024, 2) . ' Мб';
					else $memoty .= ' Кб';
					$_SESSION['import'] = 'Використано пам\'яті: '.$memoty.' <br>';
					$_SESSION['import'] .= 'Час: '.ceil(microtime(true) - $Time).' сек <br>';
					$_SESSION['import'] .= 'Оновлено товарів на складі: '.$this->import_model->updated.' <br>';
					$_SESSION['import'] .= 'Додано товарів на склад: '.$this->import_model->insertedStorage.' <br>';
					$_SESSION['import'] .= 'Додано нових товарів: '.$this->import_model->inserted.' <br>';
					$_SESSION['import'] .= 'Видалено: '.$this->import_model->deleted.' <br>';
					$_SESSION['import'] .= 'Запитів до бази даних: '.$this->db->countQuery;
					if(!empty($this->import_model->errors))
					{
						$_SESSION['import'] .= '<hr>УВАГА! Проблемні номера: <br>';
						$_SESSION['import'] .= implode(' <br>', $this->import_model->errors);
					}
				}
				else
					$_SESSION['notify']->errors = 'Перевірте коректність файлу! Ймовірно він з іншого складу або імпорт файлів для даного складу не налаштовано.';
			}
			else
				$_SESSION['notify']->errors = 'Помилка копіювання прайсу! Повторіть спробу. Якщо помилка не зникла, зверніться до розробників.';
		}
		catch (Exception $E)
		{
			$_SESSION['notify']->errors = $E -> getMessage();
		}
		$this->redirect('admin/'.$_SESSION['alias']->alias.'/update');
	}

	public function getProductByArticle()
	{
		$product = $this->getProduct('article', $this->data->post('product'));
		$this->load->json($product);
	}

	public function getProductById()
	{
		$product = $this->getProduct('id', $this->data->post('product'));
		$this->load->json($product);
	}

	private function getProduct($key, $id)
	{
		if($cooperation = $this->db->getAllDataByFieldInArray('wl_aliases_cooperation', $_SESSION['alias']->id, 'alias2'))
			foreach ($cooperation as $shop) {
				if($shop->type == 'storage')
				{
					if($key == 'article')
						return $this->load->function_in_alias($shop->alias1, '__get_Products', array($key => $id));
					else
						return $this->load->function_in_alias($shop->alias1, '__get_Product', array($key => $id, 'key' => $key));
				}
			}
		return false;
	}

	public function history()
	{
		$id = $this->data->uri(3);
		if($update = $this->db->getAllDataById('s_shopstorage_updates', $id))
		{
			$this->db->select('products_update_history as h', '*', $id, 'update');
			$this->db->join('s_shopparts_products as p', 'article, alias', '#h.product');
			$this->db->join('s_shopparts_manufactures', 'name as manufacturer_name', '#p.manufacturer');
			$products = $this->db->get('array');
			$this->load->admin_view('update/history_view', array('products' => $products, 'update' => $update));
		}
		else
			$this->load->page_404();
	}

	public function view_last_import()
	{
		$id = $this->data->uri(3);
		if($update = $this->db->getAllDataByFieldInArray('s_shopstorage_updates', $_SESSION['alias']->id, 'storage', "id DESC LIMIT 1"))
		{
			$BaseMem = memory_get_usage();
			$BaseTime = microtime(true);

			if($update[0]->file)
			{
				$ext = explode('.', $update[0]->file);
				$ext = end($ext);
				$path = 'upload/'.$_SESSION['alias']->alias.'.'.$ext;
				if(is_readable($path))
				{
					require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
					require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/SpreadsheetReader.php');

					$Spreadsheet = new SpreadsheetReader($path);

					$this->load->admin_view('update/last_import_view', array('Spreadsheet' => $Spreadsheet, 'update' => $update[0], 'path' => $path, 'BaseMem' => $BaseMem, 'BaseTime' => $BaseTime));
				}
				else
					$this->load->notify_view(array('errors' => 'Файл архіву відсутній'));
			}
		}
		else
			$this->load->page_404();
	}

	public function view_before_import()
	{
		$_SESSION['notify'] = new stdClass();
		require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
		require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/SpreadsheetReader.php');

		try
		{
			$path = false;
			if(isset($_POST['file']))
			{
				$ext = explode('.', $this->data->post('file'));
				$ext = end($ext);
				$file = 'upload/'.$_SESSION['alias']->alias.'_prepare.'.$ext;
				if(is_readable($file))
					$path = $file;
			}
			elseif(!empty($_FILES['price']['name']))
			{
				$ext = explode('.', $_FILES['price']['name']);
				$ext = end($ext);
				$path = 'upload/'.$_SESSION['alias']->alias.'_prepare.'.$ext;
				move_uploaded_file($_FILES['price']['tmp_name'], $path);
			}
			if($path)
			{
				$BaseMem = memory_get_usage();
				$BaseTime = microtime(true);
				
				$this->load->smodel('import_model');
				$cols = $Spreadsheet = false;

				$Spreadsheet = new SpreadsheetReader($path);
				$cols = $this->import_model->checkRows($Spreadsheet);

				$this->load->admin_view('update/before_import_view', array('Spreadsheet' => $Spreadsheet, 'cols' => $cols, 'BaseMem' => $BaseMem, 'BaseTime' => $BaseTime));
			}
		}
		catch (Exception $E)
		{
			$_SESSION['notify']->errors = $E -> getMessage();
		}
		$this->redirect();
	}

	public function optionsImport()
	{
		require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
		require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/SpreadsheetReader.php');

		try
		{
			$BaseMem = memory_get_usage();
			$BaseTime = microtime(true);

			$_SESSION['alias']->breadcrumb = array($_SESSION['alias']->name => 'admin/'.$_SESSION['alias']->alias, 'Налаштування складу' => 'admin/'.$_SESSION['alias']->alias.'/options', 'Налаштування структури файлу імпорту' => '');
			$_SESSION['alias']->name = 'Налаштування структури файлу імпорту для ' . $_SESSION['alias']->name;

			if($storage = $this->db->getAllDataById($_SESSION['service']->table, $_SESSION['alias']->id))
			{
				$spreadsheet = false;
				$fileName = '';
				if($fileName = $this->data->get('file'))
				{
					$ext = explode('.', $this->data->get('file'));
					$ext = end($ext);
					$file = 'upload/'.$_SESSION['alias']->alias.'_prepare.'.$ext;
					if(is_readable($file))
						$spreadsheet = new SpreadsheetReader($file);
				}
				elseif(!empty($_FILES['price']))
				{
					$fileName = $_FILES['price']['name'];
					$ext = explode('.', $_FILES['price']['name']);
					$ext = end($ext);
					$file = 'upload/'.$_SESSION['alias']->alias.'_prepare.'.$ext;
					if(move_uploaded_file($_FILES['price']['tmp_name'], $file))
						$spreadsheet = new SpreadsheetReader($file);
				}

				$this->load->admin_view('options/import_view', array('storage' => $storage, 'Spreadsheet' => $spreadsheet, 'file' => $fileName, 'BaseMem' => $BaseMem, 'BaseTime' => $BaseTime));
				exit;
			}
			else
				exit('Error config shopstorage!');
		}
		catch (Exception $E)
		{
			exit($E -> getMessage());
		}
		$this->redirect();
	}

	public function optionsImportSaveRows()
	{
		$_SESSION['notify'] = new stdClass();
		if(isset($_POST['file']) && isset($_POST['newKeyRow']) && is_numeric($_POST['newKeyRow']))
		{
			$ext = explode('.', $this->data->post('file'));
			$ext = end($ext);
			$file = 'upload/'.$_SESSION['alias']->alias.'_prepare.'.$ext;
			if(is_readable($file))
			{
				require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
				require(SYS_PATH.'libraries'.DIRSEP.'spreadsheet-reader-master/SpreadsheetReader.php');

				$row = array();
				$spreadsheet = new SpreadsheetReader($file);
				foreach ($spreadsheet as $Key => $Row)
				if($Key == $this->data->post('newKeyRow'))
				{
					foreach ($Row as $newIndex => $newName) {
						$row[$newIndex] = $newName;
					}
					break;
				}
				if(!empty($row))
				{
					$updateCols = '';
					if($storage = $this->db->getAllDataById($_SESSION['service']->table, $_SESSION['alias']->id))
					{
						if(empty($storage->updateCols))
						{
							$updateCols = new stdClass();
							$updateCols->in_id = 0; // інвентаризаційний номер поставщика (колонка коду ідентифікації у постачальника, якщо нема то артикул)
							$updateCols->article = 0; // артикул
							$updateCols->analogs = -1; // аналоги (менше нуля ігноряться)
							$updateCols->analogs_delimiter = ''; // аналоги розділювач
							$updateCols->manufacturer = 0; // виробник
							$updateCols->name = 0;
							$updateCols->count = 0;
							$updateCols->price = 0;
						}
						else
							$updateCols = unserialize($storage->updateCols);
						$updateCols->file = $this->data->post('file');
						$updateCols = serialize($updateCols);
					}
					$this->db->updateRow($_SESSION['service']->table, array('updateRows' => serialize($row), 'updateCols' => $updateCols), $_SESSION['alias']->id);
					$_SESSION['notify']->success = 'Налаштування ключового рядка успішно оновлено! Перевірте налаштування відповідності колонок до вмісту даних у них (права панель)';
				}
			}
		}
		$this->redirect();
	}

	public function optionsImportSaveCols()
	{
		$_SESSION['notify'] = new stdClass();
		if($storage = $this->db->getAllDataById($_SESSION['service']->table, $_SESSION['alias']->id))
		{
			if(!empty($storage->updateCols))
				$updateCols = unserialize($storage->updateCols);
			else
				$updateCols = new stdClass();
				
			// $updateCols->in_id = $this->data->post('in_id');
			$updateCols->in_id = -1;
			$updateCols->article = $this->data->post('article');
			$updateCols->analogs = $this->data->post('analogs');
			if(isset($_POST['analogs_delimiter']))
				$updateCols->analogs_delimiter = $this->data->post('analogs_delimiter');
			$updateCols->manufacturer = $this->data->post('manufacturer');
			$updateCols->name = $this->data->post('name');
			$updateCols->count = $this->data->post('count');
			$updateCols->price = $this->data->post('price');

			$updateCols = serialize($updateCols);

			$this->db->updateRow($_SESSION['service']->table, array('updateCols' => $updateCols), $_SESSION['alias']->id);
			$_SESSION['notify']->success = 'Налаштування відповідності колонок до вмісту даних у них оновлено.';
		}
		
		$this->redirect();
	}

	public function trancate()
	{
		$_SESSION['notify'] = new stdClass();

		if($user = $this->db->getAllDataById('wl_users', $_SESSION['user']->id))
		{
			if($user->type == 1 && $user->status == 1)
			{
				$password = sha1($user->email . md5($_POST['password']) . SYS_PASSWORD . $user->id);
				if($user->password == $password)
				{
					$this->db->deleteRow($_SESSION['service']->table.'_products', $_SESSION['alias']->id, 'storage');
					$_SESSION['notify']->success = 'Склад очищено';
				}
				else
					$_SESSION['notify']->errors = 'Невірний пароль адміністратора';
			}
			else
				$_SESSION['notify']->errors = 'Акаунт немає прав на здійснення даної операції';
		}
		else
			$_SESSION['notify']->errors = 'Помилка. Спробуйте ще раз';

		$this->redirect();
	}
	
}

?>