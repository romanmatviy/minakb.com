<?php 

//* from polycraft.ua

class transfer_oc extends Controller
{
	private $db_oc = array('host' => 'localhost', 'user' => 'root', 'password' => '', 'database' => '');

	public function index()
	{
		echo "<h1>Transfer data from OpenCart</h1>";
		if (empty($this->db_oc['database']))
			echo "Error: please config oc database!";
		else
			echo "add /groups or /products or /articles or /products_group or /oc_customer or /gen_random_passwords";
		exit;
	}

	function articles()
	{
		require_once SYS_PATH.'libraries/db.php';

		// $old = new db(array('host' => 'localhost', 'user' => 'root', 'password' => '', 'database' => 'poliuretan.lviv.ua'));
		$old = new db($this->db_oc);
		echo "<pre>";
		$product_articles = array();
		$oc_product = $old->select('oc_product as p', 'product_id, sku, upc, ean, jan, isbn')
							->join('oc_product_description as n', 'meta_keyword', array('product_id' => '#p.product_id', 'language_id' => 2))
							->get('array');
		// print_r($oc_product[0]); 
		// 					exit;
		$keys = array('sku' => 1, 'upc' => 3, 'ean' => 3, 'jan' => 3, 'isbn' => 3);
		foreach ($oc_product as $i => $row) {
			// print_r($row);
			$articles = $data = array();
			$data['product'] = $row->product_id;
			foreach ($keys as $key => $group) {
				$article = trim($row->$key);
				$last = substr($article, -1);
				while ($last == '/' || $last == '-' || $last == '.') {
					$article = trim(substr($article, 0, -1));
					$last = substr($article, -1);
				}
				$key = $this->prepareKey($article);
				if(in_array($key, $articles) || empty($article))
					continue;
				$articles[] = $article;
				$data['group'] = $group;
				$data['key'] = $key;
				$data['show'] = $article;
				$product_articles[] = $data;
			}
			$row->meta_keyword = explode(';', $row->meta_keyword);
			foreach ($row->meta_keyword as $article) {
				$article = trim($article);
				if (preg_match("/[а-я]+/i", $article) || empty($article))
					continue;
				$last = substr($article, -1);
				while ($last == '/' || $last == '-' || $last == '.') {
					$article = trim(substr($article, 0, -1));
					$last = substr($article, -1);
				}
				$key = $this->prepareKey($article);
				if(in_array($key, $articles))
					continue;
				$articles[] = $article;
				$data['group'] = 2;
				$data['key'] = $key;
				$data['show'] = $article;
				$product_articles[] = $data;
			}
		}
		// print_r($product_articles);
		$keys = array('product', 'group', 'key', 'show');
		$this->db->insertRows('product_articles', $keys, $product_articles);
	}

	private function prepareKey($text)
	{
		$text = mb_strtolower($text, "utf-8");
        $ua = array('-', '_', ' ', '`', '~', '!', '@', '#', '$', '%', '^', '&', '"', ',', '\.', '\?', '/', ';', ':', '\'', '[+]', '“', '”');
        $en = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        for ($i = 0; $i < count($ua); $i++) {
            $text = mb_eregi_replace($ua[$i], $en[$i], $text);
        }
        $text = mb_eregi_replace("[-]{2,}", '-', $text);
        return $text;
	}

	function products_group()
	{
		require_once SYS_PATH.'libraries/db.php';

		// $old = new db(array('host' => 'localhost', 'user' => 'root', 'password' => '', 'database' => 'poliuretan.lviv.ua'));
		$old = new db($this->db_oc);
		echo "<pre>";
		$oc_product_to_category = $old->getAllData('oc_product_to_category', 'category_id ASC');
		$s_shopshowcase_product_group = array();
		$positionParent = $oc_product_to_category[0]->category_id;
		$position = 1;
		foreach ($oc_product_to_category as $row) {
			$pg = array('active' => 1);
			$pg['product'] = $row->product_id;
			$pg['group'] = $row->category_id;
			if($row->category_id != $positionParent)
			{
				$position = 1;
				$positionParent = $row->category_id;
			}
			$pg['position'] = $position++;
			$s_shopshowcase_product_group[] = $pg;
			// print_r($pg);
		}
		$keys = array('product', 'group', 'position', 'active');
		$this->db->insertRows('s_shopshowcase_product_group', $keys, $s_shopshowcase_product_group);
		// Час виконання: 0.14897 сек. Використанок памяті: 125.05469 Кб. Запитів до БД: 95. Cache відключено
	}

	function products()
	{
		require_once SYS_PATH.'libraries/db.php';

		$old = new db($this->db_oc);
		echo "<pre>";
		$s_shopshowcase_products = $ntkd = array();
		$oc_product = $old->select('oc_product as p', '*')
							->join('oc_product_description as n', 'name, description, meta_description, meta_keyword', array('product_id' => '#p.product_id', 'language_id' => 2))
							->order('name ASC', 'n')
							->get('array');
		// print_r($oc_product[0]); 
							// exit;
		$now = time();
		// $positionParent = 0;
		// $position = 1;
		// $miss = array(391, 873, 1013, 1066, 2126);
		foreach ($oc_product as $i => $row) {
			// print_r($row);
			$row->name = trim($row->name);
			$last = trim(substr($row->name, -1));
			while ($last == '/' || $last == '-' || $last == '.') {
				$row->name = trim(substr($row->name, 0, -1));
				$last = substr($row->name, -1);
			}

			$data = array('wl_alias' => 8, 'group' => 0, 'old_price' => 0, 'currency' => 0, 'availability' => 1, 'position' => 0, 'author_add' => 1, 'author_edit' => 1, 'date_add' => $now, 'date_edit' => $now);
			$data['id'] = $row->product_id;
			$data['article'] = $this->data->latterUAtoEN($row->sku);
			$data['alias'] = $data['article'].'-'.$this->data->latterUAtoEN($row->name);
			$data['min'] = $row->minimum;
			$data['price'] = $row->price;
			$data['active'] = $row->status;
			
			$s_shopshowcase_products[] = $data;

			$ntkd_row = array('alias' => 8, 'content' => $data['id'], 'language' => 'uk');
			$ntkd_row['name'] = $row->name;
			$ntkd_row['description'] = $row->meta_description;
			$ntkd_row['keywords'] = $row->meta_keyword;
			$ntkd_row['text'] = $row->description;
			$ntkd[] = $ntkd_row;
			$ntkd_row['language'] = 'ru';
			$ntkd[] = $ntkd_row;
			// print_r($data);
			// print_r($ntkd_row);
			// echo "<hr>";
			// if($i == 5)
			// exit;
		}
		$keys = array('id', 'wl_alias', 'article', 'alias', 'group', 'min', 'price', 'old_price', 'currency', 'availability', 'position', 'active', 'author_add', 'date_add', 'author_edit', 'date_edit');
		$this->db->insertRows('s_shopshowcase_products', $keys, $s_shopshowcase_products);

		$keys = array('alias', 'content', 'language', 'name', 'description', 'keywords', 'text');
		$this->db->insertRows('wl_ntkd', $keys, $ntkd);
		// print_r($s_shopshowcase_groups);
		// exit;
		// Час виконання: 1.77208 сек. Використанок памяті: 75.64844 Кб. Запитів до БД: 114. Cache відключено
	}
	
	function groups()
	{
		require_once SYS_PATH.'libraries/db.php';

		$old = new db($this->db_oc);
		echo "<pre>";
		$s_shopshowcase_groups = $ntkd = array();
		$oc_category = $old->select('oc_category as c', '*')
							->join('oc_category_description', 'name', array('category_id' => '#c.category_id', 'language_id' => 2))
							->order('parent_id ASC')
							->get('array');
		// print_r($oc_category); exit;
		$now = time();
		$positionParent = 0;
		$position = 1;
		$miss = array(391, 873, 1013, 1066, 2126);
		foreach ($oc_category as $row) {
			if($row->category_id == 59)
				continue;
			$in_array = in_array($row->category_id, $miss);
			if($in_array || in_array($row->parent_id, $miss))
			{
				if(!$in_array)
					$in_array[] = $row->category_id;
				continue;
			}
			$row->name = trim($row->name);
			$last = trim(substr($row->name, -1));
			while ($last == '/' || $last == '-' || $last == '.') {
				$row->name = trim(substr($row->name, 0, -1));
				$last = substr($row->name, -1);
			}

			$data = array('wl_alias' => 8, 'author_add' => 1, 'author_edit' => 1, 'date_add' => $now, 'date_edit' => $now);
			$data['id'] = $row->category_id;
			$data['active'] = $row->status;
			$data['parent'] = $row->parent_id;
			if($row->parent_id == 59)
				$data['parent'] = 0;
			$data['alias'] = $this->data->latterUAtoEN($row->name);
			if($data['parent'] != $positionParent)
			{
				$position = 1;
				$positionParent = $data['parent'];
			}
			$data['position'] = $position++;
			$s_shopshowcase_groups[] = $data;

			$ntkd_row = array('alias' => 8, 'content' => -$data['id'], 'language' => 'uk');
			$ntkd_row['name'] = $row->name;
			$ntkd[] = $ntkd_row;
			$ntkd_row['language'] = 'ru';
			$ntkd[] = $ntkd_row;
			// print_r($data);
		}
		$keys = array('id', 'wl_alias', 'alias', 'parent', 'position', 'active', 'author_add', 'date_add', 'author_edit', 'date_edit');
		$this->db->insertRows('s_shopshowcase_groups', $keys, $s_shopshowcase_groups);

		$keys = array('alias', 'content', 'language', 'name');
		$this->db->insertRows('wl_ntkd', $keys, $ntkd);
		// print_r($s_shopshowcase_groups);
		exit;
	}

	public function oc_customer()
	{
		require_once SYS_PATH.'libraries/db.php';
		$old = new db($this->db_oc);

		$time = time();
		$this->load->library('validator');
		$inserted = 0;
		$oc_customer = $old->getAllData('oc_customer');
		$type = [1 => 6, 2 => 3, 3 => 4, 4 => 5];
		foreach ($oc_customer as $customer) {
			$user = [];
			$user['email'] = $customer->email;
			$user['phone'] = '';
			if($phone = $this->validator->getPhone($customer->telephone))
				$user['phone'] = $phone;
			$user['name'] = $customer->firstname .' '.$customer->lastname;
			$user['alias'] = $this->data->latterUAtoEN($user['name']);
			$user['type'] = $type[$customer->customer_group_id] ?? 6;
			$user['status'] = 1;
			$user['registered'] = strtotime($customer->date_added);
			$user['last_login'] = 0;
			$user['s_newsletter'] = $customer->newsletter;
			if($id = $this->db->insertRow('wl_users', $user))
			{
				$this->db->register('signup', 'transfer from OpenCart', $id);
				$inserted++;
			}
		}
		echo "inserted: ".$inserted;
		exit;
	}

	public function gen_random_passwords()
	{
		$set_password = 0;
		if($users = $this->db->getQuery("SELECT id, email FROM  `wl_users` WHERE `last_login` = 0 AND  `password` IS NULL", 'array'))
		{
			$this->load->model('wl_user_model');
			foreach ($users as $user) {
				$open_pass = bin2hex(openssl_random_pseudo_bytes(4));
				$close_pass = $this->wl_user_model->getPassword($user->id, $user->email, $open_pass);
				$update = ['password' => $close_pass, 'auth_id' => $open_pass];
				$this->db->updateRow('wl_users', $update, $user->id);
				$set_password++;
			}
		}
		echo "set_password to ".$set_password;
		exit;
	}

}

 ?>