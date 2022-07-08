<?php /* <link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/shop.css'?>"> */ ?>
<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/shop.css'?>">

<main id="compare" class="container">
	<div class="row">
		<h1><?=$_SESSION['alias']->name?></h1>
	</div>
	<section class="groups">
		<?php if(!empty($groups))
		{
			foreach ($groups as $group) { ?>
				<figure>
					<?php if($group->file_name) { ?>
						<img src="<?=IMG_PATH.$group->alias_uri.'/-'.$group->id.'/'.$group->file_name?>" alt="<?=$group->name?>">
					<?php } ?>
					<figcaption>
						<h2><?=$group->name?></h2>
						<a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$group->id?>"><?=$group->name?></a>
					</figcaption>			
				</figure>
			<?php }
			$addDiv = count($groups) % 3;
			while ($addDiv++ < 3) {
				echo "<figure class='empty'></figure>";
			} 
		} else
			echo $this->text('Немає товарів у порівнянні. Додавайте товари до порівняння характеристик та обирайте товар, який Вам підійде найкраще.'); ?>
	</section>
	<?php if(!empty($_SESSION['alias']->text)) { ?>
	    <section class="row">
	        <h4><?=$_SESSION['alias']->list?></h4>
	        <p><?=html_entity_decode($_SESSION['alias']->text)?></p>
	    </section>
	<?php } ?>
</main>