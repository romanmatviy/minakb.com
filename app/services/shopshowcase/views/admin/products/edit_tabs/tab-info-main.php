<div class="row">
	<div class="col-md-6">
		<div class="row m-b-10">
			<div class="col-md-5 text-right">Id на сайті</div>
		    <div class="col-md-7"> <strong><?=$product->id?></strong> </div>
	    </div>
	    <?php if(isset($product->id_1c)) { ?>
		    <div class="row m-b-10">
				<div class="col-md-5 text-right">Id 1c</div>
			    <div class="col-md-7"> <strong><?=$product->id_1c?></strong> </div>
		    </div>
		<?php } ?>
	    <div class="row m-b-10">
			<div class="col-md-5 text-right">Власна адреса посилання</div>
		    <div class="col-md-7"> <a href="<?=SITE_URL.$product->link?>"><?=$url.'/'?><strong><?=$product->alias?></strong></a> </div>
	    </div>
		<?php if($_SESSION['option']->ProductUseArticle) { ?>
			<div class="row m-b-10">
				<div class="col-md-5 text-right">Артикул</div>
			    <div class="col-md-7"> <strong class="f-s-16"><?=$product->article_show?></strong> </div>
		    </div>
		<?php } if(!$changePriceTab && ($_SESSION['user']->admin || !empty($marketing))) { ?>
			<div class="row m-b-10">
				<div class="col-md-5 text-right">Базова вартість</div>
			    <div class="col-md-7">
			    	<strong><?=$product->currency ? $product->price .' '.$product->currency : $this->shop_model->formatPrice($product->price) ?></strong>
			    	<?php if($product->old_price > $product->price) { ?>
			    		<del title='Стара ціна (до акції)'> <?=$product->currency ? $product->old_price .' '.$product->currency : $this->shop_model->formatPrice($product->old_price) ?> </del>
			    	<?php } ?>
			    </div>
		    </div>
		    <?php if(!empty($product->markup)) { ?>
		    	<div class="row m-b-10">
					<div class="col-md-5 text-right">Активна націнка</div>
				    <div class="col-md-7"> <strong><?=$product->markup?></strong> </div>
			    </div>
		<?php } } ?>
			<div class="row m-b-10">
				<div class="col-md-5 text-right">Наявність</div>
			    <div class="col-md-7"> <strong> <?=($_SESSION['option']->useAvailability) ? $product->availability .' од.' : $product->availability_name?></strong> </div>
		    </div>
		<?php

		if(file_exists(__DIR__ . DIRSEP .'__product_additionall_fields-info.php'))
			require_once '__product_additionall_fields-info.php';

		if($_SESSION['option']->useGroups && $groups && $product->group) {
			function parentsLink(&$parents, $all, $parent, $link)
			{
				if($parent > 0)
				{
					$link = $all[$parent]->alias .'/'.$link;
					$parents[] = $parent;
					if($all[$parent]->parent > 0) $link = parentsLink ($parents, $all, $all[$parent]->parent, $link);
					return $link;
				}
			}
			function makeLink($all, $parent, $link)
			{
				if($parent > 0)
				{
					$link = $all[$parent]->alias .'/'.$link;
					if($all[$parent]->parent > 0) $link = parentsLink ($parents, $all, $all[$parent]->parent, $link);
				}
				return $link;
			} ?>

			<div class="row m-b-10">
				<div class="col-md-5 text-right">Група/и</div>
			    <div class="col-md-7">
			<?php if($_SESSION['option']->ProductMultiGroup) {
				foreach ($product->group as $g) {
					if (empty($list[$g]))
						continue;
					$g = $list[$g];

					reset($_SESSION['alias']->breadcrumb);
					$link = SITE_URL.'admin/'.$_SESSION['alias']->alias;
					$name = key($_SESSION['alias']->breadcrumb);
	            	echo "<a href=\"{$link}\" target=\"_blank\">{$name}</a>/";
	            	if($g->parent > 0) {
	            		$parents = array();
	            		$g->link = SITE_URL.'admin/'.$_SESSION['alias']->alias .'/'. parentsLink($parents, $list, $g->parent, $g->alias);
	            		if($parents)
	            		{
	            			krsort ($parents);
	            			foreach ($parents as $parent) {
	            				$link = SITE_URL.'admin/'.$_SESSION['alias']->alias .'/'. makeLink($list, $list[$parent]->parent, $list[$parent]->alias);
	            				echo "<a href=\"{$link}\" target=\"_blank\">{$list[$parent]->name}</a>/";
	            			}
	            		}
	            	}
	            	else
	            		$g->link = SITE_URL.'admin/'.$_SESSION['alias']->alias .'/'. $g->alias;
	            	echo "<strong><a href=\"{$g->link}\" target=\"_blank\"><strong>{$g->name}</strong></a></strong> <br>";
	            }
			}
			else if(!empty($product->parents))
					foreach ($product->parents as $parent) {
						$link = SITE_URL.'admin/'.$_SESSION['alias']->alias .'/'. makeLink($list, $parent->parent, $parent->alias);
						if($product->group != $parent->id)
							echo "<a href=\"{$link}\" target=\"_blank\">{$parent->name}</a>/";
						else
							echo "<a href=\"{$link}\" target=\"_blank\"><strong>{$parent->name}</strong></a>";
					}
			echo "</div></div>"; 
		}

		if(!empty($product->similarProducts)) { ?>
			<div class="row m-b-10">
				<div class="col-md-5 text-right">Аналоги / подібні</div>
				<div class="col-md-7">
				<?php foreach($product->similarProducts as $folderName => $similarProductOrFolder) {
					if(is_array($similarProductOrFolder)) {
						if($folderName != 'default') {
							echo "<h5>{$folderName}:</h5>";
						}
						foreach($similarProductOrFolder as $similarProduct) {
							$link = SITE_URL . "admin/{$similarProduct->link}";
							echo "<a href=\"{$link}\"><strong>{$similarProduct->article_show}</strong> {$similarProduct->name}</a><br>";
						}
					}
					else if(is_object($similarProductOrFolder)) {
						$link = SITE_URL . "admin/{$similarProductOrFolder->link}";
						echo "<a href=\"{$link}\"><strong>{$similarProductOrFolder->article_show}</strong> {$similarProductOrFolder->name}</a><br>";
					}
				}
				
				if(!empty($storages)) { ?>
				<button class="btn btn-info btn-xs m-t-5" onclick="show_similarProductsInvoices()"><i class="fa fa-qrcode"></i> Показати наявність за аналогами</button>
				<?php } ?>
				</div>
			</div>
		<?php } ?>
		<div class="row m-b-10">
			<div class="col-md-5 text-right">Статус</div>
		    <div class="col-md-7"> <strong> <?php switch ($product->active) {
		    	case 1:
		    		echo 'Товар активний';
		    		break;
	    		case -1:
		    		echo 'Очікує підтвердження адміністрацією <a href="/admin/'.$_SESSION['alias']->alias.'/confirmProduct?id='.$product->id.'" class="btn btn-success btn-xs">Підтвердити</a>';
		    		break;
	    		case -2:
		    		echo 'Товар формується (створюється автором)';
		    		break;
		    	
		    	default:
		    		echo 'Товар тимчасово відключено';
		    		break;
		    }?> </strong> </div>
	    </div>
	</div>
	<div class="col-md-6">
		<?php if($product->options)
			foreach ($product->options as $option) if(!empty($option->value)) { ?>
			<div class="row m-b-10">
				<div class="col-md-5 text-right"><?=$option->name?></div>
			    <div class="col-md-7">
			    	<?php if(!empty($option->photo) && file_exists(substr($option->photo, strlen(SITE_URL)))) echo "<img src='{$option->photo}' style='width:30px'>"; ?>
			    	<?php if(is_array($option->value)) {
			    		$names = [];
			    		foreach ($option->value as $value) {
			    			$names[] =$value->name;
			    		}
			    		$names = implode(', ', $names);
			    		echo "<strong> {$names} </strong>";
			    	}
			    	elseif(is_object($option->value)) echo "<strong> {$option->value->name} </strong>";
			    	else { 
			    		$option->value = nl2br($option->value);
			    		echo "<strong> {$option->value} </strong>";
			    	}?>
			    	<?=$option->sufix?>
			    </div>
		    </div>
		<?php } ?>
	</div>
</div>
<?php
if(!empty($storages)) {
	echo '<div class="row">';
	echo "<h3>Склад / наявність</h3>";
	$invoice_to_product = $product;
	require 'tab-storages.php';
	if(!empty($product->similarProducts))
	{
		echo '<button class="btn btn-info btn-xs m-t-5 m-b-20" onclick="show_similarProductsInvoices()"><i class="fa fa-qrcode"></i> Показати наявність за аналогами</button>';
		echo "<div id='similarProductsInvoices'></div>";
		// echo "<h3>Аналоги / подібні</h3>";
		// foreach($product->similarProducts as $similarProduct) {
		// 	echo "<h4><a href=\"/admin/{$similarProduct->link}\">{$similarProduct->manufacturer} <strong>{$similarProduct->article_show}</strong> {$similarProduct->name}</a></h4>";
		// 	$invoice_to_product = $similarProduct;
		// 	require 'tab-storages.php';
		// }
	}
	echo "</div>";
}

if(!empty($marketing))
		foreach($marketing as $tab) {
			if($tab->key == 'price_per_type')
				continue;
			echo '<div class="row">';
			echo "<h3>{$tab->name}</h3>";
			echo $tab->content;
			echo "</div>";
		}
?>


<script type="text/javascript">
	function showUninstalForm () {
		if($('#uninstall-form').is(":hidden")){
			$('#uninstall-form').slideDown("slow");
		} else {
			$('#uninstall-form').slideUp("fast");
		}
	}
	function show_similarProductsInvoices() {
		$('#saveing').css("display", "block");
	    $.ajax({
	        url: ALIAS_ADMIN_URL+"similarProductsInvoices",
	        type: 'POST',
	        data: {
	            product_id:  <?=$product->id?>
	        },
	        success: function(html){
	            $('#saveing').css("display", "none");
	            $('#similarProductsInvoices').html(html);
	        },
	        error: function(){
	            alert("Помилка! Спробуйте ще раз!");
	            $('#saveing').css("display", "none");
	        },
	        timeout: function(){
	            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
	            $('#saveing').css("display", "none");
	        }
	    });
	}
</script>