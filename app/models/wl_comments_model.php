<?php

class wl_comments_model {

	private $allowed_ext = array('png', 'jpg', 'jpeg');
	public $paginator = true;
	public $get_wl_sitemap = true;

	public function get($where = array(), $type = 'array')
	{
		if($this->paginator && $type != 'single')
		{
			$_SESSION['option']->paginator_total = $this->db->getCount('wl_comments', $where);
			if(empty($_SESSION['option']->paginator_total))
				return false;
		}

		$wl_sitemap = $wl_ntkd = $wl_images = array('alias' => '#c.alias', 'content' => '#c.content');
		if($_SESSION['language'])
			$wl_ntkd['language'] = $_SESSION['language'];
		$wl_images['position'] = 1;

		$this->db->select('wl_comments as c', '*', $where)
				->join('wl_users as u', 'name as user_name, email as user_email', '#c.user')
				->join('wl_ntkd', 'name as page_name', $wl_ntkd)
				->join('wl_images', 'file_name as page_image', $wl_images)
				->join('wl_aliases', 'alias as page_alias', '#c.alias')
				->order('date_add DESC');
		if($this->get_wl_sitemap)
			$this->db->join('wl_sitemap', 'link', $wl_sitemap);
		if($type == 'single')
		{
			$this->db->join('wl_users as m', 'name as manager_name, email as manager_email', '#c.manager');
			$this->db->limit(1);
		}
		if($this->paginator && $type != 'single' && isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0 && $_SESSION['option']->paginator_total > 1)
		{
			$start = 0;
			if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}
		return $this->db->get($type);
	}

	public function add($user, &$image_names = false)
	{
		if(empty($_POST['alias']))
			return false;

		$_SESSION['notify']->success = 'Дякуємо за відгук!';

		$inputs = array('content', 'alias', 'rating', 'comment');
		$data = $this->data->prepare($inputs);
		$data['parent'] = $data['reply'] = 0;
		$data['user'] = $user;
		$data['date_add'] = time();

		$data['status'] = 2;
		if(preg_match("~(http|https|ftp|ftps|href)~", $data['comment']))
		{
			$_SESSION['notify']->success = 'Дякуємо за відгук! <br>Враховуючи політику безпеки сайту, Ваш відгук підлягає перевірці та буде опублікований після затвердження адміністрацією';
			$data['status'] = 3;
		}

		if(!empty($_FILES['images']['name'][0]))
		{
			// if(empty($_SESSION['user']->id))
			// 	$data['status'] = 3;
			if($image_name = $this->data->post('image_name'))
			{
				if(count($_FILES['images']['name']) > 1)
				{
					$image_names = $names = array();
					for ($ii=1; $ii <= count($_FILES['images']['name']); $ii++) { 
						$i = $ii - 1;
						$image_names[$i] = false;
						if($pos = strrpos($_FILES['images']['name'][$i], '.'))
						{
                			$ext = strtolower(substr($_FILES['images']['name'][$i], $pos + 1));
                			if(in_array($ext, $this->allowed_ext))
                			{
	                			$names[$i] = $image_name.'-'.$ii.'.'.$ext;
	                			$image_names[$i] = $image_name.'-'.$ii;
	                		}
						}
					}
					$data['images'] = implode('|||', $names);
				}
				else
				{
					if($pos = strrpos($_FILES['images']['name'][0], '.'))
					{
            			$ext = strtolower(substr($_FILES['images']['name'][0], $pos + 1));
            			if(in_array($ext, $this->allowed_ext))
            			{
                			$data['images'] = $image_name.'.'.$ext;
                			$image_names[] = $image_name;
                		}
					}
				}
			}
			else
			{
				$image_names = $names = array();
				for ($i=0; $i < count($_FILES['images']['name']); $i++) { 
					$image_names[$i] = false;
					if($pos = strrpos($_FILES['images']['name'][$i], '.'))
					{
            			$ext = strtolower(substr($_FILES['images']['name'][$i], $pos + 1));
            			if(in_array($ext, $this->allowed_ext))
            			{
            				$ext_len = strlen($ext) + 1;
            				$image_name = $this->data->latterUAtoEN(substr($_FILES['images']['name'][$i], 0, -$ext_len));
                			$names[$i] = $image_name.'.'.$ext;
                			$image_names[$i] = $image_name;
                		}
					}
				}
				$data['images'] = implode('|||', $names);
			}
		}

		unset($_SESSION['_POST']);
		if($id = $this->db->insertRow('wl_comments', $data))
			return $id;
		else
		{
			unset($_SESSION['notify']->success);
			$_SESSION['notify']->errors = 'Error add row to wl_comments';
			return false;
		}
	}

}

?>
