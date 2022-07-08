<?php $max = 0;
foreach ($file as $Key => $Row)
{
	if(count($Row) > $max)
		$max = count($Row);
	if($Key > 20) break;
}
$_SESSION['alias']->js_init[] = "init_importPrepare();"; ?>
<div class="row">
	<div class="panel panel-inverse">
	    <div class="panel-heading">
	        <h4 class="panel-title">Оновлення цін через файл</h4>
	    </div>
	    <div class="panel-body">
        	<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/import/analyze" method="POST" class="form-horizontal">
        		<input type="hidden" name="path" value="<?=$path?>">
	            <div class="form-group">
                    <label class="col-md-3 control-label">Колонка з ID (внутрішнім вдентифікатором товару)</label>
                    <div class="col-md-9">
                        <select name="col_id" required class="form-control">
                        	<?php for ($i=0; $i < $max; $i++) { 
                        		echo "<option value=\"{$i}\">col #{$i}</option>";
                        	} ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Колонка з ціною</label>
                    <div class="col-md-9">
                        <select name="col_price" required class="form-control">
                        	<option value="-1">Не вказано</option>
                        	<?php for ($i=0; $i < $max; $i++) {
                        		$disabled = $i == 0 ? 'disabled' : '';
                        		echo "<option value=\"{$i}\" {$disabled}>col #{$i}</option>";
                        	} ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Перший рядок з товаром</label>
                    <div class="col-md-9">
                        <select name="row_start" required class="form-control">
                        	<?php for ($i=0; $i < count($file) -1; $i++) { 
                        		echo "<option value=\"{$i}\">row #{$i}</option>";
                        		if($i >= 20) break;
                        	} ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3"></div>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-upload" aria-hidden="true"></i> Аналізувати</button>
                    </div>
                </div>
	        </form>
	    </div>
	</div>
</div>
<?php if($file) { ?>
	<div class="row">
		<div class="panel panel-inverse">
		    <div class="panel-heading">
		        <h4 class="panel-title">Аналіз вхідного файлу (перші 20 рядків)</h4>
		    </div>
		    <div class="panel-body">
	            <table id="importTable" class="table table-striped table-bordered">
					<?php echo('<tr><th style="width:200px"></th>');
					for ($i=0; $i < $max; $i++) {
						$class = ($i == 0) ? 'success' : '';
						$help = ($i == 0) ? '(ID товару)' : '';
						echo("<th class=\"col-{$i} {$class}\" data-help=\"col #{$i}\">col #{$i} {$help}</th>");
					}
					echo('</tr>');
					foreach ($file as $rowIndex => $row)
					{
						$class = ($rowIndex == 0) ? 'class="success"' : '';
						$help = ($rowIndex == 0) ? '<br>Перший рядок з товаром' : '';
						echo("<tr id='row-{$rowIndex}' {$class}><th data-help=\"row #{$rowIndex}\">row #{$rowIndex} {$help}</th>");
							for ($i=0; $i < $max; $i++) {
								$class = ($i == 0) ? 'success' : '';
								echo "<td class=\"col-{$i} {$class}\">$row[$i]</td>";
							}
						echo('</tr>');
						if($rowIndex >= 20) break;
					}
	            	?>
	            </table>
		    </div>
		</div>
	</div>
<?php } ?>

<script type="text/javascript">
function init_importPrepare()
{
	$('select[name="col_id"]').change(function(){
		let col_id = $(this).val();
		$('#importTable').find('th, td').removeClass('success');
		$('#importTable').find('th.col-'+col_id+', td.col-'+col_id).addClass('success').removeClass('warning');
		$('#importTable').find('th:not(.warning)').each(function(){
			$(this).text($(this).data('help'));
		});
		$('#importTable').find('th.col-'+col_id).each(function(){
			$(this).text($(this).data('help') + ' (ID товару)');
		});

		$('select[name="col_id"]').find('option[value="-1"]').remove();
		$('select[name="col_price"]').find('option').attr('disabled', false);
		$('select[name="col_price"]').find('option[value="'+col_id+'"]').attr('disabled', true);
	});
	$('select[name="col_price"]').change(function(){
		let col_price = $(this).val();
		$('#importTable').find('th, td').removeClass('warning');
		$('#importTable').find('th.col-'+col_price+', td.col-'+col_price).addClass('warning');
		$('#importTable').find('th:not(.success)').each(function(){
			$(this).text($(this).data('help'));
		});
		$('#importTable').find('th.col-'+col_price).each(function(){
			$(this).text($(this).data('help') + ' (Ціна товару)');
		});

		$('select[name="col_price"]').find('option[value="-1"]').remove();
		$('select[name="col_id"]').find('option').attr('disabled', false);
		$('select[name="col_id"]').find('option[value="'+col_price+'"]').attr('disabled', true);
	});
	$('select[name="row_start"]').change(function(){
		let row_start = $(this).val();
		$('#importTable').find('tr').removeClass('success');
		$('#importTable').find('tr#row-'+row_start).addClass('success');
		$('#importTable').find('tr:not(.success) th').each(function(){
			$(this).text($(this).data('help'));
		});
		$('#importTable').find('tr#row-'+row_start+' th').each(function(){
			$(this).html($(this).data('help') + '<br>Перший рядок з товаром');
		});
	});
}
</script>