<?php 

class privat24_model
{

	public function create($cart)
	{
		$pay['alias'] = $_SESSION['alias']->id;
		$pay['cart_alias'] = $cart->wl_alias;
		$pay['cart_id'] = $cart->id;
		if($_SESSION['option']->useMarkUp && $_SESSION['option']->markUp > 0) {
			$pay['murkup'] = $cart->total * $_SESSION['option']->markUp / 100;
			$pay['amount'] = $cart->total * $pay['murkup'];
		}
		else
		{
			$pay['amount'] = $cart->total;
			$pay['murkup'] = 0;
		}
		$pay['currency'] = 0;
		$pay['status'] = 'new';
		$pay['details'] = "Оплата замовлення #{$cart->id}";
		$pay['date_add'] = $pay['date_edit'] = time();
		$pay['comment'] = $pay['signature'] = '';
		$this->db->insertRow($_SESSION['service']->table, $pay);
		$id = $this->db->getLastInsertedId();
		return $this->db->getAllDataById($_SESSION['service']->table, $id);
	}

    public function validate($id)
    {
    	if(isset($_POST['payment']) && $_POST['payment'] != '')
    	{
			$signature = sha1(md5($_POST['payment'].$_SESSION['option']->password));
			if($_POST['signature'] == $signature)
			{
				parse_str($_POST['payment'], $output);
				if($output['state'] == 'test' || $output['state'] == 'ok')
				{
					$pay = $this->db->getAllDataById($_SESSION['service']->table, $id);
					if($pay && $pay->id == $output['order'])
					{
						$amount = floatval($output['amt']);
						$pay->amount = floatval($pay->amount);

						$sender_phone = '';
						if(isset($output['sender_phone'])) $sender_phone = ' '.$output['sender_phone'];
						$transaction = 'Privat24 Transaction ID: '.$output['ref'].$sender_phone;

						if($amount == $pay->amount)
						{
							$update = array();
							$pay->status = $update['status'] = $output['state'];
							$pay->comment = $update['comment'] = $transaction;
							$pay->date_edit = $update['date_edit'] = time();
							$update['signature'] = $this->signature($pay);
							$this->db->updateRow($_SESSION['service']->table, $update, $pay->id);

							return $pay;
						}
					}
				}
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
			$payment->check = ($payment->signature == $this->signature($payment)) ? true : false;
		return $payment;
	}

	private function signature($pay)
	{
		return sha1($pay->id.'Privat24pay'.$pay->alias.$pay->cart_alias.$pay->amount.$pay->currency.$pay->comment.$pay->status.$pay->details.$pay->cart_id.$pay->date_add.$pay->murkup.md5($pay->date_edit.SYS_PASSWORD));
	}

}

?>