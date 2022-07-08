<?php /* <link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/compares.css'?>"> */ ?>
<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/compares.css'?>">

<main class="container">
	<!-- <div class="row">
		<h1><?=$_SESSION['alias']->name?></h1>
	</div> -->

<?php if($compares) {
	$items = $keys = $groups = [];
	$keys['name'] = $this->text('Назва');
	foreach ($compares as $item) {
		if($item->content > 0)
		{
			if($product = $this->load->function_in_alias($item->alias, '__get_Product', $item->content))
			{
				if($product->options)
					foreach ($product->options as $option) {
						if(empty($keys[$option->id]))
							$keys[$option->id] = $option->name;
					}
				$product->compareId = $item->id;
				$product->compareGroup = 0;
				if($_SESSION['option']->groupBy != 'alias' && !empty($product->parents))
                {
                    if($_SESSION['option']->groupBy == 'grandParent')
                    {
                        $product->compareGroup = $product->parents[0]->id;
                        if(empty($groups[$product->compareGroup]))
							$groups[$product->parents[0]->id] = $product->parents[0]->name;
                    }
                    if($_SESSION['option']->groupBy == 'parent')
                    {
                        $product->compareGroup = $product->group;
                        if(empty($groups[$product->compareGroup]))
							for($i = count($product->parents) - 1; $i >= 0; $i--)
							{
								if ($product->parents[$i]->id = $product->group) {
									$groups[$product->group] = $product->parents[$i]->name;
									break;
								}
							}
                    }
                }
                if($product->compareGroup == 0 && empty($groups[0]))
					$groups[0] = $item->alias_name;
				$items[] = $product;
			}
		}
		else if($item->content < 0)
		{
			if($group = $this->load->function_in_alias($item->alias, '__get_Group', -$item->content))
			{
				if($group->options = $this->load->function_in_alias($item->alias, '__get_OptionsToGroup', -$item->content))
					foreach ($group->options as $option) {
						if(empty($keys[$option->id]))
							$keys[$option->id] = $option->name;
					}
				$group->compareId = $item->id;
				$group->compareGroup = 0;
				if($_SESSION['option']->groupBy != 'alias' && !empty($group->parents))
				{
					if($_SESSION['option']->groupBy == 'grandParent')
					{
						$group->compareGroup = $group->parents[0]->id;
						if(empty($groups[$group->compareGroup]))
							$groups[$group->parents[0]->id] = $group->parents[0]->name;
					}
					if($_SESSION['option']->groupBy == 'parent')
					{
						$group->compareGroup = $group->parent;
						if(empty($groups[$group->compareGroup]))
							for($i = count($group->parents) - 1; $i >= 0; $i--)
							{
								if ($group->parents[$i]->id = $group->parent) {
									$groups[$group->parent] = $group->parents[$i]->name;
									break;
								}
							}
					}
				}
				if($group->compareGroup == 0 && empty($groups[0]))
					$groups[0] = $item->alias_name;
				$items[] = $group;
			}
		}
	}
	if(!empty($groups))
		foreach ($groups as $compareGroup => $compareGroupName) { ?>
				<h1><?=$this->text('Порівнюємо').' '.$compareGroupName?></h1>
				<table id="compare_table">
					<?php foreach ($keys as $key => $keyName) { ?>
						<tr>
							<?php
							echo $key == 'name' ? '<th></th>' : "<td>{$keyName}</td>";
							foreach ($items as $item) {
								if($compareGroup != $item->compareGroup)
									continue;
								echo $key == 'name' ? '<th class="compare-'.$item->compareId.'">' : '<td class="compare-'.$item->compareId.'">';
								if(is_numeric($key))
								{
									if(!empty($item->options))
										foreach ($item->options as $filter) {
											if($filter->id == $key)
											{
												if(empty($filter->values) && empty($filter->value))
													break;
												if(empty($filter->values) && !empty($filter->value) && !is_array($filter->value))
													echo $filter->value;
												else if(!empty($filter->value) && is_array($filter->value))
												{
													$values = [];
													foreach ($filter->value as $option)
														$values[] = $option->name;
													echo implode(', ', $values);
												}
												else if(!empty($filter->values))
												{
													$values = [];
													foreach ($filter->values as $option)
														$values[] = $option->name;
													echo implode(', ', $values);
												}
												break;
											}
										}
								}
								else if(!empty($item->$key))
								{
									if($key == 'name' && !empty($item->link))
									{
										echo '<i class="fas fa-times-circle compare_cancel" data-id="'.$item->compareId.'"></i>';
										if(!empty($item->group_photo))
											echo "<a href=\"".SITE_URL.$item->link."\" class='box'><img src=\"".IMG_PATH.$item->group_photo."\" alt='{$item->name}' title='{$item->name}'></a><br>";
										echo "<a href=\"".SITE_URL.$item->link."\">{$item->name}</a>"; ?>
										
										<div class="actions">
											<?php if(!empty($item->price) && ($item->storage_1 || $item->storage_2)) { ?>
											<div class="hexa add_to_cart" title="<?=$this->text('Купити', 0)?>" data-product-key="<?=$item->wl_alias.'-'.$item->id?>"><?=$item->price?> грн </div>
											<img src="<?=SERVER_URL?>style/images/like.svg" alt="like.svg" title="<?=$this->text('Додати до улюблених', 0)?>">
											<img src="<?=SERVER_URL?>style/images/cart-product.svg" alt="add_to_cart.svg" title="<?=$this->text('Купити', 0)?>" class="add_to_cart" data-product-key="<?=$item->wl_alias.'-'.$item->id?>">
										<?php } else { ?>
											<button class="to-modal" data-modal="#getPrice" data-name="<?=$item->article_show.' '.$item->name?>">Уточнити ціну</button>
											<img src="<?=SERVER_URL?>style/images/like.svg" alt="like.svg" title="<?=$this->text('Додати до улюблених', 0)?>">
										<?php } ?>
										</div>
									<?php }
									else
										echo $item->$key;
								}
								echo $key == 'name' ? '</th>' : "</td>";
							} ?>
						</tr>
					<?php } ?>
				</table>
	<?php }
}
require_once APP_PATH.'views/@commons/__modals_addProductResult.php';
?>