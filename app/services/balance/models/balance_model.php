<?php 

class balance_model
{
	private $user_id = 0;
	private $user_balance = false;

	public function getPayment_s($id, $paginator = false)
	{
		$this->db->select('s_balance as p', '*', $id)
					->join('wl_aliases', 'alias as action_alias_link', '#p.action_alias')
					->join('wl_users as c', 'name as client_name, email as client_email, balance as client_balance_now', '#p.user')
					->join('wl_user_info as ui_phone', 'value as client_phone', array('field' => 'phone', 'user' => "#p.user"))
        			->join('wl_users as m', 'name as manager_name', '#p.manager');
        if($paginator)
        {
        	$this->db->order('id DESC');
	        if($_SESSION['option']->paginator_per_page > 0)
	        {
	            $start = 0;
	            if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
	                $_SESSION['option']->paginator_per_page = $_GET['per_page'];
	            if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
	                $start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
	            $this->db->limit($start, $_SESSION['option']->paginator_per_page);
	        }
	        $_SESSION['option']->paginator_total = $this->db->get('count', false);
        }
        $payment_s = $this->db->get();
        if(is_object($payment_s))
        {
        	$payment_s->security_check = false;
        	if($this->getPaymentSign($payment_s) == $payment_s->sign)
        		$payment_s->security_check = true;
        	if($paginator)
        		$payment_s = [$payment_s];
        }
        else if(is_array($payment_s))
        	foreach ($payment_s as $payment) {
        		$payment->security_check = false;
	        	if($this->getPaymentSign($payment) == $payment->sign)
	        		$payment->security_check = true;
        	}
        return $payment_s;
	}

	public function getUserBalance($user_id = 0, $force = false)
	{
		if($user_id == 0)
			$user_id = $_SESSION['user']->id;
		if($this->user_id == $user_id && is_numeric($this->user_balance) && !$force)
			return $this->user_balance;
		if($user = $this->db->getAllDataById('wl_users', $user_id))
		{
			$this->user_id = $user->id;
	        if(empty($user->balance) && empty($user->balance_sign))
	        {
	            $this->db->updateRow('wl_users', ['balance' => 0, 'balance_sign' => $this->getUserSign(0, $user->id)], $user->id);
	            $this->user_balance = 0;
	        }
	        else if($user->balance_sign != $this->getUserSign($user->balance, $user->id))
	        {
	        	// echo "new user sign: ".$this->getUserSign($user->balance);
	        	// exit;
	            $this->security_block($user->id);
	        }
	        $this->user_balance = $user->balance;
	    }
	    return $this->user_balance;
	}

	public function setUserBalance($balance, $user_id = 0)
	{
		if($user_id == 0)
			$user_id = $_SESSION['user']->id;
		$this->db->updateRow('wl_users', ['balance' => $balance, 'balance_sign' => $this->getUserSign($balance, $user_id)], $user_id);
		$this->user_id = $user_id;
		$this->user_balance = $balance;
	}

	// status => 1 Не змінює поточний баланс користувача
	public function debit($data = [])
	{
		if(empty($data['debit']))
			return false;
		if(empty($data['user']))
        	$data['user'] = $_SESSION['user']->id;
        $data['balance'] = $this->getUserBalance($data['user']);
        if(empty($data['status']))
        	$data['status'] = 1;
        if($data['status'] == 2)
        {
        	if(empty($data['manager']))
        	{
        		$data['date_edit'] = time();
        		$data['manager'] = $_SESSION['user']->id;
        	}
        	$data['balance'] += $data['debit'];
        	$this->setUserBalance($data['balance'], $data['user']);
        }
        $data['credit'] = 0;
        $keys_0 = ['action_alias', 'action_id', 'date_edit', 'manager'];
        foreach ($keys_0 as $key) {
        	if(!isset($data[$key]))
        		$data[$key] = 0;
        }
        $keys_empty_text = ['action', 'info'];
        foreach ($keys_empty_text as $key) {
        	if(empty($data[$key]))
        		$data[$key] = '';
        }
        $data['date_add'] = time();
        $data['sign'] = $this->getPaymentSign($data);

        return $this->db->insertRow('s_balance', $data);
	}

	// ЗАВЖДИ змінює баланс користувача!
	// Може зразу мати debit: $balance = getUserBalance() + $debit - $credit;
	public function credit($data = [])
	{
		if(empty($data['credit']))
			return false;
		if(empty($data['user']))
        	$data['user'] = $_SESSION['user']->id;
        if(empty($data['debit']))
        	$data['debit'] = 0;
        $data['balance'] = $this->getUserBalance($data['user']) + $data['debit'] - $data['credit'];
        $this->setUserBalance($data['balance'], $data['user']);
        if(empty($data['status']))
        	$data['status'] = 1;
        if($data['status'] == 2 && empty($data['manager']))
        {
        	$data['date_edit'] = time();
    		$data['manager'] = $_SESSION['user']->id;
        }
        $keys_0 = ['action_alias', 'action_id', 'date_edit', 'manager'];
        foreach ($keys_0 as $key) {
        	if(!isset($data[$key]))
        		$data[$key] = 0;
        }
        $keys_empty_text = ['action', 'info'];
        foreach ($keys_empty_text as $key) {
        	if(empty($data[$key]))
        		$data[$key] = '';
        }
        $data['date_add'] = time();
        $data['sign'] = $this->getPaymentSign($data);

        return $this->db->insertRow('s_balance', $data);
	}

	public function editPayment($payment, $newData = [])
	{
		if($payment->status > 1)
			return false;

		$diff = 0;
		$update = [];
		if(isset($newData['debit']) && $payment->debit != $newData['debit'])
			$update['debit'] = $newData['debit'];
		else if(isset($newData['credit']) && $payment->credit != $newData['credit'])
		{
			$update['credit'] = round($newData['credit'], 3);
			$diff = $payment->credit - $newData['credit'];
			$update['balance'] = round($payment->balance + $diff, 3);
		}
		if(!empty($update))
		{
			$update['date_edit'] = time();
			$update['manager'] = $_SESSION['user']->id;
			foreach ($update as $key => $value) {
	        	$payment->$key = $value;
	        }
	        $update['sign'] = $this->getPaymentSign($payment);
	        $this->db->updateRow('s_balance', $update, $payment->id);

	        if($diff)
	        {
	        	$balance = $this->getUserBalance($payment->user, true);
	        	$balance = round($balance + $diff, 3);
	        	$this->setUserBalance($balance, $payment->user);

	        	if($payments = $this->db->getAllDataByFieldInArray('s_balance', ['user' => $payment->user, 'status' => '<3', 'date_add' => '>'.$payment->date_add]))
	        		foreach ($payments as $pay) {
	        			$update = [];
	        			$pay->balance = $update['balance'] = $pay->balance + $diff;
	        			$update['sign'] = $this->getPaymentSign($pay);
	        			$this->db->updateRow('s_balance', $update, $pay->id);
	        		}
	        }
		}
	}

	public function confirmPayment($payment, $action_text = false)
	{
		if($payment->status > 1)
			return false;

    	$data = ['status' => 2];
		$data['manager'] = $_SESSION['user']->id;
		$data['date_edit'] = time();
        	
        if($payment->debit)
        {
        	$data['date_add'] = $data['date_edit'];
        	$data['debit'] = $payment->debit;
	        $data['balance'] = $this->getUserBalance($payment->user);
	    	$data['balance'] = $data['balance'] + $payment->debit;
	    	$this->setUserBalance($data['balance'], $payment->user);
	    }

    	if($action_text)
    	{
    		if($payment->action)
    		{
    			if($payment->debit)
					$data['action'] = $payment->action .date(' d.m.Y H:i', $payment->debit). '<hr>'.$action_text;
				else
					$data['action'] = $payment->action . '<hr>'.$action_text;
    		}
			else
				$data['action'] = $action_text;
    	}
    	else
    	{
    		if($info = $this->data->post('info'))
			{
				if($payment->info)
					$data['info'] = $payment->info . '<hr>'.$info;
				else
					$data['info'] = $info;
			}
    	}
        
        foreach ($data as $key => $value) {
        	$payment->$key = $value;
        }
        $data['sign'] = $this->getPaymentSign($payment);

        $this->db->updateRow('s_balance', $data, $payment->id);
        return true;
	}

	public function cancelPayment($payment_id)
	{
		if($payment = $this->getPayment_s($payment_id))
			if($payment->security_check)
			{
				$data_update = ['status' => 3];
				$data_update['date_edit'] = time();
				$data_update['manager'] = $_SESSION['user']->id;
				if($info = $this->data->post('info'))
				{
					if($payment->info)
						$data_update['info'] = $payment->info . '<hr>'.$info;
					else
						$data_update['info'] = $info;
				}
				
				if($payment->status == 1 && $payment->credit)
				{
					$data_update['balance'] = $this->getUserBalance($payment->user, true);
			        $data_update['balance'] += $payment->credit;
			        $this->setUserBalance($data_update['balance'], $payment->user);
				}
				if($payment->status == 2)
					$data_update['status'] = 4;
				foreach ($data_update as $key => $value) {
		        	$payment->$key = $value;
		        }
		        $data_update['sign'] = $this->getPaymentSign($payment);
				$this->db->updateRow('s_balance', $data_update, $payment_id);

				if($payment->status == 4)
				{
					$data = ['user' => $payment->user, 'status' => 2];
					$data['action'] = 'Скасування платежу #'.$payment_id;
					$data['action_id'] = $payment_id;
					$data['debit'] = $payment->credit;
					if($info = $this->data->post('info'))
						$data['info'] = $info;
					if($payment->debit)
					{
						$data['credit'] = $payment->debit;
						$this->credit($data);
					}
					else if($payment->credit)
						$this->debit($data);
				}
				return true;
			}
		return false;
	}
	
	public function getUserSign($amount = 0, $user_id = 0)
	{
		if($user_id == 0)
			$user_id = $_SESSION['user']->id;
		return sha1("user {$user_id} balance ".md5($amount.SYS_PASSWORD));
	}

	public function getPaymentSign($pay)
	{
		if(is_array($pay))
			$pay = (object) $pay;
		return sha1("Payment sign {$pay->user} balance {$pay->balance} = ".md5($pay->debit.'-'.$pay->credit.SYS_PASSWORD)." {$pay->status} {$pay->manager} {$pay->date_edit} {$pay->action_alias} {$pay->action_id} {$pay->action} {$pay->info} add {$pay->date_add}");
	}

	public function security_block($user_id = 0)
	{
		if($user_id == 0)
			$user_id = $_SESSION['user']->id;
		$this->db->updateRow('wl_users', ['status' => 3], $user_id);
        $this->db->register('profile_type', 'Система безпеки: Цифровий підпис балансу користувача не коректний!');
        if($user_id == $_SESSION['user']->id)
        {
	        $redirect_path = SITE_URL.$_SESSION['alias']->alias.'/security_block';
	        session_destroy();
	        setcookie('auth_id', '', time() - 3600, '/');
	        header ('HTTP/1.1 303 See Other');
			header("Location: {$redirect_path}");
			exit();
		}
	}

}

 ?>