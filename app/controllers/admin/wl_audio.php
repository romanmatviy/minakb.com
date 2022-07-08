<?php

class wl_audio_admin extends Controller {

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
		if(isset($_POST['alias']) && is_numeric($_POST['alias']) && isset($_POST['alias_folder']) && isset($_POST['content']) && is_numeric($_POST['content']) && isset($_FILES['audio']) && is_array($_FILES["audio"]["name"]))
		{
			$alias = $this->data->post('alias');
			$content = $this->data->post('content');

			for($i = 0; $i < count($_FILES['audio']['name']); $i++)
			{
				$uploaded = false;
				if((($_FILES["audio"]["type"][$i] == "audio/mp3") || ($_FILES["audio"]["type"][$i] == "audio/wma") || ($_FILES["audio"]["type"][$i] == "audio/mpeg") || ($_FILES["audio"]["type"][$i] == "audio/wav") || ($_FILES["audio"]["type"][$i] == "audio/ogg")) && $_FILES["audio"]["size"][$i] < 30000000)
				{
					$audio = $_FILES["audio"]["tmp_name"][$i];
					$path_info = pathinfo($_FILES['audio']['name'][$i]);
					$extension = $path_info['extension'];
					$text = $path_info['filename'];
					$name = $this->data->latterUAtoEN($text).'.'.$extension;

					$path = "audio/" . $this->data->post('alias_folder');
					if(!is_dir($path)) {
						mkdir($path, 0777);
					}

					$path .= "/" . $content;
					if(!is_dir($path)) {
						mkdir($path, 0777);
					}

					$path .= "/" . $name;
					if(is_uploaded_file($audio)) {
						if(move_uploaded_file($audio, $path)) {
							$uploaded = true;
						}
					}
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
					$data['position'] = $this->db->getCount('wl_audio', array('alias' => $alias, 'content' => $content)) + 1;
					$this->db->insertRow('wl_audio', $data);

					$ntkd = $this->db->getAllDataByFieldInArray('wl_ntkd', ['alias' => $alias, 'content' => $content]);
						foreach ($ntkd as $row) {
							if(empty($row->get_sivafc) || strripos('a', $row->get_sivafc) === false)
							{
								if(empty($row->get_sivafc))
									$this->db->updateRow('wl_ntkd', ['get_sivafc' => 'a'], $row->id);
								else
									$this->db->updateRow('wl_ntkd', ['get_sivafc' => $row->get_sivafc.'a'], $row->id);
							}
						}
					$this->db->html_cache_clear($_POST['content'], $_POST['alias']);
                    $this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);

					$_SESSION['option']->sitemap_lastedit = time();
					$this->db->updateRow('wl_options', array('value' => $_SESSION['option']->sitemap_lastedit), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastedit'));
				}
				else
				{
					if(isset($_SESSION['notify']->errors))
						$_SESSION['notify']->errors .= ', '.$_FILES['audio']['name'][$i];
					else
					{
						$_SESSION['notify'] = new stdClass();
						$_SESSION['notify']->errors = 'Помилка при завантаженні аудіо '.$_FILES['audio']['name'][$i];
					}
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
		$this->redirect('#tab-audio');
	}

	public function delete()
	{
		if($this->userCan() && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['alias']) && is_numeric($_GET['alias']) && isset($_GET['content']) && is_numeric($_GET['content'])){
			$id = $this->data->get('id');
			$position = $this->data->get('position');
			$alias = $this->data->get('alias');
			$content = $this->data->get('content');
			$name = $this->data->get('name');

			$this->db->executeQuery("UPDATE `wl_audio` SET `position` = position - 1 WHERE `position` > '{$position}' AND `alias` = '{$alias}' AND `content` = '{$content}'");
			$this->db->deleteRow('wl_audio', $id);

			$filePath = "audio/".$alias."/".$content."/".$name;
			@unlink ($filePath);

			$this->db->html_cache_clear($_GET['content'], $_GET['alias']);
            $this->load->function_in_alias($_GET['alias'], '__after_edit', $_GET['content'], true);

			$_SESSION['option']->sitemap_lastedit = time();
			$this->db->updateRow('wl_options', array('value' => $_SESSION['option']->sitemap_lastedit), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastedit'));

			header('Location: '.$_SERVER['HTTP_REFERER'].'#tab-audio');
			exit;
		} else $this->load->page_404();
	}

	public function change_position()
	{
        $res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');

        if(isset($_POST['alias']) && is_numeric($_POST['alias']) && isset($_POST['content']) && is_numeric($_POST['content']) && $_POST['alias'] > 0 && isset($_POST['id']) && is_numeric($_POST['position'])){
            $id = explode('-', $_POST['id']);
            if($id[0] == 'audio' && isset($id[1]) && is_numeric($id[1]) && $id[1] > 0){
                $id = $id[1];
                $position = $_POST['position'] + 1;

                $this->load->model('wl_position_model');
                $this->wl_position_model->table = 'wl_audio';
                $this->wl_position_model->where = "alias = {$_POST['alias']} AND content = ".$_POST['content'];

                if($this->wl_position_model->change($id, $position))
                {
                	$this->db->html_cache_clear($_POST['content'], $_POST['alias']);
                    $this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);

                    $res['result'] = true;
                    $res['error'] = '';
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
			if($this->db->updateRow('wl_audio', array('text' => $this->data->post('text')), $_POST['id']))
			{
				$res['result'] = true;
				$res['error'] = '';

				$this->db->html_cache_clear($_POST['content'], $_POST['alias']);
                $this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);
				
				$_SESSION['option']->sitemap_lastedit = time();
				$this->db->updateRow('wl_options', array('value' => $_SESSION['option']->sitemap_lastedit), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastedit'));
			}
			else
				$res['error'] = 'Фотографію не знайдено!';
		}
		header('Content-type: application/json');
		echo json_encode($res);
		exit;
	}

}
?>