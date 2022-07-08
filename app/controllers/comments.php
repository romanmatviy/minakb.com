<?php 

class Comments extends Controller {

    private $mail_notify_to = SITE_EMAIL;

    private function anchorAfter($id)
    {
        return '#reviews'; // #comment-$id;
        // '#comment_add_success';
    }
				
    function _remap($method)
    {
        if (method_exists($this, $method) && $method != 'library' && $method != 'db')
            $this->$method();
        else
            $this->index($method);
    }

    public function index()
    {
        $this->wl_alias_model->setContent();
        $this->load->model('wl_comments_model');
        $comments = $this->wl_comments_model->get(array('status' => array(1, 2), 'parent' => 0));
    	$this->load->page_view('comments_view', array('comments' => $comments, 'showAddForm' => false));
    }

    public function add()
    {
        $add = false;
        $_SESSION['notify'] = new stdClass();
        $_SESSION['notify']->title = $this->text('Відгуки', 0);

        if($this->userIs())
            $add = true;
        else
        {
            $this->load->library('recaptcha');
            if($this->recaptcha->check($this->data->post('g-recaptcha-response')))
                $add = true;
            else
                $_SESSION['notify']->errors = $this->text('Please fill in the captcha', 0);
        }

        if($add)
        {
            $this->load->library('validator');
            if(!$this->userIs())
            {
                $this->validator->setRules('E-mail', $this->data->post('email'), 'required|email');
                $this->validator->setRules('Name', $this->data->post('name'), 'required|3..30');
            }
            $this->validator->setRules('system:alias', $this->data->post('alias'), 'required');
            $this->validator->setRules('system:content', $this->data->post('content'), 'required');
            if($this->validator->run())
            {
                $userId = $name = false;
                if($this->userIs())
                {
                    $userId = $_SESSION['user']->id;
                    $name = $_SESSION['user']->name;
                }
                else
                {
                    $name = trim($this->data->post('name'));
                    if($user = $this->db->getAllDataById('wl_users', $this->data->post('email'), 'email'))
                    {
                        if(!empty($user->password))
                            $this->redirect('login');
                        if($name != $user->name)
                        {
                            $this->db->updateRow('wl_users', array('name' => $name), $user->id);
                            $this->db->register('profile_data', 'name before: '.$user->name, $user->id);
                        }
                        $userId = $user->id;
                    }
                    else
                    {
                        $user = array('name' => $name);
                        $user['email'] = trim($this->data->post('email'));
                        $user['photo'] = false;
                        $user['password'] = 'auto signup '.time();
                        $this->load->model('wl_user_model');
                        if($user = $this->wl_user_model->add($user, false, 5, false, 'from comments'))
                            $userId = $user->id;
                    }
                }

                if($userId > 0)
                {
                    if(empty($_POST['rating']))
                        $_POST['rating'] = 5;
                    $this->load->model('wl_comments_model');
                    $image_names = false;
                    if($id = $this->wl_comments_model->add($userId, $image_names))
                    {
                        if(!empty($_SESSION['notify']->success))
                            $_SESSION['notify']->success = $this->text($_SESSION['notify']->success, 0);

                        $this->load->function_in_alias($_POST['alias'], '__set_rating', $_POST['content'], true);

                        $name_field = 'images';
                        if($image_names && !empty($_FILES[$name_field]['name']))
                        {

                            $path = IMG_PATH;
                            $path = substr($path, strlen(SITE_URL));
                            $path = substr($path, 0, -1);
                            
                            if(!is_dir($path))
                            {
                                if(mkdir($path, 0777) == false)
                                    exit('Error create dir ' . $path);
                            }
                            $path .= '/comments';
                            if(!is_dir($path))
                            {
                                if(mkdir($path, 0777) == false)
                                    exit('Error create dir ' . $path);
                            }
                            $path .= '/'.$id;
                            if(!is_dir($path))
                            {
                                if(mkdir($path, 0777) == false)
                                    exit('Error create dir ' . $path);
                            }
                            $path .= '/';

                            $this->load->library('image');
                            for ($i=0; $i < count($_FILES['images']['name']); $i++) {
                                if(is_array($image_names))
                                    $name = $image_names[$i];
                                else
                                    $name = $image_names;
                                if(empty($name))
                                    continue;
                                $this->image->setExtension();
                                if($this->image->uploadArray($name_field, $i, $path, $name))
                                {
                                    $extension = $this->image->getExtension();
                                    $this->image->resize(1280, 1280);
                                    $this->image->save();
                                    if($this->image->getErrors() == '')
                                    {
                                        if($this->image->loadImage($path, $name, $extension))
                                        {
                                            $this->image->preview(130, 90);
                                            $this->image->save('m');
                                        }
                                    }
                                    else
                                        $_SESSION['notify']->errors = '<ul>'.$this->image->getErrors('<li>', '</li>').'</ul>';
                                }
                                else
                                    $_SESSION['notify']->errors = '<ul>'.$this->image->getErrors('<li>', '</li>').'</ul>';
                            }
                        }

                        if($this->mail_notify_to)
                        {
                        	if ($this->mail_notify_to == 'SITE_EMAIL')
                        		$this->mail_notify_to = SITE_EMAIL;
                            $this->wl_comments_model->paginator = false;
                            $comment = $this->wl_comments_model->get($id, 'single');
                            $this->load->library('mail');
                            $this->mail->sendTemplate('comment_add', $this->mail_notify_to, $comment);
                        }

                        if(empty($_SESSION['notify']->errors))
                            $this->redirect($this->anchorAfter($id));
                    }
                }
            }
            else
                $_SESSION['notify']->errors = '<ul>'.$this->validator->getErrors('<li>', '</li>').'</ul>';
        }
        
        if(isset($_SESSION['notify']->errors))
            $this->redirect('#comment_add_error');
        $this->redirect();
    }

}