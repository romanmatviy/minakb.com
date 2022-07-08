<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/shop.css'?>">

<main class="container">
	<?php $path__breadcrumbs = APP_PATH . 'views/@commons/__breadcrumbs.php';
	if(file_exists($path__breadcrumbs))
		require $path__breadcrumbs;

	if($this->userCan()) { ?>
		<a href="<?=SERVER_URL?>admin/<?=$_SESSION['alias']->link?>" class="pull-right m-hide" target="_blank"><i class="fas fa-user-cog"></i> Редагувати</a>
	<?php } ?>

	<h1><?=$_SESSION['alias']->name?></h1>
	<?php if(!empty($filters)) { ?>
		<i class="fas fa-sliders-h hide m-show"></i>
	<?php }
	if(!empty($_SESSION['alias']->list))
		echo "<p class=\"short\">{$_SESSION['alias']->list}</p>"; ?>

	<div class="d-flex m-wrap catalog bordered">
		<?php $class = 'w100';
		if(!empty($filters)) {
			$class = 'w80';
			echo '<aside class="w20 m-w100">';
			require_once '__filter.php';
			echo "</aside>";
		} ?>
		<article class="<?=$class?> m-w100">
			<?php if(!empty($subgroups)) {
				echo "<section class='subgroups d-flex wrap'>";
				foreach ($subgroups as $subgroup) {
					echo '<a href="'.SITE_URL.$subgroup->link.'">';
					if($subgroup->photo)
						echo '<img src="'.IMG_PATH.$subgroup->subgroup_photo.'" alt="'.IMG_PATH.$subgroup->name.'">';
					echo "<span>{$subgroup->name}</span></a>";
				}
				if($addDiv = count($subgroups) % 4)
					while ($addDiv++ < 4) {
						echo "<a class='empty'></a>";
					}
				echo "</section>";
			}

			if(!empty($products)) {
				echo "<section class='d-flex wrap products'>";
				foreach ($products as $product) {
					require '__product_subview.php';
				}
				$addDiv = count($products) % 3;
				if($addDiv)
					while ($addDiv++ < 3) {
						echo "<a class='empty'></a>";
					}
				echo "</section>";

				echo "<div class='pull-right mt-15'>Знайдено: {$_SESSION['option']->paginator_total} страв</div>";

				$this->load->library('paginator');
				echo $this->paginator->get();
			}
			elseif($_SESSION['option']->showProductsParentsPages || empty($subgroups))
			{
				if($use_filter)
					$errors = $this->text('за даним запитом товари не знайдено');
				else
					$errors = $this->text('В даній групі відсутні товари'); ?>
				<div class="p-15">
					<div class="alert alert-danger">
			            <h4><?=$this->text('Вибачте,', 0)?></h4>
			            <p><?=$errors?></p>
			        </div>
				</div>
            <?php }

			if(empty($_GET['page']) && !$use_filter && (!empty($_SESSION['alias']->list) || !empty($_SESSION['alias']->text))) {
				echo '<section class="description">';
				// if(!empty($_SESSION['alias']->list))
				// 	echo "<p class=\"short\">{$_SESSION['alias']->list}</p>";
				if(!empty($_SESSION['alias']->videos)) {
                    $this->load->library('video');
                    $this->video->show_many($_SESSION['alias']->videos);
                }
                echo $_SESSION['alias']->text;
                echo "</section>";
			} ?>
		</article>
	</div>
</main>