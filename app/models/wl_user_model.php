<?php

/*
 * Модель для роботи з базою даних користувачів.
 * For White Lion CMS 1.0
 */

class wl_user_model {

    /*
     * В цю властивість записуються всі помилки.
     */
    public $user_errors = '';
    public $new_user_type = 0; // Ід типу новозареєстрованого користувача

    /*
     * Отримуємо дані користувача з бази даних
     */
    function getInfo($id = 0, $additionall = '*', $field = 'id')
    {
    	if($id == 0 && isset($_SESSION['user']->id) && !is_string($id)) $id = $_SESSION['user']->id;
        $user = $this->db->select('wl_users as u', '*', $id, $field)
                        ->join('wl_user_types', 'name as type_name, title as type_title', '#u.type')
                        ->join('wl_user_status', 'name as status_name, title as status_title, color as status_color', '#u.status')
                        ->get('single');
        if($user && $additionall)
        {
        	$info = false;
            $user->info = array();
        	if($additionall == '*')
        		$info = $this->db->getAllDataByFieldInArray('wl_user_info', $user->id, 'user');
        	else
        	{
        		$where['user'] = $user->id;
        		$where['field'] = explode(',', $additionall);
        		$info = $this->db->getAllDataByFieldInArray('wl_user_info', $where);
        	}
        	if($info)
        		foreach ($info as $i) {
        			$user->info[$i->field] = $i->value;

                    if($i->field == 'phone')
                        $user->phone = $i->value;
        		}
        }
        return $user;
    }

    /*
     * Метод додає користувача до бази.
     * info (array) масив з основними даними користувача (email|phone, name, photo, password)
     * additionall (array) додаткові дані користувача (phone, facebook, vk, city..)
     * new_user_type (int) ід типу користувача за замовчуванням (4 - простий користувач)
     * set_password (bool) чи встановлювати пароль до профілю (класична реєстрація - так, швидка зі соц. мереж - ні). також впливає на статус новозареєстрованого користувача (класична - 2, швидка наступний після 2)
     * comment (text) службовий коментар у реєстр
     */
    public function add($info = array(), $additionall = array(), $new_user_type = 0, $set_password = true, $comment = '')
    {
        $key = $email_phone = false;
        if(!empty($info['email']))
        {
            $key = 'email';
            $email_phone = $info['email'];
        }
        else if(!empty($info['phone']))
        {
            $key = 'phone';
            $email_phone = $info['phone'];
        }
        if(empty($key))
        {
            $this->user_errors = 'Поле email або phone є обов\'язковим для реєстрації';
            return false;
        }

        $status = $info['status'] ?? 2;
    	$status = $this->db->getAllDataById('wl_user_status', $status);
        if($new_user_type == 0)
            $new_user_type = $this->new_user_type;
        if($new_user_type == 0)
            $new_user_type = $_SESSION['option']->new_user_type ?? 4;
        if(empty($info['name']))
            $info['name'] = $email_phone;

        $user;
        if($this->userExists($email_phone, $key, $user))
        {
        	if($user->type >= 5 && $set_password)
        	{
        		$data = array();
                $data['alias'] = $user->alias = $this->makeAlias($info['name']);
                $data['name'] = $user->name = $info['name'];
                $data['phone'] = $user->phone = $info['phone'] ?? '';
        		$data['photo'] = $user->photo = '';
		    	$data['type'] = $user->type = $new_user_type;
                $data['status'] = $user->status = $status->id;
		    	$data['last_login'] = time();
		    	$data['auth_id'] = $user->auth_id = md5($info['name'].'|'.$info['password'].'|'.$user->email);
                if($set_password)
		    	    $data['password'] = $user->password = $this->getPassword($user->id, $user->email, $info['password']);
                if($_SESSION['language'])
                    $data['language'] = $_SESSION['language'];
		    	if($this->db->updateRow('wl_users', $data, $user->id))
		    		$this->db->register('signup', $comment, $user->id);
        	}
            elseif(!$set_password)
                $this->user_errors = 'Користувач з таким е-мейлом вже є!';
        	else
    		{
    			$this->user_errors = 'Користувач з таким е-мейлом вже є!';
    			return false;
    		}
            $user->info = array();
            if($info = $this->db->getAllDataByFieldInArray('wl_user_info', $user->id, 'user'))
                foreach ($info as $i) {
                    $user->info[$i->field] = $i->value;
                }
        }
        else
        {
        	$user = new stdClass();
        	$data = $user->info = array();
            $data['alias'] = $user->alias = $this->makeAlias($info['name']);
        	$data['email'] = $user->email = $info['email'];
	    	$data['name'] = $user->name = $info['name'];
	    	$data['phone'] = $user->phone = $info['phone'] ?? '';
            $data['photo'] = $user->photo = '';
	    	$data['type'] = $user->type = $new_user_type;
	    	$data['status'] = $user->status = $status->id;
            if($_SESSION['language'])
                $data['language'] = $_SESSION['language'];
	    	if($set_password)
                $data['auth_id'] = $user->auth_id = md5($info['name'].'|'.$info['password'].'|'.$user->email);
            else
                $data['auth_id'] = $user->auth_id = '';
    		$data['registered'] = $user->registered = time();
            $data['last_login'] = $user->last_login = 0;

	    	if($user->id = $this->db->insertRow('wl_users', $data))
	    	{
                if($set_password)
                {
                    $password = $this->getPassword($user->id, $user->email, $info['password']);
                    $this->db->updateRow('wl_users', array('password' => $password), $user->id);
                }

	    		$this->db->register('signup', $comment, $user->id);
	    	}
        }

        if($user)
        {
            $user->load = $status->load;
        	if(!empty($additionall))
			{
				foreach ($additionall as $key => $value) {
                    if(empty($user->info[$key]))
                    {
    					$info = array();
    					$info['user'] = $user->id;
    					$info['field'] = $key;
    					$info['value'] = $value;
    					$info['date'] = time();
    					$this->db->insertRow('wl_user_info', $info);
                        $user->info[$key] = $value;
                    }
				}
			}
			return $user;
		}

    	$this->user_errors = 'Виникли проблеми при реєстрації. Будь ласка, спробуйте пізніше.';
        return false;
    }

    private function makeAlias($name)
    {
        $name = $this->data->latterUAtoEN($name);
        $name = $alias = mb_eregi_replace('-', '.', $name);
        $i = 2;
        while($check = $this->db->getAllDataById('wl_users', $alias, 'alias'))
        {
            $alias = $name .'.'. $i;
            $i++;
        }
        return $alias;
    }


    /*
     * Метод перевіряє чи емейл існує в базі.
     * Використовується при реєстрації.
     */
    public function userExists($email_phone = '', $key = 'email', &$user = false)
    {
        $email_phone = $this->db->sanitizeString($email_phone);
        if($key == 'email')
        {
            $user = $this->db->select('wl_users as u', 'id, alias, email, name, type, status, photo, password, auth_id', $email_phone, 'email')
                            ->join('wl_user_info as i', 'value as phone', ['user' => '#u.id', 'field' => 'phone'])
                            ->limit(1)
                            ->get();
        }
        elseif($key == 'phone')
        {
            $user = $this->db->select('wl_user_info as i', 'value as phone', ['field' => 'phone', 'value' => $email_phone])
                            ->join('wl_users as u', 'id, alias, email, name, type, status, photo, password, auth_id', '#i.user')
                            ->limit(1)
                            ->get();
        }

        if($user)
        {
            if($user->status == 3)
                $this->user_errors = 'Користувач із даною адресою заблокований!';
            elseif($user->type == 5)
                return false;
            else
                $this->user_errors = 'Користувач з таким е-мейлом вже є!';
            return true;
        }

        return false;
    }
	
	public function checkConfirmed($email, $code)
	{
		$user = $this->db->select('wl_users as u', '*', $email, 'email')
                ->join('wl_user_info', "value as phone", ['user' => 'u.id', 'field' => 'phone'])
                ->join('wl_user_status', 'next', '#u.status')
                ->limit(1)
                ->get();
		if($user && $code == $user->auth_id)
		{
            $status = $this->db->getAllDataById('wl_user_status', $user->next);
			$this->db->updateRow('wl_users', array('status' => $user->next), $user->id);
			$user->status = $user->next;
			$this->setSession($user);
			$this->db->register('confirmed');
			return $status;
        }
        return false;
	}

    /* 
     * Перевірка логіну та пароля. Використовується для POST авторизація.
     * Якщо логін та пароль вірні в сессії робляться відповідні записи.
     */
    public function login($key = 'email', $key_value = '')
    {	
    	$user = false;
		if($key == 'email')
		{
			$user = $this->db->select('wl_users as u', '*', [$key => $key_value])
                                ->join('wl_user_info as i', "value as phone", ['user' => '#u.id', 'field' => 'phone'])
                                ->join('wl_user_status as s', "next as status_next, load", '#u.status')
                                ->get('single');
		}
		else
		{
            if($key == 'phone')
                $this->db->select('wl_user_info as ui', 'value as phone', ['field' => $key, 'value' => $key_value]);
            else
            {
                $this->db->select('wl_user_info as ui', 'field as ui_field, value as ui_value', ['field' => $key, 'value' => $key_value])
                        ->join('wl_user_info as i', "value as phone", ['user' => '#ui.user', 'field' => 'phone']);
            }
            $this->db->join('wl_users as u', '*', '#ui.user')
                    ->join('wl_user_status as s', "next as status_next, load", '#u.status');
			$user = $this->db->get('single');
		}
        
		if($user && $key_value != '')
		{
            if($key == 'email' || $key == 'phone')
            {
                if ($user->password != $this->getPassword($user->id, $user->email, $_POST['password'])) {
                    $this->db->register('login_bad', 'User IP: ' . $this->data->userIP(), $user->id);
                    $this->user_errors = 'Пароль невірний';
                    return false;
                }
            }

			if($user->status == 1)
			{
                $update = array();

                if($_SESSION['language'] && empty($user->language))
                {
                    $user->language = $_SESSION['language'];
                    $update['language'] = $_SESSION['language'];
                }

				$this->setSession($user, false);
				$auth_id = md5($user->email.'|'.$user->password.'|auth_id|'.time());
				setcookie('auth_id', $auth_id, time() + 3600*24*31, '/');

                $update['last_login'] = time();
                $update['auth_id'] = $auth_id;

				$this->db->updateRow('wl_users', $update, $user->id);
                $this->db->register('login_ok', "By {$key}. User IP: " . $this->data->userIP());
				return $user;
			}
			elseif($user->status != 3) // banned user
			{
				$this->setSession($user);
				return $user;
			}
			else
				$this->user_errors = 'Користувач заблокований! Зверніться до адміністрації на email '.SITE_EMAIL;
		}
        else
			$this->user_errors = 'Невірна пошта чи пароль.';
		return false;
    }

    private $wl_userID_info = 0;
    private $wl_user_info = false;
    public function setAdditional($user, $key, $value = '')
    {
        if(empty($user) || empty($key))
            return false;

        if($this->wl_userID_info != $user)
        {
            $this->wl_user_info = $this->db->getAllDataByFieldInArray('wl_user_info', $user, 'user');
            $this->wl_userID_info = $user;
        }

        if($this->wl_user_info)
        	foreach ($this->wl_user_info as $additionall) {
                if($additionall->field == $key)
                {
                    if($additionall->value != $value) {
                        $this->db->updateRow('wl_user_info', array('value' => $value, 'date' => time()), $additionall->id);
                        $text = 'Попереднє значення ' . $key.': '.$additionall->value;
                        if($user != $_SESSION['user']->id)
                            $text .= ' (менеджер: '. $_SESSION['user']->id. ', '.$_SESSION['user']->name.')'; 
                        $this->db->register('profile_data', $text, $user);
                    }
                    return true;
                }
            }

        if(empty($value))
            return true;

		$this->db->insertRow('wl_user_info', ['user' => $user, 'field' => $key, 'value' => $value, 'date' => time()]);                
		return true;
    }

    public function reset($email = '')
    {
    	$user = $this->db->getAllDataById('wl_users', $email, 'email');
    	if($user)
    	{
			$data = array();
			$data['reset_key'] = $user->reset_key = md5($_POST['email'].'|'.$user->auth_id.'|'.time());
			$data['reset_expires'] = $user->reset_expires = mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"));//+ 2 ГОДИНИ!!!
			$this->db->updateRow('wl_users', $data, $user->id);
			$this->db->register('reset_sent', '', $user->id);
			return $user;
    	}
    	return false;
    }

	public function getPassword($id, $email, $password)
	{
		if(!$this->data->post('sequred'))
            $password = md5($password);
		return sha1($email . $password . SYS_PASSWORD . $id);
	}

	public function setSession($user, $updateLastLogin = true)
	{
        $_SESSION['user']->id = $user->id;
		$_SESSION['user']->alias = $user->alias;
        $_SESSION['user']->name = $user->name;
        $_SESSION['user']->email = $user->email;
        $_SESSION['user']->phone = $user->phone ?? '';
        $_SESSION['user']->type = $user->type;
        $_SESSION['user']->status = $user->status;
        $_SESSION['user']->language = $user->language;
        $_SESSION['user']->photo = NULL;
        if(!empty($user->photo))
            $_SESSION['user']->photo = IMG_PATH.'profile/'.$user->photo;
        $_SESSION['user']->admin = $_SESSION['user']->manager = 0;
        $_SESSION['user']->permissions = array('admin', 'wl_users', 'wl_ntkd', 'wl_photos', 'wl_video', 'wl_audio', 'wl_files');

        if($user->type == 1)
            $_SESSION['user']->admin = 1;

        else if($user->type == 2)
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

        if($updateLastLogin)
            $this->db->updateRow('wl_users', array('last_login' => time()), $user->id);

        return true;
	}

    public function setPhotoByLink($facebookPhotoLink, $userId = 0, $updateSession = true)
    {
        if($facebookPhoto = file_get_contents($facebookPhotoLink))
            if($info = getimagesize($facebookPhotoLink))
            {
                if($userId == 0)
                    $userId = $_SESSION['user']->id;
                if(empty($info['mime']))
                    return false;
                $mime = explode('/', $info['mime']);
                if($mime[0] != 'image')
                    return false;
                if(empty($mime[1]) || strlen($mime[1]) < 3 || $mime[1] == 'jpeg')
                    $mime[1] = 'jpg';

                $photoName = $userId;
                $photoName .= '-'.md5($userId.time()) .'.'. $mime[1];

                $path = IMG_PATH;
                $path = substr($path, strlen(SITE_URL));
                $path = substr($path, 0, -1);
                if(!is_dir($path))
                {
                    if(mkdir($path, 0777) == false)
                        return false;
                }
                $path .= '/profile';
                if(!is_dir($path))
                {
                    if(mkdir($path, 0777) == false)
                        return false;
                }
                $path .= '/';
                
                if(file_put_contents($path.$photoName, $facebookPhoto))
                {
                    $class_path = SYS_PATH.'libraries'.DIRSEP.'image.php';
                    if(file_exists($class_path))
                    {
                        require $class_path;
                        $image = new image();
                        $image->loadImage($path.$photoName);
                        $image->preview(300, 300, 100);
                        $image->save('s');
                        $image->loadImage($path.$photoName);
                        $image->preview(50, 50, 100);
                        $image->save('p');
                    }

                    $this->db->updateRow('wl_users', array('photo' => $photoName), $userId);
                    if($updateSession && isset($_SESSION['user']->photo))
                        $_SESSION['user']->photo = $path.$photoName;
                    return $photoName;
                }
            }
        return false;
    }
	
}

?>