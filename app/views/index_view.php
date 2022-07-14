<link rel="stylesheet" type="text/css"
	href="<?=SERVER_URL . 'style/shop/shop.css'?>">
<script
	src="<?=SERVER_URL . 'assets/jquery/jquery-3.5.1.min.js'?>">
</script>
<script
	src="<?=SERVER_URL . 'assets/bootstrap/js/bootstrap.min.js'?>">
</script>

<style>
	.products a {
		width: 100%;
	}
</style>


<h1><?=$_SESSION['alias']->name?>
</h1>
<?=$_SESSION['alias']->text?>

<div class="search-wrapper container">
	<div class="row">
		<div class="search-item col-md-4">
			ПОШУК АКУМУЛЯТОРІВ
			ЗА ПРИЗНАЧЕННЯМ
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal1">ЗНАЙТИ</button>
		</div>
		<div class="search-item col-md-4">
			ПОШУК АКУМУЛЯТОРІВ
			ЗА ПАРАМЕТРАМИ
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal2">ЗНАЙТИ</button>
		</div>
		<div class="search-item col-md-4">
			ПОШУК АКУМУЛЯТОРІВ
			ЗА МАРКОЮ АВТО
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal3">ЗНАЙТИ</button>
		</div>
	</div>
</div>

<!-- Modal ПОШУК АКУМУЛЯТОРІВ ЗА ПРИЗНАЧЕННЯМ -->
<div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php
                if ($groups = $this->load->function_in_alias('shop', '__get_Groups')) {
                    echo '<div class="groups_products">';
                    // echo '<h3 class="title">' . $this->text('Категорії') . '</h3>';

                    echo '<ul class="container">';
                    foreach ($groups as $group) {
                        $group->link = SITE_URL . $group->link;
                        echo "<li><a href=\"{$group->link}\">{$group->name}</a></li>";
                    }
                    echo '</ul><!-- container -->';
                    echo '</div><!-- groups_products -->';
                }
                ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Search</button>
			</div>
		</div>
	</div>
</div>

<!-- ПОШУК АКУМУЛЯТОРІВ ЗА ПАРАМЕТРАМИ -->
<div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				ПОШУК АКУМУЛЯТОРІВ ЗА ПАРАМЕТРАМИ
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Search</button>
			</div>
		</div>
	</div>
</div>

<!-- ПОШУК АКУМУЛЯТОРІВ ЗА МАРКОЮ АВТО -->
<div class="modal fade" id="modal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				ПОШУК АКУМУЛЯТОРІВ ЗА МАРКОЮ АВТО
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Search</button>
			</div>
		</div>
	</div>
</div>



<!-- shop -->
<?php

$products = $this->load->function_in_alias('shop', '__get_Products', ['limit' => 28]);

if ($products) {
    $filters = $this->load->function_in_alias('shop', '__get_OptionsToGroup'); ?>
<div class="shop container">
	<div class="row">
		<div class="filter-wrapper col-md-3">
			<form>
				<div class="filter">
					<h3><?= $this->text('Акумулятори'); ?>
					</h3>
					<h6><?= $this->text('Назва товару'); ?>
					</h6>
					<input type="search" name="name"
						value="<?=$this->data->get('name')?>"
						placeholder="<?= $this->text('Акумулятор'); ?>">
				</div>

				<?php

        // $this->load->js("js/{$_SESSION['alias']->alias}/catalog.js");
        $this->load->js('js/catalog.js');

    $open = true; // []; // $filter->ids ?? true => all filters
    $type_2 = []; // $filter->ids
    $type_3 = []; // $filter->ids
    $positions = [];

    foreach ($filters as $filter) {
        $positions[] = $filter->position;
    }
    array_multisort($positions, $filters);

    foreach ($filters as $filter) {
        $for_sort = [];
        // foreach ($filter->values as $value) {
        //     $this->load->model('shop_model');
        //     $for_sort[] = $this->shop_model->tofloat($value->name);
        // }
        // array_multisort($for_sort, SORT_ASC, $filter->values);
        // unset($for_sort);

        if (!empty($filter->values)) {
            $class_i = (is_bool($open) && $open || is_array($open) && in_array($filter->id, $open)) ? 'down' : 'up';
            if (count($_GET) > 1) {
                $class_i = 'up';
            }
            if (isset($_GET[$filter->alias])) {
                $class_i = 'down';
            }
            $class_size = '';
            if (in_array($filter->id, $type_2)) {
                $class_size = 'two';
            }
            if (in_array($filter->id, $type_3)) {
                $class_size = 'three';
            }
            $count = count($filter->values);
            $i = 0; ?>
				<div class="filter">
					<i
						class="fas fa-angle-<?=$class_i?> pull-right angle"></i>
					<h6><?=$filter->name?>
					</h6>
					<div
						class="options <?=$class_size?> <?=($class_i == 'down') ? '' : 'hide'?>">
						<?php foreach ($filter->values as $value) {
                $checked = '';
                if (isset($_GET[$filter->alias])
                                 && (is_array($_GET[$filter->alias]) && in_array($value->id, $_GET[$filter->alias]) || is_numeric($_GET[$filter->alias]) && $_GET[$filter->alias] == $value->id)) {
                    $checked = 'checked';
                }
                if ($i++ > 19 && empty($_GET[$filter->alias])) {
                    echo '<div class="more hide">';
                } ?>
						<label <?=($checked) ? 'class="active"' : ''?>>
							<input type="checkbox"
								name="<?=$filter->alias?>[]"
								value="<?=$value->id?>" <?=$checked?> >
							<i
								class="far fa-<?=($checked) ? 'check-square' : 'square'?>"></i>
							<?php if (!empty($value->photo)) {
                    echo '<img src="' . $value->photo . '" alt="' . $value->name . '" title="' . $value->name . '" >';
                    if (!in_array($filter->id, $type_2) && !in_array($filter->id, $type_3)) {
                        echo $value->name;
                    }
                } else {
                    echo $value->name;
                } ?>
							<!-- <button><i class="fas fa-search"></i> <?=$this->text('Фільтрувати')?></button>
							-->
						</label>
						<br>
						<?php if ($i > 20 && empty($_GET[$filter->alias])) {
                    echo '</div>';
                }
            } ?>
						<div class="clear"></div>
						<?php if ($count > 20 && empty($_GET[$filter->alias])) { ?>
						<div class="more">
							<i class="fas fa-angle-down"></i>
							<span class="open"><?=$this->text('Ще') . ' ' . ($count - 20)?></span>
							<span class="close hide"><?=$this->text('Згорнути') . ' ' . ($count - 20)?></span>
						</div>
						<?php } ?>
					</div>
				</div>
				<?php
        }
    } ?>
				<div class="d-flex wrap actions">
					<button><i class="fas fa-search"></i> <?=$this->text('Фільтрувати')?></button>
					<button type="reset"><i class="fas fa-broom"></i> <?=$this->text('Очистити')?></button>
				</div>
			</form>
		</div>
		<?php

    echo '<div class="products col-md-9">
	<div class="row">';

    foreach ($products as $product) {
        ?>
		<div class="col-md-4">
			<div class="product-card product-list__card"><a class="product-card__frame"
					href="<?= $product->link ?>"><img
						class="product-card__image"
						src="https://opt.tyreclub.com.ua/api/public/model_photo/12324.s190.jpg"
						alt="Tech Line TL619 6.5x16 5x118 ET46 DIA71.1 BD"></a>
				<div class="product-card__price"><?= $product->price_in ?> ₴
				</div><a class="product-card__title"
					href="<?= $product->link ?>"><?= $product->name ?></a>
				<div class="product-card__bottom"><a
						class="button button__size-medium button__color-success button__type-main button--skew button--animated button--active product-card__button"
						href="<?= $product->link ?>"><span
							class="button__body"><?= $this->text('Детальніше'); ?></span></a>
					<div><button class="product-card__control"><span class="icon product-card__icon" name="heart"><svg
									width="21" height="18" viewBox="0 0 21 18" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M19.0166 2.25162C17.9472 1.16597 16.5169 0.568063 14.9889 0.568063C13.4845 0.568063 12.0699 1.14975 11.0059 2.20614L10.4567 2.75168L9.90698 2.20598C8.84296 1.14971 7.42872 0.568024 5.92473 0.568024C4.42025 0.568024 3.00577 1.14971 1.94184 2.20598C0.878066 3.26198 0.292236 4.66605 0.292236 6.15959C0.292236 7.65312 0.878066 9.05719 1.94184 10.1132L8.98139 17.1015C9.38806 17.5051 9.92209 17.707 10.4562 17.707C10.9903 17.7069 11.5244 17.5051 11.931 17.1015L18.9252 10.1588C21.1219 7.97796 21.163 4.43087 19.0166 2.25162ZM18.1877 9.42677L11.1935 16.3696C10.7869 16.7732 10.1254 16.7732 9.71881 16.3695L2.67922 9.38109C1.81242 8.52063 1.33507 7.37655 1.33507 6.15955C1.33507 4.94255 1.81242 3.79847 2.67922 2.93796C3.54619 2.07727 4.69881 1.60327 5.92473 1.60327C7.15017 1.60327 8.30255 2.07727 9.16955 2.93796L9.71953 3.48394L8.87735 4.32062C8.6738 4.52285 8.67396 4.85057 8.87763 5.05264C8.97945 5.15363 9.11279 5.20411 9.24618 5.20411C9.37973 5.20411 9.5132 5.15348 9.61501 5.05236L11.7433 2.93793C12.6103 2.07723 13.7629 1.60323 14.9888 1.60323C16.2338 1.60323 17.3993 2.09054 18.2708 2.97533C20.0198 4.75103 19.9825 7.64509 18.1877 9.42677Z">
									</path>
								</svg></span></button></div>
				</div>
				<div class="product-card__indicators"></div>
			</div>
		</div>
		<?php
    }
    echo '</div><!-- row -->
	</div><!-- container -->
	</div><!-- row -->
	</div><!-- shop container -->
	';
}

if ($groups = $this->load->function_in_alias('shop', '__get_Groups')) {
    echo '<div class="groups_products">';
    echo '<h3 class="title">' . $this->text('Категорії') . '</h3>';

    echo '<ul class="container">';
    foreach ($groups as $group) {
        $group->link = SITE_URL . $group->link;
        echo "<li><a href=\"{$group->link}\">{$group->name}</a></li>";
    }
    echo '</ul><!-- container -->';
    echo '</div><!-- groups_products -->';
}
