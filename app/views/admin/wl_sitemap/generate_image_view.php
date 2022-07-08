<div class="superbox" data-offset="54">
	<?php if($images)  foreach($images as $image) { ?>
    <div class="superbox-list">
		<img src="<?= $image['admin']?>" data-img="<?= $image['original']?>" alt="" class="superbox-img" />
	</div>
	<?php } ?>
</div>


<?php 
	$_SESSION['alias']->js_load[] = 'assets/plugins/superbox/superbox.js';
	$_SESSION['alias']->js_load[] = 'assets/white-lion/gallery.js';
	$_SESSION['alias']->js_init[] = 'Gallery.init();'; 
?>