<div class="panel">
	<div class="panel-body">
		<div class="col-md-4">
			<p>Покупець: <strong><a href="<?php echo SITE_URL ?>admin/wl_users/<?php echo $cart->user_email ?>" class="btn btn-success btn-xs">#<?php echo $cart->user ?>. <?php echo $cart->user_name ?></a> (<?php echo $cart->user_type_name ?>)</strong> <?=!empty($cart->user_language) ? "Мова користувача: <strong>{$cart->user_language}</strong>" : ''?></p>
			<p><strong><a href="mailto:<?php echo $cart->user_email ?>"><?php echo $cart->user_email ?></a> <?php echo $cart->user_phone ?></strong></p>
			<?php echo ($cart->manager) ? ' <hr style="margin:5px 0">Менеджер: <strong><a href="' . SITE_URL . 'admin/wl_users/' . $cart->manager_email . '">' . $cart->manager_name . '</a></strong>' : '' ?>
		</div>
		<div class="col-md-4">
			<p>Поточний статус: <strong><?php echo $cart->status_name ?? 'Формування' ?></strong></p>
			<p>Загальна сума замовлення: <strong><?php echo $cart->totalFormat ?></strong></p>
			<p>Оплата: <strong>
					<?php if ($cart->payed == 0) {
						echo "Не оплачено";
					} elseif ($cart->payed >= $cart->total) {
						echo "Оплачено повністю ({$cart->payed} грн)";
					} else {
						echo "Часткова оплата <u>{$cart->payedFormat}</u>";
					} ?>
				</strong></p>
		</div>
		<div class="col-md-4">
			<p>Створено: <strong><?php echo date('d.m.Y H:i', $cart->date_add) ?></strong>
				<?php if ($_SESSION['user']->admin) { ?>
					<button onClick="$('#uninstall-form').slideToggle()" class="btn btn-danger btn-xs right"><i class="fa fa-trash"></i> Видалити замовлення</button>
				<?php } ?>
			</p>
			<p>Остання операція: <strong><?php echo $cart->date_edit > 0 ? date('d.m.Y H:i', $cart->date_edit) : 'очікує' ?></strong>
			</p>
			<?php if (isset($cart->date_1c)) { ?>
				<p>Синхронізація з 1с: <strong><?php echo $cart->date_1c > 0 ? date('d.m.Y H:i', $cart->date_1c) : 'очікує' ?></strong></p>
			<?php } ?>
		</div>
	</div>

	<?php if ($_SESSION['user']->admin) { ?>
		<div class="col-md-12">
			<div id="uninstall-form" class="alert alert-danger fade in m-t-10" style="display: none;">
				<form action="<?php echo SITE_URL . 'admin/' . $_SESSION['alias']->alias ?>/delete" method="POST">
					<h4><i class="fa fa-trash pull-left"></i> Видалити замовлення #<?php echo $cart->id ?>?</h4>
					<?php if (!empty($cart->products[0]->storage)) { ?>
						<p><label><input type="checkbox" name="storage_cancel" value="1" checked> Повернути зарезервований/списаний товар на склад</label></p>
						<p><label><input type="checkbox" name="payment_cancel" value="1" checked> Повернути зарезервовані/списані кошти</label></p>
					<?php } ?>
					<input type="hidden" name="id" value="<?php echo $cart->id ?>">
					<div style="max-width: 800px">
						<div class="form-group clearfix">
							<label class="col-md-4 control-label">Пароль адміністратора для підтвердження</label>
							<div class="col-md-8">
								<div class="input-group">
			                        <input type="password" name="password" required placeholder="Пароль адміністратора (Ваш пароль)" class="form-control">
			                        <span class="input-group-addon showHidePassword"><i class="fa fa-eye"></i></span>
			                    </div>
							</div>
						</div>
						<div class="m-t-10 text-center">
							<input type="submit" value="Видалити" class="btn btn-danger">
							<button type="button" style="margin-left:25px" onClick="$('#uninstall-form').slideToggle()" class="btn btn-info">Скасувати</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	<?php } ?>
</div>

<div class="panel">
	<div class="panel-body">
		<?php require_once 'tabs/_tabs-products.php'; ?>
	</div>
</div>

<div class="panel" id="manager_comment" <?php echo empty($cart->manager_comment) ? 'style="display:none"' : '' ?>>
	<div class="panel-body">
		<legend><i class="fa fa-comment-o" aria-hidden="true"></i> Службовий коментар до замовлення</legend>
		<textarea data-cart="<?php echo $cart->id ?>" class="form-control" rows="3"><?php echo $cart->manager_comment ?></textarea>
	</div>
</div>

<?php if (!empty($cart->comment) && $this->data->uri(3) != 'edit-shipping') { ?>
	<div class="panel">
		<div class="panel-body">
			<legend><i class="fa fa-commenting" aria-hidden="true"></i> Коментар (побажання) клієнта до замовлення</legend>
			<p><?php echo $cart->comment ?></p>
		</div>
	</div>
<?php } ?>

<div class="row">
	<div class="col-md-6">
		<?php if (!empty($cart->payment->name)) { ?>
			<div class="panel">
				<div class="panel-body">
					<form action="<?php echo SITE_URL ?>admin/<?php echo $_SESSION['alias']->alias ?>/send_mail" class="right" onsubmit="$('#saveing').css('display', 'block');">
						<input type="hidden" name="cart_id" value="<?php echo $cart->id ?>">
						<button class='btn btn-warning btn-xs' title="<?php echo $cart->user_email ?>"><i class="fa fa-envelope" aria-hidden="true"></i> Відправити повторно платіжні реквізити</button>
					</form>
					<?php
					echo '<legend><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Оплата</legend></p>';
					echo '<p>Платіжний механізм: <b>' . $cart->payment->name . '</b></p>';
					echo "<p>{$cart->payment->info}</p>";
					if (!empty($cart->payment->admin_link)) {
						echo "<a href='{$cart->payment->admin_link}' class='btn btn-info btn-xs'><i class=\"fa fa-external-link\" aria-hidden=\"true\"></i> Повна інформація по оплаті</a>";
					}
					?>
				</div>
			</div>
		<?php }
		if ($cart->payed < $cart->total && empty($cart->payment_alias) && $this->data->uri(3) != 'edit-shipping') { ?>
			<div class="panel">
				<div class="panel-body">
					<legend><i class="fa fa-credit-card-alt" aria-hidden="true"></i> Внести оплату</legend>
					<table class="table table-striped table-bordered nowrap" width="100%">
						<form action="<?php echo SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/addPayment' ?>" onsubmit="return confirm('Внести оплату')" method="POST" class="form-horizontal">
							<input type="hidden" name="cart" value="<?php echo $cart->id ?>">
							<tbody>
								<tr>
									<th>Механізм</th>
									<td>
										<select name="status" class="form-control" required>
											<?php foreach ($cart->paymentsMethod as $method) {
												if (empty($method->wl_alias)) { ?>
													<option value="<?php echo $method->id ?>"><?php echo $method->name ?></option>
											<?php }
											} ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>Сума (у валюті корзини)</th>
									<td>
										<input name="amount" type="number" min="0.01" step="0.01" class="form-control" value="<?php echo round($cart->total - $cart->payed, 2) ?>" required />
									</td>
								</tr>
								<tr>
									<th>Коментар</th>
									<td><textarea name="comment" class="form-control" rows="5" placeholder="<?=!empty($cart->user_language) ? "Мова користувача: {$cart->user_language}" : ''?>"></textarea></td>
								</tr>
								<tr>
									<th></th>
									<td>
										<button type="submit" class="btn btn-md btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Внести оплату</button>
									</td>
								</tr>
							</tbody>
						</form>
					</table>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="col-md-6">
		<?php if (($cart->shipping_id || !empty($cart->shipping_info)) && $this->data->uri(3) != 'edit-shipping') { ?>
			<div class="panel">
				<div class="panel-body">
					<?php echo ($cart->action == 'new') ? "<a href='" . SITE_URL . "admin/{$_SESSION['alias']->alias}/{$cart->id}/edit-shipping#cart' class='btn btn-primary btn-xs pull-right'><i class=\"fa fa-pencil\"></i> редагувати</a>" : '' ?>
					<legend><i class="fa fa-truck" aria-hidden="true"></i> Доставка</legend>
					<?php
					if (!empty($cart->shipping->name)) {
						echo "<p>Служба доставки: <b>{$cart->shipping->name}</b> </p>";
					}
					if (!empty($cart->shipping->text)) {
						$cart->shipping->text = htmlspecialchars_decode($cart->shipping->text);
						echo "<p>{$cart->shipping->text}</p>";
					}
					if (!empty($cart->shipping_info['recipientName'])) {
						echo "<p>Отримувач: <b>{$cart->shipping_info['recipientName']}</b> </p>";
					}
					if (!empty($cart->shipping_info['recipientPhone'])) {
						echo "<p>Контактний телефон: <b>{$cart->shipping_info['recipientPhone']}</b> </p>";
					}
					if($cart->status_weight == 0 && ($cart->shipping->pay >= 0 || $cart->shipping->pay_action == 'by_manager')) {	?>
						<form action="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/set__shipping_pay" method="POST" class="row m-b-10">
							<input type="hidden" name="cart_id" value="<?=$cart->id?>">
							<div class="form-group">
								<label class="col-md-3 control-label text-right">Вартість доставки</label>
								<div class="col-md-9">
									<div class="input-group">
										<input type="number" name="shipping_price" class="form-control" value="<?= (is_numeric($cart->shippingPrice)) ? $cart->shippingPrice : $cart->shipping->price ?>" min="0" required>
										<span class="input-group-btn">
											<button type="submit" class="btn btn-info">Зберегти</button>
										</span>
									</div>
								</div>
							</div>
						</form>
					<?php } else if (!empty($cart->shippingPrice)) {
						echo "<p>Вартість доставки: <b>{$cart->shippingPriceFormat}</b> </p>";
					} ?>
					<div class="row">
						<div class="form-group">
							<label class="col-md-3 control-label text-right">ТТН доставки</label>
							<div class="col-md-9">
								<div class="input-group">
									<input type="text" class="form-control" data-cart="<?php echo $cart->id ?>" id="shipping_ttn" value="<?php echo $cart->ttn ?>" placeholder="ТТН доставки">
									<span class="input-group-btn">
										<?php $showTTNmodal = 0;
										if ($cart->status_weight < 20 && $cartStatuses) {
											foreach ($cartStatuses as $status) {
												if ($status->weight >= 20 && $status->weight < 30) {
													$showTTNmodal = 1;
													break;
												}
											}
										}
										?>
										<button type="submit" class="btn btn-secondary" onclick="presaveTTN(<?php echo $showTTNmodal ?>)">Зберегти</button>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } elseif (($cart->action == 'new' || $cart->status == 0) && (empty($cart->shipping_id) || $this->data->uri(3) == 'edit-shipping')) {
			if ($shippings = $this->cart_model->getShippings(array('active' => 1))) { ?>
			<div class="panel" id="cart-checkout">
				<div class="panel-body">
					<legend><i class="fa fa-truck" aria-hidden="true"></i> Доставка</legend>
					<?php if (empty($cart->shipping_id)) {
						$userShipping = $this->cart_model->getUserShipping($cart->user);
					} else {
						$userShipping = new stdClass();
						$userShipping->method_id = $cart->shipping_id;
						$userShipping->info = $cart->shipping_info;
						$userShipping->city = $userShipping->department = $userShipping->address = '';
						if (!empty($userShipping->info)) {
							foreach ($userShipping->info as $key => $value) {
								$userShipping->$key = $value;
							}
						}
					}
					$shippingTypeFields = [];
					$shippingWlAlias = 0;
					if($userShipping->method_id)
					{
						$shipping = $this->cart_model->getShippings(['id' => $userShipping->method_id]);
						$shippingTypeFields = $shipping->type_fields;
			            $shippingWlAlias = $shipping->wl_alias;
					}
					else
					{
						$shippingTypeFields = $shippings[0]->type_fields;
			            $shippingWlAlias = $shippings[0]->wl_alias;
            			$shippingInfo = $shippings[0]->info;
					}
					echo '<form action="' . SITE_URL . $_SESSION['alias']->alias . '/set__shippingToOrder" method="post">';
					echo '<input type="hidden" name="order_id" value="' . $cart->id . '">';
					echo '<input type="hidden" name="redirect" value="admin/' . $_SESSION['alias']->alias . '/' . $cart->id . '">';
					include_once APP_PATH . 'services/cart/views/__shippings_subview.php';
					if ($this->data->uri(3) == 'edit-shipping') {
						echo '<a href="' . SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/' . $cart->id . '" class="btn btn-warning m-r-5" style="display: inline-block;"><i class="fa fa-undo" aria-hidden="true"></i> Назад</a>';
					}
					echo '<button type="submit" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>';
					echo "</form>";

					echo '<link rel="stylesheet" type="text/css" href="' . SERVER_URL . 'style/' . $_SESSION['alias']->alias . '/cart.css">';
					echo '<link rel="stylesheet" type="text/css" href="' . SERVER_URL . 'style/' . $_SESSION['alias']->alias . '/checkout.css">';
					echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">';
					$this->load->js(['assets/jquery-ui/1.12.1/jquery-ui.min.js', 'assets/jquery.mask.min.js', 'js/' . $_SESSION['alias']->alias . '/cities.js', 'js/' . $_SESSION['alias']->alias . '/checkout.js']);
					$this->load->js_init("$('#cart').find('input, textarea').addClass('form-control m-b-10');");
					?>
				</div>
			</div>
		<?php }
		} ?>
	</div>
</div>

<?php if ($cart->status > 0 && $cart->status_weight < 90 && $this->data->uri(3) != 'edit-shipping') { ?>
	<div class="panel">
		<div class="panel-body">
			<?php include_once 'tabs/_tabs-history.php'; ?>
		</div>
	</div>
<?php } ?>

<div class="panel">
	<div class="panel-body">
		<legend><i class="fa fa-history" aria-hidden="true"></i> Історія замовлення</legend>
		<div class="table-responsive">
			<table class="table table-striped table-bordered nowrap" width="100%">
				<thead>
					<tr>
						<th>Внесено</th>
						<th>Статус</th>
						<th>Коментар</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo date('d.m.Y H:i', $cart->date_add) ?> <br> <?php echo $cart->user_name ?></td>
						<td>Заявка</td>
						<td><?php echo $cart->comment ?> </td>
					</tr>
					<?php if ($cart->history) {
						foreach ($cart->history as $history) { ?>
							<tr>
								<td><?php echo date('d.m.Y H:i', $history->date) ?> <br> <?php echo $history->user_name ?></td>
								<td><?php echo $history->status_name ?></td>
								<td>
									<span id="comment-<?php echo $history->id ?>">
										<?php echo $history->comment ?>
									</span>
									<span>
										<?php echo ($history->user > 0 && $history->status > 1) ? "<button data-toggle='modal' data-target='#commentModal' data-comment='{$history->comment}' data-id='{$history->id}' class='right'><i class='fa fa-pencil-square-o'></i></button>" : '' ?>
									</span>
								</td>
							</tr>
					<?php }
					} ?>
				</tbody>
			</table>
		</div>
	</div>
</div>