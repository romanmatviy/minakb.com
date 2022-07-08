<div class="row">
	<div class="col-md-12">
        <div class="panel panel-inverse">
        	<div class="panel-body">
				<div class="col-md-3"></div>
				<div class="col-md-6">
					<form method="post" action="<?= SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/import/prepare' ?>" enctype="multipart/form-data">
						<div class="input-group">
					        <span class="input-group-addon">Файл до імпорту <span class="label label-warning">xlsx</span></span>
					        <input type="file" name="file" placeholder="Документ xlsx" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="file" required>
							<div class="input-group-btn">
								<button type="submit" class="btn btn-primary">Аналізувати</button>
							</div>
						</div>
					</form>
					<center><a href="<?=SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/export'?>">Завантажити приклад</a></center>
				</div>
			</div>
		</div>
	</div>
</div>