<?php 
$get_parametras = array();
foreach ($_GET as $key => $value) {
	if($key != 'request')
	{
		$get_parametras[$key] = $value;
	}
}

function get($get_parametras, $key, $value = '')
{
	if($value != '' && $value[0] == ']')
	{
		$value = substr($value, 1);
		$get_parametras[$key] = array();
		array_push($get_parametras[$key], $value);
	}
	else
	{
		$get_parametras[$key] = $value;
	}
	
	$link = '?';
	foreach ($get_parametras as $key => $value) {
		if(is_array($value))
		{
			foreach ($value as $subvalue) if($subvalue != '') {
				$link .= $key .'[]='.$subvalue.'&';
			}
		}
		elseif($value != '') {
			$link .= $key .'='.$value.'&';
		}
	}
	$link = substr($link, 0, -1);
	if($link == '') return SITE_URL.$_GET['request'];
	return $link;
}
?>
<div class="row">
	<div class="col-md-3 filter-by-block md-margin-bottom-60">
		<h1><?=$this->text('Фільтр')?></h1>
		<form>
		<input type="hidden" name="show" value="<?=(isset($_GET['show'])) ? $this->data->get('show') : ''?>">
		<input type="hidden" name="sort" value="<?=(isset($_GET['sort'])) ? $this->data->get('sort') : ''?>">
		<input type="hidden" name="per_page" value="<?=(isset($_GET['per_page'])) ? $this->data->get('per_page') : ''?>">
		<?php $filters = $this->shop_model->getOptionsToGroup($group); 
		if($filters)
		{
			foreach ($filters as $filter) if(!empty($filter->values)) {
		?>
		<div class="panel-group" id="accordion">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
							<?=$filter->name?>
							<i class="fa fa-angle-down"></i>
						</a>
					</h2>
				</div>
				<div id="collapseOne" class="panel-collapse collapse in">
					<div class="panel-body">
						<ul class="list-unstyled checkbox-list">
							<?php foreach ($filter->values as $value) { 
								$checked = '';
								if(isset($_GET[$filter->alias]) && is_array($_GET[$filter->alias]) && in_array($value->id, $_GET[$filter->alias])) $checked = 'checked';
								?>
								<li>
									<label class="checkbox">
										<input type="checkbox" name="<?=$filter->alias?>[]" value="<?=$value->id?>" <?=$checked?> />
										<i></i>
										<?=$value->name?>
										<small><a href="<?=get($get_parametras, $filter->alias, ']'.$value->id)?>">(<?=$value->count?>)</a></small>
									</label>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div><!--/end panel group-->
		<?php
			}
		}

		?>

		<button type="submit" class="btn-u btn-brd btn-brd-hover btn-u-lg btn-u-sea-shop btn-block"><?=$this->text('Відфільтрувати')?></button>
		</form>
	</div>

	<div class="col-md-9">
		<div class="row margin-bottom-5">
			<div class="col-sm-4 result-category">
				<h2><?=$this->text('Знайдено')?></h2>
				<small class="shop-bg-red badge-results">
					<?php
					if(isset($_SESSION['option']->paginator_total))
					{
						echo($_SESSION['option']->paginator_total);
						if($_SESSION['option']->paginator_total == 0) echo('');
						elseif($_SESSION['option']->paginator_total == 1) echo(' товар');
						elseif($_SESSION['option']->paginator_total > 1 && $_SESSION['option']->paginator_total < 5) echo(' товари');
						else echo(' товарів');
					} else echo(0);
					?>
				</small>
			</div>
			<div class="col-sm-8">
				<ul class="list-inline clear-both">
					<li class="grid-list-icons">
						<a href="<?=get($get_parametras, 'show')?>"><i class="fa fa-th"></i></a>
						<a href="<?=get($get_parametras, 'show', 'list')?>"><i class="fa fa-th-list"></i></a>
					</li>
					<li class="sort-list-btn">
						<h3><?=$this->text('Сортувати за')?> :</h3>
						<div class="btn-group">
							<?php $sort = array('' => 'Авто', 'price_up' => 'Ціна ↑', 'price_down' => 'Ціна ↓', 'article' => 'Артикулом'); ?>
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<?=(isset($_GET['sort'])) ? $sort[$_GET['sort']] : $sort['']?> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<?php foreach ($sort as $key => $value) { ?>
									<li><a href="<?=get($get_parametras, 'sort', $key)?>"><?=$value?></a></li>
								<?php } ?>
							</ul>
						</div>
					</li>
					<li class="sort-list-btn">
						<h3>Кількість :</h3>
						<div class="btn-group">
							<?php $sort = array('' => 30, 20 => 20, 10 => 10); ?>
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<?=(isset($_GET['per_page'])) ? $sort[$_GET['per_page']] : 30?> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<?php foreach ($sort as $key => $value) { ?>
									<li><a href="<?=get($get_parametras, 'per_page', $key)?>"><?=$value?></a></li>
								<?php } ?>
							</ul>
						</div>
					</li>
				</ul>
			</div>
		</div><!--/end result category-->

		<div class="filter-results">
			<?php if(isset($_GET['show']) && $_GET['show'] == 'list' && !empty($products)) { foreach ($products as $product) { ?>
				<div class="list-product-description product-description-brd margin-bottom-30">
					<div class="row">
						<?php if($product->photo != '') { ?>
							<div class="col-sm-4">
								<a href="<?=SITE_URL.$product->link?>">
									<img class="img-responsive sm-margin-bottom-20" src="<?=IMG_PATH.$product->g_photo?>" alt="<?=$product->article?> <?=$product->name?>">
								</a>
							</div>
						<?php } ?>
						<div class="col-sm-<?=($product->photo != '')?8:12?> product-description">
							<div class="overflow-h margin-bottom-5">
								<ul class="list-inline overflow-h">
									<li><h4 class="title-price"><a href="<?=SITE_URL.$product->link?>"><?=$product->name?></a></h4></li>
									<?php if(isset($product->options['1-vyrobnyk']) && $product->options['1-vyrobnyk']->value != '') { ?>
										<li><span class="gender text-uppercase"><?=$product->options['1-vyrobnyk']->value?></span></li>
									<?php } ?>
								</ul>										
								<div class="margin-bottom-10">
									<span class="title-price margin-right-10">$<?=$product->price?></span>
									<span class="title-price shop-red"><?=$product->price * $currency?> грн</span>
								</div>
								<p class="margin-bottom-20"><?=$product->list?></p>
								<a href="<?=SITE_URL.$product->link?>" class="btn-u btn-u-sea-shop"><?=$this->text('Детальніше')?></a>
							</div>
						</div>
					</div>
				</div>
			<?php } } else { ?>
				<div class="row illustration-v2 margin-bottom-30">
					<?php 
					$i = 0;
					if(!empty($products))
					foreach ($products as $product) {
						if($i % 3 == 0)
						{
							echo('</div>');
							echo('<div class="row illustration-v2 margin-bottom-30">');
						}
					?>
						<div class="col-md-4">
							<div class="product-img product-img-brd">
								<?php if($product->photo != '') { ?>
									<a href="<?=SITE_URL.$product->link?>">
										<img class="full-width img-responsive" src="<?=IMG_PATH.$product->g_photo?>" alt="<?=$product->article?> <?=$product->name?>">
									</a>
								<?php } ?>
								<a class="product-review" href="<?=SITE_URL.$product->link?>" title="Артикул: <?=$product->article?> <?=$product->name?>">#<?=$product->article?></a>
								<a class="add-to-cart" href="<?=SITE_URL.$product->link?>"><i class="fa fa-shopping-cart"></i><?=$this->text('Детальніше')?></a>
								<!-- Все, що додано менш ніж 2 тижні тому - новинка -->
								<?php if (($product->date_add + 1209600) > time()) { ?>
									<div class="shop-rgba-dark-green rgba-banner"><?=$this->text('Новинка!')?></div>
								<?php } ?>
							</div>
							<div class="product-description product-description-brd margin-bottom-30">
								<div class="overflow-h margin-bottom-5">
									<div class="pull-left">
										<h4 class="title-price"><a href="<?=SITE_URL.$product->link?>"><?=$product->name?></a></h4>
										<?php if(isset($product->options['1-vyrobnyk']) && $product->options['1-vyrobnyk']->value != '') { ?>
											<span class="gender text-uppercase"><?=$product->options['1-vyrobnyk']->value?></span>
										<?php } ?>
									</div>
									<div class="product-price">
										<span class="title-price pull-left">$<?=$product->price?></span>
										<span class="title-price shop-red"><?=$product->price * $currency?> грн</span>
									</div>
								</div>
							</div>
						</div>
					<?php
						$i++;
					}
					?>
				</div>
			<?php } ?>
		</div><!--/end filter resilts-->

		<div class="text-center">
			<?php
            $this->load->library('paginator');
            echo $this->paginator->get();
            ?>
		</div><!--/end pagination-->
	</div>
</div><!--/end row-->