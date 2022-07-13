<h1><?=$_SESSION['alias']->name?>
</h1>
<?=$_SESSION['alias']->text?>

<?php

$products = $this->load->function_in_alias('shop', '__get_Products', ['limit' => 28]);

if ($products) {
    echo '<div class="products container"><div class="row">';

    foreach ($products as $product) {
        // dd($product);?>
<div class="col-md-4">
	<div class="product-card product-list__card"><a class="product-card__frame"
			href="<?= $product->link ?>"><img
				class="product-card__image" src="https://opt.tyreclub.com.ua/api/public/model_photo/12324.s190.jpg"
				alt="Tech Line TL619 6.5x16 5x118 ET46 DIA71.1 BD"></a>
		<div class="product-card__price"><?= $product->price_in ?> ₴
		</div><a class="product-card__title"
			href="<?= $product->link ?>"><?= $product->name ?></a>
		<div class="product-card__bottom"><a
				class="button button__size-medium button__color-success button__type-main button--skew button--animated button--active product-card__button"
				href="<?= $product->link ?>"><span
					class="button__body"><?= $this->text('Детальніше'); ?></span></a>
			<div><button class="product-card__control"><span class="icon product-card__icon" name="heart"><svg
							width="21" height="18" viewBox="0 0 21 18" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M19.0166 2.25162C17.9472 1.16597 16.5169 0.568063 14.9889 0.568063C13.4845 0.568063 12.0699 1.14975 11.0059 2.20614L10.4567 2.75168L9.90698 2.20598C8.84296 1.14971 7.42872 0.568024 5.92473 0.568024C4.42025 0.568024 3.00577 1.14971 1.94184 2.20598C0.878066 3.26198 0.292236 4.66605 0.292236 6.15959C0.292236 7.65312 0.878066 9.05719 1.94184 10.1132L8.98139 17.1015C9.38806 17.5051 9.92209 17.707 10.4562 17.707C10.9903 17.7069 11.5244 17.5051 11.931 17.1015L18.9252 10.1588C21.1219 7.97796 21.163 4.43087 19.0166 2.25162ZM18.1877 9.42677L11.1935 16.3696C10.7869 16.7732 10.1254 16.7732 9.71881 16.3695L2.67922 9.38109C1.81242 8.52063 1.33507 7.37655 1.33507 6.15955C1.33507 4.94255 1.81242 3.79847 2.67922 2.93796C3.54619 2.07727 4.69881 1.60327 5.92473 1.60327C7.15017 1.60327 8.30255 2.07727 9.16955 2.93796L9.71953 3.48394L8.87735 4.32062C8.6738 4.52285 8.67396 4.85057 8.87763 5.05264C8.97945 5.15363 9.11279 5.20411 9.24618 5.20411C9.37973 5.20411 9.5132 5.15348 9.61501 5.05236L11.7433 2.93793C12.6103 2.07723 13.7629 1.60323 14.9888 1.60323C16.2338 1.60323 17.3993 2.09054 18.2708 2.97533C20.0198 4.75103 19.9825 7.64509 18.1877 9.42677Z">
							</path>
						</svg></span></button></div>
		</div>
		<div class="product-card__indicators"></div>
	</div>
</div>
<?php
echo '</div></div>';
    }
}

if ($groups = $this->load->function_in_alias('shop', '__get_Groups')) {
    echo '<div class="groups_products">';
    echo '<h3 class="title">' . $this->text('Категорії') . '</h3>';

    echo '<ul class="container">';
    foreach ($groups as $group) {
        $group->link = SITE_URL . $group->link;
        echo "<li><a href=\"{$group->link}\">{$group->name}</a></li>";
    }
    echo '</ul>';
    echo '</div>';
}
