<?php

class wl_services_admin extends Controller {
				
    function _remap($method)
    {
        $_SESSION['alias']->name = 'Сервіси';
        $_SESSION['alias']->breadcrumb = array('Сервіси' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        if($_SESSION['user']->admin == 1)
        {
            if($this->data->uri(2) != '')
            {
                $name = $this->data->uri(2);
                $name = $this->db->sanitizeString($name);
                $service = $this->db->getAllDataById('wl_services', $name, 'name');
                if($service)
                {
                    $aliases = $this->db->getAllDataByFieldInArray('wl_aliases', $service->id, 'service');
                    $options = $this->db->getAllDataByFieldInArray('wl_options', $service->id, 'service');
                    $_SESSION['alias']->name = 'Сервіс "'.$service->title.'"';
                    $_SESSION['alias']->breadcrumb = array('Сервіси' => 'admin/wl_services', $service->title => '');
                    $this->load->admin_view('wl_services/options_view', array('service' => $service, 'aliases' => $aliases,'options' => $options));
                }
                else
                    $this->load->page_404();
            }
            else
                $this->load->admin_view('wl_services/list_view');
        }
        else
            $this->load->page_404();
    }

    public function install()
    {
        if($_SESSION['user']->admin == 1 && isset($_POST['name']) && $_POST['name'] != '')
        {
            $path = APP_PATH.'services'.DIRSEP.$_POST['name'].DIRSEP.'models/install_model.php';
            if(file_exists($path))
            {
                require_once($path);
                $install = new install();
                $install->db = $this->db;

                $wl_services = array();
                $wl_services['name'] = $install->name;
                $wl_services['title'] = $install->title;
                $wl_services['description'] = $install->description;
                $wl_services['table'] = $install->table_service;
                $wl_services['group'] = $install->group;
                $wl_services['multi_alias'] = $install->multi_alias;
                $wl_services['version'] = $install->version;
                if($this->db->insertRow('wl_services', $wl_services))
                {
                    $id = $this->db->getLastInsertedId();
                    $this->db->register('service_install', $id.'. '.$install->name.' ('.$install->version.')');

                    if(!empty($install->options))
                    {
                        $option = array('service' => $id, 'alias' => 0);
                        foreach ($install->options as $key => $value) {
                            $option['name'] = $key;
                            $option['value'] = $value;
                            $this->db->insertRow('wl_options', $option);
                        }
                    }

                    $install->service = $id;
                    $install->install_go();

                    unset($_SESSION['_POST']);
                    $this->load->redirect("admin/wl_aliases/add?service=".$id);
                }
            }
        }
        $this->load->redirect();
    }

    // Видалити сервіс разом зі всіма налаштуваннями. 
    // У параметрі передаються дані про видалення контенту, що був зібраний.
    public function uninstall()
    {
        if($_SESSION['user']->admin == 1 && isset($_POST['admin-password']) && isset($_POST['id']))
        {
            $this->load->model('wl_user_model');
            $admin = $this->wl_user_model->getInfo(0, false);
            $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $_SESSION['user']->email, $_POST['admin-password']);
            if($password == $admin->password)
            {
                $service = $this->db->getAllDataById('wl_services', $_POST['id']);
                if($service)
                {
                    $path = APP_PATH.'services'.DIRSEP.$service->name.DIRSEP.'models/install_model.php';
                    if(file_exists($path))
                    {
                        require_once($path);
                        $install = new install();
                        $install->db = $this->db;

                        if(isset($_POST['content']) && $_POST['content'] == 1)
                        {
                            $aliases = $this->db->getAllDataByFieldInArray('wl_aliases', $service->id, 'service');
                            if(!empty($aliases))
                            {
                                foreach ($aliases as $alias) {
                                    $additionally = "{$alias->id}. {$alias->alias}. ". $service->name .' ('.$service->id.')';

                                    if(isset($install->options['folder']))
                                    {
                                        $where = array('service' => $alias->service, 'alias' => $alias->id, 'name' => 'folder');
                                        if($option = $this->db->getAllDataById('wl_options', $where))
                                        {
                                            $folder = $option->value;

                                            $path = IMG_PATH.$folder;
                                            $path = substr($path, strlen(SITE_URL));
                                            $this->data->removeDirectory($path);
                                        }
                                    }
                                    if(method_exists("install", "alias_delete")) $install->alias_delete($alias->id, $alias->table, true);
      
                                    $this->db->deleteRow('wl_aliases_cooperation', $alias->id, 'alias1');
                                    $this->db->deleteRow('wl_aliases_cooperation', $alias->id, 'alias2');
                                    $this->db->deleteRow('wl_images', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_images_sizes', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_language_words', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_ntkd', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_ntkd_robot', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_sitemap', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_statistic_pages', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_video', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_audio', $alias->id, 'alias');
                                    $this->db->deleteRow('wl_user_permissions', $alias->id, 'permission');

                                    $this->db->cache_delete($alias->alias, 'wl_aliases');
                                    $this->db->cache_delete_all(false, $alias->alias);
                                    if(isset($_SESSION['alias-cache'][$_POST['id']]))
                                        unset($_SESSION['alias-cache'][$_POST['id']]);
                                    if(isset($_SESSION['alias-cache'][$alias->alias]))
                                        unset($_SESSION['alias-cache'][$alias->alias]);
                                    
                                    $this->db->register('alias_delete', $additionally);
                                }
                                $this->db->deleteRow('wl_aliases', $service->id, 'service');
                            }
                            $this->db->deleteRow('wl_options', $service->id, 'service');
                        }

                        if(method_exists("install", "uninstall")) $install->uninstall($service->id);
                    }

                    $this->db->deleteRow('wl_services', $service->id);
                    $this->db->register('service_uninstall', $service->id.'. '.$service->name.' ('.$service->version.')');

                    $this->load->redirect("admin/wl_services");
                }
            }
            else
                $_SESSION['notify']->error = 'Невірний пароль адміністратора';
        }

        $this->load->redirect();
    }

    public function saveOption()
    {
        $res = array('result' => false, 'error' => "Помилка! Дані не збережено!");
        if($_SESSION['user']->admin == 1 && isset($_POST['id']) && is_numeric($_POST['id']))
            if($this->db->updateRow('wl_options', array('value' => $_POST['value']), $_POST['id']))
                $res['result'] = true;
        
        if(isset($_POST['json']) && $_POST['json'])
            $this->load->json($res);
        else
            $this->load->redirect("admin/wl_services");
    }

}

?>