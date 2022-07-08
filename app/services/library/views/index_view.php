<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/library.css'?>">

<main class="container">
	<?php if($this->userCan()) { ?>
		<a href="<?=SERVER_URL?>admin/<?=$_SESSION['alias']->link?>" class="pull-right" target="_blank"><i class="fas fa-user-cog"></i> Редагувати</a>
	<?php } ?>
	<h1><?=$_SESSION['alias']->name?></h1>
	<section class="articles">
		<?php if(!empty($articles)) {
			foreach ($articles as $article) { ?>
				<figure class="bubba">
					<?php if($article->photo) { ?>
						<img src="<?=IMG_PATH.$article->photo?>" alt="<?=$article->name?>">
					<?php } ?>
					<figcaption>
						<h2><?=$article->name?></h2>
						<?php if(false && $article->list) { ?>
							<p><?=$this->data->getShortText($article->list, 60)?></p>
						<?php } ?>
						<a href="<?=SITE_URL.$article->link?>"><?=$article->name?></a>
					</figcaption>			
				</figure>
			<?php }
			$addDiv = count($articles) % 3;
			while ($addDiv++ < 3) {
				echo "<figure class='empty'></figure>";
			} } ?>
	</section>
	<?php if(!empty($_SESSION['alias']->text)) { ?>
	    <section class="row">
	        <h4><?=$_SESSION['alias']->list?></h4>
	        <p><?=html_entity_decode($_SESSION['alias']->text)?></p>
	    </section>
	<?php } ?>
</main>