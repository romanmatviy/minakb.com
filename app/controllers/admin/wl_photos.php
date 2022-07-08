<?php

class wl_photos_admin extends Controller {

	private $extension = false;
	private $error = false;

    function _remap($method)
    {
    	$_SESSION['alias']->name = 'Images';
        $_SESSION['alias']->breadcrumb = array('Images' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        $this->redirect("admin/wl_images");
    }

    public function add()
    {
        $filejson = new stdClass();
        $id = $this->data->uri(3);
        if(is_numeric($id) && isset($_POST['ALIAS_ID']) && isset($_POST['ALIAS_FOLDER']) && isset($_POST['PHOTO_FILE_NAME']) && isset($_POST['PHOTO_TITLE']))
        {
            $path = IMG_PATH;
            $path = substr($path, strlen(SITE_URL));
            $path = substr($path, 0, -1);
            $name_field = 'photos';
            $error = 0;
            if(!is_dir($path))
            {
                if(mkdir($path, 0777) == false)
                {
                    $error++;
                    $filejson->files['error'] = 'Error create dir ' . $path;
                } 
            }
            $path .= '/'.$this->data->post('ALIAS_FOLDER');
            if(!is_dir($path))
            {
                if(mkdir($path, 0777) == false)
                {
                    $error++;
                    $filejson->files['error'] = 'Error create dir ' . $path;
                } 
            }
            $path .= '/'.$id;
            if(!is_dir($path))
            {
                if(mkdir($path, 0777) == false)
                {
                    $error++;
                    $filejson->files['error'] = 'Error create dir ' . $path;
                } 
            }
            $path .= '/';

            if(!empty($_FILES[$name_field]['name'][0]) && $error == 0)
            {
                $length = count($_FILES[$name_field]['name']);
                for($i = 0; $i < $length; $i++) {
                    $data = array();
                    $data['alias'] = $this->data->post('ALIAS_ID');
                    $data['content'] = $id;
                    $data['section_id'] = $this->data->post('SECTION_ID');
                    $data['file_name'] = $data['title'] = $data['link'] = '';
                    $data['author'] = $_SESSION['user']->id;
                    $data['date_add'] = time();
                    $photo_id = $this->db->insertRow('wl_images', $data);
                    $photo_name = $this->data->post('PHOTO_FILE_NAME');
                    if(strlen($photo_name) > 220)
                        $photo_name = substr($photo_name, 0, 220);
                    $photo_name .= '-' . $photo_id;

                    $newMain = false;
                    if(!empty($_POST['newMain']))
                    {
                        $newMain = true;
                            $data['position'] = 1;
                    }

                    if($extension = $this->savephoto($name_field, $path, $photo_name, true, $i))
                    {
                        $photo_name .= '.'.$extension;
                        $position = 1;
                        if($newMain)
                            $this->db->executeQuery('UPDATE `wl_images` SET `position`=`position`+1 WHERE `alias` = '.$data['alias'].' AND `content` = '.$data['content'].' AND `section_id` = '.$data['section_id']);
                        else
                            $position = $this->db->getCount('wl_images', array('alias' => $data['alias'], 'content' => $id, 'section_id' => $data['section_id']));
                        $this->db->updateRow('wl_images', array('file_name' => $photo_name, 'position' => $position), $photo_id);

                        $this->updateAdditionall($data['alias'], $data['content']);

                        $photo['id'] = $photo_id;
                        $photo['name'] = $this->data->post('PHOTO_TITLE');
                        $photo['date'] = date('d.m.Y H:i');
                        $photo['url'] = IMG_PATH.$this->data->post('ALIAS_FOLDER').'/'.$id.'/'.$photo_name;
                        $photo['thumbnailUrl'] = IMG_PATH.$this->data->post('ALIAS_FOLDER').'/'.$id.'/admin_'.$photo_name;
                        $filejson->files[] = $photo;

                        $ntkd = $this->db->getAllDataByFieldInArray('wl_ntkd', ['alias' => $data['alias'], 'content' => $data['content']]);
                            foreach ($ntkd as $row) {
                                if(empty($row->get_sivafc) || in_array('i', str_split($row->get_sivafc)) === false)
                                {
                                    if(empty($row->get_sivafc))
                                        $this->db->updateRow('wl_ntkd', ['get_sivafc' => 'i'], $row->id);
                                    else
                                        $this->db->updateRow('wl_ntkd', ['get_sivafc' => $row->get_sivafc.'i'], $row->id);
                                }
                            }
                        $this->db->html_cache_clear($data['content'], $data['alias']);
                    }
                    else
                    {
                    	$this->db->deleteRow('wl_images', $photo_id);
	                    $prefix = array('');
	                    if($sizes = $this->db->getAliasImageSizes($data['alias']))
	                        foreach ($sizes as $resize) {
	                            $prefix[] = $resize->prefix.'_';
	                        }
	                    foreach ($prefix as $p) {
	                    	if($this->extension)
	                    	{
	                    		$filename = $path.$p.$photo_name.'.'.$this->extension;
	                        	@unlink ($filename);
	                    	}
	                    	else
	                    	{
		                    	$extensions = array('.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp');
		                    	foreach ($extensions as $ext) {
		                    		$filename = $path.$p.$photo_name.$ext;
		                        	@unlink ($filename);
		                    	}
		                    }
	                    }
                        
                        $photo['id'] = 0;
                        $photo['error'] = 'Error image file <strong>'.$_FILES[$name_field]['name'][$i].'</strong>';
                        if($this->error)
                        	$photo['error'] .= $this->error;
                        $photo['date'] = date('d.m.Y H:i');
                        $photo['url'] = SERVER_URL.'style/admin/images/no_image_available.jpg';
                        $photo['thumbnailUrl'] = SERVER_URL.'style/admin/images/no_image_available.jpg';
                        $filejson->files[] = $photo;
                    }
                }
            }
            if($error > 0)
            {
                $photo['result'] = false;
                $filejson->files[] = $photo;
            }
        }
        if(empty($filejson->files))
        {
            $photo['result'] = false;
            $photo['error'] = "Access Denied!";
            $filejson->files[] = $photo;
        }
        
        $this->load->json($filejson);
    }

    public function save()
    {
        $res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');
        if(isset($_POST['photo']) && is_numeric($_POST['photo']) && isset($_POST['name']))
        {
            if($photo = $this->db->getAllDataById('wl_images', $_POST['photo']))
            {
                switch ($_POST['name']) {
                    case 'title':
                    case 'link':
                        if($this->db->updateRow('wl_images', array($_POST['name'] => $this->data->post('title')), $photo->id))
                        {
                            $res['result'] = true;
                            $res['error'] = '';
                            $this->db->html_cache_clear($photo->content, $photo->alias);
                        }
                        $this->updateAdditionall($photo->alias, $photo->content);
                        break;
                    
                    case 'main':
                        $this->load->model('wl_position_model');
                        $this->wl_position_model->table = 'wl_images';
                        $this->wl_position_model->where = "alias = {$photo->alias} AND content = ".$photo->content;

                        if($this->wl_position_model->change($photo->id, 1))
                        {
                            $res['result'] = true;
                            $res['error'] = '';
                            $this->db->html_cache_clear($photo->content, $photo->alias);
                        }
                        $this->updateAdditionall($photo->alias, $photo->content);
                        break;

                    default:
                        if($_SESSION['language'])
                        {
                            $name = explode('-', $_POST['name']);
                            if($name[0] == 'title' && in_array($name[1], $_SESSION['all_languages']))
                            {
                                $data = array();
                                $data['type'] = 'photo';
                                $data['content'] = $photo->id;
                                $data['language'] = $name[1];
                                if($text = $this->db->getAllDataById('wl_media_text', $data))
                                    $this->db->updateRow('wl_media_text', array('text' => $this->data->post('title')), $text->id);
                                else
                                {
                                    $data['text'] = $this->data->post('title');
                                    $this->db->insertRow('wl_media_text', $data);
                                }
                                $res['result'] = true;
                                $res['error'] = '';
                                $this->db->html_cache_clear($photo->content, $photo->alias);
                                $this->updateAdditionall($photo->alias, $photo->content);
                            }
                        }
                        break;
                }
            }
            else
                $res['error'] = 'Фотографію не знайдено!';
        }
        $this->load->json($res);
    }
    
    public function delete()
    {
        $res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');
        if(isset($_POST['photo']) && is_numeric($_POST['photo']) && isset($_POST['ALIAS_FOLDER']))
        {
            if($photo = $this->db->getAllDataById('wl_images', $_POST['photo']))
            {
                if($this->db->deleteRow('wl_images', $photo->id))
                {
                    $path = IMG_PATH.$this->data->post('ALIAS_FOLDER').'/'.$photo->content.'/';
                    $path = substr($path, strlen(SITE_URL));
                    $prefix = array('');
                    if($sizes = $this->db->getAliasImageSizes($photo->alias))
                        foreach ($sizes as $resize) {
                            $prefix[] = $resize->prefix.'_';
                        }
                    foreach ($prefix as $p) {
                        $filename = $path.$p.$photo->file_name;
                        @unlink ($filename);
                    }

                    if($_SESSION['language'])
                        $this->db->deleteRow('wl_media_text', array('type' => 'photo', 'content' => $photo->id));
                    $this->db->executeQuery("UPDATE `wl_images` SET `position` = `position` - 1 WHERE `alias` = '{$photo->alias}' AND `content` = '{$photo->content}' AND `position` > '{$photo->position}'");
                    $this->db->html_cache_clear($photo->content, $photo->alias);
                    $this->updateAdditionall($photo->alias, $photo->content);

                    $res['result'] = true;
                    $res['error'] = '';
                }
            }
            else
                $res['error'] = 'Фотографію не знайдено!';
        }
        $this->load->json($res);
    }

    private function savephoto($name_field, $path, $name, $array = false, $i = 0)
    {
        if(!empty($_FILES[$name_field]['name']))
        {
            $this->load->library('image');
            if($array) $this->image->uploadArray($name_field, $i, $path, $name);
            else $this->image->upload($name_field, $path, $name);
            $extension = $this->image->getExtension();
            if(!empty($_POST['resizer']) && $_POST['resizer'] != 'false')
            {
                $this->image->save();
                if($this->image->getErrors() == '')
                {
                    if($sizes = $this->db->getAliasImageSizes($this->data->post('ALIAS_ID')))
                    {
                        foreach ($sizes as $resize) {
                            if($resize->prefix == '' || $resize->prefix == 'admin')
                            {
                                if($this->image->loadImage($path, $name, $extension))
                                {
                                    if(in_array($resize->type, array(1, 11, 12)))
                                        $this->image->resize($resize->width, $resize->height, $resize->quality, $resize->type);
                                    if(in_array($resize->type, array(2, 21, 22)))
                                        $this->image->preview($resize->width, $resize->height, $resize->quality, $resize->type);
                                    $this->image->save($resize->prefix);
                                }
                                else
                                {
                                	$this->extension = $extension;
                                	$this->error = $this->image->getErrors();
                                	return false;
                                }
                            }
                        }
                    }
                }
            }
            return $extension;
        }
        return false;
    }

	public function saveImgOption(){
		$res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');
		if ( $id = $this->data->post( 'id' ) ) {
			if ( $photo = $this->db->getAllDataById( 'wl_images', $id ) ) {
				$option = $_POST[ 'option' ];
				$this->db->executeQuery(
					"UPDATE `wl_images` SET `options` = '".serialize( $option )."' WHERE `alias` = '{$photo->alias}' AND `id` = '{$photo->id}'"
				);
				$res = array('result' => true, 'error' => 'Збережено');
			}
		}

		$this->load->json($res);
	}

    private function updateAdditionall($alias = 0, $content = NULL)
    {
        if($this->data->post('additional_table') && $this->data->post('additional_table_id') && $this->data->post('additional_fields'))
        {
            $data = array();
            $fields = explode(',', $this->data->post('additional_fields', false));
            foreach ($fields as $field) {
                $field = explode('=>', $field);
                if(isset($field[1]))
                {
                    switch ($field[1]) {
                        case 'user':
                            $data[$field[0]] = $_SESSION['user']->id;
                            break;
                        case 'time':
                            $data[$field[0]] = time();
                            break;
                        default:
                            $data[$field[0]] = $field[1];
                            break;
                    }
                }
            }
            if(!empty($data))
            {
                $additional_table_key = $this->data->post('additional_table_key');
                if(!$additional_table_key)
                    $additional_table_key = 'id';
                $this->db->updateRow($this->data->post('additional_table'), $data, $this->data->post('additional_table_id'), $additional_table_key);
            }
        }
        if($alias > 0 && $content != NULL)
            $this->load->function_in_alias($alias, '__after_edit', $content, true);
    }

    public function change_position(){
        $res = array('result' => false, 'error' => 'Доступ заборонено! Тільки автор або адміністрація!');

        if(isset($_POST['alias']) && is_numeric($_POST['alias']) &&
            isset($_POST['content']) && is_numeric($_POST['content']) &&
            isset($_POST['section_id']) && is_numeric($_POST['section_id']) &&
            $_POST['alias'] > 0 && isset($_POST['id']) && is_numeric($_POST['position'])){
            $id = explode('-', $_POST['id']);
            if($id[0] == 'photo' && isset($id[1]) && is_numeric($id[1]) && $id[1] > 0){
                $id = $id[1];
                $position = $_POST['position'] + 1;

                $this->load->model('wl_position_model');
                $this->wl_position_model->table = 'wl_images';
                $this->wl_position_model->where = "alias = {$_POST['alias']} AND content = {$_POST['content']} AND section_id = {$_POST['section_id']}";

                if($this->wl_position_model->change($id, $position))
                {
                    $res['result'] = true;
                    $res['error'] = '';

                    $this->db->html_cache_clear($_POST['content'], $_POST['alias']);
                    $this->load->function_in_alias($_POST['alias'], '__after_edit', $_POST['content'], true);
                }
            }
        }
        $this->load->json($res);
    }

}

?>