<?php

/*

 	Service "NovaPoshta.ua 1.2"
	for WhiteLion 1.0

*/

class novaposhta extends Controller {

    function _remap($method, $data = array())
    {
        if (method_exists($this, $method)) {
        	if(empty($data)) $data = null;
            return $this->$method($data);
        } else {
        	$this->index($method);
        }
    }

    public function index($uri)
    {
        $this->load->page_404(false);
    }

    public function getCities()
    {
        $with_warehouses = $this->data->uri(2) == 'warehouse' ? true : false;
        $cities = [];
        if($city = $this->data->get('term')) {
            if($_SESSION['option']->api_key)
                if($data = $this->get_by_api('Address', 'searchSettlements', ['CityName' => $city, 'Limit' => 15]))
                    if($data->data[0]->TotalCount)
                        foreach ($data->data[0]->Addresses as $address) {
                            if($with_warehouses && $address->Warehouses > 0 || !$with_warehouses)
                            {
                                $city = new stdClass();
                                $city->id = $address->DeliveryCity ?? $address->Ref;
                                $city->value = $address->Present ?? $address->SettlementTypeCode .' '.$address->MainDescription;
                                $cities[] = $city;
                            }
                        }
        }
        $this->load->json($cities);
    }

    public function getWarehouses($CityRef = false)
    {
        $warehouses = [];
        $city = $CityRef;
        if(!$city)
            $city = $this->data->post('city');
        if($city) {
            if($_SESSION['option']->api_key)
                if($data = $this->get_by_api('AddressGeneral', 'getWarehouses', ['CityRef' => $city]))
                    if($data->success && !empty($data->data))
                        foreach ($data->data as $np) {
                            if($_SESSION['option']->minTotalMaxWeightAllowed >= $np->TotalMaxWeightAllowed && $np->TotalMaxWeightAllowed > 0)
                                continue;
                            if($_SESSION['option']->minTotalMaxWeightAllowed >= $np->PlaceMaxWeightAllowed && $np->PlaceMaxWeightAllowed > 0)
                                continue;
                            $warehouse = new stdClass();
                            $warehouse->id = $np->Ref;
                            $warehouse->name = ($_SESSION['language'] == 'ru') ? $np->DescriptionRu : $np->Description;
                            $warehouse->info = $warehouse->title = '';
                            $days = ['Monday' => 'Пн', 'Tuesday' => 'Вт', 'Wednesday' => 'Ср', 'Thursday' => 'Чт', 'Friday' => 'Пт', 'Saturday' => 'Сб', 'Sunday' => 'Нд'];
                            if($_SESSION['language'] == 'ru') $days['Sunday'] = 'Вс';
                            $schedule = [$this->text('Графік роботи:')];
                            foreach ($np->Schedule as $day => $value) {
                                $value = str_replace('-', ' - ', $value);
                                $schedule[] = $days[$day].' <strong>'.$value.'</strong>';
                            }
                            $div = '<div class="pull-right">'.$this->text('Вантажопідйомність').' <strong>';
                            if($np->TotalMaxWeightAllowed)
                                $div .= $this->text('до').' '.$np->TotalMaxWeightAllowed.' кг';
                            else if($np->PlaceMaxWeightAllowed)
                                $div .= $this->text('до').' '.$np->PlaceMaxWeightAllowed.' кг';
                            else $div .= $this->text('без обмежень');
                            $div .= '</strong><br><br>Тел: '.$this->data->formatPhone($np->Phone, 'strong').'</div>';
                            $warehouse->info = $div. '<div style="width:35%">'.implode('<br>', $schedule).'</div><div class="clearfix"></div>';
                            $warehouse->title = strip_tags( implode('; ', $schedule) );
                            $warehouses[] = $warehouse;
                        }
        }
        if($CityRef)
            return $warehouses;
        $this->load->json($warehouses);
    }

    public function getAddresses()
    {
        $addresses = [];
        if($city = $this->data->uri(2))
            if($_SESSION['option']->api_key)
                if($address = $this->data->get('term'))
                    if($data = $this->get_by_api('Address', 'getStreet', ['CityRef' => $city, 'FindByString' => $address]))
                        if($data->success && !empty($data->data))
                            foreach ($data->data as $np) {
                                $address = new stdClass();
                                $address->id = $np->Ref;
                                $address->value = $np->StreetsType .' '. $np->Description;
                                $addresses[] = $address;
                            }
        $this->load->json($addresses);
    }

    public function __get_Search($content)
    {
    	return false;
    }

    public function __get_info($info)
    {
        $text = '';
        if($info['method'] == 'warehouse')
        {
            $text = $this->text('На відділення').'<br>';
            if(!empty($info['city']))
                $text .= $info['city'].'<br>';
            if(!empty($info['department']))
                $text .= $info['department'];
        }
        else
        {
            $text = $this->text("Кур'єром").'<br>';
            if(!empty($info['city']))
                $text .= $info['city'].'<br>';
            if(!empty($info['address_street']))
                $text .= $info['address_street'].' '.$info['address_house'];
        }
        // if(!empty($info['recipientName']))
        // {
        //     $text .= "<p>{$this->text('Отримувач')}: <strong>{$info['recipientName']}";
        //     if(!empty($info['recipientPhone']))
        //         $text .= ", {$info['recipientPhone']}</strong>";
        //     $text .= "</strong></p>";
        // }
        return $text;
    }

    public function __get_Shipping_to_cart($userShipping)
    {
        $this->load->view('__cart_view', array('userShipping' => $userShipping));
        return true;
    }

    public function __set_Shipping_from_cart()
    {
        $info = array('text' => '');
        $info['info'] = array('method' => '', 'city' => '', 'department' => '');
        $info['info']['method'] = $this->data->post('nova-poshta-method');
        $info['text'] = $info['info']['method'] == 'warehouse' ? $this->text('На відділення') : $this->text("Кур'єром");
        $info['text'] .= '<br>';
        if($city = $this->data->post('novaposhta-city'))
        {
            $info['info']['city'] = $city;
            $info['info']['city_ref'] = $this->data->post('nova-poshta-city-ref');
            $info['text'] .= $city.'<br>';
        }
        if($info['info']['method'] == 'warehouse')
        {
            if($department = $this->data->post('nova-poshta-warehouse'))
            {
                $info['info']['department'] = $department;
                $info['info']['warehouse_ref'] = $this->data->post('nova-poshta-warehouse-ref');
                $info['text'] .= $department;
            }
        }
        else
        {
            $info['info']['address_street'] = $this->data->post('novaposhta-address-street');
            $info['info']['address_house'] = $this->data->post('novaposhta-address-house');
            $info['text'] .= $info['info']['address_street'].' '.$info['info']['address_house'];
        }
        
        return $info;
    }

    private function get_by_api($modelName, $calledMethod, $data = [])
    {
        if($_SESSION['option']->api_key)
        {
            $POSTFIELDS = ['apiKey' => $_SESSION['option']->api_key, 'modelName' => $modelName, 'calledMethod' => $calledMethod, 'methodProperties' => $data];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://api.novaposhta.ua/v2.0/json/",
                CURLOPT_RETURNTRANSFER => True,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($POSTFIELDS),
                CURLOPT_HTTPHEADER => array("content-type: application/json")
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err)
                echo "cURL Error #:" . $err;
            else {
                $response = json_decode($response);
                if($response->success)
                    return $response;
            }
        }
        return false;
    }

}

?>