<?php

class Logout extends Controller {

    function index()
    {
        session_destroy();
        setcookie('auth_id', '', time() - 3600, '/');
        header ('HTTP/1.1 303 See Other');
        header('Location: '.SITE_URL);
        exit();
    }

    public function as_user()
    {
        if(!empty($_SESSION['user']->real_user_id) && is_numeric($_SESSION['user']->real_user_id))
        {
            $this->load->model('wl_user_model');
            if($user = $this->wl_user_model->getInfo($_SESSION['user']->real_user_id))
            {
                if($user->type < 3 && $user->reset_expires == $_SESSION['user']->id)
                {
                	$to = 'admin/wl_users/'.$_SESSION['user']->email;
                	$this->db->register('logout_as_user', $user->id .'. '.$user->name);
                	$this->wl_user_model->setSession($user, false);
                    unset($_SESSION['user']->real_user_id);
                    $this->db->updateRow('wl_users', ['reset_expires' => 0], $_SESSION['user']->id);
                    $this->redirect($to);
                } 
            }
        }
        $this->load->page_404(false);
    }

}

?>
