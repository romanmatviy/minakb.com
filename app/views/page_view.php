<html lang="<?=$_SESSION['language']?>" prefix="og: http://ogp.me/ns#">
<head>
	<title><?=$_SESSION['alias']->title?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?=$_SESSION['alias']->title?>">
    <meta name="description" content="<?=$_SESSION['alias']->description?>">
    <meta name="keywords" content="<?=$_SESSION['alias']->keywords?>">
    <meta name="author" content="webspirit.com.ua">

    <meta property="og:locale"             content="<?=$_SESSION['language']?>_UA" />
    <meta property="og:title"              content="<?=$_SESSION['alias']->title?>" />
    <meta property="og:description"        content="<?=$_SESSION['alias']->description?>" />
    	<?php if(!empty($_SESSION['alias']->image)) { ?>
	<meta property="og:image"			   content="<?=IMG_PATH.$_SESSION['alias']->image?>" />
    	<?php } ?>

	<?=html_entity_decode($_SESSION['option']->global_MetaTags, ENT_QUOTES)?>
    <?=html_entity_decode($_SESSION['alias']->meta, ENT_QUOTES)?>

	<link rel="shortcut icon" href="<?=SERVER_URL?>style/admin/images/whitelion-black.png">

	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">
	<link href="<?=SERVER_URL?>assets/font-awesome-5.15.1/css/all.min.css" rel="stylesheet" />
	<link href="<?=SERVER_URL?>style/ws__main.css" rel="stylesheet" />
	<link href="<?=SERVER_URL?>style/style.css" rel="stylesheet" />
</head>
<body>
     
	<?php
		include "@commons/header.php";

		echo('<main class="container">');
		if(isset($view_file)) require_once($view_file.'.php');
		echo('</main>');

		include "@commons/footer.php";
	?>

	<div id="divLoading"></div>
	<div id="modal-bg"></div>

	<script type="text/javascript" src="<?=SERVER_URL?>assets/jquery/jquery-3.5.1.min.js"></script>
	<script type="text/javascript" src="<?=SERVER_URL?>assets/sticky.min.js"></script>
	<script type="text/javascript">
		var SERVER_URL = '<?=SERVER_URL?>';
		var SITE_URL = '<?=SITE_URL?>';
		var ALIAS_URL = '<?=SITE_URL.$_SESSION['alias']->alias?>/';

		var sticky = new Sticky('header.sticky');
		$('header i.fa-bars').click(function(event) {
			$('header').addClass('mobile');
		});
		$('header i.fa-times').click(function(event) {
			$('header').removeClass('mobile');
		});
		$('.modal .close, .modal .fa-times').click(function(event) {
	        event.preventDefault;
	        $(this).closest('.modal').hide()
	        $('#modal-bg').hide()
	    });
	</script>
	<?php
		if(!empty($_SESSION['alias']->js_load)) {
			foreach ($_SESSION['alias']->js_load as $js) {
				echo '<script type="text/javascript" src="'.SERVER_URL.$js.'"></script> ';
			}
		}
		if(!empty($_SESSION['alias']->js_init)) {
	?>
	<script type="text/javascript">
	    $(document).ready(function() {
	        <?php foreach ($_SESSION['alias']->js_init as $js) { echo $js.'; '; } ?>
	    });
	</script>
	<?php } ?>
	
</body>
</html>