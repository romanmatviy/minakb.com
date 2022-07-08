<?php

class wl_files_admin extends Controller {

	private $allowed_ext = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'mp4');

    function _remap($method)
    {
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index()
    {
    	header("Location: ".SITE_URL);
    	exit();
	}

	public function save()
	{
		if(isset($_POST['alias']) && is_numeric($_POST['alias']) && isset($_POST['alias_folder']) && isset($_POST['content']) && is_numeric($_POST['content']) && isset($_FILES['file']) && is_array($_FILES['file']["name"]))
		{
			$alias = $this->data->post('alias');
			$content = $this->data->post('content');

			$max_size = 31457280;
			$post_max_size = $this->data->parse_size(ini_get('post_max_size'));
		    if ($post_max_size > 0) {
		    	$max_size = $post_max_size;
		    }

			for($i = 0; $i < count($_FILES['file']['name']); $i++)
			{
				$path_info = pathinfo($_FILES['file']['name'][$i]);
				$extension = $path_info['extension'];

				$uploaded = false;
				if(in_array($extension, $this->allowed_ext) && $_FILES['file']["size"][$i] <= $max_size)
				{
					$file = $_FILES['file']["tmp_name"][$i];
					
					$text = $path_info['filename'];
					$name = $this->data->latterUAtoEN($text).'.'.$extension;

					$path = 'files';
			   		if(!is_dir($path))
		            	mkdir($path, 0777);

					$path = "files/" . $this->data->post('alias_folder');
					if(!is_dir($path)) {
						mkdir($path, 0777);
					}

					$path .= "/" . $content;
					if(!is_dir($path)) {
						mkdir($path, 0777);
					}

					$path .= "/" . $name;
					if(is_uploaded_file($file)) {
						if(move_uploaded_file($file, $path)) {
							$uploaded = true;
						}
					}
				}
				else
				{
					if(!isset($_SESSION['notify']->errors))
						$_SESSION['notify'] = new stdClass();
					else
						$_SESSION['notify']->errors .= '<br> ';
					$_SESSION['notify']->errors = '<strong>'.$_FILES['file']['name'][$i].'</strong> Тип файлу (розширення) заборонено в цілях безпеки або файл завеликий (більше 30Мб)';
				}

				if($uploaded)
				{
					$data = array();
					$data['author'] = $_SESSION['user']->id;
					$data['date_add'] = time();
					$data['alias'] = $alias;
					$data['content'] = $content;
					$data['name'] = $name;
					$data['extension'] = $extension;
					$data['text'] = $text;
					$data['position'] = $this->db->getCount('wl_files', array('alias' => $alias, 'content' => $content)) + 1;
					$this->db->insertRow('wl_files', $data);

					$ntkd = $this->db->getAllDataByFieldInArray('wl_ntkd', ['alias' => $alias, 'content' => $content]);
						foreach ($ntkd as $row) {
							if(empty($row->get_sivafc) || strripos('f', $row->get_sivafc) === false)
							{
								if(empty($row->get_sivafc))
									$this->db->updateRow('wl_ntkd', ['get_sivafc' => 'f'], $row->id);
								else
									$this->db->updateRow('wl_ntkd', ['get_sivafc' => $row->get_sivafc.'f'], $row->id);
							}
						}
					$this->db->html_cache_clear($content, $alias);
					$this->load->function_in_alias($alias, '__after_edit', $content, true);
				}
			}
		}
		else
		{
			$post_max_size = ini_get('post_max_size') ?? 0;
			$_SESSION['notify'] = new stdClass();
			$_SESSION['notify']->errors = 'Розмір файлу завеликий';
			if($post_max_size)
				$_SESSION['notify']->errors .= '. Максимально допустимий розмір '.$post_max_size;
		}
		$this->redirect('#tab-files');
	}

	public function save_preview()
	{
		if (isset($_POST['folder']) && isset($_POST['id']) && is_numeric($_POST['id']) && isset($_FILES['preview']))
			if($file = $this->db->getAllDataById('wl_files', $_POST['id']))
			{
				$path_info = pathinfo($_FILES['preview']['name']);
				$extension = $path_info['extension'];
				$folder = $this->data->post('folder');
				
				$_SESSION['option']->folder = $folder;
				$filePath = $this->data->get_file_path($file);
				$filePath = substr($filePath, strlen(SERVER_URL));
				if(file_exists($filePath))
				{
					$tmp_name = $_FILES['preview']["tmp_name"];
					$filePath .= '.'.$extension;
					if(is_uploaded_file($tmp_name)) {
						if(move_uploaded_file($tmp_name, $filePath))
						{
							if($file->preview_extension != $extension)
							{
								$this->db->updateRow('wl_files', ['preview_extension' => $extension], $file->id);
							}

							$this->db->html_cache_clear($file->content, $file->alias);
							$this->load->function_in_alias($file->alias, '__after_edit', $file->content, true);
						}
					}
				}
			}
		$this->redirect('#tab-files');
	}

	public function delete()
	{
		if($this->userCan() && isset($_GET['id']) && is_numeric($_GET['id']))
		{
			$id = $this->data->get('id');
			if($file = $this->db->getAllDataById('wl_files', $id))
			{
				$this->db->executeQuery("UPDATE `wl_files` SET `position` = position - 1 WHERE `position` > '{$file->position}' AND `alias` = '{$file->alias}' AND `content` = '{$file->content}'");
				$this->db->deleteRow('wl_files', $id);

				if($folder = $this->data->get('folder'))
				{
					$_SESSION['option']->folder = $folder;
					$filePath = $this->data->get_file_path($file);
					$filePath = substr($filePath, strlen(SERVER_URL));
					if(file_exists($filePath))
						unlink($filePath);
				}

				$this->db->html_cache_clear($file->content, $file->alias);
				$this->load->function_in_alias($file->alias, '__after_edit', $file->content, true);
			}
			
		}
		$this->redirect('#tab-files');
	}

	public function change_position(){
        $res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');

        if(isset($_POST['alias']) && is_numeric($_POST['alias']) && isset($_POST['content']) && is_numeric($_POST['content']) && $_POST['alias'] > 0 && isset($_POST['id']) && is_numeric($_POST['position'])){
            $id = explode('-', $_POST['id']);
            if($id[0] == 'file' && isset($id[1]) && is_numeric($id[1]) && $id[1] > 0){
                $id = $id[1];
                $position = $_POST['position'] + 1;

                $this->load->model('wl_position_model');
                $this->wl_position_model->table = 'wl_files';
                $this->wl_position_model->where = "alias = {$_POST['alias']} AND content = ".$_POST['content'];

                if($this->wl_position_model->change($id, $position)){
                    $res['result'] = true;
                    $res['error'] = '';
                    $this->db->html_cache_clear($_POST['content'], $_POST['alias']);
                    $this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);
                }
            }
        }

        header('Content-type: application/json');
     	echo json_encode($res);
		exit;
    }

    public function save_text()
	{
		$res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');
		if(isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['text']))
		{
			if($this->db->updateRow('wl_files', array('text' => $this->data->post('text')), $_POST['id']))
			{
				$res['result'] = true;
				$res['error'] = '';
				if(isset($_POST['alias']) && is_numeric($_POST['alias']) && isset($_POST['content']) && is_numeric($_POST['content']) && $_POST['alias'] > 0)
				{
					$this->db->html_cache_clear($_POST['content'], $_POST['alias']);
                    $this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);
				}
			}
			else
				$res['error'] = 'Файл не знайдено!';
		}
		header('Content-type: application/json');
		echo json_encode($res);
		exit;
	}

}
?>