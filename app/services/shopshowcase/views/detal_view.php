<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/shop.css'?>">
<link rel="stylesheet" type="text/css" href="/assets/lightSlider/css/lightslider.css">
<link rel="stylesheet" type="text/css" href="/assets/lightGallery/css/lightgallery.css">

<main class="container product" itemscope itemtype="https://schema.org/Product">
    <?php if($this->userIs()) {
        if($this->userCan()) { ?>
            <a href="<?=SERVER_URL?>admin/<?=$product->link?>" class="pull-right btn btn-warning" target="_blank"><i class="fas fa-cogs"></i> Редагувати Admin</a>
        <?php } if($product->author_add == $_SESSION['user']->id || $this->userCan()) { ?>
            <a href="<?=SITE_URL.$product->link?>?edit" class="pull-right btn btn-success" target="_blank"><i class="fas fa-paint-brush"></i> Редагувати</a>
    <?php } } 
    
    $path__breadcrumbs = APP_PATH . 'views/@commons/__breadcrumbs.php';
	if(file_exists($path__breadcrumbs))
		require $path__breadcrumbs;

    if ($product->active == -2) { ?>
        <div class="alert alert-warning">
            <h3>Етап 3. Корекція вигляду</h3>
            <p>Коли вказано всю інформацію про товар, сторінка виглядає гармонійно, натисніть кнопку "Надіслати товар на перевірку". <br>Адміністрація сайту перевірить вказану інформацію та активує товар або ж надішле повідомлення щодо відмови вказавжи причину.</p>
            <form action="<?=SITE_URL.$_SESSION['alias']->alias?>/confirm" method="post">
                <input type="hidden" name="id" value="<?=$product->id?>">
                <button class="btn btn-success"><i class="fas fa-cloud-upload-alt"></i> Надіслати товар на перевірку</button>
            </form>
        </div>
    <?php }
    if ($product->active == -1) { ?>
        <div class="alert alert-success">
            <h3>Товар надіслано на перевірку</h3>
            <p>Адміністрація сайту перевірить вказану інформацію та активує товар або ж надішле повідомлення щодо відмови вказавжи причину.</p>
        </div>
    <?php } 

    $this->load->model('wl_user_model');
    $author = $this->wl_user_model->getInfo($product->author_add); ?>

    <h1><?=$_SESSION['alias']->name?></h1>
    <?php if(!empty($_SESSION['alias']->list))
            echo "<p class=\"short\">".nl2br($_SESSION['alias']->list)."</p>"; ?>

    <div class="d-flex m-wrap">
        <div class="w33-5 m-w100">
            <?php if(!empty($_SESSION['alias']->images)) {
                echo '<div class="product-gallery">';
                foreach ($_SESSION['alias']->images as $i => $image) {
                    $path = 'images/'.$image->path;
                    if (!file_exists($path))
                        continue; ?>
                
                    <figure data-thumb="<?=IMG_PATH.$image->thumb_path?>" data-src="<?=IMG_PATH.$image->path?>" itemprop="image">
                        <img src="<?=IMG_PATH.$image->detal_path?>" alt="<?=$image->title ?? $product->name?>" />
                        <figcaption><?=$image->title ?? $product->name?></figcaption>
                    </figure>

                <?php }
                echo "</div>";
            }
            else
                echo '<img src="'.SERVER_URL.'style/'.$_SESSION['alias']->alias.'/no_photo.png">';
            ?>
        </div>
        <div class="w33-5 m-w100 specifications">
            <table class="bordered w100">
                <thead>
                    <tr>
                        <td colspan="2">Коротко</td>
                    </tr>
                </thead>
                <tbody>
                    <?php /* ?>
                    <tr>
                        <td><?=$this->text('Артикул')?></td>
                        <td itemprop="sku"><?=$product->article_show?></td>
                    </tr>
                    <?php */ 
                    $address = '';
                    if(!empty($product->options))
                    foreach ($product->options as $option) {
                        if(!empty($option->value) && !is_array($option->value))
                            echo "<tr><td>{$option->name}</td><td>{$option->value->name}</td></tr>";
                        else if(!empty($option->value) && is_array($option->value))
                        {
                            $values = [];
                            foreach ($option->value as $opt)
                                $values[] = $opt->name;
                            $values = implode('<br> ', $values);
                            if($option->alias == '8-lokacija')
                                continue;
                            // echo '<tr><td>'.$option->name.'</td><td title="'.$values.'">'.$this->data->getShortText($values, 15)."</td></tr>";
                            echo '<tr><td>'.$option->name.'</td><td>'.$values."</td></tr>";
                        }
                    } ?>
                    <tr><td>Додано на сайт</td><td><?=date('d.m.Y H:i', $product->date_add)?></td></tr>
                </tbody>
            </table>
            <?php if(!empty($_SESSION['alias']->text)) { ?>
            <div class="w100 bordered h-fit" itemprop="description">
                <h4>Детальніше</h4>
                <?=$_SESSION['alias']->text?>
            </div>
            <?php }
            if(!empty($author->info['shipping'])) { $shipping = str_replace('-', '<li>', $author->info['shipping']); ?>
                <div class="shipping-info bordered">
                    <h4>Доставка</h4>
                    <ul><?=html_entity_decode($shipping)?></ul>
                </div>
            <?php } ?>
            <div class="shipping-info bordered" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                <h4>Оцінка <?php if(empty($product->rating)) $product->rating = 0;
                for($i = 0; $i < round($product->rating); $i++) { ?>
                            <i class="fas fa-star"></i>
                        <?php } for($i = round($product->rating); $i < 5; $i++) { ?>
                            <i class="far fa-star"></i>
                        <?php } ?></h4>
                Страву оцінено <strong itemprop="ratingValue"><?=$product->rating ?? 0?></strong>/5
                на основі <strong itemprop="reviewCount"><?=$product->rating_votes ?? 0?></strong> відгуків
            </div>
        </div>
        <div class="w33-5 m-w100">
            <div class="author bordered d-flex">
                <?php if($author) {
                    $user_photo = ($author->photo) ? IMG_PATH.'profile/p_'.$author->photo : SERVER_URL.'style/images/avatar.png'; ?>
                <a href="/seller/<?=$author->alias?>"><img src="<?=$user_photo?>" alt="<?=$author->name?>"></a>
                <div>
                    <?php $from_sex = 'господаря';
                     if(isset($author->info['sex'])) {
                        switch ($author->info['sex']) {
                             case 'man':
                                 echo "<h4>Господар</h4>";
                                 break;
                             case 'woman':
                                 $from_sex = 'господині';
                                 echo "<h4>Господиня</h4>";
                                 break;
                             case 'company':
                                 $from_sex = $author->name;
                                 echo "<h4>Компанія</h4>";
                                 break;
                             default:
                                 echo "<h4>Господар/господиня</h4>";
                                 break;
                         }
                    } else echo "<h4>Господар/господиня</h4>"; ?>
                    <a href="/seller/<?=$author->alias?>"><?=$author->name?></a>
                    <p><?=$product->lokacija ?? ''?></p>
                    <p>З нами від <i><?=date('d.m.Y', $author->registered)?></i></p>
                </div>
                <?php } else echo "Відсутня інформація про автора!"; ?>
            </div>
            <div class="order bordered">
                <h4><?=$product->availability == 1 ? 'В наявності (готово до видачі)' : 'Під замовлення'?></h4>
                <?php if($product->old_price > $product->price) { ?>
                <del><?=$product->old_price_format?></del> 
                <?php } ?>
                <strong class="text-center d-block"><?=$product->price_format?>/<?=$product->cina_za ?? 'одиницю'?></strong>
                <?php if($this->userIs() && $product->author_add == $_SESSION['user']->id) { ?>
                    <a href="<?=SITE_URL.$product->link?>?edit" id="showContacts" class="text-center"><i class="fas fa-cogs"></i> Редагувати</a>
                <?php } else { ?>
                    <button id="showContacts" data-id="<?=$product->id?>">Показати контактні дані</button>
                    <!-- <p class="or text-center">або</p>
                    <button id="toOrderModel">Залишити заявку</button> -->
                <?php } ?>
            </div>
            <?php if(!empty($author->info['pay'])) { $pay = str_replace('-', '<li>', $author->info['pay']); ?>
                <div class="pay-info bordered">
                    <h4>Оплата</h4>
                    <ul><?=html_entity_decode($pay)?></ul>
                </div>
            <?php } ?>
        </div>
    </div>
    <div id="tabs">
        <ul class="tabs <?=(!empty($_SESSION['alias']->videos))?'':'tabs-4'?>">
            <?php if(!empty($_SESSION['alias']->videos)) { ?>
            <li><a href="#video"><span><?=$this->text('Відео')?></span></a></li>
            <?php } ?>
            <li><a href="#similar"><span><?=$this->text('Подібні')?></span></a></li>
            <li><a href="#more"><span><?=$this->text('Ще страви від').' '.$from_sex?></span></a></li>
            <li><a href="#delivery-and-payments"><span><?=$this->text('Доставка та оплата')?></span></a></li>
            <li><a href="#reviews"><span><?=$this->text('Відгуки')?></span></a></li>
        </ul>
        <?php if(!empty($_SESSION['alias']->videos)) { ?>
        <div id="video">
            <div class="d-flex wrap">
                <?php if(!empty($_SESSION['alias']->videos)) {
                        $this->load->library('video');
                        $this->video->show_many($_SESSION['alias']->videos, '</div>', '<div class="w50-5 m-w100">');
                    } 
                if(!empty($_SESSION['alias']->files))
                    foreach ($_SESSION['alias']->files as $file) {
                        if($file->extension == 'pdf')
                        {
                            echo '<a href="'.$this->data->get_file_path($file).'" target="_blank" class="preview">';
                            $preview_path = IMG_PATH.'file_preview.jpg';
                            if(!empty($file->preview_extension))
                            {
                                $file->name .= '.'.$file->preview_extension;
                                $preview_path = $this->data->get_file_path($file);
                            }
                            echo "<img src='{$preview_path}'></a>";
                            
                            // echo '<embed src="'.$this->data->get_file_path($file).'" type="application/pdf" width="100%" height="700" alt="pdf" pluginspage="https://get.adobe.com/ru/reader/">';
                        }
                        else
                            echo '<iframe src="'.$this->data->get_file_path($file).'"> <a href="'.$this->data->get_file_path($file).'" class="load_pdf flex" target="_blank">'.$this->text('Завантажити креслення PDF').'</a></iframe>';
                    }
                ?>
            </div>
        </div>
                <?php } ?>
        <div id="similar">
            <?php $product_id = $product->id; $product_author_add = $product->author_add;
            $product_author_add_alias = $product->author_add_alias;
            $_SESSION['option']->paginator_per_page = 8;
            if($similar = $this->shop_model->getProducts($product->group, $product->id)) {
                echo "<section class='d-flex wrap products bordered'>";
                $this->setProductsPrice($similar);
                foreach ($similar as $product) {
                    require '__product_subview.php';
                }
                $addDiv = count($similar) % 4;
                if($addDiv)
                    while ($addDiv++ < 4) {
                        echo "<a class='empty'></a>";
                    }
                echo "</section>";
            if($_SESSION['option']->paginator_total >= $_SESSION['option']->paginator_per_page) { ?>
                <div class='p-15 text-center'><a href="<?=SITE_URL.$product->group_link?>" class="btn btn-info">Дивитися більше</a></div>
            <?php } } else { ?>
                <p>На даний момент це єдина страва у групі <strong><?=$product->group_name?></strong></p>
            <?php } ?>
        </div>
        <div id="more">
            <?php $_GET['author_add'] = $product_author_add;
            if($similar = $this->shop_model->getProducts(-1, $product_id)) {
                echo "<section class='d-flex wrap products bordered'>";
                $this->setProductsPrice($similar);
                foreach ($similar as $product) {
                    require '__product_subview.php';
                }
                $addDiv = count($similar) % 4;
                if($addDiv)
                    while ($addDiv++ < 4) {
                        echo "<a class='empty'></a>";
                    }
                echo "</section>";
            if($_SESSION['option']->paginator_total >= $_SESSION['option']->paginator_per_page) { ?>
            <div class='p-15 text-center'><a href="/<?=$_SESSION['alias']->alias?>/search?author=<?=$product_author_add_alias?>" class="btn btn-info">Дивитися всі страви <?=$from_sex?></a></div>
            <?php } } else { ?>
                <p>На даний момент це єдина страва <?=$from_sex?></p>
            <?php } ?>
        </div>
        <div id="delivery-and-payments">
            <?php // echo $this->function_in_alias('delivery-and-payments', '__get_Text'); ?>
            <?php if(!empty($author->info['shipping'])) { $shipping = str_replace('-', '<li>', $author->info['shipping']); ?>
                <div class="shipping-info bordered">
                    <h4>Доставка</h4>
                    <ul><?=html_entity_decode($shipping)?></ul>
                </div>
            <?php }
            if(!empty($author->info['pay'])) { $pay = str_replace('-', '<li>', $author->info['pay']); ?>
                <div class="pay-info bordered">
                    <h4>Оплата</h4>
                    <ul><?=html_entity_decode($pay)?></ul>
                </div>
            <?php } ?>
        </div>
        <div id="reviews" class='d-flex wrap'>
            <?php $this->load->model("wl_comments_model");
            $_SESSION['option']->paginator_per_page = 20;
            $comments = $this->wl_comments_model->get(['status' => '<3', 'parent' => 0, 'content_author' => $product_author_add]);

            $showAddForm = true; $image_name = false;
            if($this->userIs() && $product->author_add == $_SESSION['user']->id)
            {
                echo "<div class='alert alert-warning w100'>Увага! Автор товару не може додавати відгуки про себе</div>";
                $showAddForm = false;
            }
            $alias = $_SESSION['alias']->id;
            $alias = $_SESSION['alias']->alias == 'dishes' ? 8 : 9; // for lamure
            $content = $_SESSION['alias']->content;
            $comments_title = 'Відгуки про господаря';
            $_GET = ['request' => 'reviews', 'author' => $product_author_add_alias];
            include APP_PATH."views/@wl_comments/index_view.php";?>
        </div>
    </div>
</main>

<?php $this->load->js(['assets/jquery-ui/ui/minified/jquery-ui.min.js',
                        'assets/lightGallery/js/lightgallery.js',
                        'assets/lightSlider/js/lightslider.js',
                        'assets/lightGallery/modules/lg-thumbnail.min.js',
                        'js/'.$_SESSION['alias']->alias.'/product.js']); ?>