<?php

class wl_sitemap_admin extends Controller {
				
    public function _remap($method)
    {
        $_SESSION['alias']->name = 'Карта сайту';
        $_SESSION['alias']->breadcrumb = array('Site Map' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        $id = $this->data->uri(2);
        if(is_numeric($id))
        {
            if($sitemap = $this->db->getAllDataById('wl_sitemap', $id))
            {
                $_SESSION['alias']->name = 'SiteMap '.$sitemap->link;
                $_SESSION['alias']->breadcrumb = array('SiteMap' => 'admin/wl_sitemap', $sitemap->link => '');
                $sitemap->name = '';
                $where = array('alias' => $sitemap->alias, 'content' => $sitemap->content);
                if($_SESSION['language'])
                    $where['language'] = $_SESSION['language'];

                if($sitemap->alias > 0)
                {
                    $this->db->select('wl_ntkd', 'name', $where);
                    if($ntkd = $this->db->get())
                    {
                        $_SESSION['alias']->breadcrumb = array('SiteMap' => 'admin/wl_sitemap', $ntkd->name => '');
                        $sitemap->name = $ntkd->name;
                    }
                }
                else
                    $where['content'] = $sitemap->id;

                $this->db->select('wl_statistic_pages as s', '`day`, `unique`, `views`', $where);
                $this->db->order('id DESC');
                $start = 0;
                $_SESSION['option']->paginator_per_page = ($sitemap->code < 299) ? 30 : 10;
                if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
                    $start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
                $this->db->limit($start, $_SESSION['option']->paginator_per_page);
                $statistic = $this->db->get('array');

                $this->load->admin_view('wl_sitemap/edit_view', array('sitemap' => $sitemap, 'wl_statistic' => $statistic));
            }
            else
                $this->load->page_404();
        }
        elseif($id == '')
        {
            $start = 0;
            $_SESSION['option']->paginator_per_page = 50;
            $where = array();
            if($this->data->get('alias') == 'yes')
                $where['alias'] = '>0';
            if($this->data->get('alias') == 'no')
                $where['alias'] = '0';
            if($code = $this->data->get('code'))
                $where['code'] = $code;
            if($link = $this->data->get('link'))
                $where['link'] = '%'.$link;

            $this->db->select('wl_sitemap', 'id, link, alias, code, time, changefreq, priority', $where);
            if(isset($_GET['sort']) && $_GET['sort'] == 'down')
                $this->db->order('id DESC');
            if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
                $start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
            $this->db->limit($start, $_SESSION['option']->paginator_per_page);
            $sitemap = $this->db->get('array', false);
            $_SESSION['option']->paginator_total = $this->db->get('count');
            
            $this->load->admin_view('wl_sitemap/index_view', array('sitemap' => $sitemap));
        }
        else
            $this->load->page_404();
        
        $_SESSION['alias']->id = 0;
        $_SESSION['alias']->alias = 'admin';
        $_SESSION['alias']->table = '';
        $_SESSION['alias']->service = '';
    }

    public function add_redirect()
    {
        $this->load->admin_view('wl_sitemap/add_redirect_view');
    }

    public function save_add_redirect()
    {
        $_SESSION['notify'] = new stdClass();
        if(!empty($_POST['from']) && isset($_POST['to']))
        {
            $sitemap = $this->db->getAllDataById('wl_sitemap', $this->data->post('from'), 'link');
            if($sitemap)
            {
                $_SESSION['notify']->errors = 'Лінк за даною адресою <strong>'.SITE_URL.$sitemap->link.'</strong> вже існує <a href="'.SITE_URL.'admin/wl_sitemap/'.$sitemap->id.'" class="btn btn-success btn-xs">Редагувати</a>';
                $this->redirect();
            }
            else
            {
                $data = array();
                $data['link'] = $this->data->post('from');
                $data['link_sha1'] = sha1($data['link']);
                $data['data'] = $this->data->post('to');
                $data['code'] = 301;
                $data['time'] = time();
                $data['changefreq'] = 'never';
                $data['alias'] = $data['content'] = $data['priority'] = 0;
                $this->db->insertRow('wl_sitemap', $data);
                $_SESSION['notify']->success = 'Лінк успішно додано <a href="'.SITE_URL.'admin/wl_sitemap/'.$sitemap->id.'" class="btn btn-success btn-xs">Редагувати</a>';
                $this->redirect('admin/wl_sitemap?sort=down');
            }
        }
    }

    public function save()
    {
        if(isset($_POST['id']) && $_POST['id'] > 0)
        {
            $data = array('time' => time(), 'data' => NULL);
            $data['code'] = $this->data->post('code');
            switch ($data['code']) {
                case '200':
                case '201':
                    $data['priority'] = $this->data->post('priority') * 10;
                    $data['changefreq'] = $this->data->post('changefreq');
                    if(empty($_POST['active']) && $data['priority'] > 0)
                        $data['priority'] *= -1;
                    break;
                
                case '301':
                    $data['data'] = $this->data->post('redirect');
                    break;
            }
            if($this->db->updateRow('wl_sitemap', $data, $_POST['id']))
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->success = 'Дані оновлено!';
            }
        }
        $this->redirect();
    }

    public function delete()
    {
        if(isset($_POST['id']) && $_POST['id'] > 0)
        {
            if($_POST['code_hidden'] == $_POST['code_open'])
            {
                $this->db->select('wl_sitemap', 'id, link, alias, content, code', $_POST['id']);
                if($sitemap = $this->db->get())
                {
                    $this->db->deleteRow('wl_sitemap_from', $sitemap->id, 'sitemap');
                    if($sitemap->alias == 0)
                        $this->db->deleteRow('wl_statistic_pages', array('alias' => 0, 'content' => $sitemap->id));
                    $this->db->deleteRow('wl_sitemap', $sitemap->id);
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = 'Дані <strong>'.SITE_URL.$sitemap->link.'</strong> успішно видалено!';
                    $this->redirect('admin/wl_sitemap?code='.$sitemap->code);
                }
            }
            else
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->errors = 'Невірний код безпеки!';
            }
        }
        $this->redirect();
    }

    public function cache()
    {
        echo 'need update';
        // $id = $this->data->uri(3);
        // if(is_numeric($id))
        // {
        //     if($sitemap = $this->db->getAllDataById('wl_sitemap', $id))
        //     {
        //         if($sitemap->data)
        //         {
        //             if(extension_loaded('zlib'))
        //                 echo ( gzdecode ($sitemap->data) );
        //             else
        //                 echo ( $sitemap->data );
        //         }
        //         else
        //             $this->load->notify_view(array('errors' => 'За адресою <strong>'.SITE_URL.$sitemap->link.'</strong> дані Cache-сторінки відсутні.'));
        //     }
        //     else
        //         $this->load->page_404();
        // }
        // else
        //     $this->load->page_404();
    }

    public function cleanCache()
    {
        echo 'need update';
        // if(isset($_POST['id']) && $_POST['id'] > 0)
        // {
        //     if($_POST['code_hidden'] == $_POST['code_open'])
        //     {
        //         $this->db->updateRow('wl_sitemap', array('data' => NULL), $_POST['id']);
        //         $_SESSION['notify'] = new stdClass();
        //         $_SESSION['notify']->success = 'Cache сторінки успішно видалено!';
        //     }
        //     else
        //     {
        //         $_SESSION['notify'] = new stdClass();
        //         $_SESSION['notify']->errors = 'Невірний код безпеки!';
        //     }
        // }
        // $this->redirect();
    }

    public function deleteAllByRequire()
    {
        if($_POST['code_hidden'] == $_POST['code_open'])
        {
            if($_POST['alias'] == -1 && $_POST['code'] == 0)
            {
                $this->db->executeQuery("TRUNCATE wl_sitemap");
                $this->db->executeQuery("TRUNCATE wl_sitemap_from");
                $this->db->deleteRow('wl_statistic_pages', array('alias' => 0));
            }
            else
            {
                $where = array();
                if($_POST['alias'] == 1)
                    $where['alias'] = '>0';
                elseif($_POST['alias'] == 0)
                    $where['alias'] = 0;

                if($code = $this->data->post('code'))
                    $where['code'] = $code;

                if(empty($where))
                {
                    $this->db->executeQuery("TRUNCATE wl_sitemap");
                    $this->db->executeQuery("TRUNCATE wl_sitemap_from");
                    $this->db->deleteRow('wl_statistic_pages', array('alias' => 0));
                }
                else
                {
                    $data = $this->db->select('wl_sitemap', 'id, alias, code', $where)->get('array');
                    if($data)
                    {
                        $this->db->deleteRow('wl_sitemap', $where);
                        if($_POST['code'] == 404 || $_POST['code'] == 0)
                        {
                            if($_POST['alias'] == -1 && $language)
                                $this->db->executeQuery("TRUNCATE wl_sitemap_from");
                            else
                            {
                                $ids = array();
                                foreach ($data as $row) {
                                    if($row->code == 404)
                                        $ids[] = $row->id;
                                }
                                if(!empty($ids))
                                    $this->db->deleteRow('wl_sitemap_from', array('sitemap' => $ids));
                            }
                        }
                        if($_POST['alias'] < 1)
                        {
                            $where = array('alias' => 0);
                            foreach ($data as $row) {
                                if($row->alias == 0)
                                {
                                    $where['content'] = $row->id;
                                    $this->db->deleteRow('wl_statistic_pages', $where);
                                }
                            }
                        }
                    }
                }
            }
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->success = 'Записи успішно видалено!';
        }
        else
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->errors = 'Невірний код безпеки!';
        }
        $this->redirect();
    }

    public function multi_edit()
    {
        if(!empty($_POST['sitemap-ids']) && !empty($_POST['do']))
        {
            $post_ids = explode(',', $_POST['sitemap-ids']);
            $ids = array();
            foreach ($post_ids as $id) {
                if(is_numeric($id) && $id > 0)
                    $ids[] = $id;
            }

            if(!empty($ids))
            {
                $data = array();

                if($_POST['do'] == 'clearCache')
                    $data['data'] = NULL;
                elseif($_POST['do'] == 'delete')
                {
                    $this->db->deleteRow('wl_sitemap', array('id' => $ids));
                    foreach ($ids as $id) {
                        $this->db->deleteRow('wl_sitemap_from', $id, 'sitemap');
                        $this->db->deleteRow('wl_statistic_pages', array('alias' => 0, 'content' => $id));
                    }
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = 'Дані успішно видалено!';
                }
                elseif($_POST['do'] == 'save')
                {
                    if(!empty($_POST['active-code']) && $_POST['active-code'] == 1)
                        $data['code'] = $this->data->post('code');
                    if(empty($data) || $data['code'] != 404)
                    {
                        if(!empty($_POST['active-changefreq']) && $_POST['active-changefreq'] == 1)
                            $data['changefreq'] = $this->data->post('changefreq');
                        if(!empty($_POST['active-priority']) && $_POST['active-priority'] == 1)
                            $data['priority'] = $this->data->post('priority') * 10;

                        if(!empty($_POST['active-index']) && $_POST['active-index'] == 1)
                        {
                            if(!empty($_POST['index']) && $_POST['index'] == 1)
                            {
                                if(isset($data['priority']))
                                {
                                    if($data['priority'] < 0)
                                        $data['priority'] *= -1;
                                }
                                else
                                {
                                    $this->db->executeQuery('UPDATE `wl_sitemap` SET `priority` = `priority` * -1 WHERE `id` IN ('.implode(', ', $ids).') AND `priority` < 0');
                                }
                            }
                            else
                            {
                                if(isset($data['priority']))
                                {
                                    if($data['priority'] > 0)
                                        $data['priority'] *= -1;
                                    else
                                        $data['priority'] = -2;
                                }
                                else
                                {
                                    $this->db->executeQuery('UPDATE `wl_sitemap` SET `priority` = `priority` * -1 WHERE `id` IN ('.implode(', ', $ids).') AND `priority` > 0');
                                    $this->db->executeQuery('UPDATE `wl_sitemap` SET `priority` = -2 WHERE `id` IN ('.implode(', ', $ids).') AND `priority` = 0');
                                }
                                unset($data['changefreq']);
                            }
                        }
                    }
                }
                if(!empty($data))
                {
                    $data['time'] = time();
                    $this->db->updateRow('wl_sitemap', $data, array('id' => $ids));
                    $_SESSION['notify'] = new stdClass();
                    $_SESSION['notify']->success = 'Дані успішно оновлено!';
                }
            }
        }
        $this->redirect();
    }

    public function clearSiteCache()
    {
        echo 'need update';
        // $this->db->updateRow('wl_sitemap', array('data' => NULL), array('code' => '!301'));
        // $_SESSION['notify'] = new stdClass();
        // $_SESSION['notify']->success = 'Cache сайту видалено!';
        // $this->redirect();
    }

    public function generate()
    {
        $_SESSION['alias']->name = 'Генерувати карту сайту';
        $_SESSION['alias']->breadcrumb = array('Site Map' => 'admin/wl_sitemap', 'Налаштування' => '');
        $this->load->admin_view('wl_sitemap/generate_view');
    }

    public function generate_image()
    {
        $_SESSION['alias']->name = 'Карта сайту зображень';
        $_SESSION['alias']->breadcrumb = array('Site Map' => 'admin/wl_sitemap', 'Зображення' => '');

        $allImages = $this->db->select('wl_images as i', 'id, file_name as photo, content' )
                         ->join('wl_ntkd', 'name', array('alias' => '#i.alias', 'content' => 0))
                         ->join('wl_options', 'value, alias', array('alias' => '#i.alias', 'name' => 'folder' ))
                         ->get('array');

        $images = array();
        if($allImages)
        {
            $this->load->library('image');
            foreach($allImages as $key => $allImage)
            {
                if($allImage->photo)
                {
                    $images[$key]['original'] = IMG_PATH.$allImage->value.'/'.$allImage->content.'/'.$allImage->photo;
                    $images[$key]['admin'] = IMG_PATH.$allImage->value.'/'.$allImage->content.'/admin_'.$allImage->photo;

                    $path = substr($images[$key]['original'], strlen(SITE_URL));

                    if(@getimagesize($path))
                    {
                        if(!getimagesize(substr($images[$key]['admin'], strlen(SITE_URL))) && $this->image->loadImage($path))
                        {
                            $this->image->preview(150, 150, 100, 2);
                            $this->image->save('admin');
                        }
                    }
                    else
                    {
                        unset($images[$key]);
                        // $this->db->deleteRow('wl_images', $allImage->id);
                    }
                }
            }
        }

        $this->load->admin_view('wl_sitemap/generate_image_view', array('images' => $images));
    }

    public function start_generate_images()
    {
        if(!file_exists('sitemap.xml'))
            return false;
        
        $where = array('alias' => '#i.alias', 'content' => '#i.content');
        $where_ntkd = array('alias' => '#i.alias', 'content' => 0);
        if($_SESSION['language'])
            $where_ntkd['language'] = $_SESSION['language'];
        $allImages = $this->db->select('wl_images as i', 'id, file_name as photo, content' )
                         ->join('wl_ntkd', 'name', $where_ntkd)
                         ->join('wl_options', 'value, alias', array('alias' => '#i.alias', 'name' => 'folder' ))
                         ->join('wl_sitemap', 'link', $where)
                         ->get('array');


        if($allImages)
        {
            $xmlString = file_get_contents('sitemap.xml');

            $dom = new DomDocument;
            $dom->preserveWhiteSpace = FALSE;
            $dom->loadXML($xmlString);

            $urls = $dom->getElementsByTagName('url'); 

            foreach($allImages as $key => $allImage)
            {
                if($allImage->photo)
                {
                    $photo = IMG_PATH.$allImage->value.'/'.$allImage->content.'/'.$allImage->photo;

                    $path = substr($photo, strlen(SITE_URL));

                    if(@getimagesize($path))
                    {
                        $link = SITE_URL.ltrim($allImage->link, '/');

                        if(!isset($allImages[$link]))
                        {
                            $allImages[$link] = $allImage;
                            $allImages[$link]->photos = array($photo);
                        }
                        else
                            $allImages[$link]->photos[] = $photo;
                    }
                }

                unset($allImages[$key]);
            }

            foreach ($urls as $url) 
            {
                $textContent = $url->getElementsByTagName('loc')->item(0)->textContent;
                if(isset($allImages[$textContent]))
                {
                    foreach ($allImages[$textContent]->photos as $photo) 
                    {
                        $imageImage = $dom->createElement('image:image');
                        $url->appendChild($imageImage);

                        $imageLoc = $dom->createElement('image:loc');
                        $imageImage->appendChild($imageLoc);

                        $text = $dom->createTextNode($photo);
                        $imageLoc->appendChild($text);
                    }
                }
            }

            $dom->saveXML();

            $dom->save('sitemap.xml');
        }
    }

    public function save_generate()
    {
        $fields = array('sitemap_active' => 0, 'sitemap_autosent' => 0);
        foreach ($fields as $key => $value) {
            if(isset($_POST[$key]) && $_POST[$key] == 1) $value = 1;
            $this->db->updateRow('wl_options', array('value' => $value), array('service' => 0, 'alias' => 0, 'name' => $key));
        }
        $_SESSION['notify'] = new stdClass();
        $_SESSION['notify']->success = 'Загальні налаштування SiteMap успішно оновлено!';
        $this->redirect();
    }

    public function re_generate()
    {
        $_SESSION['notify'] = new stdClass();
        if($this->data->post('code_hidden') == $this->data->post('code_open') && $this->data->post('code_hidden') > 0)
        {
            $where = array('code' => 301);
            if(empty($_POST['deletePageCode']))
                $where['+code'] = 404;
            if($exeption = $this->db->getAllDataByFieldInArray('wl_sitemap', $where))
            {
                foreach ($where as $key => $value) {
                    $value = '!'.$value;
                }
                $this->db->deleteRow('wl_sitemap', $where);
                $where = "NOT IN ( ";
                foreach ($exeption as $row) {
                    $where .= "'{$row->id}', ";
                }
                $where = substr($where, 0, -2);
                $where .= ')';
                $this->db->executeQuery("DELETE FROM `wl_sitemap_from` WHERE `sitemap` {$where}");
                $this->db->executeQuery("DELETE FROM `wl_statistic_pages` WHERE `alias` = 0 AND `content` {$where}");
            }
            else
            {
                $this->db->executeQuery("TRUNCATE wl_sitemap");
                $this->db->executeQuery("TRUNCATE wl_sitemap_from");
                $this->db->deleteRow('wl_statistic_pages', array('alias' => 0));
            }

            $data = array();
            //додано link_sha1 бо це поле Not NULL в таблиці
            $keys = array('link_sha1','link', 'alias', 'content', 'code', 'data', 'time', 'changefreq', 'priority');
            if(!empty($_POST['aliases']) && is_array($_POST['aliases']))
                foreach ($_POST['aliases'] as $alias_id) {
                    if(is_numeric($alias_id))
                    {
                        if($rows = $this->function_in_alias($alias_id, '__get_SiteMap_Links'))
                            foreach ($rows as $row) {
                                if(empty($row['alias']))
                                    $row['alias'] = $alias_id;
                                //додано link_sha1 бо це поле Not NULL в таблиці
                                if(empty($row['link_sha1']))
                                    $row['link_sha1'] = sha1($row['link']);
                                if(empty($row['code']))
                                    $row['code'] = 200;
                                if(empty($row['time']))
                                    $row['time'] = time();
                                if(empty($row['changefreq']))
                                    $row['changefreq'] = 'daily';
                                if(empty($row['priority']))
                                    $row['priority'] = 5;
                                if(empty($row['time']))
                                    $row['time'] = time();
                                $data[] = $row;

                                if(count($data) > 1000)
                                {
                                    $this->db->insertRows('wl_sitemap', $keys, $data);
                                    $data = array();
                                    
                                }
                            }
                    }
                }
            if(!empty($data))
                $this->db->insertRows('wl_sitemap', $keys, $data);
            $_SESSION['notify']->success = 'Карту сайту сформовано успішно';
        }
        else
            $_SESSION['notify']->errors = 'Невірний код безпеки!';

        $this->redirect('admin/wl_sitemap');
    }

    public function start_generate()
    {
        $_SESSION['notify'] = new stdClass();
        if($this->data->post('code_hidden') == $this->data->post('code_open') && $this->data->post('code_hidden') > 0)
        {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '1024M');
            
            $this->load->model('wl_cache_model');
            if($sitemap = $this->wl_cache_model->SiteMap(true))
            {
                $this->load->library('SitemapGenerator');
                $links = array();
                foreach ($sitemap as $url) {
                    if($url->link == '')
                        continue;
                    if($url->link[0] == '/')
                        $url->link = substr($url->link, 1);
                    if($url->link == 'main')
                        $url->link = '';
                    if(empty($links[$url->link]))
                    {
                        $this->sitemapgenerator->addUrl(SITE_URL.$url->link, date('c', $url->time), $url->changefreq, $url->priority/10);
                        $links[$url->link] = 1;
                    }
                    if($_SESSION['language'])
                    {
                        $link = $url->link;
                        foreach ($_SESSION['all_languages'] as $language) {
                            if($language != $_SESSION['all_languages'][0])
                            {
                                $url->link = $language.'/'.$link;
                                if(empty($links[$url->link]))
                                {
                                    $this->sitemapgenerator->addUrl(SITE_URL.$url->link, date('c', $url->time), $url->changefreq, $url->priority/10);
                                    $links[$url->link] = 1;
                                }
                            }
                        }
                    }
                }
                if(!empty($links))
                {
                    try {
                        // create sitemap
                        $this->sitemapgenerator->createSitemap();
                        // write sitemap as file
                        $this->sitemapgenerator->writeSitemap();
                        // update robots.txt file
                        $this->sitemapgenerator->updateRobots();
                        // added images
                        $this->start_generate_images();

                        $this->db->updateRow('wl_options', array('value' => time()), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastgenerate'));
                        $_SESSION['notify']->success = 'SiteMap успішно згенеровано!';

                        if($this->data->post('sent') == 1)
                        {
                            // submit sitemaps to search engines
                            // $result = $this->sitemapgenerator->submitSitemap("yahooAppId");
                            $result = $this->sitemapgenerator->submitSitemap();
                            $this->db->updateRow('wl_options', array('value' => time()), array('service' => 0, 'alias' => 0, 'name' => 'sitemap_lastsent'));
                            // shows each search engine submitting status
                            $_SESSION['notify']->success .= "<br><br><pre>";
                            $_SESSION['notify']->success .= print_r($result, true);
                            $_SESSION['notify']->success .= "</pre>";
                        }
                    }
                    catch (Exception $exc) {
                        $_SESSION['notify']->errors = '<strong>'.$exc->getMessage().'</strong> <br>'.nl2br($exc->getTraceAsString());
                    }
                }
            }
        }
        else
            $_SESSION['notify']->errors = 'Невірний код безпеки!';

        $this->redirect();
    }

}

?>