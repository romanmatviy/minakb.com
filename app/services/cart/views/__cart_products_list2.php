<div class="table__cart_products_list2 w100">
	<div class="thead m-hide">
		<div class="th photo m-hide"><?=$this->text('Фото')?></div>
		<div class="th name <?=($actions)?'':'no_actions'?>"><?=$this->text('Назва')?></div>
		<div class="th price m-hide"><?=$this->text('Ціна')?></div>
		<div class="th amount"><?=$this->text('К-сть')?></div>
		<div class="th sum"><?=$this->text('Сума')?></div>
		<?php if($actions) { ?>
		<div class="th actions"><?=$this->text('Дія')?></div>
		<?php } ?>
	</div>
	<?php if($products) foreach($products as $i => $product) { ?>
	<div class="tr <?=($product->active)?'':'disabled'?>" id="product-<?=$product->key?>" <?=($product->active)?'':'title="Товар відкладено"'?>">
		<div class="td photo"><a href="<?=SITE_URL.$product->info->link?>">
			<?php if($product->info->photo) { ?>
				<img src="<?=IMG_PATH?><?=$product->info->cart_photo ?? $product->info->admin_photo ?>" alt="<?=$product->info->name ?>">
			<?php } else
						echo '<img src="/style/images/no_image2.png">'; ?>
			</a>
		</div>
		<div class="td name <?=($actions)?'':'no_actions'?>">
			<a href="<?=SITE_URL.$product->info->link?>"><?=$product->info->name ?></a>
			<p>Артикул: <strong><?=$product->info->article_show ?></strong></p>
			<?php if(!empty($product->product_options)) {
			$p = '';
			foreach ($product->product_options as $option) {
				if(!empty($p))
					$p .= ', ';
				$p .= "{$option->name}: <strong>{$option->value_name}</strong>";
			}
			echo "<p>{$p}</p>";
			} ?>
		</div>
		<div class="td price m-hide" id="pricePerOne-<?= $product->key ?>"><?=$product->info->price_format ?></div>
		<div class="td amount">
			<span class="minusInCart btn btn-info">-</span>
			<?php $max = $product->storage->amount_free ?? -1;
			if($max < 0 && $product->info->useAvailability)
				$max = $product->info->availability; ?>
			<input type="number" id="productQuantity-<?= $product->key?>" value="<?= $product->quantity?>" data-key="<?= $product->key?>" data-max="<?= $max ?>" placeholder="1" class="form-control m-0i">
			<span class="plusInCart btn btn-info">+</span>
		</div>
		<div class="td sum" id="priceSum-<?= $product->key ?>"><?=$product->info->sum_format ?></div>
		<?php if($actions) { ?>
		<div class="td actions">
			<button type="button" class="delete pull-right" value="<?=$product->key?>" title="<?=$this->text('Видалити товар з корзини')?>"><i class="fas fa-times"></i></button>
			<div class="product-active <?=($product->active)?'':'postpone'?> d-flex h-center v-center pull-right" title="<?=$this->text('Відкласти/додати товар до замовлення')?>">
				<input type="checkbox" value="<?=$product->key?>" <?=($product->active)?'checked':''?>>
				<span><?=(!$product->active)?$this->text('Замовити') : $this->text('Відкласти')?></span>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>

<script>
	var product_active = '<?=$this->text('Відкласти')?>';
	var product_disabled = '<?=$this->text('Замовити')?>';
</script>