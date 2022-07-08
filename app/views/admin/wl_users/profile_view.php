<?php if(!empty($_SESSION['notify']->error) || !empty($_SESSION['notify']->success)) { ?>
<div class="row">
    <div class="col-md-3"></div>
    <!-- begin col-6 -->
    <div class="col-md-6">
    	<?php if(!empty($_SESSION['notify']->success)) { ?>
	    	<div class="alert alert-success fade in m-b-15">
				<?=$_SESSION['notify']->success?>
				<span class="close" data-dismiss="alert">&times;</span>
			</div>
		<?php } if(!empty($_SESSION['notify']->error)) { ?>
			<div class="alert alert-danger fade in m-b-15">
				<strong>Помилка!</strong>
				<?=$_SESSION['notify']->error?>
				<span class="close" data-dismiss="alert">&times;</span>
			</div>
		<?php } ?>
    </div>
</div>
<?php unset($_SESSION['notify']->success, $_SESSION['notify']->error); } ?>

<!-- begin row -->
<div class="row">
    <!-- begin col-6 -->
    <div class="col-md-6">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
            <div class="panel-heading">
                <h4 class="panel-title">Інформація про користувача</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>email користувача</td>
                            <td><?=$user->email?></td>
                        </tr>
                        <tr>
                            <td>Ім'я користувача</td>
                            <td><?=$user->name?></td>
                        </tr>
                        <tr>
                            <td>Тип користувача</td>
                            <td>
                                <?php $type = $this->db->getAllDataById('wl_user_types', $user->type); echo($type->title); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Статус акаунта</td>
                            <td>
                                <?php $status = $this->db->getAllDataById('wl_user_status', $user->status); echo($status->title); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Дата реєстрації</td>
                            <td><?=date("d.m.Y H:i", $user->registered)?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- end panel -->
    </div>
    <!-- end col-6 -->
    <div class="col-md-6">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-2">
            <div class="panel-heading">
                <h4 class="panel-title">Змінити пароль</h4>
            </div>
            <div class="panel-body">
                <form action="<?=SITE_URL?>admin/wl_users/changePassword" method="POST">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Поточний пароль</label>
                        <div class="col-md-8">
                            <input type="password" name="password" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Новий пароль</label>
                        <div class="col-md-8">
                            <input type="password" name="new-password" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Повторити новий пароль</label>
                        <div class="col-md-8">
                            <input type="password" name="re-new-password" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-4"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-sm btn-success ">Зберегти</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php

	$this->db->executeQuery("SELECT r.*, d.title_public, d.help_additionall as help FROM wl_user_register as r LEFT JOIN wl_user_register_do as d ON d.id = r.do WHERE r.user = {$user->id} ORDER BY id DESC");
	if($this->db->numRows() > 0){
		$register = $this->db->getRows('array');
	?>
	<!-- begin col-6 -->
    <div class="col-md-12">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-3">
            <div class="panel-heading">
                <h4 class="panel-title">Реєстр дій</h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered">
                <thead>
					<tr>
						<th>id</th>
						<th>Дата</th>
						<th>Дія</th>
						<th>Додатково</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($register as $r) { ?>
					<tr>
						<td><?=$r->id?></td>
						<td><?=date("d.m.Y H:i", $r->date)?></td>
						<td><?=$r->title_public?></td>
						<td title="<?=$r->help?>"><?=$r->additionally?></td>			
					</tr>
				<?php } ?>
				</tbody>
			</table>
            </div>
        </div>
        <!-- end panel -->
    </div>
    <!-- end col-6 -->
	<?php } ?>
    
</div>
<!-- end row -->