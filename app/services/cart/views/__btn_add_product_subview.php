<?php $showSelectQuantity = true;
if($_SESSION['cart']->initJsStyle) {
	if(isset($_SESSION['alias']->alias_from) && $_SESSION['alias']->alias_from != $_SESSION['alias']->id)
		$_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load[] = 'js/'.$_SESSION['alias']->alias.'/cart.js';
	else
		$_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/cart.js';
	echo '<link rel="stylesheet" type="text/css" href="'.SERVER_URL.'style/'.$_SESSION['alias']->alias.'/cart.css">';
	$_SESSION['cart']->initJsStyle = false;
}
$quantity = 1;
$productKey = $product->wl_alias . '-' . $product->id;
if(isset($product->storage_alias) && isset($product->storage_id) && $product->storage_id > 0)
	$productKey .= '-' . $product->storage_alias . '-' . $product->storage_id;
$options = '[]';
if(!empty($product->options))
{
	$options = array();
	foreach ($product->options as $key => $option) {
		if($option->toCart)
			$options[] = $option->id;
	}
	$options = '['.implode(',', $options).']';
}
?>
<div class="row">
    <div class="form-group">
    	<?php if($showSelectQuantity) { $quantity = 0; ?>
	        <label class="control-label col-md-2"><?=$this->text('Кількість', 0)?>:</label>
	        <div class="col-md-4">
	            <div class="input-group">
	                <span class="input-group-btn"><button class="btn btn-success" onclick="changeQuantity(this)">-</button></span>
	                <input type="number" class="form-control" value="1" id="productQuantity">
	                <span class="input-group-btn"><button class="btn btn-success" onclick="changeQuantity(this)">+</button></span>
	            </div>
	        </div>
        <?php } ?>
        <div class="col-md-3">
           <button type="button" class="btn addToCartBtn" onclick="cart.add(<?= "'".$productKey."', ".$quantity.", ".$options?>)"><i class="lil-add_shopping_cart"></i> <?=$this->text('Додати до корзини')?></button>
        </div>
    </div>
</div>



<div id="cart-add-result-bg">
	<div id="cart-add-result">
		<div class="product-img">
			<img src="<?=IMG_PATH.$product->photo?>" alt="<?=$product->name?>">
		</div>
		<h4 class="product-name"><?=$product->name?></h4>
		<p class="product-options"></p>
		<h4 class="product-price"><?=$product->price_format?></h4>
		<div class="actions">
			<button class="btn btn-success exit"><?=$this->text('Продовжити покупки')?></button>
			<a class="btn btn-danger" href="<?=SITE_URL.$_SESSION['alias']->alias?>"><?=$this->text('До корзини')?></a>
		</div>
	</div>
</div>

<script>
	var CART_URL = '<?=SITE_URL.$_SESSION['alias']->alias?>/';
</script>