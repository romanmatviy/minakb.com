<?php

$page = false;

if (sw($_SERVER['REQUEST_URI'], 'partners')) {
    // dd($_SERVER['REQUEST_URI']);

    $page = 'partners';
}

?>

<section class="section category-grid second-style"
	id="<?= $page ? $page : 'page' ?>">
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
				<form action="<?=SERVER_URL?>save/feedback"
					method="POST" name="message" class="inputs-border mt20">
					<div class="form-group">
						<input class="form-control" type="text"
							placeholder="<?=$this->text('Ваше ім\'я')?>"
							name="name">
					</div>
					<div class="form-group">
						<input class="form-control" type="text"
							placeholder="<?=$this->text('Email')?>"
							name="email">
					</div>
					<div class="form-group">
						<textarea class="form-control h160" name="message"
							placeholder="<?=$this->text('Повідомлення')?>"></textarea>
					</div>
					<?php
                                // $this->load->library('recaptcha');
                                // $this->recaptcha->form();
                            ?>
					<div class="form-group text-right mt-40">
						<button type="submit" class="btn btn-default"><?=$this->text('Надіслати заявку')?></button>
					</div>
				</form>
			</div>

		</div><!-- /.row -->
	</div><!-- /.container -->
</section><!-- /.category-grid -->

<?php
    $_SESSION['alias']->js_load[] = 'js/catalog.js';
