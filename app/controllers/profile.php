<?php

class Profile extends Controller {

    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
            if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
            $this->index($method);
        }
    }

    public function index($uri = false)
    {
        $this->load->model('wl_user_model');
        if($uri && $this->userCan())
        {
            if($user = $this->wl_user_model->getInfo($uri, false, 'alias'))
            {
                $this->wl_alias_model->setContent($user->id, 202);
                $_SESSION['alias']->title = $user->name.'. Кабінет користувача';
                $_SESSION['alias']->name = $user->name;

                if($this->load->function_in_alias('cart', 'my', $user->id))
                    exit;

                $this->load->profile_view(false, array('user' => $user));
            }
            else 
                $this->load->page_404(false);
        } 
        elseif($this->userIs())
        {
            if ($user = $this->wl_user_model->getInfo($_SESSION['user']->id)) {
                $this->wl_alias_model->setContent($user->id, 202);
                $_SESSION['alias']->title = $user->name . '. Кабінет користувача';
                $_SESSION['alias']->name = $user->name;

                if ($this->load->function_in_alias('cart', 'my', $user->id))
                exit;

                $this->load->profile_view(false, array('user' => $user));
            } else
                $this->load->page_404(false);
        }
        else
            $this->redirect('login');
    }

    public function edit()
    {
        if($this->userIs())
        {
            $_SESSION['alias']->title = $_SESSION['user']->name.'. Кабінет користувача';
            $_SESSION['alias']->name = 'Кабінет користувача';
            $_SESSION['alias']->content = $_SESSION['user']->id;
            $_SESSION['alias']->code = 202;

            $this->load->model('wl_user_model');
            $registerDo = $this->db->getQuery("SELECT r.*, d.name, d.title_public as title_public FROM wl_user_register as r LEFT JOIN wl_user_register_do as d ON d.id = r.do WHERE r.user = {$_SESSION['user']->id} AND d.public = 1", 'array');

            $this->load->profile_view('__edit_view', array('user' => $this->wl_user_model->getInfo(), 'registerDo' => $registerDo));
        }
        else
            $this->redirect('login');
    }

    public function saveUserInfo()
    {
        if($this->userIs())
        {
            if($name = $this->data->post('name'))
            {
                if(mb_strlen($name, 'UTF-8') > 3 && $_SESSION['user']->name != $name)
                {
                    $this->db->updateRow('wl_users', array('name' => $name), $_SESSION['user']->id);
                    $this->db->register('profile_data', 'Попереднє значення name: '.$_SESSION['user']->name);
                    $_SESSION['user']->name = $name;
                }
                unset($_POST['name']);
            }
                
            $error = false;
            if(!empty($_POST['phone']))
            {
                $this->load->library('validator');
                $this->validator->setRules($this->text('Номер телефону'), $_POST['phone'], 'phone');
                if(!$this->validator->run())
                {
                    $error = $this->validator->getErrors();
                    unset($_POST['phone']);
                }
                else
                    $_POST['phone'] = $_SESSION['user']->phone = $this->validator->getPhone($_POST['phone']);
            }
            if(isset($_POST['s_newsletter']) && is_numeric($_POST['s_newsletter']))
            {
                $this->db->updateRow('wl_users', array('s_newsletter' => $this->data->post('s_newsletter')), $_SESSION['user']->id);
            }
            if(isset($_POST['language']) && in_array($_POST['language'], $_SESSION['all_languages']))
            {
                $this->db->updateRow('wl_users', array('language' => $this->data->post('language')), $_SESSION['user']->id);
            }
            if(!empty($_POST))
            {
                $this->load->model('wl_user_model');
                foreach ($_POST as $key => $value)
                    if(!in_array($key, ['name', 'language', 'photos', 's_newsletter']))
                        $this->wl_user_model->setAdditional($_SESSION['user']->id, $key, $this->data->post($key));
            }
            if($error)
            {
                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->errors = $error;
            }
        }
        $this->redirect();
    }

    public function upload_avatar()
    {
        $res = array();

        if($this->userIs())
        {
            $error = 0;
            $name_field = 'photos';

            $path = IMG_PATH.'profile';
            $path = substr($path, strlen(SITE_URL));
            if(!is_dir($path))
                if(mkdir($path, 0777) == false) {
                    $error++;
                    $res['error'] = 'Error create dir ' . $path;
                }
            $path .= '/';

            if(!empty($_FILES[$name_field]['name']) && $error == 0)
            {
                $data = array();

                $name = $_SESSION['user']->id.'-'.md5($_SESSION['user']->email.'|'.$_SESSION['user']->name.'|photo|'.time());
                $this->load->library('image');
                $this->image->upload($name_field, $path, $name);
                $extension = $this->image->getExtension();
                $this->image->save();
                if($this->image->getErrors() == '') {
                    $this->image->loadImage($path, $name, $extension);
                    $this->image->resize(400);
                    $this->image->save('s');
                    $this->image->loadImage($path, $name, $extension);
                    $this->image->preview(100, 100, 100);
                    $this->image->save('p');
                    $data['photo'] = $name.'.'.$extension;
                }

                if(!empty($data)){
                    $this->db->updateRow('wl_users', $data, $_SESSION['user']->id);

                    $photo['id'] = $_SESSION['user']->id;
                    $photo['date'] = date('d.m.Y H:i');
                    $photo['url'] = $path.$data['photo'];
                    $photo['thumbnailUrl'] = $path.'s_'.$data['photo'];
                    $res[] = $photo;
                }
            } else $error++;

            if($error > 0){
                $photo['result'] = false;
                $photo['error'] = "Access Denied!";
                $res[] = $photo;
            }
        }

        if(empty($res)){
            $photo['result'] = false;
            $photo['error'] = "Access Denied!";
            $res[] = $photo;
        }

        header('Content-type: application/json');
        echo json_encode(array('files' => $res));
        exit;
    }

    public function save_security()
    {
        if($this->userIs())
        {
            $_SESSION['notify'] = new stdClass();
            $user = $this->db->getAllDataById('wl_users', $_SESSION['user']->id);
            $this->load->library('validator');
            if(!empty($user->password))
                $this->validator->setRules('Поточний пароль', $this->data->post('old_password'), 'required|5..20');
            $this->validator->setRules('Новий пароль', $this->data->post('new_password'), 'required|5..20');
            $this->validator->password($this->data->post('new_password'), $this->data->post('new_password_re'));
            if($this->validator->run())
            {
                $this->load->model('wl_user_model');
                if(!empty($user->password))
                {
                    $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $_SESSION['user']->email, $_POST['old_password']);
                    if($password != $user->password)
                    {
                        $_SESSION['notify']->errors = 'Невірний поточний пароль';
                        $this->redirect('#security');
                    }
                }
                $password = $this->wl_user_model->getPassword($_SESSION['user']->id, $_SESSION['user']->email, $_POST['new_password']);
                if($this->db->updateRow('wl_users', array('password' => $password), $_SESSION['user']->id))
                    if($this->db->register('reset', $user->password))
                    {
                        $this->load->library('mail');
                        if($this->mail->sendTemplate('reset/notify_success', $_SESSION['user']->email, array('name' => $_SESSION['user']->name)))
                            $_SESSION['notify']->success = 'Пароль змінено';
                    }
            }
            else
                $_SESSION['notify']->errors = '<ul>'.$this->validator->getErrors('<li>', '</li>').'</ul>';
            $this->redirect('#security');
        }
        $this->redirect('login');
    }

    public function facebook()
    {
        if(!$this->userIs())
            $this->redirect('login');

        $res = array('result' => false);

        $this->load->library('facebook');
        $user_profile = null;

        if ($accessToken = $this->data->post('accessToken'))
        {
            $this->facebook->setAccessToken($accessToken);

            try {
                $user_profile = $this->facebook->api('/me?fields=email,id,name,link');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user_profile = null;
            }
        }

        if ($user_profile)
        {
            $res['result'] = true;
            if($info = $this->db->getAllDataById('wl_user_info', array('field' => 'facebook', 'value' => $user_profile['id'])))
                if($info->user != $_SESSION['user']->id)
                {
                    $res['error'] = 'Користувач з даним профілем facebook вже підключено!';
                    $res['result'] = false;
                }
            if($res['result'])
            {
                $this->load->model('wl_user_model');
                $this->wl_user_model->setAdditional($_SESSION['user']->id, 'facebook', $user_profile['id']);
                if(!empty($user_profile['link']))
                    $this->wl_user_model->setAdditional($_SESSION['user']->id, 'facebook_link', $user_profile['link']);
                if(empty($_SESSION['user']->photo))
                {
                    $facebookPhotoLink = 'https://graph.facebook.com/'.$user_profile['id'].'/picture?width=9999';
                    $this->wl_user_model->setPhotoByLink($facebookPhotoLink);
                }

                $_SESSION['notify'] = new stdClass();
                $_SESSION['notify']->success = 'Профіль facebook успішно підключено.';
            }
        }
        else
        {
            $loginUrl = $this->facebook->getLoginUrl();
            header('Location: '.$loginUrl);
            exit;
        }

        $this->json($res);
    }

    public function facebook_disable()
    {
        if($this->userIs())
        {
            $this->db->deleteRow('wl_user_info', ['user' => $_SESSION['user']->id, 'field' => 'facebook']);
            $this->db->deleteRow('wl_user_info', ['user' => $_SESSION['user']->id, 'field' => 'facebook_link']);
        }
        $this->redirect();
    }

    public function __get_Search($content = 0)
    {
        return false;
    }

}
?>