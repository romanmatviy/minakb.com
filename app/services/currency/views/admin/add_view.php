<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs"><i class="fa fa-line-chart"></i> До валют</a>
            	</div>
                <h4 class="panel-title">Додати валюту</h4>
            </div>
            <div class="panel-body">
	            <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" class="form-horizontal">
	            	<input type="hidden" name="id" value="0">
	                <div class="form-group">
	                	<label class="col-md-4 control-label">Код</label>
						<div class="col-md-8">
							<select name="code" class="form-control">
								<?php $codes = ['UAH', 'USD', 'EUR', 'RUR'];
									foreach ($codes as $code) {
										if(isset($_SESSION['currency'][$code]))
											continue;
										echo "<option value=\"{$code}\">{$code}</option>";
									}
								 ?>
							</select>
						</div>
					</div>
					<div class="form-group">
	                	<label class="col-md-4 control-label">Коефіціент (курс) відносно базової валюти</label>
						<div class="col-md-8">
							<input type="number" name="currency" value="1" min="0" step="0.01" class="form-control" <?=count($_SESSION['currency']) == 4 ? 'disabled' : ''?>>
						</div>
                    </div>
					<div class="form-group">
                        <label class="col-md-4 control-label"></label>
                        <div class="col-md-8">
							<button type="submit" class="btn btn-sm btn-warning" <?=count($_SESSION['currency']) == 4 ? 'disabled' : ''?>><i class="fa fa-plus"></i> Додати валюту</button>
						</div>
	                </div>
                </form>
            </div>
        </div>
    </div>
</div>