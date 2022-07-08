<div id="reviews-list" class="w60-5 m-w100">
<?php if($comments) {

	if(!empty($_SESSION['notify']->success)): ?>
	    <div id="comment_add_success" class="alert alert-success">
	        <span class="close" data-dismiss="alert">×</span>
	        <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Success!')?></h4>
	        <p><?=$this->text($_SESSION['notify']->success)?></p>
	    </div>
	<?php endif; ?>

	<?php foreach ($comments as $comment) { ?>
		<div id="comment-<?=$comment->id?>">
			<div class="rating">
				<?php for($i = 0; $i < $comment->rating; $i++) { ?>
					<i class="fas fa-star" aria-hidden="true"></i>
				<?php } for($i = $comment->rating; $i < 5; $i++) { ?>
					<i class="far fa-star" aria-hidden="true"></i>
				<?php } ?>
				<div class="pull-right">
					<span><?=$comment->user_name?></span>
					<time><?=date('d.m.Y H:i', $comment->date_add)?></time>
				</div>
			</div>
			<p class="reviews-article mb-0">
				<?=nl2br($comment->comment)?>
			</p>
			<?php if($comment->images) {
				echo('<p>');
				$comment->images = explode('|||', $comment->images);
				foreach ($comment->images as $image) {
					echo '<a href="'.IMG_PATH.'comments/'.$comment->id.'/'.$image.'" class="lightgallery"><img src="'.IMG_PATH.'comments/'.$comment->id.'/m_'.$image.'"></a>';
				}
				echo('</p>');
			} ?>
			<?php if($comment->reply) {
				$this->wl_comments_model->paginator = false;
			if($replys = $this->wl_comments_model->get(array('parent' => $comment->id, 'status' => '<3')))
				foreach ($replys as $reply) { ?>
					<div class="reply">
						<div class="reviews-author-date">
							<span><?=$reply->user_name?></span>
							<time><?=date('d.m.Y H:i', $reply->date_add)?></time>
						</div>
						<p class="reviews-article mb-0">
							<?=nl2br($reply->comment)?>
						</p>
					</div>
			<?php } } ?>
		</div>
	<?php }

    $this->load->library('paginator');
    echo $this->paginator->get();
    ?>

	<link rel="stylesheet" type="text/css" href="/assets/lightGallery/css/lightgallery.css">

<?php $_SESSION['alias']->js_load[] = "assets/lightGallery/js/lightgallery.js";
	$_SESSION['alias']->js_init[] = "$('#reviews-list').lightGallery({ selector: 'a.lightgallery' })";
} else echo "<p>".$this->text('Відгуки відсутні. Будьте першим! Залиште відгук про товар')."</p>"; ?>
</div>