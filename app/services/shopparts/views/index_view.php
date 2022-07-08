<?php if(isset($products)){ ?>
<div class="cube-portfolio container margin-bottom-60">
    <div class="content-xs">
        <div id="grid-container">
            <?php
                foreach($products as $article){
            ?>
                    <div class="cbp-item">
                        <div class="cbp-caption" style="border: 2px solid white;margin-top:2px">
                            <div class="cbp-caption-defaultWrap">
                                <img src="<?= IMG_PATH.$article->s_photo?>" alt="">
                            </div>
                            <div class="cbp-caption-activeWrap">
                                <div class="cbp-l-caption-alignCenter">
                                    <div class="cbp-l-caption-body">                            
                                        <ul class="link-captions">
                                            <li><a href="<?=SITE_URL.$article->link?>"><i class="rounded-x fa fa-link"></i></a></li>
                                        </ul>
                                        <div class="cbp-l-grid-agency-title"><?=$article->name?></div>
                                        <button onclick="cart.add(<?= $article->id.', '.$article->wl_alias?>)">Додати до корзини</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>