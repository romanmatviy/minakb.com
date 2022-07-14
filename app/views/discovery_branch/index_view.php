<section class="section category-grid second-style">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<div class="abouttext pb30">
					<h1 class="headblog"><?=$_SESSION['alias']->list?>
					</h1>
					<?=html_entity_decode($_SESSION['alias']->text)?>
				</div>
			</div>

			<div class="col-md-6 mb-3">
				Text
			</div>

		</div><!-- /.row -->
	</div><!-- /.container -->
</section><!-- /.category-grid -->
<?php
    $_SESSION['alias']->js_load[] = 'js/catalog.js';
