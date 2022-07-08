<table class="__cart_products_list">
	<thead class="m-hide">
		<tr>
			<th><?=count($products)?></th>
			<td colspan="2"><?=$this->text('Всього товарів у кошику')?></td>
		</tr>
	</thead>
	<tbody>
		<?php if($products) foreach($products as $i => $product) { ?>
		<tr id="product-<?=$product->key?>" <?=($product->active)?'':'class="disabled"'?>>
			<td><div><?=$i + 1?></div></td>
			<td><a href="<?=SITE_URL.$product->info->link?>">
				<?php if($product->info->photo) { ?>
					<img src="<?=IMG_PATH?><?=$product->info->cart_photo ?? $product->info->admin_photo ?>" alt="<?=$product->info->name ?>">
				<?php } else
							echo '<img src="/style/images/no_image2.png">'; ?>
				</a></td>
			<td>
				<div class="name_action">
					<?php if($actions) { ?>
						<button type="button" class="delete right" value="<?=$product->key?>" title="<?=$this->text('Видалити товар з корзини')?>"><i class="fas fa-times"></i></button>
						<?php if($_SESSION['option']->useCheckBox || !$product->active) { ?>
							<div class="product-active flex h-center v-center right" title="<?=$this->text('Відкласти/додати товар до замовлення')?>">
								<input type="checkbox" value="<?=$product->key?>" <?=($product->active)?'checked':''?>>
								<i class="<?=($product->active)?'fas fa-check':'far fa-circle'?>"></i>
								<span><?=(!$product->active)?$this->text('Відкладено') : $this->text('У кошику')?></span>
							</div>
					<?php } } ?>
					<a href="<?=SITE_URL.$product->info->link?>"><strong><?=$product->info->article_show ?></strong> <?=$product->info->name ?></a>
				</div>
				<?php if(!empty($product->product_options)) {
					$p = '';
					foreach ($product->product_options as $option) {
						if(!empty($p))
							$p .= ', ';
						$p .= "{$option->name}: <strong>{$option->value_name}</strong>";
					}
					echo "<p>{$p}</p>";
					} ?>
				
				<table class="simple_product">	
					<thead>
						<tr>
							<?php if(!empty($product->storage)) { ?>
								<td><?=$this->text('Склад / Термін')?></td>
							<?php } ?>
							<td><?=$this->text('Ціна')?></td>
							<td><?=$this->text('Кількість шт.')?></td>
							<td><?=$this->text('Загальна ціна')?></td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php if(!empty($product->storage)) { ?>
							<td><?=$product->storage->storage_name?></td>
							<?php } ?>
							<th id="pricePerOne-<?= $product->key ?>"><?=$product->info->price_format ?></th>
							<td class="amount">
								<div class="flex">
									<button class="minusInCart">-</button>
									<input type="number" id="productQuantity-<?= $product->key?>" value="<?= $product->quantity?>" data-key="<?= $product->key?>" data-max="<?= $product->storage->amount_free ?? $product->info->availability?>" placeholder="1">
									<button class="plusInCart">+</button>
								</div>
							</td>
							<th id="priceSum-<?= $product->key ?>"><?=$product->info->sum_format ?></th>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<script>
	var product_active = '<?=$this->text('У кошику')?>';
	var product_disabled = '<?=$this->text('Відкладено')?>';
</script>