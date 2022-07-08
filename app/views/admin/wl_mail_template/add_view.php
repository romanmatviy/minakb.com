<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/wl_mail_template" class="btn btn-info btn-xs">До всіх розсилок</a>
                </div>
                <h4 class="panel-title">Додати розсилку</h4>
            </div>
            <div class="panel-body">
				<form action="<?=SITE_URL?>admin/wl_mail_template/save" method="POST" class="form-horizontal">
					<table>
						<div class="form-group">
							<label class="col-md-3 control-label">Від</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="from" value="<?= SITE_EMAIL?>" placeholder="<?= SITE_EMAIL?>" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">До</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="to" placeholder="to" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Зберегти в історію</label>
							<div class="col-md-9">
								<input type="radio" name="saveToHistory" value="1">Так
								<input type="radio" name="saveToHistory" value="0" checked>Ні
							</div>
						</div>
						<div class="form-group">
	                    	<div class="col-md-3"></div>
	                        <div class="col-md-9">
	                        	<input type="submit" class="btn btn-sm btn-warning " value="Додати">
							</div>
						</div>
					</table>
				</form>
            </div>
        </div>
    </div>
</div>