<?php 

class import_model
{
	// id виробників, чиї товари попадають у групу оригінали
	private $originals = array(1, 6);

	private $manufacturer = false;
	private $manufacturers_names = false;
	private $markUp = false;
	public $inserted = 0;
	public $insertedStorage = 0;
	public $updated = 0;
	public $deleted = 0;
	public $errors = array();
	public $message = '';
	public $min_price_UAH = 400; // 0-ignore
	public $min_price_USD = 0; // 0-ignore // вираховується на основі min_price_UAH та курсу

	public function show($spreadsheet, $limit = 50)
	{
		// echo('<meta charset="utf-8"><pre>');
		echo('<meta charset="utf-8">');
		$i = 0;
		if(!empty($spreadsheet))
			foreach ($spreadsheet as $Key => $Row)
			{
				echo $Key.': ';
				if ($Row)
					print_r($Row);
				else
					var_dump($Row);
				$i++;
				if($limit > 0 && $i > $limit)
					exit;
			}
		exit;
	}

	/* Configs:
		setManufacturer => id виробника для всіх товарів
		setCount => примусова кількість
	*/

	public function autotrend($spreadsheet, &$cols, &$rows)
	{
		$this->message = 'Оброблення колонки ціна';

		foreach ($spreadsheet as $key => $row)
		{
			$good = 0;
			foreach ($row as $i => $name) {
				if(isset($rows[$i]) && $rows[$i] == $name)
					$good++;
			}
			if($good == count($rows))
			{
				$cols->start = $key + 1;
				break;
			}
			if($key > 300)
				return false;
		}
		if($cols->start > 0)
		{
			$products = array();
			$i = 0;
			$view_before_import = false;
			$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
			$request = explode('/', $request);
			if(in_array('view_before_import', $request))
				$view_before_import = true;

			foreach ($spreadsheet as $key => $row)
			{
				if($key >= $cols->start && $row[$cols->article] != '')
				{
					$price = explode(' ', $row[$cols->price]);
					// if(!is_numeric($row[$cols->price]))
					// $row[$cols->price] = str_replace(',', '', $row[$cols->price]);
					$row[$cols->price] = $price[0];
				}
				$products[] = $row;
				$i++;

				if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
					break;
				elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
					break;
            }

            $cols->start = 0;
            return $products;
        }
	}

	public function master_servis($spreadsheet, &$cols, &$rows)
	{
		$this->message = 'Ручне розпізнавання розряду ціни більше 999 грн';

		foreach ($spreadsheet as $key => $row)
		{
			$good = 0;
			foreach ($row as $i => $name) {
				if(isset($rows[$i]) && $rows[$i] == $name)
					$good++;
			}
			if($good == count($rows))
			{
				$cols->start = $key + 1;
				break;
			}
			if($key > 300)
				return false;
		}
		if($cols->start > 0)
		{
			$products = array();
			$i = 0;
			$view_before_import = false;
			$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
			$request = explode('/', $request);
			if(in_array('view_before_import', $request))
				$view_before_import = true;

			foreach ($spreadsheet as $key => $row)
			{
				if($key >= $cols->start && $row[$cols->article] != '')
				{
					// if(!is_numeric($row[$cols->price]))
					$row[$cols->price] = str_replace(',', '', $row[$cols->price]);
				}
				$products[] = $row;
				$i++;

				if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
					break;
				elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
					break;
            }

            $cols->start = 0;
            return $products;
        }
	}

	public function asianparts($spreadsheet, &$cols, &$rows)
	{
		$this->message = 'Задано примусово виробник MOBIS. Ціна відносно вхідної -48%';

		foreach ($spreadsheet as $key => $row)
		{
			$good = 0;
			foreach ($row as $i => $name) {
				if(isset($rows[$i]) && $rows[$i] == $name)
					$good++;
			}
			if($good == count($rows))
			{
				$cols->start = $key + 1;
				break;
			}
			if($key > 300)
				return false;
		}
		if($cols->start > 0)
		{
			$products = array();
			$i = 0;
			$view_before_import = false;
			$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
			$request = explode('/', $request);
			if(in_array('view_before_import', $request))
				$view_before_import = true;

			foreach ($spreadsheet as $key => $row)
			{
				if($key >= $cols->start && $row[$cols->article] != '')
				{
					if(!is_numeric($row[$cols->price]))
						$row[$cols->price] = str_replace(',', '.', $row[$cols->price]);
					$row[$cols->price] *= 0.52;
				}
				$products[] = $row;
				$i++;

				if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
					break;
				elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
					break;
            }

            $cols->start = 0;
            return $products;
        }

		return $spreadsheet;
	}

	public function planetauto($spreadsheet, &$cols, &$rows)
	{
		$rows = array(
			    0 => 'Виробник',
			    1 => 'Номер',
			    2 => 'Назва',
			    3 => 'Кількість',
			    4 => 'Ціна',
			    5 => 'Доставка'
			    );
		$cols->article = 1; // артикул
		$cols->analogs = -1; // аналоги (менше нуля ігноряться)
		$cols->analogs_delimiter = ''; // аналоги розділювач
		$cols->manufacturer = 0; // виробник
		$cols->name = 2;
		$cols->count = 3;
		$cols->price = 4;
		$this->min_price_UAH = 0;

		unset($spreadsheet);
		if(isset($_POST['file']))
		{
			$ext = explode('.', $this->data->post('file'));
			if(end($ext) != 'xlsx')
				return false;
		}
		elseif(!empty($_FILES['price']['name']))
		{
			$ext = explode('.', $_FILES['price']['name']);
			if(end($ext) != 'xlsx')
				return false;
		}

		$view_before_import = false;
		$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
		$request = explode('/', $request);
		if(in_array('view_before_import', $request))
			$view_before_import = true;

		if($view_before_import)
			$Filepath = 'upload/planetauto_prepare.xlsx';
		else
			$Filepath = 'upload/planetauto.xlsx';
		
		$Zip = new ZipArchive;
		$Status = $Zip -> open($Filepath);

		if ($Status !== true)
		{
			throw new Exception('SpreadsheetReader_XLSX: File not readable ('.$Filepath.') (Error '.$Status.')');
			return false;
		}

		// Getting the general workbook information
		if ($Zip -> locateName('xl/sharedStrings.xml') !== false && $Zip -> locateName('xl/worksheets/sheet1.xml') !== false)
		{
			$xml = simplexml_load_string ($Zip -> getFromName('xl/sharedStrings.xml'));

			$sharedStringsArr = array();
		    foreach ($xml->children() as $item) {
		        $sharedStringsArr[] = (string)$item->t;
		    }

		    $xml = simplexml_load_string ($Zip -> getFromName('xl/worksheets/sheet1.xml'));

		    unset($Zip);

			$_SESSION['import']['all_products'] = count($xml->sheetData->row) - 2;

            //по каждой строке
            $key = 0;
            $products = array();
            foreach ($xml->sheetData->row as $item) {
                $row = array();
                //по каждой ячейке строки
                $cell = 0;
                foreach ($item as $child) {
                    $attr = $child->attributes();
                    $value = isset($child->v) ? (string)$child->v : false;
                    $row[$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                    $row[$cell] = trim($row[$cell]);
                    $cell++;
                }

				$good = 0;
				foreach ($row as $i => $name) {
					if(isset($rows[$i]) && $rows[$i] == trim($name))
						$good++;
				}
				if($good == count($rows))
				{
					$cols->start = $key + 1;
					break;
				}
				$key++;
				if($key > 300)
					return false;
			}

			if($cols->start > 0)
			{
				if($this->min_price_UAH > 0)
				{
					$currency = $this->db->getAllDataById('s_currency', 'USD', 'code');
					$this->min_price_USD = $this->min_price_UAH / $currency->currency;
					$this->min_price_UAH = 0;
				}

				$i = $key = 0;
				foreach ($xml->sheetData->row as $item) {
					if($key >= $cols->start)
					{
						$row = array();
		                //по каждой ячейке строки
		                $cell = 0;
		                foreach ($item as $child) {
		                    $attr = $child->attributes();
		                    $value = isset($child->v) ? (string)$child->v : false;
		                    $row[$cell] = (isset($attr['t']) && isset($sharedStringsArr[$value])) ? $sharedStringsArr[$value] : $value;
		                    $row[$cell] = trim($row[$cell]);
		                    $cell++;
		                }

		                if(isset($row[$cols->article]) && isset($row[$cols->price]) && $row[$cols->article] != '' && $row[$cols->price] != '')
		                {
							if(!is_numeric($row[$cols->price]))
								$row[$cols->price] = str_replace(',', '.', $row[$cols->price]);
							$row[$cols->price] *= 1.07;

							if($row[$cols->price] >= $this->min_price_USD)
			                {
			            		$products[] = $row;
			            		$i++;
			                }

			                if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
								break;
							elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
								break;
						}
					}
					$key++;
				}
            }

            unset($xml);
            unset($sharedStringsArr);
            $this->min_price_UAH = 0;
            $cols->start = 0;
            return $products;
        }
		return false;
	}

	public function deniskievkia($spreadsheet, &$cols, &$rows)
	{
		$cols->setManufacturer = 1;
		$this->message = 'Задано примусово виробник MOBIS. Ціна відносно вхідної -20%';

		foreach ($spreadsheet as $key => $row)
		{
			$good = 0;
			foreach ($row as $i => $name) {
				if(isset($rows[$i]) && $rows[$i] == $name)
					$good++;
			}
			if($good == count($rows))
			{
				$cols->start = $key + 1;
				break;
			}
			if($key > 300)
				return false;
		}
		if($cols->start > 0)
		{
			$products = array();
			$i = 0;
			$view_before_import = false;
			$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
			$request = explode('/', $request);
			if(in_array('view_before_import', $request))
				$view_before_import = true;

			foreach ($spreadsheet as $key => $row)
			{
				if($key >= $cols->start && $row[$cols->article] != '')
				{
					if(!is_numeric($row[$cols->price]))
						$row[$cols->price] = str_replace(',', '.', $row[$cols->price]);
					$row[$cols->price] *= 0.8;
				}
				$products[] = $row;
				$i++;

				if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
					break;
				elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
					break;
            }

            $cols->start = 0;
            return $products;
        }

		return $spreadsheet;
	}

	public function deniskievhyndai($spreadsheet, &$cols, &$rows)
	{
		$this->message = 'Задано примусово виробник MOBIS. Ціна відносно вхідної -20%. Перевіряється тільки перша вкладка.';
		unset($spreadsheet);

		$priceRows = array();
		foreach ($rows as $key => $value) {
			$key--;
			if($key >= 0)
				$priceRows[$key] = trim($value);
		}
		if(end($priceRows) == '')
			array_pop($priceRows);
		$cols->setManufacturer = 1;
		
		if(isset($_POST['file']))
		{
			$ext = explode('.', $this->data->post('file'));
			if(end($ext) != 'xlsx')
				return false;
		}
		elseif(!empty($_FILES['price']['name']))
		{
			$ext = explode('.', $_FILES['price']['name']);
			if(end($ext) != 'xlsx')
				return false;
		}

		$view_before_import = false;
		$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
		$request = explode('/', $request);
		if(in_array('view_before_import', $request))
			$view_before_import = true;

		if($view_before_import)
			$Filepath = 'upload/deniskievhyndai_prepare.xlsx';
		else
			$Filepath = 'upload/deniskievhyndai.xlsx';
		
		$Zip = new ZipArchive;
		$Status = $Zip -> open($Filepath);

		if ($Status !== true)
		{
			throw new Exception('SpreadsheetReader_XLSX: File not readable ('.$Filepath.') (Error '.$Status.')');
			return false;
		}

		// Getting the general workbook information
		if ($Zip -> locateName('xl/sharedStrings.xml') !== false && $Zip -> locateName('xl/worksheets/sheet1.xml') !== false)
		{
			$xml = simplexml_load_string ($Zip -> getFromName('xl/sharedStrings.xml'));

			$sharedStringsArr = array();
		    foreach ($xml->children() as $item) {
		        $sharedStringsArr[] = (string)$item->t;
		    }

		    $xml = simplexml_load_string ($Zip -> getFromName('xl/worksheets/sheet1.xml'));

		    unset($Zip);

			$_SESSION['import']['all_products'] = count($xml->sheetData->row) - 2;

            //по каждой строке
            $key = 0;
            $products = array();
            foreach ($xml->sheetData->row as $item) {
                $row = array();
                //по каждой ячейке строки
                $cell = 0;
                foreach ($item as $child) {
                    $attr = $child->attributes();
                    $value = isset($child->v) ? (string)$child->v : false;
                    $row[$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                    $row[$cell] = trim($row[$cell]);
                    $cell++;
                }

				$good = 0;
				foreach ($row as $i => $name) {
					if(isset($priceRows[$i]) && $priceRows[$i] == trim($name))
						$good++;
				}
				if($good == count($priceRows))
				{
					
					$cols->start = $key + 1;
					break;
				}
				$key++;
				if($key > 300)
					return false;
			}

			if($cols->start > 0)
			{
				$i = $key = 0;
				foreach ($xml->sheetData->row as $item) {
					if($key >= $cols->start)
					{
						$row = array();
		                //по каждой ячейке строки
		                $cell = 0;
		                foreach ($item as $child) {
		                    $attr = $child->attributes();
		                    $value = isset($child->v) ? (string)$child->v : false;
		                    $row[$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
		                    $row[$cell] = trim($row[$cell]);
		                    $cell++;
		                }

		                if($row[$cols->article] != '' && $row[$cols->price] != '')
		                {
							// $price = explode(' ', $row[$cols->price]);
							// if(isset($price[1]) && $price[1] == 'грн')
							// {
							// 	$row[$cols->price] = $price[0];

								if(!is_numeric($row[$cols->price]))
									$row[$cols->price] = str_replace(',', '.', $row[$cols->price]);
								$row[$cols->price] *= 0.8;

								if($row[$cols->price] > $this->min_price_UAH)
				                {
				            		$products[] = $row;
				            		$i++;
				                }
							// }

			                if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
								break;
							elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
								break;
						}
					}
					$key++;
				}
            }

            unset($xml);
            unset($sharedStringsArr);
            $this->min_price_UAH = 0;
            $cols->start = 0;
            return $products;
        }
		return false;
	}

	public function kiev_hyundai($spreadsheet, &$cols, &$rows)
	{
		$cols->setManufacturer = 1;
		$this->message = 'Задано примусово виробник MOBIS';

		foreach ($spreadsheet as $key => $row)
		{
			$good = 0;
			foreach ($row as $i => $name) {
				if(isset($rows[$i]) && $rows[$i] == $name)
					$good++;
			}
			if($good == count($rows))
			{
				$cols->start = $key + 1;
				break;
			}
			if($key > 300)
				return false;
		}

		return $spreadsheet;
	}

	public function kiev_kia($spreadsheet, &$cols, &$rows)
	{
		unset($spreadsheet);
		$cols->setManufacturer = 1;
		$this->message = 'Задано примусово виробник MOBIS. перевіряється тільки перша вкладка.';

		if(isset($_POST['file']))
		{
			$ext = explode('.', $this->data->post('file'));
			if(end($ext) != 'xlsx')
				return false;
		}
		elseif(!empty($_FILES['price']['name']))
		{
			$ext = explode('.', $_FILES['price']['name']);
			if(end($ext) != 'xlsx')
				return false;
		}

		$view_before_import = false;
		$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
		$request = explode('/', $request);
		if(in_array('view_before_import', $request))
			$view_before_import = true;

		if($view_before_import)
			$Filepath = 'upload/kiev_kia_prepare.xlsx';
		else
			$Filepath = 'upload/kiev_kia.xlsx';
		
		$Zip = new ZipArchive;
		$Status = $Zip -> open($Filepath);

		if ($Status !== true)
		{
			throw new Exception('SpreadsheetReader_XLSX: File not readable ('.$Filepath.') (Error '.$Status.')');
			return false;
		}

		$products = array();

		// Getting the general workbook information
		if ($Zip -> locateName('xl/sharedStrings.xml') !== false && $Zip -> locateName('xl/worksheets/sheet1.xml') !== false)
		{
			$xml = simplexml_load_string ($Zip -> getFromName('xl/sharedStrings.xml'));

			$sharedStringsArr = array();
		    foreach ($xml->children() as $item) {
		        $sharedStringsArr[] = (string)$item->t;
		    }

		    $xml = simplexml_load_string ($Zip -> getFromName('xl/worksheets/sheet1.xml'));

		    unset($Zip);

			$_SESSION['import']['all_products'] = count($xml->sheetData->row) - 2;

            //по каждой строке
            $i = 0;
            foreach ($xml->sheetData->row as $item) {
                $row = array();
                //по каждой ячейке строки
                $cell = 0;
                foreach ($item as $child) {
                    $attr = $child->attributes();
                    $value = isset($child->v) ? (string)$child->v : false;
                    $row[$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                    $row[$cell] = trim($row[$cell]);
                    $cell++;
                }

                if(isset($row[$cols->price]))
                {
	                if(is_numeric($row[$cols->price]) && $row[$cols->price] > $this->min_price_UAH)
	                {
	            		$products[] = $row;
	            		$i++;
	                }
	            }

                if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
					break;
				elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
					break;
            }

            unset($xml);
            unset($sharedStringsArr);
            $this->min_price_UAH = 0;
            $cols->start = 0;
        }

		return $products;
	}

	public function omega($spreadsheet, &$cols, &$rows)
	{
		$this->message = 'Прайс має 2 вкладки. Товари у 2-й вкладці. Наявні 3 колонки наявності (кількості). Відбувається об\'єднання наявності в одну колонку. Ціна автоматично переводиться в USD на основі ExchangeRate з першої вкладки';

		if(isset($_POST['file']))
		{
			$ext = explode('.', $this->data->post('file'));
			if(end($ext) != 'xlsx')
				return false;
		}
		elseif(!empty($_FILES['price']['name']))
		{
			$ext = explode('.', $_FILES['price']['name']);
			if(end($ext) != 'xlsx')
				return false;
		}
		
		if($spreadsheet)
		{
			$products = array();
			$currency_to_1 = 0;
			foreach ($spreadsheet as $key => $row)
			{
				$row[0] = trim($row[0]);
				if(count($row) == 2 && $row[0] == 'USD')
				{
					$row[1] = str_replace(',', '.', $row[1]);
					$currency_to_1 = (float) $row[1];
				}
			}

			if($currency_to_1 == 0)
				return false;

			unset($spreadsheet);

			$view_before_import = false;
			$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
			$request = explode('/', $request);
			if(in_array('view_before_import', $request))
				$view_before_import = true;

			if($view_before_import)
				$Filepath = 'upload/omega_prepare.xlsx';
			else
				$Filepath = 'upload/omega.xlsx';
			
			$Zip = new ZipArchive;
			$Status = $Zip -> open($Filepath);

			if ($Status !== true)
			{
				throw new Exception('SpreadsheetReader_XLSX: File not readable ('.$Filepath.') (Error '.$Status.')');
				return false;
			}

			// Getting the general workbook information
			if ($Zip -> locateName('xl/sharedStrings.xml') !== false && $Zip -> locateName('xl/worksheets/sheet2.xml') !== false)
			{
				$goodManufacturers = array();
				if($manufacturers = $this->db->getAllData('s_shopparts_manufactures'))
				{
					foreach ($manufacturers as $m) {
						if($m->name != '')
							$goodManufacturers[] = $m->name;
					}
				}

				$xml = simplexml_load_string ($Zip -> getFromName('xl/sharedStrings.xml'));

				$sharedStringsArr = array();
			    foreach ($xml->children() as $item) {
			        $sharedStringsArr[] = (string)$item->t;
			    }

			    $xml = simplexml_load_string ($Zip -> getFromName('xl/worksheets/sheet2.xml'));

			    unset($Zip);

				$_SESSION['import']['all_products'] = count($xml->sheetData->row) - 2;

	            //по каждой строке
	            $i = 0;
	            foreach ($xml->sheetData->row as $item) {
	                $row = array();
	                //по каждой ячейке строки
	                $cell = 0;
	                foreach ($item as $child) {
	                    $attr = $child->attributes();
	                    $value = isset($child->v) ? (string)$child->v : false;
	                    $row[$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
	                    $row[$cell] = trim($row[$cell]);
	                    $cell++;
	                }

	                if($i == 0)
					{
						if($row[0] == 'Бренд' &&
							$row[1] == 'Карточка' &&
							$row[2] == 'Код по каталогу' &&
							$row[3] == 'Наименование' &&
							$row[6] == 'Ваша цена' &&
							$row[7] == 'Львов  (доставка сегодня)' &&
							$row[8] == 'Склад Киев (доставка — завтра)' &&
							$row[9] == 'Склад Харьков (доставка — послезавтра)'	)
						{
							$cols->article = 2; // артикул
							$cols->analogs = -1; // аналоги (менше нуля ігноряться)
							$cols->analogs_delimiter = ''; // аналоги розділювач
							$cols->manufacturer = 0; // виробник
							$cols->name = 3;
							$cols->count = 7;
							$cols->price = 6;
							$cols->start = 0;
							$i = 1;
							continue;
						}
						return false;
					}

	                $manufacturer = mb_strtoupper($row[0]);
	                if($manufacturer == '' || $manufacturer == '<>')
	                	continue;
	                $row[$cols->price] = str_replace(',', '.', $row[$cols->price]);

	                if(is_numeric($row[$cols->price]) && in_array($manufacturer, $goodManufacturers))
	                {
	                	$row[$cols->price] *= $currency_to_1;

						$count = 0;		
						$amount_cols = array(7, 8, 9);
						foreach ($amount_cols as $ac) {
							$amount = $row[$ac];
							if(is_numeric($amount))
								$count += $amount;
							elseif(is_string($amount) && ($amount[0] == '>' || $amount[0] == '≥'))
							{
								$amount = substr($amount, 1);
								if(is_numeric($amount))
									$count += $amount + 1;
							}
						}
						if($count > 0)
						{
							$row[$cols->count] = $count;
							unset($row[8], $row[9]);
	                		$products[] = $row;
	                		$i++;
						}
	                }

	                if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
						break;
					elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
						break;
	            }

	            unset($xml);
	            unset($sharedStringsArr);
	            $this->min_price_UAH = 0;
	        }
			return $products;
		}
		return false;
	}

	public function seul2($spreadsheet, &$cols, &$rows)
	{
		$this->message = 'Коректне оброблення валюти в комірці ціни';
		if($spreadsheet)
		{
			foreach ($spreadsheet as $key => $row)
			{
				$good = 0;
				foreach ($row as $i => $name) {
					if(isset($rows[$i]) && $rows[$i] == $name)
						$good++;
				}
				if($good == count($rows))
				{
					$cols->start = $key + 1;
					break;
				}
				if($key > 300)
					return false;
			}

			$products = array();

			if($this->min_price_UAH > 0)
			{
				$currency = $this->db->getAllDataById('s_currency', 'USD', 'code');
				$this->min_price_USD = $this->min_price_UAH / $currency->currency;
				$this->min_price_UAH = 0;
			}

			foreach ($spreadsheet as $key => $row)
			{
				if($key >= $cols->start && isset($row[$cols->price]) && $row[$cols->price] != '')
				{
					$price = explode(' ', $row[$cols->price]);
					if(count($price) == 2 && $price[1] == 'USD' && is_numeric($price[0]) && $price[0] > $this->min_price_USD)
					{
						$product = array();
						foreach ($row as $keyRow => $valueRow) {
							$product[$keyRow] = $valueRow;
						}
						$product[$cols->price] = $price[0];
						$products[$key] = $product;
					}
				}
			}
			return $products;
		}
		return $spreadsheet;
	}

	public function grandavto($spreadsheet, &$cols, &$rows)
	{
		unset($spreadsheet);
		$cols->setManufacturer = 1;
		$this->message = 'Власний парсер';

		if(isset($_POST['file']))
		{
			$ext = explode('.', $this->data->post('file'));
			if(end($ext) != 'xlsx')
				return false;
		}
		elseif(!empty($_FILES['price']['name']))
		{
			$ext = explode('.', $_FILES['price']['name']);
			if(end($ext) != 'xlsx')
				return false;
		}

		$view_before_import = false;
		$request = (empty($_GET['request'])) ? 'main' : $_GET['request'];
		$request = explode('/', $request);
		if(in_array('view_before_import', $request))
			$view_before_import = true;

		if($view_before_import)
			$Filepath = 'upload/grandavto_prepare.xlsx';
		else
			$Filepath = 'upload/grandavto.xlsx';
		
		$Zip = new ZipArchive;
		$Status = $Zip -> open($Filepath);

		if ($Status !== true)
		{
			throw new Exception('SpreadsheetReader_XLSX: File not readable ('.$Filepath.') (Error '.$Status.')');
			return false;
		}

		$products = array();

		// Getting the general workbook information
		if ($Zip -> locateName('xl/sharedStrings.xml') !== false && $Zip -> locateName('xl/worksheets/sheet1.xml') !== false)
		{
			$xml = simplexml_load_string ($Zip -> getFromName('xl/sharedStrings.xml'));

			$sharedStringsArr = array();
		    foreach ($xml->children() as $item) {
		        $sharedStringsArr[] = (string)$item->t;
		    }

		    $xml = simplexml_load_string ($Zip -> getFromName('xl/worksheets/sheet1.xml'));

		    unset($Zip);

			$_SESSION['import']['all_products'] = count($xml->sheetData->row) - 2;

            //по каждой строке
            $i = 0;
            foreach ($xml->sheetData->row as $item) {
                $row = array();
                //по каждой ячейке строки
                $cell = 0;
                foreach ($item as $child) {
                    $attr = $child->attributes();
                    $value = isset($child->v) ? (string)$child->v : false;
                    $row[$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                    $row[$cell] = trim($row[$cell]);
                    $cell++;
                }

                if(isset($row[$cols->price]))
                {
	                if(is_numeric($row[$cols->price]) && $row[$cols->price] > $this->min_price_UAH)
	                {
	            		$products[] = $row;
	            		$i++;
	                }
	            }

                if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i >= $_GET['showRows'])
					break;
				elseif($view_before_import && !isset($_GET['showRows']) && $i >= 200)
					break;
            }

            unset($xml);
            unset($sharedStringsArr);
            $this->min_price_UAH = 0;
            $cols->start = 0;
        }

		return $products;
	}

	public function checkRows(&$spreadsheet, $import = false)
	{
		if($storage = $this->db->getAllDataById($_SESSION['service']->table, $_SESSION['alias']->id))
		{
			$storage->updateRows = unserialize($storage->updateRows);
			$cols = unserialize($storage->updateCols);
			if(!empty($storage->updateRows) && !empty($cols) && !empty($spreadsheet))
			{
				$cols->start = -1;

				$function = $_SESSION['alias']->alias;
				if(method_exists($this, $function))
					$spreadsheet = $this->$function($spreadsheet, $cols, $storage->updateRows);
				else
				{
					foreach ($spreadsheet as $key => $row)
					{
						$good = 0;
						foreach ($row as $i => $name) {
							if(isset($storage->updateRows[$i]) && $storage->updateRows[$i] == $name)
								$good++;
						}
						if($good == count($storage->updateRows))
						{
							$cols->start = $key + 1;
							break;
						}
						if($key > 300)
							return false;
					}
				}

				if($cols->start >= 0)
				{
					if($import)
						return $this->import($spreadsheet, $cols);
					else
						return $cols;
				}
			}
		}
		return false;
	}

	private function import($spreadsheet, $cols)
	{
		$inStorage = array();
		$invoiceIs = array();
		$products_ids = null;
		$products_articles = null;

		$storage = array();
		$storage['storage'] = $_SESSION['alias']->id;
		$storage['file'] = $this->data->post('file');
		$storage['price_for_1'] = $this->data->post('currency_to_1');
		$storage['currency'] = $this->data->post('currency');
		$storage['manager'] = $_SESSION['user']->id;
		$storage['date'] = time();
		$this->db->insertRow('s_shopstorage_updates', $storage);
		$_STORAGE_UPDATE_ID = $this->db->getLastInsertedId();

		if($this->data->post('delete') == 1)
		{
			$this->db->select('s_shopstorage_products as s', 'id as invoice, product as id, price_in, price_out, amount', $_SESSION['alias']->id, 'storage');
			$this->db->join('s_shopparts_products', 'wl_alias, article, manufacturer, analogs', '#s.product');
			$products = $this->db->get('array');
			if($products) 
				foreach ($products as $product) 
					if($product->wl_alias == $this->data->post('shop'))
					{
						$product->amount = (int) $product->amount;
						$inStorage[$product->id] = clone $product;
						$product->article = (string) $product->article;
						$article = $product->article .'___'.$product->manufacturer;
						$products_articles[$article] = $product->id;
						$invoiceIs[$product->id] = 1;
					}
			unset($products);
		}

		if(empty($inStorage))
		{
			$this->db->select('s_shopparts_products', 'id, article, manufacturer, analogs', $this->data->post('shop'), 'wl_alias');
			$products = $this->db->get('array');
			if($products) 
				foreach ($products as $product) {
					$product->article = (string) $product->article;
					$article = $product->article .'___'.$product->manufacturer;
					if(array_key_exists($article, $products_articles))
					{
						$product_id = $products_articles[$article];
						$this->db->deleteRow('s_shopparts_products', $product->id);
						$this->db->deleteRow('s_shopparts_product_options', $product->id, 'product');
						$this->db->updateRow('s_cart_products', array('product' => $product_id), array('product' => $product->id));
						$this->db->updateRow('s_shopstorage_products', array('product' => $product_id), array('product' => $product->id));
						$this->db->updateRow('products_update_history', array('product' => $product_id), array('product' => $product->id));
						$this->db->updateRow('s_shopparts_search_history', array('product_id' => $product_id), array('product_id' => $product->id));
						$this->dublicats++;
					}
					else
					{
						$product->amount = 0;
						$products_ids[$product->id] = clone $product;
						$products_articles[$article] = $product->id;
					}
				}
			unset($products);
		}

		if($manufacturers = $this->db->getAllData('s_shopparts_manufactures'))
		{
			$this->manufacturer = $this->manufacturers_names = array();
			foreach ($manufacturers as $m) {
				$this->manufacturers_names[$m->id] = $m->name;
				if($m->main_id > 0)
					$this->manufacturer[$m->name] = $m->main_id;
				else
					$this->manufacturer[$m->name] = $m->id;
			}
		}

		if($this->min_price_UAH > 0)
		{
			$currency = $this->db->getAllDataById('s_currency', 'USD', 'code');
			$this->min_price_USD = $this->min_price_UAH / $currency->currency;
			$this->min_price_UAH = 0;
		}

		foreach ($spreadsheet as $key => $row)
		{
			$checkRowManufacturer = false;
			if(isset($cols->setManufacturer) && $cols->setManufacturer > 0)
				$checkRowManufacturer = true;
			elseif(isset($row[$cols->manufacturer]) && $row[$cols->manufacturer] != '')
				$checkRowManufacturer = true;

			if(!is_numeric($row[$cols->price]))
				$row[$cols->price] = str_replace(',', '.', $row[$cols->price]);

			if($key >= $cols->start && $row[$cols->article] != '' && $checkRowManufacturer && is_numeric($row[$cols->price]))
			{
				$product = false;
				$id = $amount = 0;
				$price_in = $this->getPriceIn($row[$cols->price]);

				if($price_in < $this->min_price_USD)
					continue;

				if(isset($cols->setManufacturer) && is_numeric($cols->setManufacturer) && $cols->setManufacturer > 0)
				{
					$article = $this->makeArticle($row[$cols->article]);
					$manufacturer = $cols->setManufacturer;
				}
				else
				{
					$article = $this->makeArticle($row[$cols->article], $row[$cols->manufacturer]);
					$manufacturer = $this->getManufacturer($row[$cols->manufacturer]);
				}

				if($cols->count >= 0)
				{
					$amount = $row[$cols->count];
					if(is_string($amount) && ($amount[0] == '>' || $amount[0] == '≥'))
					{
						$amount = substr($amount, 1);
						if(is_numeric($amount))
							$amount++;
						else
							continue;
					}
				}
				elseif(isset($cols->setCount) && $cols->setCount > 0)
					$amount = $cols->setCount;
				else
					continue;

				if($article == '')
					continue;

				$ArticleManufacturer = $article .'___'.$manufacturer;

				if(isset($products_articles[$ArticleManufacturer]))
				{
					$id = $products_articles[$ArticleManufacturer];

					if(isset($products_ids[$id]))
						$product = $products_ids[$id];
					elseif(isset($inStorage[$id]))
						$product = $inStorage[$id];
				}
				else
				{
					$where = array();
					$where['wl_alias'] = $this->data->post('shop');
					$where['article'] = $article;
					$where['manufacturer'] = $manufacturer;
					$this->db->select('s_shopparts_products as p', 'id, article, manufacturer, analogs', $where);
					if($product = $this->db->get())
					{
						if(is_object($product))
						{
							$id = $product->id;
							$product->amount = 0;
							$products_articles[$ArticleManufacturer] = $product->id;
							$products_ids[$id] = clone $product;
						}
						elseif(is_array($product))
						{
							if(is_object($product[0]))
							{
								$id = $product[0]->id;
								$product[0]->amount = 0;
								$products_articles[$ArticleManufacturer] = $product[0]->id;
								$products_ids[$id] = clone $product[0];

								for($i = 1; $i < count($product); $i++)
								{
									$this->db->deleteRow('s_shopparts_products', $product[$i]->id);
									$this->db->deleteRow('s_shopparts_product_options', $product[$i]->id, 'product');
									$this->db->updateRow('s_cart_products', array('product' => $id), array('product' => $product[$i]->id));
									$this->db->updateRow('s_shopstorage_products', array('product' => $id), array('product' => $product[$i]->id));
									$this->db->updateRow('products_update_history', array('product' => $id), array('product' => $product[$i]->id));
									$this->db->updateRow('s_shopparts_search_history', array('product_id' => $id), array('product_id' => $product[$i]->id));
									$this->dublicats++;
								}
							}
							else
							{
								echo "Import storages error: <br>";
								var_dump($product);
								exit;
							}
						}
						else
						{
							echo "Import storages error: <br>";
							var_dump($product);
							exit;
						}
					}
				}

				if($product && $row[$cols->price] != '' && $price_in > 0)
				{
					if(isset($inStorage[$product->id]))
					{
						if($this->data->post('checkPrice') == -1)
						{
							if($price_in != $inStorage[$product->id]->price_in || $amount != $inStorage[$product->id]->amount)
							{
								$price = array();
								$price['price_in'] = $price_in;
								$price['price_out'] = 0;
								// $price['price_out'] = $this->getPriceOut($price_in);
								$price['amount'] = $amount;
								$price['manager_edit'] = $_SESSION['user']->id;
								$price['date_edit'] = time();
								$this->db->updateRow('s_shopstorage_products', $price, $inStorage[$product->id]->invoice);
								$this->updated++;
								$invoiceIs[$product->id] = 1;

								$history = array();
								$history['update'] = $_STORAGE_UPDATE_ID;
								$history['product'] = $product->id;
								$history['price_old'] = $inStorage[$product->id]->price_in;
								$history['price_new'] = $price_in;
								$history['amount_old'] = $inStorage[$product->id]->amount;
								$history['amount_new'] = $amount;
								$this->db->insertRow('products_update_history', $history);
							}
						}
						else
						{
							$price_out = unserialize($inStorage[$product->id]->price_out);
							$price_out = $price_out[$this->data->post('checkPrice')];
							if($price_in != $price_out || $amount != $inStorage[$product->id]->amount)
							{
								$price = array();
								$price['price_in'] = $price_out;
								$price['price_out'] = 0;
								// $price['price_out'] = $this->getPriceOut($price_in, $this->data->post('checkPrice'));
								$price['amount'] = $amount;
								$price['manager_edit'] = $_SESSION['user']->id;
								$price['date_edit'] = time();
								$this->db->updateRow('s_shopstorage_products', $price, $inStorage[$product->id]->invoice);
								$this->updated++;
								$invoiceIs[$product->id] = 1;
							}
						}

						$products_ids[$id] = clone $inStorage[$product->id];
						unset($inStorage[$product->id]);
					}
					elseif(!isset($invoiceIs[$product->id]) && $price_in > 0 && $amount > 0)
					{
						$price = array();
						$price['storage'] = $_SESSION['alias']->id;
						$price['product'] = $product->id;
						$price['price_in'] = $price_in;
						$price['price_out'] = 0;
						// $price['price_out'] = $this->getPriceOut($price_in, $this->data->post('checkPrice'));
						$price['amount'] = $amount;
						$price['manager_add'] = $price['manager_edit'] = $_SESSION['user']->id;
						$price['date_add'] = $price['date_edit'] = time();
						$this->db->insertRow('s_shopstorage_products', $price);

						$this->insertedStorage++;
						$invoiceIs[$product->id] = 1;

						$history = array();
						$history['update'] = $_STORAGE_UPDATE_ID;
						$history['product'] = $product->id;
						$history['price_old'] = 0;
						$history['price_new'] = $price_in;
						$history['amount_old'] = 0;
						$history['amount_new'] = $amount;
						$this->db->insertRow('products_update_history', $history);
					}

					if($cols->analogs >= 0 && isset($row[$cols->analogs]) && $row[$cols->analogs] != '')
					{
						$update_analogs = false;
						$product->analogs = explode(',', $product->analogs);
						$analogs = explode($cols->analogs_delimiter, $row[$cols->analogs]);
						foreach ($analogs as $analog) {
							$analog = $this->makeArticle($analog);
							if(!in_array($analog, $product->analogs))
							{
								$product->analogs[] = $analog;
								$update_analogs = true;
							}
						}
						if($update_analogs)
						{
							$product->analogs = implode(',', $product->analogs);
							$this->db->updateRow('s_shopparts_products', array('analogs' => $product->analogs), $product->id);
						}
					}
				}
				elseif($this->data->post('insert') == 1 && !isset($invoiceIs[$id]) && $price_in > 0)
				{
					$manufacturer = 0;
					if(isset($cols->setManufacturer) && is_numeric($cols->setManufacturer) && $cols->setManufacturer > 0)
						$manufacturer = $cols->setManufacturer;
					else
						$manufacturer = $this->getManufacturer($row[$cols->manufacturer]);

					$where['wl_alias'] = $this->data->post('shop');
					$where['article'] = $article;
					$where['alias'] = $this->data->latterUAtoEN($this->manufacturers_names[$manufacturer].'-'.$article .'-'.trim($row[$cols->name]));
					$where['manufacturer'] = $manufacturer;
					$where['name'] = $row[$cols->name];
					$where['group'] = 0;
					$where['price'] = $price_in;
					$where['orign'] = (in_array($manufacturer, $this->originals)) ? 1 : 0;
					$where['active'] = 1;
					$where['author_add'] = $where['author_edit'] = $_SESSION['user']->id;
					$where['date_add'] = $where['date_edit'] = time();
					$where['analogs'] = '';
					if($cols->analogs >= 0 && isset($row[$cols->analogs]) && $row[$cols->analogs] != '')
					{
						$analogs = explode($cols->analogs_delimiter, $row[$cols->analogs]);
						foreach ($analogs as $analog) {
							$analog = $this->makeArticle($analog);
						}
						$where['analogs'] = implode(',', $analogs);
					}
					if($this->db->insertRow('s_shopparts_products', $where))
					{
						$id = $this->db->getLastInsertedId();

						if($row[$cols->price] != '' && $price_in > 0)
						{
							$price = array();
							$price['storage'] = $_SESSION['alias']->id;
							$price['product'] = $id;
							$price['price_in'] = $price_in;
							$price['price_out'] = 0;
							// $price['price_out'] = $this->getPriceOut($price_in);
							$price['amount'] = $amount;
							$price['manager_add'] = $price['manager_edit'] = $_SESSION['user']->id;
							$price['date_add'] = $price['date_edit'] = time();
							$this->db->insertRow('s_shopstorage_products', $price);
							$invoiceIs[$id] = 1;
							$this->insertedStorage++;
						}

						$history = array();
						$history['update'] = $_STORAGE_UPDATE_ID;
						$history['product'] = $id;
						$history['price_old'] = 0;
						$history['price_new'] = $price_in;
						$history['amount_old'] = 0;
						$history['amount_new'] = $amount;
						$this->db->insertRow('products_update_history', $history);

						$products_articles[$article] = $id;
						$product_new = new stdClass();
						$product_new->id = $id;
						$product_new->manufacturer = $manufacturer;
						$products_ids[$id] = $product_new;

						$this->inserted++;
					}
				}
			}
		}

		if($this->data->post('delete') == 1 && !empty($inStorage))
		{
			foreach ($inStorage as $row) {
				if($this->db->deleteRow('s_shopstorage_products', $row->invoice)) 
				{
					$this->deleted++;

					$history = array();
					$history['update'] = $_STORAGE_UPDATE_ID;
					$history['product'] = $row->id;
					$history['price_old'] = $row->price_in;
					$history['price_new'] = 0;
					$history['amount_old'] = $row->amount;
					$history['amount_new'] = 0;
					$this->db->insertRow('products_update_history', $history);
				}
			}
		}

		$storage = array();
		$storage['inserted'] = $this->insertedStorage;
		$storage['updated'] = $this->updated;
		$storage['deleted'] = $this->deleted;
		$this->db->updateRow('s_shopstorage_updates', $storage, $_STORAGE_UPDATE_ID);

		return true;
	}

	private function getManufacturer($manufacturer)
	{
		$manufacturer = trim($manufacturer);
		$manufacturer = mb_strtoupper($manufacturer);
		if(substr($manufacturer, -1) == '_')
			$manufacturer = substr($manufacturer, 0, -1);

		if(is_array($this->manufacturer))
		{
			if(isset($this->manufacturer[$manufacturer]))
				return $this->manufacturer[$manufacturer];
			else
			{
				$data = array();
				$data['wl_alias'] = $this->data->post('shop');
				$data['alias'] = $this->data->latterUAtoEN($manufacturer);
				$data['main_id'] = 0;
				$data['name'] = $manufacturer;
				if($this->db->insertRow('s_shopparts_manufactures', $data))
				{
					$id = $this->db->getLastInsertedId();
					$this->manufacturer[$manufacturer] = $id;
					$this->manufacturers_names[$id] = $manufacturer;
					return $id;
				}
			}
		}
		return 0;
	}

	public function getPriceIn($price_in)
	{
		if($this->data->post('currency') == 'USD')
			return round($price_in, 2);
		elseif($this->data->post('currency_to_1') > 0)
			return round($price_in / $this->data->post('currency_to_1'), 2);
		else
			return false;
	}

	private function getPriceOut($price_in, $priceTo = -1)
	{
		if(!$this->markUp)
		{
			$markUps = $this->db->getAllDataByFieldInArray('s_shopstorage_markup', $_SESSION['alias']->id, 'storage');
			if($markUps)
			{
				$this->markUp = array();
				foreach ($markUps as $mark) {
					$this->markUp[$mark->user_type] = $mark->markup + 100;
				}
			}
		}
		if($this->markUp)
		{
			if($priceTo >= 0) $price_in = $price_in * 100 / $this->markUp[$priceTo];
			$price_out = array();
			foreach ($this->markUp as $key => $value) {
				$price_out[$key] = round(($price_in * $value / 100), 2);
			}
			return serialize($price_out);
		}
	}

	public function makeArticle($article, $manufacturer = '')
	{
		$article = (string) $article;
		$article = trim($article);
		$article = strtoupper($article);
		$article = mb_eregi_replace("[ ]{2,}", ' ', $article);
		$article = str_replace(' ', '', $article);
		$article = str_replace('-', '', $article);
		$article = str_replace('.', '', $article);

		$last = substr($article, -1);
		if($last == '_')
			$article = substr($article, 0, -1);
		
		if($manufacturer != '')
		{
			$a = explode('_', $article);
			$manufacturer = mb_strtoupper($manufacturer);
			if(array_pop($a) == $manufacturer)
				$article = implode('_', $a);
			elseif($last == ')')
			{
				$a = substr($article, 0, -1);
				$a = explode('(', $a);
				if(count($a) == 2 && array_pop($a) == $manufacturer)
					$article = $a[0];
			}
		}
		return $article;
	}

}
?>
