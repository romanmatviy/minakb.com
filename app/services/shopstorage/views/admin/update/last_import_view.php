<div class="row">
	<div class="panel panel-inverse">
	    <div class="panel-heading">
	    	<div class="panel-heading-btn">
            	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/update" class="btn btn-info btn-xs"><i class="fa fa-refresh"></i> Оновити склад</a>
            </div>
	        <h4 class="panel-title">Історія. Останнє оновлення</h4>
	    </div>
	    <div class="panel-body">
	        <div class="table-responsive">
	            <table class="table table-striped table-bordered nowrap" width="100%">
	            	<tr>
	            		<td>Файл</td>
	            		<th><?=$update->file?></th>
	            		<td>Валюта</td>
	            		<th><?=$update->currency?></th>
	            		<td>Додано</td>
	            		<th><?=$update->inserted?></th>
	            		<td>Видалено</td>
	            		<th><?=$update->deleted?></th>
	            	</tr>
	            	<tr>
	            		<td>Дата</td>
	            		<th><?=date('d.m.Y H:i', $update->date)?></th>
	            		<td>Курс</td>
	            		<th><?=$update->price_for_1?></th>
	            		<td>Оновлено</td>
	            		<th><?=$update->updated?></th>
	            		<td>Менеджер</td>
	            		<th><?php if($update->manager > 0) { 
	            			$manager = $this->db->getAllDataById('wl_users', $update->manager);
	            			?>
	            			<a href="<?=SITE_URL.'admin/wl_users/'.$manager->email?>"><?=$update->manager.'. '.$manager->name?></a>
	            			<?php } else 
	            			{
	            				echo "Автооновлення"; ?>
	            				<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/history/'.$update->id?>" class="btn btn-info btn-xs">Детально</a>
	            			<?php } ?>
	            		</th>
	            	</tr>
	            </table>
	        </div>
	    </div>
	</div>
</div>
<?php if($Spreadsheet) { ?>
	<div class="row">
		<div class="panel panel-inverse">
		    <div class="panel-heading">
		    	<div class="panel-heading-btn">
	            	<a href="<?=SITE_URL.$path?>" class="btn btn-success btn-xs"><i class="fa fa-arrow-circle-down"></i> Завантажити файл</a>
	            </div>
		        <h4 class="panel-title">Аналіз вхідного файлу</h4>
		    </div>
		    <div class="panel-body">
	            <table class="table table-striped table-bordered">
	            	<?php $max = 0;
	            	foreach ($Spreadsheet as $Key => $Row)
					{
						if(count($Row) > $max) $max = count($Row);
						if($Key > 200) break;
					}
					$max++;
					foreach ($Spreadsheet as $Key => $Row)
					{
						echo('<tr>');
						echo("<th>{$Key}</th>");
						for($i = 0; $i < $max; $i++)
						{
							if(isset($Row[$i]))
								echo("<td style='max-width:200px'>{$Row[$i]}</td>");
							else
								echo('<td></td>');
						}
						echo('</tr>');
						if($Key > 200) break;
					}
	            	?>
	            	<tr>
	            		<th colspan="<?=$max?>" class="text-center">Виведено перші 200 рядків. Для повної інформації <a href="<?=SITE_URL.$path?>" class="btn btn-info btn-xs"><i class="fa fa-arrow-circle-down"></i> Завантажити файл</a></th>
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