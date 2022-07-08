<!-- begin row -->
<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-6">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
            <div class="panel-heading">
                <h4 class="panel-title">Дані нового користувача</h4>
            </div>
            <div class="panel-body">
                <form action="<?=SITE_URL?>admin/wl_users/save" method="POST" class="form-horizontal">
                	<input type="hidden" name="id" value="0">
                    <div class="form-group" title="УВАГА! На даний емейл буде надіслано пароль користувача">
                        <label class="col-md-3 control-label">email користувача</label>
                        <div class="col-md-9">
                            <input type="email" name="email" class="form-control" value="<?=$this->data->re_post('email')?>" required placeholder="email" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Номер телефону</label>
                        <div class="col-md-9">
                            <input type="number" name="phone" class="form-control" value="<?=$this->data->re_post('phone')?>" placeholder="phone" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Ім'я користувача</label>
                        <div class="col-md-9">
                            <input type="text" name="name" class="form-control" value="<?=$this->data->re_post('name')?>" required placeholder="Ім'я користувача" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Пароль</label>
                        <div class="col-md-9">
                            <select class="form-control" name="typePassword" onchange="chengePassword(this)" required>
								<option value="toMail">Згенерувати та вислати на пошту</option>
								<option value="own">Задати пароль</option>
                            </select>
                        </div>
                    </div>
                    <div id="userPassword" class="form-group" style="display:none">
                        <label class="col-md-3 control-label">Пароль користувача</label>
                        <div class="col-md-9">
                            <input type="text" name="user-password" class="form-control" value="<?=$this->data->re_post('user-password')?>" />
                        </div>

                        <script type="text/javascript">
							function chengePassword (e) {
								if(e.value == 'own'){
									$('#userPassword').slideDown('slow');
								} else {
									$('#userPassword').slideUp('slow');
								}
							}
						</script>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Тип користувача</label>
                        <div class="col-md-9">
                            <select class="form-control" name="type" onchange="chengeType(this)" required>
							<?php $types = $this->db->getAllDataByFieldInArray('wl_user_types', 1, 'active');
								foreach ($types as $type) {
									if(!$_SESSION['user']->admin && $type->id < 3)
										continue;
									echo('<option value="'.$type->id.'"');
									if($type->id == 2) echo " selected";
									echo('>'.$type->title.'</option>');
								} ?>
                            </select>
                        </div>
                    </div>
					<?php if($_SESSION['user']->admin) { ?>
                    <div id="permissions" class="form-group">
                        <label class="col-md-3 control-label">Сторінки доступу</label>
                        <div class="col-md-9">
                            <?php $permissions = $this->db->getAllData('wl_aliases');
								foreach ($permissions as $p) { ?>
									<input type="checkbox" id="<?=$p->alias?>" name="permissions[]" value="<?=$p->id?>"><label for="<?=$p->alias?>"><?=$p->alias?></label>
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
                        <label class="col-md-3 control-label">Пароль менеджера</label>
                        <div class="col-md-9">
                            <input type="password" name="admin-password" class="form-control" required placeholder="Пароль менеджера для підтвердження" />
                        </div>
                    </div>
                    <div class="form-group">
                    	<div class="col-md-3"></div>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success ">Зберегти</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- end panel -->
    </div>
    <!-- end col-6 -->
</div>