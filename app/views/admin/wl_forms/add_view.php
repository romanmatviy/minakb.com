<?php $_SESSION['alias']->js_load[] = 'assets/switchery/switchery.min.js'; ?>
<link rel="stylesheet" href="<?=SITE_URL?>assets/switchery/switchery.min.css" />

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL?>admin/wl_forms" class="btn btn-info btn-xs">До всіх форм</a>
                </div>
                <h4 class="panel-title">Додати форму</h4>
            </div>
            <div class="panel-body">
				<form action="<?=SITE_URL?>admin/wl_forms/add_save" method="POST" class="form-horizontal">
					<table>
						<div class="form-group">
							<label class="col-md-4 control-label"><strong>alias/uri форми*</strong></label>
							<div class="col-md-8">
								<input type="text" class="form-control" name="name" placeholder="feedback" required>
								<small>англ. літери. <?= SITE_URL.'save/'?>* - лінк надсилання даних</small>
							</div>
						</div>
						<div class="form-group">
								<label class="col-md-4 control-label">Захищено Google recaptcha <br><small>Вимагає заповненої галочки</small></label>
								<div class="col-md-8">
									<input type="checkbox" name="captcha" data-render="switchery" value="1" >
								</div>
							</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Назва форми <br><small>Відображається у sidebar</small></label>
							<div class="col-md-8">
								<input type="text" class="form-control" name="title" placeholder="Зворотній зв'язок">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Метод передачі інформації <br><small>method="GET/POST"</small></label>
								<div class="col-md-8">
								<label><input type="radio" name="type" value="get" required> GET</label>
								<label class="m-l-15"><input type="radio" name="type" value="post" checked> POST</label>
							</div>
						</div>
						<div class="form-group">
	                    	<div class="col-md-4"></div>
	                        <div class="col-md-8">
	                        	<input type="submit" class="btn btn-sm btn-warning" value="Додати">
							</div>
						</div>
					</table>
				</form>
            </div>
        </div>
    </div>
</div>