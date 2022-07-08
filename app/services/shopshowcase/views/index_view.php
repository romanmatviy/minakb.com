<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/index.css'?>">

<main class="container">
	<?php if($this->userCan()) { ?>
		<a href="<?=SERVER_URL?>admin/<?=$_SESSION['alias']->link?>" class="pull-right" target="_blank"><i class="fas fa-user-cog"></i> Редагувати</a>
	<?php } ?>
	<h1><?=$_SESSION['alias']->name?></h1>
	<?php if(!empty($catalogAllGroups)) { ?>
		<section class="groups">
			<?php foreach ($catalogAllGroups as $group) { ?>
				<figure>
					<?php if($group->photo) { ?>
						<img src="<?=IMG_PATH.$group->photo?>" alt="<?=$group->name?>">
					<?php } ?>
					<figcaption>
						<h2><?=$group->name?></h2>
						<?php if($group->list) { ?>
							<p><?=$group->list?></p>
						<?php } ?>
						<a href="<?=SITE_URL.$group->link?>"><?=$group->name?></a>
					</figcaption>			
				</figure>
			<?php }
			$addDiv = count($catalogAllGroups) % 3;
			while ($addDiv++ < 3) {
				echo "<figure class='empty'></figure>";
			} ?>
	</section>
	<?php } if(!empty($_SESSION['alias']->text)) { ?>
	    <section class="row">
	        <h4><?=$_SESSION['alias']->list?></h4>
	        <p><?=html_entity_decode($_SESSION['alias']->text)?></p>
	    </section>
	<?php } ?>
</main>