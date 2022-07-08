<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>" class="btn btn-info btn-xs">Назад</a>
            	</div>
                <h4 class="panel-title">Редагувати перевізника</h4>
            </div>
            <div class="panel-body">
	            <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST">
	            	<input type="hidden" name="id" value="<?=$delivery->id?>">
	                <div class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
	                    	<tr>
	                    		<th>Статус активності</th>
	                    		<td>
		                    		<select name="active" class="form-control">
		                    			<option value="1" <?=($delivery->active == 1) ? 'selected' : ''?>>Перевізник активний</option>
		                    			<option value="0" <?=($delivery->active == 0) ? 'selected' : ''?>>Перевізник тимчасово недоступний</option>
		                    		</select>
	                    		</td>
	                    	</tr>
							<tr>
								<th>Назва</th>
								<td><input type="text" name="name" value="<?=$delivery->name?>" required class="form-control"></td>
							</tr>
							<tr>
								<th>Сайт</th>
								<td><input type="text" name="site" value="<?=$delivery->site?>" class="form-control"></td>
							</tr>
							<tr>
								<th>Інформація</th>
								<td><textarea name="info" class="form-control"><?=$delivery->info?></textarea></td>
							</tr>
							<tr>
								<th>Відправка</th>
								<td>
									<label><input type="radio" name="department" value="1" <?=$delivery->department == 1 ? 'checked' : ''?>> у відділення</label>
									<label><input type="radio" name="department" value="0" <?=$delivery->department == 0 ? 'checked' : ''?>> за адресою</label>
									<label><input type="radio" name="department" value="2" <?=$delivery->department == 2 ? 'checked' : ''?>> без адреси</label>
								</td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" class="btn btn-sm btn-success" value="Зберегти"></td>
							</tr>
	                    </table>
	                </div>
                </form>
            </div>
        </div>
    </div>
</div>