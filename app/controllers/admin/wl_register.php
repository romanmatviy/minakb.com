<?php

class wl_register_admin extends Controller {
                
    function _remap($method)
    {
        $_SESSION['alias']->name = 'Реєстр';
        $_SESSION['alias']->breadcrumb = array('Реєстр' => '');
        if (method_exists($this, $method) && $method != 'library' && $method != 'db') {
            $this->$method();
        } else {
            $this->index($method);
        }
    }

    public function index(){
        if($_SESSION['user']->admin == 1){
            if($this->data->uri(2) != ''){
                $id = $this->data->uri(2);
                $id = $this->db->sanitizeString($id);
                if(is_numeric($id)) {
                    $register = $this->db->getQuery('SELECT r.*, do.title, do.title_public, do.public, do.help_additionall, u.name as user_name, u.email FROM wl_user_register as r LEFT JOIN wl_user_register_do as do ON do.id = r.do LEFT JOIN wl_users as u ON u.id = r.user WHERE r.id = '.$id);
                    if(is_object($register) && $register->id > 0){
                        $_SESSION['alias']->name = 'Реєстр. Запис №'.$register->id;
                        $_SESSION['alias']->breadcrumb = array('Реєстр' => 'admin/wl_register', 'Запис №'.$register->id => '');
                        $this->load->admin_view('wl_register/detal_view', array('register' => $register));
                    }
                }
            } else {
                $this->load->admin_view('wl_register/index_view');
            }
        }
    }

    public function getlist()
    {
        if($_SESSION['user']->admin == 1){
            $wl_register = $this->db->getQuery('SELECT r.*, do.title, u.name as user_name FROM wl_user_register as r LEFT JOIN wl_user_register_do as do ON do.id = r.do LEFT JOIN wl_users as u ON u.id = r.user', 'array');
            if($wl_register){
                foreach ($wl_register as $register) {
                    $register->name = '<a href="'.SITE_URL.'admin/wl_register/'.$register->id.'">'.$register->title.'</a>';
                    $register->user = (string) $register->user_name .' ('.$register->user.')';
                    $register->date = (string) date('d.m.Y H:i', $register->date);
                }
            }
            header('Content-type: application/json');
            echo json_encode(array('data' => $wl_register));
            exit;
        }
    }

}

?>
