<div class="row">
	<div class="panel panel-inverse">
	    <div class="panel-heading">
	    	<div class="panel-heading-btn">
            	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-success btn-xs"><i class="fa fa-cogs"></i> Налаштування складу</a>
            	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/optionsImport" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування структури файлу</a>
            </div>
	        <h4 class="panel-title">Оновлення складу через файл</h4>
	    </div>
	    <div class="panel-body">
	        <div class="table-responsive">
	        	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/updateStart" method="POST" class="form-horizontal">
		            <input type="hidden" name="checkPrice" value="-1">
		            <input type="hidden" name="insert" value="<?=$this->data->post('insert')?>">
		            <input type="hidden" name="delete" value="<?=$this->data->post('delete')?>">
		            <input type="hidden" name="shop" value="<?=$this->data->post('shop')?>">
		            <input type="hidden" name="file" value="<?=isset($_FILES['price']['name']) ? $_FILES['price']['name'] : $this->data->post('file')?>">
		            <input type="hidden" name="currency" value="<?=$this->data->post('currency')?>">
		            <input type="hidden" name="currency_to_1" value="<?=$this->data->post('currency_to_1')?>">
		            <table class="table table-striped table-bordered nowrap" width="100%">
		            	<tr>
		            		<td>Файл: <strong><?=isset($_FILES['price']['name']) ? $_FILES['price']['name'] : $this->data->post('file')?></strong></td>
		            		<td>Вхідна валюта: <strong><?=$this->data->post('currency')?></strong></td>
		            		<td>Дата: <strong><?=date('d.m.Y H:i')?></strong></td>
		            	</tr>
		            	<tr>
		            		<?php if($Spreadsheet && $cols && $cols->start >= 0) { 
		            			if(isset($_SESSION['import']['all_products']))
		            				$all = $_SESSION['import']['all_products'];
		            			else
		            			{
			            			$all = 0;
			            			foreach ($Spreadsheet as $Key => $row)
			            				$all++;
			            			$all -= $cols->start;
			            		}
		            			?>
		            			<td>Всіх товарів у прайсі: <strong><?=$all?></strong></td>
		            		<?php } else { ?>
		            			<td>Менеджер: <strong><?=$_SESSION['user']->name?></strong></td>
		            		<?php } if($this->data->post('currency') != 'USD') { ?>
		            			<td>Курс валют <strong>1 USD = <?=$this->data->post('currency_to_1').' '.$this->data->post('currency')?></strong></td>
		            		<?php } else { ?>
		            			<td></td>
		            		<?php } ?>
		            		<td>
		            			<?php if($Spreadsheet && $cols && $cols->start >= 0) { ?>
		            				<button type="submit" class="btn btn-warning">Оновити</button>
		            				<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-info"><i class="fa fa-refresh"></i> Вказати інший файл</a>
		            			<?php } else { ?>
			            			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-warning"><i class="fa fa-refresh"></i> Вказати інший файл</a>
			            			<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/optionsImport?file=<?=isset($_FILES['price']['name']) ? $_FILES['price']['name'] : $this->data->post('file')?>" class="btn btn-info"><i class="fa fa-cogs"></i> До налаштувань структури файлу</a>
			            		<?php } ?>
		            		</td>
		            	</tr>
		            </table>
		        </form>
	        </div>
	    </div>
	</div>
</div>
<?php if($Spreadsheet && $cols && $cols->start >= 0) { $colspan = 5; ?>
	<div class="row">
		<div class="panel panel-inverse">
		    <div class="panel-heading">
		        <h4 class="panel-title">Аналіз вхідного файлу</h4>
		    </div>
		    <div class="panel-body">
	            <table class="table table-striped table-bordered">
					<tr>
						<th>Виробник</th>
						<th>Артикул</th>
						<th>Назва</th>
						<?php if($cols->count >= 0) { $colspan++; ?>
							<th>Кількість</th>
						<?php } ?>
						<th>Ціна вхідна <strong><?=$this->data->post('currency')?></strong></th>
						<th>Ціна вихідна <strong>USD</strong></th>
						<?php if($cols->analogs >= 0) { $colspan++; ?>
							<th>Аналоги</th>
						<?php } ?>
					</tr>
					<?php $i = 0; $setManufacturer = false;
					if(isset($cols->setManufacturer) && is_numeric($cols->setManufacturer) && $cols->setManufacturer > 0)
					{
						if($manufacturer = $this->db->getAllDataById('s_shopparts_manufactures', $cols->setManufacturer))
							$setManufacturer = $manufacturer->name;
					}

					$viewOnlyGood = true;
					if($this->import_model->min_price_UAH > 0)
					{
						if($this->data->post('currency') == 'USD')
						{
							$currency = $this->db->getAllDataById('s_currency', 'USD', 'code');
							$currency = $currency->currency;
						}
						else
							$currency = $this->data->post('currency_to_1');
						
						$this->import_model->min_price_USD = $this->import_model->min_price_UAH / $currency;
						$this->import_model->min_price_UAH = 0;
					}
					
					foreach ($Spreadsheet as $Key => $row)
					{
						if($Key >= $cols->start)
						{
							if(!is_numeric($row[$cols->price]))
								$row[$cols->price] = str_replace(',', '.', $row[$cols->price]);

							if($viewOnlyGood)
							{
								if(isset($row[$cols->price]) && is_numeric($row[$cols->price]))
								{
									$price = $row[$cols->price];
									if($this->data->post('currency') != 'USD')
										$price = $row[$cols->price] / $this->data->post('currency_to_1');
									if($price < $this->import_model->min_price_USD)
										continue;
								}
								else
									continue;
								
							}

							$class = (isset($row[$cols->price]) && is_numeric($row[$cols->price])) ? '' : 'class="danger"';
							echo("<tr {$class}>");
								echo('<td>');
									if($setManufacturer)
										echo $setManufacturer;
									elseif(isset($row[$cols->manufacturer]))
										echo($row[$cols->manufacturer]);
								echo('</td>');
								echo('<td>');
									if(isset($row[$cols->article]))
									{
										if($setManufacturer)
											echo($this->import_model->makeArticle($row[$cols->article]));
										else
											echo($this->import_model->makeArticle($row[$cols->article], $row[$cols->manufacturer]));
									}
								echo('</td>');
								echo('<td>');
									if(isset($row[$cols->name])) echo($row[$cols->name]);
								echo('</td>');
									if($cols->count >= 0)
									{
										echo('<td>');
										if(isset($row[$cols->count])) echo($row[$cols->count]);
										echo('</td>');
									}
								echo('<td>');
									if(isset($row[$cols->price])) echo($row[$cols->price]);
								echo('</td>');
								echo('<td>');
									if(isset($row[$cols->price]) && is_numeric($row[$cols->price]))
									{
										if($this->data->post('currency') == 'USD') echo($row[$cols->price]);
										else echo round($row[$cols->price] / $this->data->post('currency_to_1'), 2);
									}
								echo('</td>');
								if($cols->analogs >= 0)
								{
									echo('<td>');
									if(isset($row[$cols->analogs]))
									{
										$analogs = explode($cols->analogs_delimiter, $row[$cols->analogs]);
										foreach ($analogs as $analog) {
											echo('<span class="label label-default">'.$analog.'</span> ');
										}
									}
									echo('</td>');
								}
							echo('</tr>');
							$i++;

							if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $i > $_GET['showRows'])
								break;
							elseif(!isset($_GET['showRows']) && $i >= 200) break;
						}
					}
	            	?>
	            	<tr>
	            		<th colspan="<?=$colspan?>" class="text-center">Виведено перші <?=(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0) ? $_GET['showRows'] : 200?> рядків. <a href="?showRows=500" class="btn btn-info btn-xs"><i class="fa fa-arrow-circle-down"></i> Вивести 500 рядків</a></th>
	            	</tr>
	            </table>
		        <?php $CurrentMem = memory_get_usage();
		        	$memoty = round(($CurrentMem - $BaseMem)/1024, 2);
					if($memoty > 1024) $memoty = round($memoty / 1024, 2) . ' Мб';
					else $memoty .= ' Кб';
		        	echo 'Використано пам\'яті: '.$memoty.' <br>';
					echo 'Час: '.ceil(microtime(true) - $BaseTime).' сек';
				?>
		    </div>
		</div>
	</div>
<?php } else { ?>
<div class="row">
	<div class="alert alert-danger fade in m-b-15">
		<strong>Помилка!</strong>
		Структура вхідного файлу не відповідає заявленій! <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/optionsImport?file=<?=isset($_FILES['price']['name']) ? $_FILES['price']['name'] : $this->data->post('file')?>" class="btn btn-xs btn-warning"><i class="fa fa-cogs"></i> До налаштувань структури файлу</a>
		<span class="close" data-dismiss="alert">×</span>
	</div>
</div>
<?php } ?>