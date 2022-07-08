<?php 

class novapay_model
{

	public $url_test = 'https://api-qecom.novapay.ua/v1';
	// public $url_test = 'https://mebliskif.requestcatcher.com/';
	public $url_pay = 'https://api-ecom.novapay.ua/v1';

	public function create($novapay_id, $cart)
	{
		$pay['alias'] = $_SESSION['alias']->id;
		$pay['cart_alias'] = $cart->wl_alias;
		$pay['cart_id'] = $cart->id;
		$pay['amount'] = $cart->total;
		$pay['novapay_id'] = $novapay_id;
		$pay['status'] = 'created';
		$pay['details'] = "Оплата замовлення #{$cart->id}";
		$pay['date_add'] = $pay['date_edit'] = time();
		$pay['signature'] = '';
		$pay['id'] = $this->db->insertRow($_SESSION['service']->table, $pay);
		return (object) $pay;
	}

	public function validate($novapay)
	{
		$path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP.'novapay_public_key.pem';
		if(!file_exists($path))
		{
			echo "novapay_public_key.pem not found";
			return false;
		}

		$binary_signature = false;
		$headers = apache_request_headers();
		foreach ($headers as $header => $value) {
			$header = strtolower($header);
			if($header == 'x-sign')
			{
				// echo $value .' ||| ';
				$binary_signature = base64_decode($value);
				break;
			}
		}
		if(empty($binary_signature))
		{
			echo "empty X-Sign";
			return false;
		}
		$public_key = openssl_pkey_get_public(file_get_contents($path));
		$data_string = file_get_contents('php://input');
		// var_dump($data_string);
		// echo $data_string .' ||| Content-Length: ' . strlen($data_string).' ';
		if(openssl_verify($data_string, $binary_signature, $public_key, OPENSSL_ALGO_SHA1) == 1)
		{
			if($pay = $this->db->getAllDataById($_SESSION['service']->table, $novapay->external_id))
			{
				if($pay->novapay_id == $novapay->id)
				{
					$data = array();
					$pay->status = $data['status'] = $novapay->status;
					$pay->date_edit = $data['date_edit'] = time();
					$data['signature'] = $this->signature($pay);
					$this->db->updateRow($_SESSION['service']->table, $data, $pay->id);

					$pay->amount = floatval($pay->amount);
					$novapay->amount = floatval($novapay->amount);
					if($novapay->amount == $pay->amount && in_array($pay->status, ['holded', 'hold_confirmed', 'paid']))
					{
						$pay->comment = "Зарезервовано <strong>{$pay->amount}грн</strong>";
						if($pay->status == 'paid')
							$pay->comment = "Оплачено <strong>{$pay->amount}грн</strong>";
						if(isset($_SESSION['option']->successPayStatusToCart) && $_SESSION['option']->successPayStatusToCart > 0)
							$pay->cart_status = $_SESSION['option']->successPayStatusToCart;

						return $pay;
					}
					else if($pay->status == 'voided')
					{
						$pay->cart_status = 7;
						return $pay;
					}
				}
			}
			else
				echo "Payment {$novapay->external_id} in db not found";
		}
		else
			echo "ssl verify error";
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
		return sha1($pay->id.'NoVaPaY'.$pay->alias.$pay->cart_alias.$pay->amount.'UAH'.$pay->status.$pay->novapay_id.$pay->details.$pay->cart_id.$pay->date_add.md5($pay->date_edit.SYS_PASSWORD));
	}

	public function create_novapay_session($pay, $debug = false)
	{
		$data = [];
		$data['merchant_id'] = $_SESSION['option']->merchant_id;
		$name = explode(' ', $this->latterENtoUA($pay->cart['user_name']));
		$data['client_first_name'] = $name[1] ?? '';
		$data['client_last_name'] = $name[0];
		$client_patronymic = explode('.', SITE_NAME);
		$data['client_patronymic'] = $this->latterENtoUA($client_patronymic[0]);
		$data['client_phone'] = $pay->cart['user_phone'] ?? ''; // string	phone in international format
		$data['callback_url'] = SERVER_URL.$_SESSION['alias']->alias.'/validate'; // string	url for receiving session status postbacks (server-server)
		// $data['callback_url'] = 'https://mebliskif.requestcatcher.com/'; // string	url for receiving session status postbacks (server-server)
		$data['metadata'] = false; // object	any data one needs to be returned in postbacks
		$data['success_url'] = SITE_URL.$pay->return_url; // string	optional url for button “return to the shop” on payment status page
		$data['fail_url'] = SITE_URL.$pay->return_url; // string	optional url for button “return to the shop” on payment status page

		$result = $this->novapay_requst('/session', $data, $debug);
		if(!empty($result->id))
			return $result->id; // novapay session id
		return false;
	}

	public function create_novapay_payment($pay, $debug = false)
	{
		if(empty($pay))
			return false;

		$data = [];
		$data['merchant_id'] = $_SESSION['option']->merchant_id;
		$data['session_id'] = $pay->novapay_id;
		$data['external_id'] = $pay->id;
		$data['amount'] = $pay->amount;
		$data['products'] = $pay->products;
		switch ($_SESSION['option']->mode) {
			case 'payment':
				$data['use_hold'] = false;
				break;
			
			case 'complete-hold':
				$data['use_hold'] = true;
				break;
			
			case 'confirm-delivery-hold':
				$data['use_hold'] = true;
				$data['delivery'] = $pay->delivery;
				break;
		}
		$result = $this->novapay_requst('/payment', $data, $debug);
		if (!empty($result->url))
			return $result;
		else
		{
			echo "<pre>";
			print_r($result);
		}
		return false;
	}

	public function complete_hold($pay, $debug = NULL)
	{
		$data = [];
		$data['merchant_id'] = $_SESSION['option']->merchant_id;
		$data['session_id'] = $pay->novapay_id;
		// $data['amount'] = $pay->amount;
		return $this->novapay_requst('/complete-hold', $data, $debug);
	}

	public function confirm_delivery_hold($pay, $debug = false)
	{
		$data = [];
		$data['merchant_id'] = $_SESSION['option']->merchant_id;
		$data['session_id'] = $pay->novapay_id;
		$result = $this->novapay_requst('/confirm-delivery-hold', $data, $debug);
		if(!empty($result->express_waybill))
		{
			$data = array();
			$pay->details = $data['details'] = $pay->details .". ТТН: <strong>{$result->express_waybill}</strong>";
			$pay->date_edit = $data['date_edit'] = time();
			$data['signature'] = $this->signature($pay);
			$this->db->updateRow($_SESSION['service']->table, $data, $pay->id);

			$pay->express_waybill = $result->express_waybill;
			$pay->comment = "ТТН Нової пошти: <strong>{$result->express_waybill}</strong>";
			if(isset($_SESSION['option']->successPayStatusToCart) && $_SESSION['option']->successPayStatusToCart > 0)
				$pay->cart_status = $_SESSION['option']->successPayStatusToCart;
			return $pay;
		}
		return false;
	}

	public function void($pay, $debug = false)
	{
		$data = [];
		$data['merchant_id'] = $_SESSION['option']->merchant_id;
		$data['session_id'] = $pay->novapay_id;
		return $this->novapay_requst('/void', $data, $debug);
	}

	public function get_status($pay, $debug = false)
	{
		$data = [];
		$data['merchant_id'] = $_SESSION['option']->merchant_id;
		$data['session_id'] = $pay->novapay_id;
		$result = $this->novapay_requst('/get-status', $data, $debug);
		if(!empty($result->status))
		{
			if($pay->status != $result->status)
			{
				$data = array();
				$pay->status = $data['status'] = $result->status;
				$pay->date_edit = $data['date_edit'] = time();
				$data['signature'] = $this->signature($pay);
				$this->db->updateRow($_SESSION['service']->table, $data, $pay->id);
			}
			return $pay;
		}
		return false;
	}

	public function novapay_requst($uri, $data, $debug = false)
	{
		$data_string = json_encode($data);

		$url = $this->url_pay;
		if($_SESSION['option']->testPay)
		{
			$url = $this->url_test;
			$data['merchant_id'] = 1;
		}
		$url .= $uri;

		$CURLOPT_HTTPHEADER = array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($data_string),
			'x-sign: ' . $this->get_x_sign($data_string)
		);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER);

		$result = curl_exec($ch);
		if(!empty($result) && !$debug)
			return json_decode($result);
		else if($debug === NULL)
			return true;
		else
		{
			echo "<pre>";
			echo $url."<br>";
			print_r($data);
			echo "<br>";
			echo "CURLOPT_HTTPHEADER:<br>";
			print_r($CURLOPT_HTTPHEADER);
			echo "<hr>result:<br>";
			if(empty($result))
				var_dump($result);
			else
				print_r(json_decode($result));
			exit;
		}
	}

	public function get_x_sign($data='')
	{
		$path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP.'private.pem';
		if($_SESSION['option']->testPay)
		{
			$path = APP_PATH.'services'.DIRSEP.'novapay'.DIRSEP.'keys'.DIRSEP.'test_merchant_private.pem';
			$_SESSION['option']->privatePassphrase = '';
		}
		if (file_exists($path))
		{
            try {
                $binary_signature = "";
				$private_key = openssl_pkey_get_private (file_get_contents($path), $_SESSION['option']->privatePassphrase);
                openssl_sign($data, $binary_signature, $private_key, OPENSSL_ALGO_SHA1);

                return base64_encode($binary_signature);
            } catch (Exception $E) {
                echo $E->getMessage();
                exit;
            }
        }
        return false;
	}

	public function latterENtoUA($text)
	{
        $en = array('A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'F', 'f', 'G', 'g', 'H', 'h', 'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'Q', 'q', 'R', 'r', 'S', 's', 'T', 't', 'U', 'u', 'V', 'v', 'W', 'w', 'X', 'x', 'Y', 'y', 'Z', 'z');
        $ua = array('А', 'а', 'Б', 'б', 'С', 'с', 'Д', 'д', 'Е', 'е', 'Ф', 'ф', 'Г', 'г', 'Г', 'г', 'І', 'і', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'К', 'к', 'Р', 'р', 'С', 'с', 'Т', 'т', 'У', 'у', 'В', 'в', 'В', 'в', 'Кз', 'кз', 'Йо', 'йо', 'З', 'з');
        for ($i = 0; $i < count($en); $i++) {
            $text = mb_ereg_replace($en[$i], $ua[$i], $text);
        }
        return $this->mb_ucfirst($text);
    }

    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
      $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
      $str_end = "";
      if ($lower_str_end) {
        $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
      }
      else {
        $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
      }
      $str = $first_letter . $str_end;
      return $str;
    }

}

?>