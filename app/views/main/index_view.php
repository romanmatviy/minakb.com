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
						<button type="submit" class="btn btn-default"><?=$this->text('Надіслати повідомлення')?></button>
					</div>
				</form>
			</div>

		</div><!-- /.row -->
	</div><!-- /.container -->
</section><!-- /.category-grid -->
<iframe
	src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2641.08315295225!2d24.99286631596776!3d48.550799979258656!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDjCsDMzJzAyLjkiTiAyNMKwNTknNDIuMiJF!5e0!3m2!1suk!2sua!4v1514366681299"
	height="250" frameborder="0" style="border:0; width: 100%" allowfullscreen></iframe>
<?php
    $_SESSION['alias']->js_load[] = 'js/catalog.js';
