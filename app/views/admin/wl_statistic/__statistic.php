<link href="<?=SERVER_URL?>assets/bootstrap-datepicker/css/datepicker.css" rel="stylesheet">
<link href="<?=SERVER_URL?>assets/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet">
<?php
$_SESSION['alias']->js_load[] = 'assets/bootstrap-datepicker/js/bootstrap-datepicker.js';
$_SESSION['alias']->js_init[] = '$(".input-daterange").datepicker({todayHighlight:!0, autoclose:!0, endDate:\'0d\', format:\'dd.mm.yyyy\'});';
?>
<div class="row">
    <div class="panel panel-inverse" data-sortable-id="form-plugins-1">
        <div class="panel-heading">
            <h4 class="panel-title">Фільтр</h4>
        </div>
        <div class="panel-body panel-form">
            <form action="#tab-statistic" class="form-inline form-bordered p-10" _lpchecked="1">
                <div class="form-group">
                    <label class="col-md-3 control-label">Оберіть період</label>
                    <div class="col-md-9">
                        <div class="input-group input-daterange">
                        	<?php $day = strtotime('-1 month'); ?>
                            <input type="text" class="form-control" name="start" placeholder="від" value="<?=$this->data->re_get('start', date('d.m.Y', $day))?>">
                            <span class="input-group-addon">-</span>
                            <input type="text" class="form-control" name="end" placeholder="до" value="<?=$this->data->re_get('end', date('d.m.Y'))?>">
                        </div>
                    </div>
                </div>
                <?php if($_SESSION['language']) { ?>
	                <div class="form-group">
	                	<label class="col-md-3 control-label">Мова</label>
                        <div class="col-md-9">
		                	<select name="language" class="form-control">
		                		<option value="*">всі мови</option>
		                		<?php
		                		foreach ($_SESSION['all_languages'] as $language) {
		                			$selected = ($this->data->re_get('language') == $language) ? 'selected' : '';
		                			echo("<option value='{$language}' {$selected}>{$language}</option>");
		                		}
		                		?>
		                	</select>
						</div>
	                </div>
		        <?php } ?>
                <div class="form-group">
                    <label class="col-md-3 control-label"></label>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-sm btn-success">Пошук</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
	<div class="col-md-12 ui-sortable">
		<div class="panel panel-inverse" data-sortable-id="form-plugins-2">
			<div class="panel-body">
				<table class="table table-striped table-responsive table-hover">
		            <thead>
		                <tr>
		                    <th>#</th>
		                    <?php if($_SESSION['language']) { ?>
		                    	<th>Мова</th>
		                    <?php } ?>
		                    <th>День</th>
		                    <th>Унікальні відвідувачі</th>
		                    <th>Загальні перегляди</th>
		                </tr>
		            </thead>
		            <tbody>
		            	<?php 
		            	$this->load->model('wl_analytic_model');
		            	if($wl_statistic = $this->wl_analytic_model->getPageStatistic()) foreach ($wl_statistic as $statistic) { ?>
			                <tr>
			                    <td><?=$statistic->id?></td>
			                    <?php if($_SESSION['language']) { ?>
			                    	<td><?=$statistic->language?></td>
			                    <?php } ?>
			                    <td><?=date('d.m.Y', $statistic->day)?></td>
			                    <td><?=$statistic->unique?></td>
			                    <td><?=$statistic->views?></td>
			                </tr>
		                <?php } ?>
		            </tbody>
		        </table>
		        <?php
	                $this->load->library('paginator');
	                echo $this->paginator->get();
	            ?>
	        </div>
	    </div>
    </div>
</div>