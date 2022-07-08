<link rel="stylesheet" href="<?=SERVER_URL?>style/comments.css">

<main class="container" id="comments">
    <h1><?=$_SESSION['alias']->name?></h1>
    <?php if(!empty($_SESSION['alias']->list))
        echo "<p class=\"short\">{$_SESSION['alias']->list}</p>";

    $reviews_list_cssClass = 'w100';
    $showSeller = true;
    require_once '@wl_comments/list_view.php'; ?>
</main>

<link rel="stylesheet" href="<?=SERVER_URL?>assets/blueimp/css/blueimp-gallery.min.css">
<?php $_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.blueimp-gallery.min.js"; ?>
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>