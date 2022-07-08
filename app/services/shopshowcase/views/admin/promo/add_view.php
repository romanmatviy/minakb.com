<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/promo" class="btn btn-info btn-xs"><i class="fa fa-tasks" aria-hidden="true"></i> До всіх акцій</a>
                </div>
                <h4 class="panel-title">Заповність необхідні дані</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_promo" method="POST" class="form-horizontal">
					<input type="hidden" name="id" value="0">
					<div class="form-group">
                        <label class="col-md-3 control-label">Початок дії акції</label>
                        <div class="col-md-9">
                        	<div class="col-xs-6">
                        		<input type="text" class="form-control datepicker " name="from_date" value="<?=$this->data->re_post('from_date', date('d.m.Y'))?>" required autocomplete="off">
                        	</div>
                            <div class="col-xs-6">
                            	<input type="time" class="form-control" name="from_time" value="<?=$this->data->re_post('from_time', date('H:i'))?>" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Кінець акції</label>
                        <div class="col-md-9">
                        	<div class="col-xs-6">
                        		<input type="text" class="form-control datepicker " name="to_date" value="<?=$this->data->re_post('to_date')?>" required autocomplete="off">
                        	</div>
                            <div class="col-xs-6">
                            	<input type="time" class="form-control" name="to_time" value="<?=$this->data->re_post('to_time', '00:00')?>" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Відсоток знижки</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="percent" min="1" step="0.5" value="<?=$this->data->re_post('percent')?>" required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Інформація</label>
                        <div class="col-md-9">
                        	<textarea name="info" rows="3" class="form-control"><?=$this->data->re_post('info')?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-floppy-o" aria-hidden="true"></i> Додати</button>
                        </div>
                    </div>
				</form>
			</div>
		</div>
	</div>
</div>


<link  href="<?=SERVER_URL?>assets/datepicker/dist/datepicker.min.css" rel="stylesheet">
<?php
    $_SESSION['alias']->js_load[] = "assets/datepicker/dist/datepicker.min.js";
    $_SESSION['alias']->js_load[] = "assets/datepicker/i18n/datepicker.uk.js";
    $_SESSION['alias']->js_init[] = "promo_add_init()";
 ?>
<script>
	function promo_add_init() {
		var datepicker_config = { language: 'uk', 'startDate': '<?=date('d.m.Y')?>', 'autoHide':true };
		$('input.datepicker').datepicker(datepicker_config).on('pick.datepicker', function (e) {
	        if(e.target.name == 'from_date')
	        {
	        	$('input.datepicker[name=to_date]').datepicker('setStartDate', e.date);
	        	$('input.datepicker[name=to_date]').datepicker('show');
	        }
	    });
	}
</script>