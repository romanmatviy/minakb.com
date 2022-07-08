<div class="table-responsive" >
	<h4 class="left"><i class="fa fa-id-card" aria-hidden="true"></i> Поточний статус: <label class="label label-<?= $cart->status_color ?? 'warning' ?>"><?= $cart->status_name ?? 'Формування' ?></label></h4>
	<a href="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/<?=$cart->id?>/print" class="btn btn-sm btn-info pull-right"><i class="fa fa-print"></i> Підготувати до друку</a>
	<?php if($cart->status_weight == 0) { ?>
		<button class="btn btn-sm btn-warning pull-right m-r-15" id="toggleNewProduct" onclick="$('#newProduct').toggle();"><i class="fa fa-plus"></i> Додати товар</button>
	<?php } $colspan = 6; ?>
	
	<div class="clearfix"></div><br>
    <table class="table table-striped table-bordered nowrap" width="100%">
	    <thead>
	    	<tr>
	    		<?php if(!empty($cart->products[0]->info->article)) { ?>
	    			<th>Артикул</th>
	    		<?php } ?>
	    		<th>Продукт</th>
	    		<?php if(!empty($cart->products[0]->storage)) { $colspan++; ?>
	    			<th>Склад</th>
	    		<?php } ?>
		    	<th>Ціна</th>
		    	<th>Кількість од.</th>
		    	<th>Разом</th>
		    	<?php if($cart->status_weight == 0){ ?><th></th><?php } ?>
	    	</tr>
	    </thead>
	    <tbody>
	    	<?php if($cart->products) foreach($cart->products as $product) { ?>
	    	<tr id="productId-<?= $product->id ?>">
	    		<?php if(!empty($product->info->article)) { ?>
	    			<td><a href="<?=SITE_URL .'admin/'. $product->info->link?>" target="_blank"><?= $product->info->article_show ?? $product->info->article ?></a></td>
	    		<?php } ?>

	    		<td>
	    			<?php if($cart->status_weight == 0 && !empty($product->info->options)) { 
	    				foreach ($product->info->options as $option) {
							if($option->toCart) { ?>
	    				<button type="button" class="btn btn-xs btn-info right" onclick="$('#edit-product-options-<?=$product->id?>').slideToggle()">Редагувати</button>
	    			<?php break; } } }
	    			if(!empty($product->info->photo) && !empty($product->info->admin_photo)) { ?>
		    			<a href="<?=SITE_URL.$product->info->link?>" class="left">
		    				<img src="<?=IMG_PATH?><?=(isset($product->info->cart_photo)) ? $product->info->cart_photo : $product->info->admin_photo ?>" alt="<?=$this->text('Фото'). ' '. $product->info->name ?>" width="90">
		    			</a>
	    			<?php }
	    			if(!empty($product->info))
	    				echo '<strong>'.$product->info->name.'</strong>';
	    			$product_options = [];
	    			if(!empty($product->product_options))
					{
						$product->product_options = unserialize($product->product_options);
						foreach ($product->product_options as $option) {
							$product_options[$option->id] = $option->value_id;
							echo "<br>{$option->name}: <strong>{$option->value_name}</strong>";
						} 
					} 
					if($cart->status_weight == 0 && !empty($product->info->options)) {
					?>
					<div class="clearfix"></div>
					<form class="form-horizontal m-t-10" id="edit-product-options-<?=$product->id?>" style="display: none;" action="<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/updateproductoptions'?>" method="post">
						<input type="hidden" name="cart" value="<?=$cart->id?>">
						<input type="hidden" name="productRow" value="<?=$product->id?>">
						<?php foreach ($product->info->options as $option) {
							if($option->toCart) { ?>
							<div class="form-group">
		                        <label class="col-md-5 control-label"><strong><?=$option->name?></strong>
		                        	<?=($option->changePrice) ? '<br><small>Впливає на ціну</small>' : ''?>
		                        </label>
		                        <div class="col-md-7">
		                        	<select name="option-<?=$option->id?>" class="form-control" required>
		                        		<option value="0">Не встановлено</option>
		                        		<?php foreach ($option->value as $value) {
		                        			$selected = '';
		                        			if(isset($product_options[$option->id]) && $product_options[$option->id] == $value->id)
		                        				$selected = 'selected';
		                        			echo "<option value='{$value->id}' {$selected}>{$value->name}</option>";
		                        		} ?>
		                        	</select>
		                        </div>
		                    </div>	
						<?php } } ?>
						<div class="form-group">
	                        <label class="col-md-5 control-label"></label>
	                        <div class="col-md-7">
	                            <button type="submit" class="btn btn-sm btn-success">Зберегти</button>
	                            <button type="button" class="btn btn-sm btn-info m-r-10" onclick="$(this).closest('form').slideUp()">Скасувати</button>
	                        </div>
	                    </div>
					</form>
					<?php } ?>
	    		</td>

	    		<?php if(!empty($product->storage)) { ?>
	    		<td width="20%">
	    			<?php echo $product->storage->storage_name;
	    				if($cart->status_weight == 0) {
	    					echo ' / '.$product->storage->amount_free . 'од. ';
	    					echo "<button onclick='showProductInvoices(this, ".$product->product_alias.', '.$product->product_id.', '.$product->id.', '.$product->storage_invoice.")' class='right'><i class='fa fa-exchange'></i></button>";
	    				} ?>
	    		</td>
	    		<?php } ?>

	    		<td id="productPrice-<?= $product->id ?>">
	    			<?= $product->price_format ?>
	    			<?php if($cart->status_weight == 0 && $_SESSION['user']->admin){ ?>
		    			<a href="#modal-edit-product-price" data-toggle="modal" class='right btn btn-xs btn-info' title="Редагувати ціну" data-product-name="<?= $product->info->article_show ?? $product->info->article ?> <?= $product->info->name ?? ''?>" data-product-price="<?=$product->price?>" data-product-row-id="<?=$product->id?>"><i class='fa fa-edit'></i></a>
		    		<?php } ?>
	    		</td>

	    		<td width="15%" >
	    			<?php if($cart->status_weight == 0){ ?>
	    			<form action="<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/changeProductQuantity'?>" method="POST">
	    				<div class="input-group">
							<input type="number" name="quantity" id="productQuantity-<?= $product->id ?>" class="form-control" value="<?= $product->quantity?>">
							<input type="hidden" name="id" value="<?= $product->id ?>">
							<?php $toHistory = '';
							if(!empty($product->info->article))
								$toHistory = $product->info->article.' ';
							if(!empty($product->info))
								$toHistory .= $product->info->name.' ';
							$toHistory .= '. Зміна кількості з '.$product->quantity.' на ';
								?>
							<input type="hidden" name="toHistory" value="<?= $toHistory ?>">
							<span class="input-group-btn">
	    						<button type="submit" class="btn btn-secondary"><i class='fa fa-save'></i></button>
	    					</span>
	    				</div>
	    			</form>
	    			<?php } else echo $product->quantity; 
	    			if(!empty($product->quantity_returned))
	    				echo "<br>Повернено: {$product->quantity_returned} од."; ?>
	    		</td>

	    		<td id="productTotalPrice-<?= $product->id ?>">
    				<?php if($product->discount)
    				{
    					echo "<del title='{$product->sumBefore_format}'>{$product->sumBefore_format}</del><br>";
    					echo "<strong title='Знижка {$product->discountFormat}'>{$product->sum_format}</strong>";
    				}
    				else
    					echo "<strong>{$product->sum_format}</strong>"; ?>
    			</td>

	    		<?php if($cart->status_weight == 0){ ?>
	    			<td><button onclick="removeProduct(<?= $product->id?>)"><i class='fa fa-remove'></i></button></td>
	    		<?php } ?>
	    	</tr>
	    	<?php } 
	    	if ($cart->subTotal != $cart->total) { ?>
	    		<tr>
	    			<td colspan="<?=$colspan?>" class="text-right" >
						Сума: <strong><?= $cart->subTotalFormat ?></strong>
					</td>
	    		</tr>
				<?php if ($cart->discount) { ?>
				<tr>
	    			<td colspan="<?=$colspan?>" class="text-right" >
						Знижка: <strong><?= $cart->discountFormat ?></strong>
					</td>
	    		</tr>
				<?php } if ($cart->shippingPrice) { ?>
				<tr>
	    			<td colspan="<?=$colspan?>" class="text-right" >
						Доставка: <strong><?= $cart->shippingPriceFormat ?></strong>
					</td>
	    		</tr>
			<?php } } ?>
	    	<tr>
	    		<td colspan="<?=$colspan?>" class="text-right" >
	    			<?php if(empty($cart->manager_comment)) { ?>
	    				<button type="button" onClick="$('#manager_comment').slideToggle()" class="btn btn-xs btn-info pull-left"><i class="fa fa-comment-o" aria-hidden="true"></i> Службовий коментар до замовлення</button>
					<?php } ?>
	    			<h4 class="m-0">До оплати: <strong id="totalPrice2"><?= $cart->totalFormat ?></strong> <?php 
                                if($cart->payed == 0) echo "<label class=\"label label-danger\">Не оплачено</label>";
                                elseif($cart->payed >= $cart->total) echo "<label class=\"label label-success\">Оплачено повністю</label>";
                                else echo "<label class=\"label label-warning\">Часткова оплата <u>{$cart->payedFormat}</u></label>"; 
                                ?></h4>
	    		</td>
	    	</tr>
	    </tbody>
    </table>

    <?php if($cart->status == 0) { ?>
    	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias.'/'?>finishAddCart" method="post" class="text-center">
    		<input type="hidden" name="cart" value="<?=$cart->id?>">
    		<button class="btn btn-lg btn-danger"><i class="fa fa-check" aria-hidden="true"></i> Сформувати замовлення</button>
    		<div class="clearfix"></div>
    	</form>
    <?php } ?>
</div>
<?php if($cart->status_weight == 0)
		require_once '_tabs-add_product.php'; ?>


<script>
	function removeProduct(id) {
		if(confirm('Ви впевнені, що хочете видалити цей товар?')){
			$.ajax({
				url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/remove'?>",
				type:"POST",
				data:{
					"id":id
				},
				success:function (res) {
					$("#totalPrice, #totalPrice2").text(res.total);
					$("#productId-"+id).remove();
				}
			})
		}
	}

	function showProductInvoices(el, product_alias, product_id, row_id, active_invoice_id) {
		$.ajax({
			url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/showProductInvoices'?>",
			type:"POST",
			data:{
				"alias": product_alias,
				"product": product_id,
				"userType": <?=$cart->user_type?>
			},
			success:function (res) {
				if(res){
					var select = $('<select/>');
					$.each(res, function (index, value) {
						var selected = (value.id == active_invoice_id) ? 'selected' : '';
						select.append("<option "+selected+" value="+value.storage+'.'+value.id+">"+value.storage_name+" / "+value.amount_free+" од. / "+value.price_out+" "+value.currency+" за од. </option>")
					})
					$(el).parent().empty().append(select).append("<button onclick='changeProductInvoice(this, "+row_id+")' class='pull-right'><i class='fa fa-save'></i></button>");
				}
			}
		})
	}

	function changeProductInvoice(el, row_id) {
		var chenge_price = confirm('Поміняти ціну в корзині?') ? true : false;
		$.ajax({
			url: "<?= SITE_URL.'admin/'. $_SESSION['alias']->alias.'/changeProductInvoice'?>",
			type:"POST",
			data: {
				"row_id": row_id,
				"storage_alias_id": $(el).parent().find('select').val(),
				"chenge_price": chenge_price
			},
			success:function (res) {
				if(res) {
					if(res.totalFormat) {
						$("#productPrice-"+row_id).text(res.priceFormat);
						$("#productTotalPrice-"+row_id).text(res.sumFormat);
						$("#totalPrice, #totalPrice2").text(res.totalFormat);
					}
					$(el).parent().empty().html(res.text+"<button onclick='showProductInvoices(this, "+res.product_alias+', '+res.product_id+', '+row_id+', '+res.invoice_id+")' class='right'><i class='fa fa-exchange'></i></button>");
				}
			}
		})
	}

</script>
<?php if($cart->status_weight == 0 && $_SESSION['user']->admin) { ?>
<div class="modal fade" id="modal-edit-product-price">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Встановити/редагути ціну для <strong class="product-name"></strong></h4>
			</div>
			<form action="<?=SERVER_URL.'admin/'.$_SESSION['alias']->alias.'/save_new_price'?>" method="post">
				<div class="modal-body">
					<label>Нова ціна</label>
					<input type="number" name="product-new-price" class="form-control" placeholder="Нова ціна товару" step="0.01" min="0" required>
					Увага! Ціна встановлюється <u>без додаткових перевірок</u> та стає <u>кінцевою за одиницю товару/тослуги.</u> Якщо була знижка, вона скасовується.
					<br>
					<br>
					<label>Пароль адміністратора для підтвердження</label>
					<div class="input-group">
                        <input type="password" name="password" title="Пароль адміністратора для підтвердження" class="form-control" required>
                        <span class="input-group-addon showHidePassword"><i class="fa fa-eye"></i></span>
                    </div>
					<input type="hidden" name="cart-id" value="<?=$cart->id?>">
					<input type="hidden" name="product-row-id" value="0">
					<input type="hidden" name="product-name" value="">
				</div>
				<div class="modal-footer">
					<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Скасувати</a>
					<button type="submit" class="btn btn-sm btn-success">Зберегти</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
window.onload = function () {
	$('#modal-edit-product-price').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget) // Button that triggered the modal
		var id = button.data('product-row-id')
		var name = button.data('product-name')
		var price = button.data('product-price')

		var modal = $(this)
		modal.find('.product-name').text(name)
		modal.find('input[name=product-name]').val(name)
		modal.find('input[name=product-new-price]').val(price)
		modal.find('input[name=product-row-id]').val(id)
	});
};
</script>
<?php } ?>