<div id="shopping-cart-in-menu" class="shop-badge badge-icons pull-right">
    <a href="#"><i class="fa fa-shopping-cart"></i></a>
    <span class="badge badge-sea rounded-x" id="productsCount"><?= ($products) ? count($products) : 0?></span>
    <div class="badge-open">
        <ul class="list-unstyled mCustomScrollbar" data-mcs-theme="minimal-dark">
        	<?php if($products)
	    	{
	    		$this->load->js('assets/slimscroll/jquery.slimscroll.min.js');
				$this->load->js('assets/slimscroll/slimscroll.init.js');
	    		foreach ($products as $product) { ?>
            <li id="product-<?=$product->key?>">
                <?php if(!empty($product->info->admin_photo)){ ?>
                	<a href="<?=SITE_URL.$product->info->link?>"><img src="<?=IMG_PATH.$product->info->admin_photo?>" class="img-responsive product-img" alt="<?=$product->info->name?>"></a>
                <?php } ?>
                    <p class="product-title clearfix">
                        <a href="<?=SITE_URL.$product->info->link?>"><?=html_entity_decode($product->info->name)?></a>

                        <br>
						<span class="amount"><?=$product->priceFormat?> x <?=$product->quantity?></span>
					</p>
            </li>
            <?php } } else {?>
            <li>
                <h6 class="text-center cart-empty"><?=$this->text('Корзина пуста', 0)?></h6>
            </li>
            <?php } ?>
        </ul>
        <div class="subtotal">
            <div class="overflow-h margin-bottom-10">
                <span><?=$this->text('Разом', 0)?></span>
                <span class="pull-right subtotal-cost"><?=$subTotal?></span>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center">
                    <a href="<?= SITE_URL?>cart" class="btn-u btn-brd btn-brd-hover btn-u-sea-shop btn-block"><?=$this->text('До корзини', 0)?> <i class="fa fa-shopping-cart cart-white"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>