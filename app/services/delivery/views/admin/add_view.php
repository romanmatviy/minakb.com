<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">Назад</a>
            	</div>
                <h4 class="panel-title">Додати перевізника</h4>
            </div>
            <div class="panel-body">
	            <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST">
	            	<input type="hidden" name="id" value="0">
	                <div class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
							<tr>
								<th>Назва</th>
								<td><input type="text" name="name" required class="form-control"></td>
							</tr>
							<tr>
								<th>Сайт</th>
								<td><input type="text" name="site" class="form-control"></td>
							</tr>
							<tr>
								<th>Інформація</th>
								<td><textarea name="info" class="form-control"></textarea></td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" class="btn btn-sm btn-success" value="Додати"></td>
							</tr>
	                    </table>
	                </div>
                </form>
            </div>
        </div>
    </div>
</div>