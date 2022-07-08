<?php 

class pb_payparts_model
{
	private $payURL = 'https://payparts2.privatbank.ua/ipp/v2/payment/create';
	private $holdURL = 'https://payparts2.privatbank.ua/ipp/v2/payment/hold';
	private $stateURL = 'https://payparts2.privatbank.ua/ipp/v2/payment/state';
	private $confirmHoldURL = 'https://payparts2.privatbank.ua/ipp/v2/payment/confirm';
	private $cancelHoldUrl = 'https://payparts2.privatbank.ua/ipp/v2/payment/cancel';

	public function create($cart)
	{
		if(!in_array($_SESSION['option']->merchantType, array('II', 'PP', 'PB', 'IA'), false))
			exit('MerchantType must be in array(\'II\', \'PP\', \'PB\', \'IA\')');
		
		$pay['alias'] = $_SESSION['alias']->id;
		$pay['merchant_type'] = $_SESSION['option']->merchantType;
		$pay['parts_count'] = $this->data->post('parts_count');
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
		$pay['status'] = 'new';
		$pay['details'] = "Оплата замовлення #{$cart->id}";
		$pay['date_add'] = $pay['date_edit'] = time();
		$pay['comment'] = $pay['signature'] = '';
		$id = $this->db->insertRow($_SESSION['service']->table, $pay);

		$orderId = $cart->id.'/'.$id;
		$responseUrl = SERVER_URL.$_SESSION['alias']->alias.'/validate/'.$id;
		// $responseUrl = 'https://epart.requestcatcher.com/validate/'.$id;
		$returnUrl = SERVER_URL.$cart->return_url;
		$productsString = '';
		$productsList = [];

		// $this->amount += $arr['count'] * $arr['price'];
		foreach ($cart->products as $product) {
			$product_name = trim($product->info->name);
			$product_price = (string) $product->price;
			$productsString .= $product_name . $product->quantity . $product_price * 100;
			$productsList[] = ['name' => $product_name, 'count' => $product->quantity, 'price' => $product_price];
		}

		$SignatureForCall = array(
			$_SESSION['option']->password,
			$_SESSION['option']->storeId,
			$orderId,
			(string)($cart->total * 100),
			$pay['parts_count'],
			$_SESSION['option']->merchantType,
			$responseUrl,
			$returnUrl,
			$productsString,
			$_SESSION['option']->password
		);
		$param['storeId'] = $_SESSION['option']->storeId;
		$param['orderId'] = $orderId;
		$param['amount'] = $cart->total;
		$param['partsCount'] = $pay['parts_count'];
		$param['merchantType'] = $_SESSION['option']->merchantType;
		$param['products'] = $productsList;
		$param['responseUrl'] = $responseUrl;
		$param['redirectUrl'] = $returnUrl;
		$param['signature'] = $this->calcSignature($SignatureForCall);

		$CreateResult = json_decode($this->sendPost($param, $this->payURL), true);

		if(empty($CreateResult['token']))
		{
			echo "<pre>";
			if (isset($_SESSION['user']->id) && $_SESSION['user']->id > 0 && $_SESSION['user']->admin) {
				echo "request params: <br>";
				print_r($param);
				echo "<br>answer:<br>";
			}
			print_r($CreateResult);
			return false;
		}

		if(!isset($CreateResult['message']))
			$CreateResult['message'] = '';

		$checkSignature = array(
			$_SESSION['option']->password,
			$CreateResult['state'],
			$CreateResult['storeId'],
			$CreateResult['orderId'],
			$CreateResult['message'],
			$CreateResult['token'],
			$_SESSION['option']->password
		);
		if($this->calcSignature($checkSignature) === $CreateResult['signature'])
			return $CreateResult;
		else
			return false;
	}

	public function validate($id)
	{
		$_POST = json_decode(file_get_contents('php://input'), true);
    	if(isset($_POST['paymentState']) && $_POST['paymentState'] != '')
    	{
			$signature = $this->calcSignature([$_SESSION['option']->password, $_SESSION['option']->storeId, $_POST['orderId'], $_POST['paymentState'], $_POST['message'], $_SESSION['option']->password]);
			if($_POST['signature'] == $signature)
			{
				if($pay  = $this->db->getAllDataById($_SESSION['service']->table, $id))
				{
					$orderId = $pay->cart_id.'/'.$id;
					if($orderId == $_POST['orderId'])
					{
						$update = array();
						$pay->status = $update['status'] = $_POST['paymentState'];
						$pay->comment = $update['comment'] = $_POST['message'];
						$pay->date_edit = $update['date_edit'] = time();
						$update['signature'] = $this->signature($pay);
						$this->db->updateRow($_SESSION['service']->table, $update, $pay->id);

						if($_POST['paymentState'] === "SUCCESS")
						{
							if(isset($_SESSION['option']->successPayStatusToCart) && $_SESSION['option']->successPayStatusToCart > 0)
								$pay->cart_status = $_SESSION['option']->successPayStatusToCart;

							return $pay;
						}
					}
				}
			}
			// else {
			// 	echo "request params: <br>";
			// 	print_r($_POST);
			// 	echo "<br>ok signature:<strong>{$signature}</strong>";
			// }
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

	private function sendPost($param, $url){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json','Accept: application/json; charset=utf-8'));
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	    return curl_exec($ch);
  	}

	private function calcSignature($array){
		$signature = '';
		foreach($array as $item)$signature .= $item;
		return base64_encode(sha1($signature, true));
	}

	private function signature($pay)
	{
		return sha1($pay->id.'PbPayParts'.$pay->alias.$pay->merchant_type.$pay->cart_alias.$pay->amount.'UAH'.$pay->parts_count.$pay->comment.$pay->status.$pay->details.$pay->cart_id.$pay->date_add.$pay->murkup.md5($pay->date_edit.SYS_PASSWORD));
	}

}

?>