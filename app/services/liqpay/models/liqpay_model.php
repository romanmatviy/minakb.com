<?php 

class liqpay_model
{

	public function create($cart)
	{
		$pay['alias'] = $_SESSION['alias']->id;
		$pay['cart_alias'] = $cart->wl_alias;
		$pay['cart_id'] = $cart->id;
		if($_SESSION['option']->useMarkUp && $_SESSION['option']->markUp > 0) {
			if($_SESSION['option']->markUp > 0)
				$pay['murkup'] = $cart->total * $_SESSION['option']->markUp / 100;
			else if($_SESSION['option']->markUp < 0)
				$pay['murkup'] = -1 * $_SESSION['option']->markUp / 100;
			$pay['amount'] = $cart->total * $pay['murkup'];
		}
		else
		{
			$pay['amount'] = $cart->total;
			$pay['murkup'] = 0;
		}
		$pay['status'] = 'new';
		$pay['details'] = "Оплата замовлення #{$cart->id}";
		$pay['date_add'] = $pay['date_edit'] = time();
		$pay['comment'] = $pay['signature'] = '';
		$id = $this->db->insertRow($_SESSION['service']->table, $pay);
		return $this->db->getAllDataById($_SESSION['service']->table, $id);
	}

	public function validate($id)
	{
    	if(isset($_POST['data']) && $_POST['data'] != '')
    	{
			$signature = base64_encode( sha1( $_SESSION['option']->private_key . $_POST['data'] . $_SESSION['option']->private_key , 1 ) );
			if($_POST['signature'] == $signature)
			{
				$data = json_decode ( base64_decode ($_POST['data']) );
				if($data->version == 3 && ($data->status == 'success' || $data->status == 'sandbox'))
				{
					$pay = $this->db->getAllDataById($_SESSION['service']->table, $id);
					if($pay && $pay->id == $data->order_id)
					{
						$pay->amount = floatval($pay->amount);
						$data->amount = floatval($data->amount);

						$sender_phone = '';
						if(isset($data->sender_phone)) $sender_phone = ' '.$data->sender_phone;
						$transaction = 'LiqPay Transaction ID: '.$data->transaction_id.$sender_phone;

						if($data->amount == $pay->amount)
						{
							$update = array();
							$pay->status = $update['status'] = $data->status;
							$pay->comment = $update['comment'] = $transaction;
							$pay->date_edit = $update['date_edit'] = time();
							$update['signature'] = $this->signature($pay);
							$this->db->updateRow($_SESSION['service']->table, $update, $pay->id);

							if(isset($_SESSION['option']->successPayStatusToCart) && $_SESSION['option']->successPayStatusToCart > 0)
								$pay->cart_status = $_SESSION['option']->successPayStatusToCart;

							return $pay;
						}
					}
				}
				else
				{
					$pay = $this->db->getAllDataById($_SESSION['service']->table, $id);
					if($pay && $pay->id == $data->order_id)
					{
						$sender_phone = '';
						if(isset($data->sender_phone)) $sender_phone = ' '.$data->sender_phone;
						$transaction = 'LiqPay Transaction ID: '.$data->status.' '.$sender_phone;

						$update = array();
						$pay->status = $update['status'] = $data->status;
						$pay->comment = $update['comment'] = $transaction;
						$pay->date_edit = $update['date_edit'] = time();
						$update['signature'] = $this->signature($pay);
						$this->db->updateRow($_SESSION['service']->table, $update, $pay->id);
					}
				}
			}
		}
		return false;
	}

	public function getPayments()
	{
		// $this->db->select($_SESSION['service']->table.' as p', '*', $_SESSION['alias']->id, 'alias');
		$this->db->select($_SESSION['service']->table.' as p', '*');
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
		return sha1($pay->id.'LiQpaY'.$pay->alias.$pay->cart_alias.$pay->amount.'UAH'.$pay->comment.$pay->status.$pay->details.$pay->cart_id.$pay->date_add.$pay->murkup.md5($pay->date_edit.SYS_PASSWORD));
	}

}

?>