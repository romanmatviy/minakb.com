<?php if(!empty($_SESSION['alias']->images)) {
	echo '<div class="row">
			<h3>Фото</h3>
			<div class="row" id="PHOTOS">';
	foreach($_SESSION['alias']->images as $photo) {
		echo '<a href="'.IMG_PATH.$photo->path.'" class="pull-left m-r-15">
                <img src="'.IMG_PATH.$photo->admin_path.'">
            </a>';
	}
	echo "<div class='clearfix'></div></div></div>"; ?>

	<!-- The blueimp Gallery widget -->
	<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
	    <div class="slides"></div>
	    <h3 class="title"></h3>
	    <a class="prev">‹</a>
	    <a class="next">›</a>
	    <a class="close">×</a>
	    <a class="play-pause"></a>
	    <ol class="indicator"></ol>
	</div>
	<script type="text/javascript">
		window.onload = function () {
			document.getElementById('PHOTOS').onclick = function (event) {
		        event = event || window.event;
		        var target = event.target || event.srcElement;
	            var link = target.src ? target.parentNode : target,
	                options = {index: link, event: event},
	                links = this.getElementsByTagName('a');
	            blueimp.Gallery(links, options);
		    };
		}
	</script>
	<?php
	$_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.blueimp-gallery.min.js";
	echo '<link rel="stylesheet" href="https://blueimp.github.io/Gallery/css/blueimp-gallery.min.css">';
}


if($videos = $this->db->getAllDataByFieldInArray('wl_video', array('alias' => $_SESSION['alias']->id, 'content' => $_SESSION['alias']->content))) {
	echo '<div class="row m-t-20"><h3>Відео</h3>';
	foreach($videos as $video){ ?>
		<div class="col-md-6 text-center">
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
		</div>
<?php }
	echo "</div>";
}

if($files = $this->db->getAllDataByFieldInArray('wl_files', array('alias' => $_SESSION['alias']->id, 'content' => $_SESSION['alias']->content))) {
	echo '<div class="row m-t-20"><h3>Файли</h3><ul>';
	foreach($files as $file){ ?>
		<li>
			<a href="<?= $this->data->get_file_path($file)?>" target="_blank">
				<?php switch ($file->extension) {
					case 'pdf':
						echo '<i class="fa fa-file-pdf-o fa-2x" title="Дивитися"></i>';
						break;
					case 'doc':
					case 'docx':
						echo '<i class="fa fa-file-word-o fa-2x" title="Дивитися"></i>';
						break;
					case 'xls':
					case 'xlsx':
						echo '<i class="fa fa-file-excel-o fa-2x" title="Дивитися"></i>';
						break;
					case 'ppt':
					case 'pptx':
						echo '<i class="fa fa-file-excel-o fa-2x" title="Дивитися"></i>';
						break;
					case 'mp4':
						echo '<i class="fa fa-film fa-2x" title="Дивитися"></i>';
						break;
					
					default:
						echo '<i class="fa fa-file" aria-hidden="true" title="Дивитися"></i>';
						break;
				} 
				echo " <strong>{$file->text}</strong>";?>
			</a>
		</li>
	<?php }
	echo "</ul></div>";
} ?>