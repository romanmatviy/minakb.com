<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-body">
				<form action="<?=SITE_URL?>admin/wl_users/export_file" method="POST" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Типи користувачів</label>
                        <div class="col-md-9">
                        	<?php $types = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
                        		$types_list = array();
								foreach ($types as $type) {
									$types_list[] = $type->title;
									$count = $this->db->getCount('wl_users', $type->id, 'type');
									?>
									<label><input type="checkbox" name="types[]" value="<?=$type->id?>" <?=($count)?'':'disabled'?> /> <?=$type->title?> (<?=$count?>)</label><br>
								<?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Поля вигрузки</label>
                        <div class="col-md-9">
                        	<label><input type="checkbox" name="fields[]" value="id" /> Внутрішній id</label><br>
                        	<label><input type="checkbox" name="fields[]" value="email" checked /> Email</label><br>
                        	<label><input type="checkbox" name="fields[]" value="name" checked /> Ім'я</label><br>
                        	<label><input type="checkbox" name="fields[]" value="type_name" /> Тип (<?=implode(', ', $types_list); ?>)
                        	</label><br>
                        	<label><input type="checkbox" name="fields[]" value="status_name" /> Статус (<?php $statuses = $this->db->getAllData('wl_user_status');
                        		$statuses_list = array();
								foreach ($statuses as $status) $statuses_list[] = $status->title;
								echo implode(', ', $statuses_list); ?>)
								</label><br>
                        	<label><input type="checkbox" name="fields[]" value="registered" /> Дата реєстрації (dd.mm.yyyy hh:ii)</label><br>
                        	<label><input type="checkbox" name="fields[]" value="last_login" /> Дата останнього входу (dd.mm.yyyy hh:ii)</label><br>
                        	<?php if(!empty($fields_additionall)) foreach ($fields_additionall as $field) { ?>
                        		<label><input type="checkbox" name="fields[]" value="<?=$field?>" /> <?=$field?> </label><br>
                        	<?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Тип вигрузки (документу)</label>
                        <div class="col-md-9">
                        	<label><input type="radio" name="file" value="csv" required /> csv</label> 
                        	<label><input type="radio" name="file" value="xls" /> xls</label> 
                        	<label><input type="radio" name="file" value="xlsx" /> xlsx</label>
                        </div>
                    </div>
                    <div class="form-group">
                    	<div class="col-md-3"></div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success ">Експорт</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>