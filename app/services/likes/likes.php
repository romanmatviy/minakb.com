<?php

class likes extends Controller {
				
    function _remap($method, $data = array())
    {
        if (method_exists($this, $method))
        {
            if(empty($data))
                $data = null;
            return $this->$method($data);
        }
        else
            $this->index($method);
    }

    public function index()
    {
        if($this->userIs())
        {
            $this->wl_alias_model->setContent();
            $this->load->smodel('likes_model');
            $where = array('user' => $_SESSION['user']->id, 'status' => 1);
            $likes = $this->likes_model->getLikesWithData($where);
            if($likes)
                $this->load->model('wl_search_model');
            $this->load->model('wl_user_model');
            $this->load->profile_view('__user_likes_view', array('user' => $this->wl_user_model->getInfo(), 'likes_list' => $likes));
        }
        else
            $this->redirect('login');
    }

    public function setLike()
    {
        if($this->userIs())
        {
            if (isset($_POST['alias']) && isset($_POST['content']))
            {
                $this->load->smodel('likes_model');
                $this->load->json($this->likes_model->setLike($_SESSION['user']->id));
            }
            else
                $this->load->json('Like error: no set page alias or content');
        }
        else
            $this->load->json('no login');
    }

    public function cancelLike()
    {
        if($this->userIs())
        {
            if (isset($_POST['alias']) && isset($_POST['content']))
            {
                $this->load->smodel('likes_model');
                $this->load->json($this->likes_model->cancelLike($_SESSION['user']->id));
            }
            else
                $this->load->json('Like error: no set page alias or content');
        }
        else
            $this->load->json('no login');
    }

    public function __show_Like_Btn($data)
    {
        $alias = $_SESSION['alias']->alias_from;
        $content = NULL;
        $user = ($this->userIs()) ? $_SESSION['user']->id : 0;
        $userLike = false;
        if(is_array($data))
        {
            if(isset($data['alias']))
                $alias = $data['alias'];
            if(isset($data['content']))
                $content = $data['content'];
            if(isset($data['id']))
                $content = $data['id'];
            if(isset($data['user']))
                $user = $data['user'];
        }
        elseif(is_numeric($data))
            $content = $data;
        if($content === NULL)
            return false;

        $this->load->smodel('likes_model');
        $where = array('alias' => $alias, 'content' => $content);
        $likes = $this->likes_model->getLikes($where);
        $likes_count = 0;
        if($likes)
            $likes_count = count($likes);
        if($user > 0 && $likes)
            foreach ($likes as $like) {
                if($like->user == $user)
                {
                    if($like->status == 1)
                        $userLike = true;
                    else
                        $likes_count--;
                    break;
                }
            }

        $this->load->view('__button_view', array('likes' => $likes_count, 'userLike' => $userLike, 'alias' => $alias, 'content' => $content, 'page' => $data));
    }

    public function login()
    {
        if($this->userIs())
        {
            $this->setLike();
            exit();
        }

        if($this->data->post('email') && $this->data->post('password'))
        {
            $_SESSION['notify'] = new stdClass();
            $_SESSION['notify']->action = 'like-login';
            $res = array('result' => false, 'message' => '');

            $key = 'email';
            $email_phone = $this->data->post('email');
            $password = $this->data->post('password');
            $this->load->library('validator');
            if(!$this->validator->email('email', $email_phone))
            {
                if($email_phone = $this->validator->getPhone($email_phone))
                {
                    $key = 'phone';
                    $password = $email_phone;
                }
                else
                    $key = false;
            }

            if($key)
            {
                $this->load->model('wl_user_model');
                if($this->wl_user_model->login($key, $password))
                {
                    if (isset($_POST['alias']) && isset($_POST['content']))
                    {
                        $this->load->smodel('likes_model');
                        $res += $this->likes_model->setLike($_SESSION['user']->id);
                        $_SESSION['notify']->success = $res['message'] = $this->text('Товар у списку Ваших бажань');
                    }
                    $res['result'] = true;
                }
                else
                    $_SESSION['notify']->error = $res['message'] = $this->text('Неправильно введено email/телефон або пароль');
            }
            else
                $_SESSION['notify']->error = $res['message'] = $this->text('Невірний формат email/номеру телефону');
        }

        if (isset($_POST['ajax']))
        {
            unset($_SESSION['notify']);
            $this->load->json($res);
        }
        $this->redirect();
    }

    public function signup()
    {
        if($this->userIs())
        {
            $this->setLike();
            exit();
        }

        $_SESSION['notify'] = new stdClass();
        $_SESSION['notify']->action = 'like-signup';
        $res = array('result' => false, 'message' => '');
        if(trim($this->data->post('name')) != '' && $this->data->post('email'))
        {
            $data = array();
            $data['name'] = $name = trim($this->data->post('name'));
            $data['email'] = $email = trim($this->data->post('email'));
            $data['password'] = $password = bin2hex(openssl_random_pseudo_bytes(4));
            $data['photo'] = '';

            $this->load->library('validator');
            if($this->validator->email('email', $email))
            {
                $this->load->model('wl_user_model');
                if($user = $this->wl_user_model->add($data))
                {
                    $info['auth_id'] = $user->auth_id;
                    $info['email'] = $email;
                    $info['name'] = $name;
                    $info['password'] = $password;

                    $this->load->library('mail');
                    $this->mail->sendTemplate('signup/user_signup_with_password', $email, $info);
                    $this->wl_user_model->setSession($user);

                    if (isset($_POST['alias']) && isset($_POST['content']))
                    {
                        $this->load->smodel('likes_model');
                        $this->likes_model->setLike($_SESSION['user']->id);
                        $_SESSION['notify']->success = $res['message'] = $this->text('Товар у списку Ваших бажань');
                    }
                    $res['result'] = true;
                }
                else
                    $_SESSION['notify']->error = $res['message'] = $this->text('Користувач з таким е-мейлом вже є', 0);
            }
            else
                $_SESSION['notify']->error = $res['message'] = $this->text('Невірний формат email', 0);
        }
        else
            $_SESSION['notify']->error = $res['message'] = $this->text('Відсутні обов\'язкові поля email та name', 0);

        if (isset($_POST['ajax']))
        {
            unset($_SESSION['notify']);
            $this->load->json($res);
        }
        $this->redirect();
    }

    public function __get_Search($content='')
    {
        return false;
    }
	
}

?>