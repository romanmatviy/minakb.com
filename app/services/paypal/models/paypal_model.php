<?php 

class paypal_model
{

	public function getPaypalLink()
	{
		if($_SESSION['option']->testPay)
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		return 'https://www.paypal.com/cgi-bin/webscr';;
	}

	public function create($cart)
	{
		$pay['alias'] = $_SESSION['alias']->id;
		$pay['cart_alias'] = $cart->wl_alias;
		$pay['cart_id'] = $cart->id;
		$pay['amount'] = $cart->total;
		$pay['currency_code'] = $_SESSION['option']->currency_code;
		$pay['status'] = 'new';
		$pay['details'] = "Order #{$cart->id}";
		$pay['date_add'] = $pay['date_edit'] = time();
		$pay['comment'] = $pay['signature'] = '';

		$object = new stdClass();
		$object->id = $this->db->insertRow($_SESSION['service']->table, $pay);
		$object->formLink = $this->getPaypalLink();

		foreach ($pay as $key => $value) {
			$object->$key = $value;
		}

		$signature = $this->signature($object);
		$this->db->updateRow($_SESSION['service']->table, array('signature' => $signature), $object->id);

		return $object;
	}

	public function validate()
	{
		if(empty($_POST['item_number']) || !is_numeric($_POST['item_number']) || $_POST['item_number'] <= 0)
			exit;

		$pay_id = $this->data->post('item_number');
		if($pay = $this->db->getAllDataById($_SESSION['service']->table, $pay_id))
		{
			if($pay->status == 'Completed')
				exit;

			if($pay->signature != $this->signature($pay))
				$this->saveToHistory($pay_id, 'bad signature');

			$postdata = ""; 
			foreach ($_POST as $key => $value)
				$postdata .= $key."=".urlencode($value)."&";  
			$postdata.="cmd=_notify-validate"; 
			$curl = curl_init($this->getPaypalLink()); 
			curl_setopt ($curl, CURLOPT_HEADER, 0); 
			curl_setopt ($curl, CURLOPT_POST, 1); 
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $postdata); 
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 2); 
			$response = curl_exec ($curl); 
			curl_close ($curl); 

			if ($response != "VERIFIED")
				$this->saveToHistory($pay_id, $response);

			$fields = array();
			$fields['business'] = $_SESSION['option']->receiverEmail;
			$fields['txn_type'] = "web_accept";
			$fields['item_name'] = $pay->details;
			$fields['mc_gross'] = $pay->amount;
			$fields['mc_currency'] = $pay->currency_code;
			
			$go = true;
			foreach ($fields as $key => $value) {
				if($_POST[$key] != $value)
				{
					$go = false;
					break;
				}
			}

			if (!$go)
				$this->saveToHistory($pay->id, '!Дані платежу не співпадають');

			$payment_status = $this->data->post('payment_status');
			$transaction = 'PayPal Transaction ID: '.$this->data->post('txn_id');

			$update = array();
			$pay->status = $update['status'] = $payment_status;
			$pay->comment = $update['comment'] = $transaction;
			$pay->date_edit = $update['date_edit'] = time();
			$update['signature'] = $this->signature($pay);
			$this->db->updateRow($_SESSION['service']->table, $update, $pay->id);

			$this->saveToHistory($pay->id, $payment_status, false);
			if($payment_status == 'Completed')
				return $pay;
			elseif($payment_status == 'Pending')
			{
				$pay->cart_status = 1;
				return $pay;
			}
		}
		return false;
	}

	public function getPayments()
	{
		$this->db->select($_SESSION['service']->table.' as p', '*', $_SESSION['alias']->id, 'alias');
		$this->db->join('wl_aliases', 'alias as cart_alias_name', '#p.cart_alias');
		$this->db->order('id DESC');
		return $this->db->get('array');
	}

	public function getPayment($id)
	{
		$this->db->select($_SESSION['service']->table.' as p', '*', $id);
		$this->db->join('wl_aliases', 'alias as cart_alias_name', '#p.cart_alias');
		$payment = $this->db->get();
		if($payment)
		{
			$payment->check = ($payment->signature == $this->signature($payment)) ? true : false;
			$payment->history = $this->db->getAllDataByFieldInArray($_SESSION['service']->table.'_history', $payment->id, 'pay_id');
			if($payment->history)
				foreach ($payment->history as $row) {
					$row->check = ($row->signature == $this->signatureHistory($row)) ? true : false;
				}
		}
		return $payment;
	}

	private function signature($pay)
	{
		return sha1($pay->id.'PayPalLsignature'.$pay->alias.$pay->cart_alias.$pay->amount.$pay->comment.$pay->status.$pay->details.$pay->cart_id.$pay->date_add.$pay->currency_code.md5($pay->date_edit.SYS_PASSWORD));
	}

	public function saveToHistory($pay_id, $response, $exit = true)
	{
		$history = array();
		$history['pay_id'] = $pay_id;
		$history['status'] = $response;
		$history['details'] = print_r($_POST, true);
		$history['date'] = $history['signature'] = time();
		if($id = $this->db->insertRow($_SESSION['service']->table.'_history', $history))
		{
			$sign = $this->signatureHistory($history, $id);
			$this->db->updateRow($_SESSION['service']->table.'_history', array('signature' => $sign), $id);
		}
		if($exit)
			exit;
	}

	public function signatureHistory($row, $id = false)
	{
		if(is_array($row) && $id)
			return sha1($id.$row['pay_id'].'PayPalLHistory'.$row['pay_id'].$row['status'].$row['date'].md5($row['details'].SYS_PASSWORD));
		else if(is_object($row))
			return sha1($row->id.$row->pay_id.'PayPalLHistory'.$row->pay_id.$row->status.$row->date.md5($row->details.SYS_PASSWORD));
		return false;
	}

}

?>