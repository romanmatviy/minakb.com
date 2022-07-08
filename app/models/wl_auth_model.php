<?php

class wl_Auth_model {

    function authByCookies()
    {
        $this->db->select('wl_users as u', "id, alias, name, email, type, status, language, last_login", $_COOKIE['auth_id'], 'auth_id')
                ->join('wl_user_info', "value as phone", ['user' => 'u.id', 'field' => 'phone'])
                ->limit(1);
        if($user = $this->db->get('single'))
        {
            $_SESSION['user']->id = $user->id;
            $_SESSION['user']->alias = $user->alias;
            $_SESSION['user']->name = $user->name;
            $_SESSION['user']->email = $user->email;
            $_SESSION['user']->phone = $user->phone;
            $_SESSION['user']->type = $user->type;
			$_SESSION['user']->status = $user->status;
            $_SESSION['user']->language = $user->language;
            $_SESSION['user']->permissions = array('admin', 'wl_users', 'wl_ntkd', 'wl_images', 'wl_video', 'wl_audio', 'wl_files');
            $_SESSION['user']->admin = $_SESSION['user']->manager = 0;

            if($user->type == 1)
                $_SESSION['user']->admin = 1;
            elseif($user->type == 2)
            {
                $search_forms = array();
                $_SESSION['user']->manager = 1;
                $this->db->select('wl_user_permissions as up', '*', $user->id, 'user');
                $this->db->join('wl_aliases', 'alias', '#up.permission');
                if($permissions = $this->db->get('array'))
                    foreach($permissions as $permission)
                    {
                        if($permission->permission == 0)
                            $_SESSION['user']->permissions[] = 'wl_comments';
                        if($permission->permission > 0)
                            $_SESSION['user']->permissions[] = $permission->alias;
                        else
                            $search_forms[] = -$permission->permission;
                    }
                if(!empty($search_forms))
                {
                    $_SESSION['user']->permissions[] = 'wl_forms';
                    if($forms = $this->db->getAllDataByFieldInArray('wl_forms', array('id' => $search_forms)))
                        foreach ($forms as $form) {
                            $_SESSION['user']->permissions[] = 'form_'.$form->name;
                        }
                }
            }

            $updateData = [];

            $time5min = $user->last_login + 60*5;
            if(time() > $time5min)
                $updateData['last_login'] = time();

            if($_SESSION['language'] && $user->language != $_SESSION['language'])
            {
                $updateData['language'] = $_SESSION['language'];
                $_SESSION['user']->language = $_SESSION['language'];
            }

            if(!empty($updateData))
                $this->db->updateRow('wl_users', $updateData, $user->id);
        }
    }

}

?>
