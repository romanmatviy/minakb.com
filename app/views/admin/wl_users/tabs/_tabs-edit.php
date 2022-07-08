<div class="col-md-7">
	<div class="panel panel-inverse" data-sortable-id="form-stuff-1">
	    <div class="panel-heading">
	        <h4 class="panel-title"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Редагувати користувача</h4>
	    </div>
	    <div class="panel-body">
			<form autocomplete="off" action="<?=SITE_URL?>admin/wl_users/save" method="POST" class="form-horizontal">
				<input type="hidden" name="id" value="<?=$user->id?>">
			    <div class="form-group" title="УВАГА! Зміна email призведе до ліквідації паролю користувача (необхідно встановити заново)">
			        <label class="col-md-3 control-label">Email користувача</label>
			        <div class="col-md-9">
			            <input type="email" name="email" class="form-control" value="<?=$user->email?>" required placeholder="email"  autocomplete="off"/>
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="col-md-3 control-label">Номер телефону</label>
			        <div class="col-md-9">
			            <input type="number" name="phone" class="form-control" value="<?=$user->phone?>" required placeholder="phone користувача"  autocomplete="off"/>
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="col-md-3 control-label">Alias користувача</label>
			        <div class="col-md-9">
			            <input type="text" name="alias" class="form-control" value="<?=$user->alias?>" required placeholder="alias користувача"  autocomplete="off"/>
			        </div>
			    </div>
				<div class="form-group">
			        <label class="col-md-3 control-label">Ім'я користувача</label>
			        <div class="col-md-9">
			            <input type="text" name="name" class="form-control" value="<?=$user->name?>" required placeholder="Ім'я користувача"  autocomplete="off"/>
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="col-md-3 control-label">Змінити пароль користувача</label>
			        <div class="col-md-9">
			        	<input type="checkbox" name="active_password" id="active_password" value="1"> <label for="active_password">Встановити новий пароль:</label>
			            <input type="text" name="password" class="form-control" placeholder="Новий пароль" />
			            (Поточний: <?=$user->password?>)
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="col-md-3 control-label">Тип користувача</label>
			        <div class="col-md-9">
			            <select class="form-control" name="type" onchange="chengeType(this)" required>
						<?php foreach ($types as $type) {
								if(!$_SESSION['user']->admin && $type->id < 3)
									continue;
								echo('<option value="'.$type->id.'"');
								if($type->id == $user->type) echo " selected";
								echo('>'.$type->title.'</option>');
							} ?>
			            </select>
			        </div>
			    </div>
				<?php if($_SESSION['user']->admin) { ?>
			    <div id="permissions" class="form-group" <?=($user->type == 2)?'':'style="display: none"'?>>
			        <label class="col-md-3 control-label">Сторінки доступу</label>
			        <div class="col-md-9">
			            <?php $permissions = $this->db->getAllData('wl_aliases');
							$up = $this->db->getAllDataByFieldInArray('wl_user_permissions', $user->id, 'user');
							$user_permissions = array();
							if(!empty($up)) foreach ($up as $upp) {
								$user_permissions[] = $upp->permission;
							}
							foreach ($permissions as $p) { ?>
								<input type="checkbox" id="<?=$p->alias?>" name="permissions[]" value="<?=$p->id?>" <?=(in_array($p->id, $user_permissions))?'checked':''?>><label for="<?=$p->alias?>"><?=$p->alias?></label>
						<?php }
						if( $sidebarForms = $this->db->getQuery("SELECT `id`, `title` FROM `wl_forms`", 'array') ){
							echo "<p>Форми:</p>";
							foreach ($sidebarForms as $form) { ?>
								<input type="checkbox" id="form-<?=$form->id?>" name="permissions[]" value="-<?=$form->id?>" <?=(in_array(-$form->id, $user_permissions))?'checked':''?>><label for="form-<?=$form->id?>"><?=$form->title?></label>
						<?php } }
						if(!empty($_SESSION['option']->showInAdminWl_comments)) { ?>
							<br><input type="checkbox" id="wl_comments" name="permissions[]" value="0" <?=(in_array(0, $user_permissions))?'checked':''?>><label for="wl_comments">Відгуки та коментарі</label>
						<?php } ?>
			        </div>

			        <script type="text/javascript">
						function chengeType (e) {
							if(e.value == 2){
								$('#permissions').slideDown('slow');
							} else {
								$('#permissions').slideUp('slow');
							}
						}
					</script>
			    </div>
				<?php } ?>
			    <div class="form-group">
			        <label class="col-md-3 control-label">Статус акаунта</label>
			        <div class="col-md-9">
			        	<select class="form-control" name="status" required>
			            <?php foreach ($status as $s) {
								echo('<option value="'.$s->id.'"');
								if($s->id == $user->status) echo " selected";
								echo('>'.$s->title.'</option>');
							} ?>
						</select>
			        </div>
			    </div>

				<?php if(!empty($user->info)) foreach($user->info as $key => $value) { ?>
				<div class="form-group">
			        <label class="col-md-3 control-label"><?= $key ?></label>
			        <div class="col-md-9">
			            <input type="text" name="info[<?= $key ?>]" class="form-control" value="<?= $value ?>"  autocomplete="off"/>
			        </div>
			    </div>
				<?php }

				if(isset($user->s_newsletter)) { ?>
				<div class="form-group">
			    	<label class="col-md-3 control-label">Розсилка</label>
			        <div class="col-md-9">
			        	<select name="s_newsletter" class="form-control">
			        		<option value="1" <?=$user->s_newsletter ? 'selected' : ''?>>Отримувати</option>
			        		<option value="0" <?=$user->s_newsletter ? '' : 'selected'?>>Не отримувати</option>
			        	</select>
			        </div>
			    </div>
				<?php } ?>

			    <div class="form-group">
			    	<label class="col-md-3 control-label">Дата реєстрації</label>
			        <div class="col-md-9">
			        	<p class="form-control-static"><?=date("d.m.Y H:i", $user->registered)?></p>
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="col-md-3 control-label">Пароль менеджера</label>
			        <div class="col-md-9">
			        	<div class="input-group">
	                        <input type="password" name="admin-password" class="form-control" required placeholder="Пароль адміністратора для підтвердження змін" />
	                        <span class="input-group-addon showHidePassword" data-for="admin-password"><i class="fa fa-eye"></i></span>
	                    </div>
			        </div>
			    </div>
			    <div class="form-group">
			    	<div class="col-md-3"></div>
			        <div class="col-md-9">
			            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Зберегти</button>
			        </div>
			    </div>
			</form>
		</div>
	</div>
</div>
<?php if($_SESSION['user']->admin) { ?>
<div class="col-md-5">
	<div class="panel panel-danger" data-sortable-id="form-stuff-1">
	    <div class="panel-heading">
	        <h4 class="panel-title"><i class="fa fa-trash-o"></i> Видалити користувача</h4>
	    </div>
	    <div class="panel-body">
	        <form action="<?=SITE_URL?>admin/wl_users/delete" method="POST" class="form-horizontal">
	            <input type="hidden" name="id" value="<?=$user->id?>">
	            <div class="form-group">
	                <label class="col-md-3 control-label">Пароль адміністратора</label>
	                <div class="col-md-9">
	                	<div class="input-group">
	                        <input type="password" name="admin-password" class="form-control" required placeholder="Пароль адміністратора для підтвердження" />
	                        <span class="input-group-addon showHidePassword" data-for="admin-password"><i class="fa fa-eye"></i></span>
	                    </div>
	                </div>
	            </div>
	            <div class="form-group">
	                <div class="col-md-3"></div>
	                <div class="col-md-9">
	                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash-o"></i> Видалити</button>
	                </div>
	            </div>
	        </form>
	    </div>
	</div>
</div>
<?php } ?>