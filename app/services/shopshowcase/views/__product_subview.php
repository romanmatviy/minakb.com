<?php
if(empty($add_additionall_class) && isset($label) && $label == 'promo' && !empty($product->price))
{
	$add_additionall_class = true;
	if(!empty($additionall_class))
		$additionall_class .= ' promo';
	else
		$additionall_class = 'promo';
} ?>
<a href="<?=SITE_URL.$product->link?>" <?=isset($additionall_class) && $additionall_class ? 'class="'.$additionall_class.'"' : ''?>>
	<?php if(isset($label) && $label == 'promo') echo('<div class="promo">'.$this->text('Акція', 0).'</div>'); ?>
	<?php if(isset($label) && $label == 'new') echo('<div class="new">'.$this->text('Новинка', 0).'</div>'); ?>
	<?php if(isset($label) && $label == 'hit') echo('<div class="hit">'.$this->text('Хіт продажу', 0).'</div>');
	// $path = SERVER_URL.'style/'.$_SESSION['alias']->alias.'/no_photo.png';
	$path = SERVER_URL.'style/images/no_photo.png';
	if(!empty($product->catalog_photo))
		$path = IMG_PATH.$product->catalog_photo;
	?>
	<img src="<?=$path?>" alt="<?=$product->name?>" <?=(!empty($product->catalog_photo))?'':'class="w-auto-i m-auto d-block"'?>>
	<p title="<?=$product->name?>" class="name"><?=$this->data->getShortText($product->name, 100)?></p>
	<p><strong><?=$product->user_name?></strong> <span class="pull-right"><?=$product->lokacija ?? ''?></span></p>
	<div class="price">
		<?php if($product->old_price > $product->price) { ?>
		<del><?=$product->old_price_format?></del> 
		<?php } ?>
		<strong><?=$product->price_format?>/<?=$product->cina_za ?? ''?></strong>
	</div>
	<div class="actions">
		<div class="add_to_cart"><?=$this->text('Замовити', 0)?></div>
		<?php /*
		<img src="<?=SERVER_URL?>style/images/like.svg" alt="like.svg" class="add_to_like" title="<?=$this->text('Додати до улюблених', 0)?>">
		<img src="<?=SERVER_URL?>style/images/compare.svg" alt="compare.svg" class="add_to_compare" data-id="0" data-alias="<?=$product->wl_alias?>" data-content="<?=isset($product->price) ? '':'-'?><?=$product->id?>" title="<?=$this->text('Додати до порівняння', 0)?>">
		*/ ?>
	</div>
	<?php if(false && !empty($product->filters)) { ?>
	<div class="all_filters">
		<table>
			<?php foreach ($product->filters as $filter) {
				if(empty($filter->values) && empty($filter->value))
					continue;
				echo "<tr><td>{$filter->name}</td>";
				if(empty($filter->values) && !empty($filter->value) && !is_array($filter->value))
					echo '<td>'.$filter->value."</td>";
				else if(!empty($filter->value) && is_array($filter->value))
				{
					$values = [];
					foreach ($filter->value as $option)
						$values[] = $option->name;
					$values = implode(', ', $values);
					echo '<td title="'.$values.'">'.$this->data->getShortText($values, 15)."</td>";
				}
				else if(!empty($filter->values))
				{
					$values = [];
					foreach ($filter->values as $option)
						$values[] = $option->name;
					$values = implode(', ', $values);
					echo '<td title="'.$values.'">'.$this->data->getShortText($values, 15)."</td>";
				}
				echo "</tr>";
			} ?>
		</table>
	</div>
	<?php } ?>
</a>