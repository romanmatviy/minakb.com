<?php if(isset($_SESSION['notify']->success)) { ?>
	<div class="row">
		<div class="alert alert-success fade in">
            <h4>Успіх!</h4>
            <p><?=$_SESSION['notify']->success?></p>
        </div>
	</div>
<?php unset($_SESSION['notify']); } ?>

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-inverse">
		    <div class="panel-heading">
		    	<div class="panel-heading-btn">
	            	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-warning btn-xs"><i class="fa fa-refresh"></i> До імпорту</a>
	            	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs"><i class="fa fa-cogs"></i> Налаштування складу</a>
	            </div>
		        <h4 class="panel-title">Ключовий рядок</h4>
		    </div>
		    <div class="panel-body">
		    	<p>Рядок ідентифікації прайсу відносно складу за назвами колонок у файлі. До даного рядка інформація вважається службовою і на імпорт не впливає (ігнорується). Після рядка повинні починатися позиції товару</p>
		        <div class="table-responsive">
		            <table class="table table-striped table-bordered nowrap" width="100%">
		            	<tr>
		            		<th colspan="2">Поточні дані</th>
		            		<th colspan="2">Нові дані</th>
		            	</tr>
		            	<tr>
		            		<th>Колонка (номер)</th>
		            		<th>Назва (дані)</th>
		            		<th>Колонка (номер)</th>
		            		<th>Назва (дані)</th>
		            	</tr>
						<?php
							if(empty($storage->updateRows))
							{
								if($this->data->get('newKeyRow'))
								{

								}
								else
								{
									echo('<tr><td colspan="2">Не задано</td>');
									echo('<td colspan="2">Не налаштовано</td></tr>');
								}
							}
							else
							{
								$storage->updateRows = unserialize($storage->updateRows);
								$storage->updateCols = unserialize($storage->updateCols);
								$i = 0;
								foreach ($storage->updateRows as $key => $value) {
									echo "<tr><td>$key</td><td>$value</td>";
									if(isset($_GET['newKeyRow']) && $Spreadsheet)
									{
										foreach ($Spreadsheet as $Key => $Row)
											if($Key == $this->data->get('newKeyRow'))
											{
												foreach ($Row as $newIndex => $newName) {
													if($newIndex == $i)
														echo "<td>$i</td><td>$newName</td>";
												}
												break;
											}
									}
									elseif($i == 0)
										echo('<td colspan="2" rowspan="'.count($storage->updateRows).'">Не задано</td>');
									echo "</tr>";
									$i++;
								}
								if(isset($storage->updateCols->file))
									echo("<tr><td colspan='4'>На основі файлу <strong>{$storage->updateCols->file}</strong></td></tr>");
							}
							if(isset($_GET['newKeyRow']) && is_numeric($_GET['newKeyRow']) && $Spreadsheet) { ?>
							<tr>
								<td colspan="2"></td>
								<td colspan="2">
									<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/optionsImportSaveRows" method="POST">
										<input type="hidden" name="file" value="<?=$this->data->get('file')?>">
										<input type="hidden" name="newKeyRow" value="<?=$this->data->get('newKeyRow')?>">
										<button type="submit" class="btn btn-sm btn-danger">Оновити ключові рядки</button>
									</form>
								</td>
							</tr>
							<?php } ?>
		            </table>
		        </div>
		    </div>
		</div>
	    <div class="panel panel-inverse">
		    <div class="panel-heading">
		        <h4 class="panel-title">Аналізувати вхідний файл</h4>
		    </div>
		    <div class="panel-body">
		    	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/optionsImport" enctype="multipart/form-data" method="POST" class="form-inline">
		    		<div class="form-group m-r-10">
		    			<label class="control-label">Вхідний прайс (xls, xlsx, csv)</label>
		    		</div>
		    		<div class="form-group m-r-10">
						<input type="file" name="price" required="required" class="form-control">
					</div>
		    		<button type="submit" class="btn btn-sm btn-success">Аналізувати файл</button>
		    		<?php if(isset($_GET['file'])) { ?>
		    		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update?file=<?=$this->data->get('file')?>" class="btn btn-warning btn-sm"><i class="fa fa-refresh"></i> До імпорту на основі <?=$this->data->get('file')?></a>
		    		<?php } ?>
		    	</form>
		    </div>
	    </div>
	</div>
	<?php if(!empty($storage->updateCols) && !empty($storage->updateRows)) { ?>
	<div class="col-md-6">
		<div class="panel panel-inverse">
		    <div class="panel-heading">
		        <h4 class="panel-title">Колонка і вміст даних</h4>
		    </div>
		    <div class="panel-body">
		    	<p>Вміст даних у колонках за вхідним файлом.</p>

		    	<?php $this->load->smodel('import_model');
				$function = $_SESSION['alias']->alias;
				if(method_exists($this->import_model, $function)) { 
					$this->import_model->$function(false, $storage->updateCols, $storage->updateRows);
					?>
					<div class="alert alert-warning">
			            <p>Увага! Даний склад має спеціальний обробник в моделі імпорту (коді):</p>
			            <p><strong><?=$this->import_model->message?></strong></p>
			        </div>
				<?php } ?>

		    	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/optionsImportSaveCols" method="POST">

			        <div class="table-responsive">
			            <table class="table table-striped table-bordered nowrap" width="100%">
			            	<tr>
			            		<th>Код</th>
			            		<th width="40%">Інформація</th>
			            		<th width="40%">Дані</th>
			            	</tr>
			            	<?php $rowKeys = array();
			            	// $rowKeys['in_id'] = 'інвентаризаційний артикул постачальника (код ідентифікації у постачальника, якщо немає тоді артикул)';
			            	$rowKeys['article'] = 'артикул';
			            	$rowKeys['manufacturer'] = 'виробник';
			            	$rowKeys['name'] = 'назва товару';
			            	$rowKeys['price'] = 'вхідна ціна';
			            	foreach ($rowKeys as $rowKey => $rowTitle) { ?>
				            	<tr>
				            		<td><?=$rowKey?></td>
				            		<td><?=$rowTitle?></td>
				            		<td><select name="<?=$rowKey?>" class="form-control">
				            			<?php foreach ($storage->updateRows as $key => $value) {
				            				$selected = ($storage->updateCols->$rowKey == $key) ? 'selected' : '';
				            				echo('<option value="'.$key.'" '.$selected.'>'.$key.'. '.$value.'</option>');
				            			} ?>
				            			</select></td>
				            	</tr>
			            	<?php } ?>
			            	<tr>
			            		<td>count</td>
			            		<td>кількість (од.)</td>
			            		<td><select name="count" class="form-control">
			            			<option value="-1">дані відсутні</option>
			            			<?php foreach ($storage->updateRows as $key => $value) {
				            				$selected = ($storage->updateCols->count == $key) ? 'selected' : '';
				            				echo('<option value="'.$key.'" '.$selected.'>'.$key.'. '.$value.'</option>');
				            			} ?>
			            			</select></td>
			            		
			            	</tr>
			            	<?php if($storage->updateCols->count < 0) { ?>
				            	<tr>
				            		<td>setCount</td>
				            		<td>встановити примусово кількість товару (од.)</td>
				            		<td><input type="number" name="setCount" value="<?=(isset($storage->updateCols->setCount)) ? $storage->updateCols->setCount : 0?>" class="form-control" min="1" required></td>
				            	</tr>
			            	<?php } ?>
			            	<tr>
			            		<td>analogs</td>
			            		<td>аналоги</td>
			            		<td><select name="analogs" class="form-control">
			            			<option value="-1">дані відсутні</option>
			            			<?php foreach ($storage->updateRows as $key => $value) {
				            				$selected = ($storage->updateCols->analogs == $key) ? 'selected' : '';
				            				echo('<option value="'.$key.'" '.$selected.'>'.$key.'. '.$value.'</option>');
				            			} ?>
			            			</select></td>
			            		
			            	</tr>
			            	<?php if($storage->updateCols->analogs >= 0) { ?>
				            	<tr>
				            		<td>analogs_delimiter</td>
				            		<td>аналоги розділювач: <?='"'.$storage->updateCols->analogs_delimiter.'" '. strlen($storage->updateCols->analogs_delimiter).' символ/ів' ?></td>
				            		<td><input type="text" name="analogs_delimiter" value="<?=$storage->updateCols->analogs_delimiter?>" class="form-control" required></td>
				            	</tr>
			            	<?php } 
			            	$rowKeys['in_id'] = '';
			            	$rowKeys['file'] = '';
			            	$rowKeys['count'] = '';
			            	$rowKeys['setCount'] = '';
			            	$rowKeys['analogs'] = '';
			            	$rowKeys['analogs_delimiter'] = '';
			            	foreach ($storage->updateCols as $rowKey => $rowValue) if(!array_key_exists($rowKey, $rowKeys)) { ?>
				            	<tr>
				            		<td colspan="2"><strong><i><?=$rowKey?></i></strong></td>
				            		<td><?=$rowValue?></td>
				            	</tr>
			            	<?php } ?>
			            </table>
			        </div>

					<button type="submit" class="btn btn-sm btn-danger">Оновити колонки</button>
				</form>
		    </div>
		</div>
	</div>
	<?php } ?>
</div>
<?php if($Spreadsheet) { ?>
	<div class="row">
		<div class="panel panel-inverse">
		    <div class="panel-heading">
		        <h4 class="panel-title">Аналіз вхідного файлу</h4>
		    </div>
		    <div class="panel-body">
	            <table class="table table-striped table-bordered">
	            	<?php $max = 0;
	            	foreach ($Spreadsheet as $Key => $Row)
					{
						if(count($Row) > $max) $max = count($Row);
						if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $Key > $_GET['showRows'])
							break;
						elseif(!isset($_GET['showRows']) && $Key > 200) break;
					}
					echo('<tr><th></th>');
					for ($i=0; $i < $max; $i++) { 
						echo("<th>{$i}</th>");
					}
					echo('</tr>');
					foreach ($Spreadsheet as $Key => $Row)
					{
						if(isset($_GET['newKeyRow']) && $_GET['newKeyRow'] == $Key)
							echo('<tr><td colspan="'.($max+1).'">'.print_r($Row, true).'</td></tr>');
						
						echo('<tr>');
						$color = (isset($_GET['newKeyRow']) && $_GET['newKeyRow'] == $Key) ? 'success' : 'warning';
						echo("<td style='width:170px'><a href='".SITE_URL."admin/{$_SESSION['alias']->alias}/optionsImport?file={$file}&newKeyRow={$Key}' class='btn btn-xs btn-{$color}'>Обрати ключовий рядок <strong>#{$Key}</strong></a></td>");
						for($i = 0; $i < $max; $i++)
						{
							if(isset($Row[$i]))
								echo("<td style='max-width:200px'>{$Row[$i]}</td>");
							else
								echo('<td></td>');
						}
						echo('</tr>');
						if(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0 && $Key > $_GET['showRows'])
							break;
						elseif(!isset($_GET['showRows']) && $Key > 200) break;
					}
	            	?>
	            	<tr>
	            		<th colspan="<?=++$max?>" class="text-center">Виведено перші <?=(isset($_GET['showRows']) && is_numeric($_GET['showRows']) && $_GET['showRows'] > 0) ? $_GET['showRows'] : 200?> рядків. <a href="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/optionsImport?file=<?=$file?>&showRows=500" class="btn btn-info btn-xs"><i class="fa fa-arrow-circle-down"></i> Вивести 500 рядків</a></th>
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
<?php } ?>