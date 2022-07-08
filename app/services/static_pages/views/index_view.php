<section>
    <div class="container">
        <div class="row">
            <div class="col-md-9 col-sm-9">
                <h1 class="blog-post-title"><?=$_SESSION['alias']->name?></h1>
                <ul class="blog-post-info list-inline">
                    <li>
                        <a href="#">
                            <i class="fa fa-clock-o"></i> 
                            <span class="font-lato"><?=date('d.m.Y H:i', $page->date_edit)?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-user"></i> 
                            <span class="font-lato"><?=$page->author_edit_name?></span>
                        </a>
                    </li>
                </ul>

                <?php
                if($_SESSION['alias']->list != '')
                    echo ("<strong>{$_SESSION['alias']->list}</strong>");
                if($_SESSION['alias']->images) { ?>

                <!-- OWL SLIDER -->
                <div class="owl-carousel buttons-autohide controlls-over" data-plugin-options='{"items": 1, "autoPlay": 4500, "autoHeight": false, "navigation": true, "pagination": true, "transitionStyle":"fadeUp", "progressBar":"false"}'>
                    <?php foreach ($_SESSION['alias']->images as $photo) { ?>
                        <a class="lightbox" href="<?=IMG_PATH.$photo->path?>" data-plugin-options='{"type":"image"}'>
                            <img class="img-responsive" src="<?=IMG_PATH.$photo->path?>" alt="<?=$photo->title?>" />
                        </a>
                    <?php } ?>
                </div>
                <!-- /OWL SLIDER -->
                <?php }
                
                echo($_SESSION['alias']->text);

                if($_SESSION['alias']->videos) {
                    echo('<div class="margin-bottom-20 embed-responsive embed-responsive-16by9">');
                    $this->video->show_many($_SESSION['alias']->videos);
                    echo('</div>');
                }
                ?>
            </div>
        </div>
    </div>
</section>