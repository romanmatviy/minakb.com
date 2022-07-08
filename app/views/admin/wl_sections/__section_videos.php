<?php if ($echoSectionTag) { ?>
	<section id="section_<?= $section_id ?>" class="m-t-15">
<?php } ?>

	<div class="row">
		<div class="col-sm-10">
			<input type="text" class="editSection form-control" name="title" data-section_id="<?= $section_id ?>" value="<?= $title ?>" placeholder="Видимий заголовок [section_title]" title="Видимий заголовок [section_title]">
		</div>
		<div class="col-sm-2">
			<a href="#modal-config-section" class="btn btn-success pull-right" data-toggle="modal" data-section_id="<?= $section_id ?>" title="Налаштування секції [<?= $name ?>]"><i class="fa fa-cogs" aria-hidden="true"></i> Налаштування секції</a>
		</div>
	</div>
	<input type="text" class="editSection form-control m-t-15" name="value" data-section_id="<?= $section_id ?>" value="<?= $value ?>" placeholder="Видимий опис [section_value]" title="Видимий опис [section_value]">

	<form method="post" action="<?=SITE_URL?>admin/wl_video/save" class="m-t-15">
		<input type="hidden" name="alias" value="<?=$alias_id?>">
		<input type="hidden" name="content" value="<?=$content_id?>">
		<input type="hidden" name="section_id" value="<?=$section_id?>">
		<div class="input-group">
	        <span class="input-group-addon">Додати відео:</span>
	        <input type="text" name="video" placeholder="Адреса відеозапису" class="form-control" required>
			<div class="input-group-btn">
				<button type="submit" class="btn btn-primary">Додати</button>
			</div>
		</div>
	</form>
	<center>Підтримуються сервіси youtu.be, youtube.com, vimeo.com</center>

	<?php if(!isset($_SESSION['alias']->videos))
 			$_SESSION['alias']->videos = $this->db->getAllDataByFieldInArray('wl_video', array('alias' => $alias_id, 'content' => $content_id));
 	if($_SESSION['alias']->videos)
		foreach($_SESSION['alias']->videos as $video) if($video->section_id == $section_id) { ?>
			<div class="f-left center video">
				<?php if($video->site == 'youtube'){ ?>
					<a href="https://www.youtube.com/watch?v=<?=$video->link?>">
						<img src="https://img.youtube.com/vi/<?=$video->link?>/mqdefault.jpg">
					</a>
				<?php } elseif($video->site == 'vimeo'){ 
					$vimeo = false;
					@$vimeo = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$video->link.php"));
					if($vimeo){
					?>
					<a href="https://vimeo.com/<?=$video->link?>">
						<img src="<?=$vimeo[0]['thumbnail_large']?>">
					</a>
				<?php } } ?>
				<strong title="Код відео для вставки в текст">{video-<?=$video->id?>}</strong> 
				<a href="<?=SITE_URL?>admin/wl_video/delete?id=<?=$video->id?>" class="<?=$_SESSION['alias']->alias?>">Видалити</a>
			</div>
	<?php } ?>
	<div style="clear:both"></div>

<?php if ($echoSectionTag) { ?>
	</section>
<?php } ?>