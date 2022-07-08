<link rel="stylesheet" href="<?=SITE_URL?>style/css/unify/page_search_inner.css">

<!-- 
    PAGE HEADER 
    
    CLASSES:
        .page-header-xs = 20px margins
        .page-header-md = 50px margins
        .page-header-lg = 80px margins
        .page-header-xlg= 130px margins
        .dark           = dark page header

        .shadow-before-1    = shadow 1 header top
        .shadow-after-1     = shadow 1 header bottom
        .shadow-before-2    = shadow 2 header top
        .shadow-after-2     = shadow 2 header bottom
        .shadow-before-3    = shadow 3 header top
        .shadow-after-3     = shadow 3 header bottom
-->
<section class="page-header dark page-header-xs">
    <div class="container">

        <h1><?=$_SESSION['alias']->name?></h1>

    </div>
</section>
<!-- /PAGE HEADER -->

<!--=== Search Block Version 2 ===-->
<div class="search-block-v2">
    <div class="container">
        <div class="col-md-6 col-md-offset-3">
            <form action="<?=SITE_URL?>search">
                <h2><?=$this->text('Повторити пошук')?></h2>
                <?php if(isset($_SESSION['notify'])) require_once 'admin/notify_view.php'; ?>
                <div class="input-group sidebar-search">
                    <input type="text" name="by" value="<?=$this->data->re_get('by')?>" class="form-control" placeholder="<?=$this->text('Шукати', 0)?>" required>
                    <span class="input-group-btn">
                        <button class="btn btn-inverse" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>
                <div class="input-group">
                    
                </div>
            </form>
        </div>
    </div>    
</div><!--/container-->     
<!--=== End Search Block Version 2 ===-->

<!--=== Search Results ===-->
<div class="container s-results margin-bottom-50">
    <span class="results-number"><?=$this->text('About').' '.$_SESSION['option']->paginator_total.' '.$this->text('results')?></span>

    <?php 
    if(!empty($data)) {
        foreach ($data as $search) {
    ?>
        <div class="inner-results">
            <h3><a href="<?=SITE_URL.$search->link?>"><?=$search->name?></a></h3>
            <ul class="list-inline up-ul">
                <li><?=SITE_URL.$search->link?></li>
                <?php 
                if(is_array($search->additional)) {
                    foreach ($search->additional as $link => $name) {
                        echo("<li><a href=\"".SITE_URL.$link."\">{$name}</a></li>");
                    }
                }
                ?>
            </ul>
            <div class="overflow-h">
                <?php if($search->image) { ?>
                    <img src="<?=IMG_PATH.$search->image?>" alt="<?=$search->name?>" title="<?=$search->name?>">
                <?php } ?>
                <div class="overflow-a">
                    <?php
                    if($search->list != ''){
                        echo("<p>{$search->list}</p>");
                    } else {
                        echo($this->data->getShortText($search->text, 400));
                    }
                    if($search->date > 0) {
                    ?>
                        <ul class="list-inline down-ul">
                            <li><?=date('d.m.Y', $search->date).' By '.$search->author_name?></li>
                            <!-- <li>2,092,675 views</li> -->
                        </ul>
                    <?php } ?>
                </div>       
            </div>    
        </div>

        <hr>
    <?php
        }
    }
    ?>
    
    <div class="margin-bottom-30"></div>

    <div class="text-left">
        <?php
            $this->load->library('paginator');
            echo($this->paginator->get());
        ?>                                              
    </div>
</div><!--/container-->     
<!--=== End Search Results ===-->