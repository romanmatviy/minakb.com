<div class="table-responsive">
    <table class="table table-striped table-bordered nowrap" width="100%">
    	<tbody>
    		<tr>
				<th>ID</th>
				<td><?= $cart->id?></td>
			</tr>
    		<tr>
				<th>Покупець</th>
				<td><?php if($cart->user) { ?>
						<a href="<?=SITE_URL?>admin/wl_users/<?= ($cart->user_email) ? $cart->user_email : $cart->user?>" class="btn btn-success btn-xs"><?= ($cart->user_name) ? $cart->user_name : 'Гість'?></a>
					<?php } else echo "Гість"; ?>
				</td>
			</tr>
    		<tr>
				<th>Тип покупця</th>
				<td><?= $cart->user_type_name?></td>
			</tr>
    		<tr>
				<th>E-mail</th>
				<td><?= $cart->user_email?></td>
			</tr>
    		<tr>
				<th>Телефон</th>
				<td><?= $cart->user_phone?></td>
			</tr>
    		<tr>
				<th>Статус</th>
				<td><?= $cart->status_name?></td>
			</tr>
    		<tr>
				<th>Загальна сума</th>
				<td><span id="totalPrice"><?= $cart->total?> </span> грн</td>
			</tr>
    		<tr>
				<th>Дата заявки</th>
				<td><?= date('d.m.Y H:i', $cart->date_add) ?></td>
			</tr>
    		<tr>
				<th>Дата останньої операції (обробки)</th>
				<td><?= $cart->date_edit > 0 ? date('d.m.Y H:i', $cart->date_edit) : '' ?></td>
			</tr>
    	</tbody>
    </table>
</div>

<button onClick="$('#uninstall-form').slideToggle()" class="btn btn-danger btn-xs">Видалити замовлення #<?=$cart->id?></button>
<?php if($_SESSION['user']->admin) { ?>
	<div id="uninstall-form" class="alert alert-danger fade in m-t-10" style="display: none;">
        <form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/delete" method="POST">
			<h4><i class="fa fa-trash pull-left"></i> Видалити замовлення #<?=$cart->id?>?</h4>
			<input type="hidden" name="id" value="<?=$cart->id?>">
			<div class="form-group">
			    <label class="col-md-3 control-label">Пароль адміністратора для підтвердження</label>
			    <div class="col-md-9">
			        <input type="password" name="password" required placeholder="Пароль адміністратора (Ваш пароль)" class="form-control">
			    </div>
			</div>
			<input type="submit" value="Видалити" class="btn btn-danger">
			<button type="button" style="margin-left:25px" onClick="$('#uninstall-form').slideToggle()" class="btn btn-info">Скасувати</button>
        </form>
  </div>
<?php } ?>