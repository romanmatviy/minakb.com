<?php

class wl_images_admin extends Controller {

    function _remap($method)
    {
        $_SESSION['alias']->name = 'Редагування розміру зображень';
        $_SESSION['alias']->breadcrumb = array('Розміри зображень' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        if($_SESSION['user']->admin == 1)
        {
            @$_SESSION['alias']->id = 0;
            $_SESSION['alias']->table = '';
            $_SESSION['alias']->service = false;
            $alias = $this->data->uri(2);
            if($alias != '')
            {
                if($alias == 'all')
                {
                    $alias = new stdClass();
                    $alias->id = $alias->service = 0;
                    $alias->alias = 'all';
                }
                else
                    $alias = $this->db->getAllDataById('wl_aliases', $alias, 'alias');
                if(is_object($alias))
                {
                    $alias->name = '';
                    $alias->title = '';

                    if($alias->service > 0)
                    {
                        if($service = $this->db->getAllDataById('wl_services', $alias->service))
                        {
                            $alias->title = $service->title;
                            $alias->service_name = $service->name;
                        }
                    }

                    $text = '';
                    if($alias->service > 0) $text = " (на основі сервісу ".$alias->title.")";
                    $_SESSION['alias']->name = 'Розміри зображень '.$alias->alias.$text;
                    $_SESSION['alias']->breadcrumb = array('Розміри зображень' => 'admin/wl_images', $alias->alias => '');
                    
                    if($this->data->uri(3) == 'add')
                        $this->load->admin_view('wl_images/add_view', array('alias' => $alias));
                    elseif(is_numeric($this->data->uri(3)))
                    {
                        $wl_image = $this->db->getAllDataById('wl_images_sizes', $this->data->uri(3));
                        if($wl_image && $wl_image->alias == $alias->id)
                        {
                            $_SESSION['alias']->breadcrumb = array('Розміри зображень' => 'admin/wl_images', $alias->alias => 'admin/wl_images/'.$alias->alias, $wl_image->name => '');
                            $this->load->admin_view('wl_images/edit_view', array('alias' => $alias, 'wl_image' => $wl_image));
                        }
                        else
                            $this->load->page_404();
                    }
                    else
                        $this->load->admin_view('wl_images/list_view', array('alias' => $alias));
                }
                else
                    $this->load->page_404();
            }
            else
                $this->load->admin_view('wl_images/index_view');
        }
        else
            $this->load->page_404();
    }

    public function add()
    {
        if(isset($_POST['alias']) && is_numeric($_POST['alias']))
        {
            $photo = array();
            $photo['alias'] = $this->data->post('alias');
            $photo['active'] = 1;
            $photo['name'] = $this->data->post('name');
            $photo['prefix'] = $this->data->post('prefix');
            $photo['type'] = $this->data->post('type');
            $photo['width'] = $this->data->post('width');
            $photo['height'] = $this->data->post('height');
            $photo['quality'] = 100;
            $id = $this->db->insertRow('wl_images_sizes', $photo);

            $this->db->imageReSizes = [];
            $this->db->cache_delete_all(false, $this->data->post('alias_name'));

            $this->load->redirect('admin/wl_images/'.$this->data->post('alias_name').'/'.$id);
        }
    }

    public function save()
    {
        if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0)
        {
            $data['active'] = $this->data->post('active');
            $data['name'] = $this->data->post('name');
            $data['prefix'] = $this->data->post('prefix');
            $data['type'] = $this->data->post('type');
            $data['width'] = $this->data->post('width');
            $data['height'] = $this->data->post('height');
            $data['quality'] = $this->data->post('quality');
            $this->db->updateRow('wl_images_sizes', $data, $_POST['id']);

            $this->db->imageReSizes = [];
            $alias = $this->data->post('alias');
            if(is_numeric($alias))
            {
                if($a = $this->db->getAllDataById('wl_aliases', $alias))
                    $this->db->cache_delete_all(false, $a->alias);
            }

            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Дані успішно оновлено';
            $this->load->redirect();
        }
    }

    public function delete()
    {
        if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0)
        {
            if($this->data->post('close_number') == $this->data->post('user_namber'))
            {
                if($this->data->post('prefix') != '')
                {
                    if($folder = $this->db->getAllDataById('wl_options', array('alias' => $this->data->post('alias'), 'name' => 'folder')))
                    {
                        if($images = $this->db->getAllDataByFieldInArray('wl_images', $this->data->post('alias'), 'alias'))
                        {
                            $path = substr(IMG_PATH, strlen(SITE_URL));
                            $path .= $folder->value.'/';
                            foreach ($images as $image) {
                                @unlink($path.$image->content.'/'.$this->data->post('prefix').'_'.$image->file_name);
                            }
                        }
                        $_SESSION['notify'] = new stdClass();
                        $_SESSION['notify']->success = 'Всі зображення з префіксом <strong>'.$this->data->post('prefix').'</strong> успішно видалено. Зміну розміру зображення видалено.';
                    }
                }
                unset($this->db->imageReSizes[$this->data->post('alias')]);
                $this->db->cache_delete('imageReSizes', $this->data->post('alias_name'));

                $this->db->deleteRow('wl_images_sizes', $this->data->post('id'));
                $this->load->redirect('admin/wl_images/'.$this->data->post('alias_name'));
            }
            else
            {
                $_SESSION['notify_error_delete'] = 'Невірний номер! Введіть коректний номер зліва у вільне поле:';
                $this->load->redirect();
            }
        }
    }

    public function copy()
    {
        if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0)
        {
            if($this->data->post('close_number') == $this->data->post('user_namber'))
            {
                if($wl_image = $this->db->getAllDataById('wl_images_sizes', $this->data->post('id')))
                {
                    $photo = array();
                    $photo['alias'] = $this->data->post('alias');
                    $photo['active'] = 1;
                    $photo['name'] = $this->data->post('name');
                    $photo['prefix'] = $this->data->post('prefix');
                    $photo['type'] = $wl_image->type;
                    $photo['width'] = $wl_image->width;
                    $photo['height'] = $wl_image->height;
                    $photo['quality'] = $wl_image->quality;
                    $id = $this->db->insertRow('wl_images_sizes', $photo);

                    if(isset($_SESSION['alias-cache'][$photo['alias']]->imageReSizes))
                        unset($_SESSION['alias-cache'][$photo['alias']]->imageReSizes);
                    
                    $alias = $this->db->getAllDataById('wl_aliases', $this->data->post('alias'));
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = 'Дані успішно скопійовано';
                    $this->load->redirect('admin/wl_images/'.$alias->alias.'/'.$id);
                }
            }
            else
                $_SESSION['notify_error_copy'] = 'Невірний номер! Введіть коректний номер зліва у вільне поле';
        }
        $this->load->redirect();
    }

    public function deleteImages()
    {
        if($this->data->post('close_number') == $this->data->post('user_namber'))
        {
            if($this->data->post('alias') != 0 && $this->data->post('prefix') != '')
            {
                if($folder = $this->db->getAllDataById('wl_options', array('alias' => $this->data->post('alias'), 'name' => 'folder')))
                {
                    if($images = $this->db->getAllDataByFieldInArray('wl_images', $this->data->post('alias'), 'alias'))
                    {
                        $path = substr(IMG_PATH, strlen(SITE_URL));
                        $path .= $folder->value.'/';
                        foreach ($images as $image) {
                            @unlink($path.$image->content.'/'.$this->data->post('prefix').'_'.$image->file_name);
                        }
                    }
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = 'Всі зображення з префіксом <strong>'.$this->data->post('prefix').'</strong> успішно видалено. Нові зображення необхідного розміру згенеруються автоматично за запитом.';
                }
                else
                    $_SESSION['notify_error_deleteImages'] = 'Папку із зображеннями не задано в налаштуваннях аліасу';
            }
            elseif($this->data->post('alias') == 0 && $this->data->post('prefix') != '')
            {
                if($aliases = $this->db->getAllData('wl_aliases'))
                {
                    foreach ($aliases as $alias) {
                        if($folder = $this->db->getAllDataById('wl_options', array('alias' => $alias->id, 'name' => 'folder')))
                        {
                            if($images = $this->db->getAllDataByFieldInArray('wl_images', $alias->id, 'alias'))
                            {
                                $path = substr(IMG_PATH, strlen(SITE_URL));
                                $path .= $folder->value.'/';
                                foreach ($images as $image) {
                                    @unlink($path.$image->content.'/'.$this->data->post('prefix').'_'.$image->file_name);
                                }
                            }
                        }
                    }
                }
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->success = 'Всі зображення з префіксом <strong>'.$this->data->post('prefix').'</strong> успішно видалено з цілого сайту. Нові зображення необхідного розміру згенеруються автоматично за запитом.';
            }
        }
        else
            $_SESSION['notify_error_deleteImages'] = 'Невірний номер! Введіть коректний номер зліва у вільне поле';
        $this->load->redirect();
    }

}

?>