<link rel="stylesheet" type="text/css" href="<?=SERVER_URL.'style/'.$_SESSION['alias']->alias.'/shop.css'?>">
<link rel="stylesheet" type="text/css" href="/assets/lightGallery/css/lightgallery.css">

<main class="container product edit">
    <a href="<?=SITE_URL.$product->link?>" class="pull-right btn btn-success"><i class="fas fa-eye"></i> Перегляд товару</a>
    <?php if($this->userCan()) { ?>
        <a href="<?=SITE_URL.'admin/'.$product->link?>" class="pull-right btn btn-warning" target="_blank"><i class="fas fa-cogs"></i> Редагувати Admin</a>
    <?php }

    require APP_PATH.'views/@commons/__breadcrumbs.php';

    if(!empty($_SESSION['notify']->errors)) { ?>
       <div class="alert alert-danger">
            <span class="close" data-dismiss="alert">×</span>
            <h4><i class="fas fa-exclamation-triangle"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Помилка!'?></h4>
            <p><?=$_SESSION['notify']->errors?></p>
        </div>
    <?php } elseif(!empty($_SESSION['notify']->success)) { ?>
        <div class="alert alert-success">
            <span class="close" data-dismiss="alert">×</span>
            <h4><i class="fas fa-check"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Успіх!'?></h4>
            <p><?=$_SESSION['notify']->success?></p>
        </div>
    <?php } unset($_SESSION['notify']);
    
    if ($product->active == -2) { ?>
        <div class="alert alert-warning">
            <h3>Етап 2. Тепер необхідно вказати додаткову інформацію: повний та короткий опис, додаткові характеристики, фото, відео</h3>
            <p>Коли всю інформацію заповнено, <a href="<?=SITE_URL.$product->link?>" class="btn btn-success"><i class="fas fa-eye"></i> перегляньте товар як покупець</a> За потреби внесіть праки.</p>
            <p><u>На кінцевій сторінці є кнопка надіслати товар на перевірку до адміністрації</u></p>
        </div>
    <?php }
    if (false && $product->active == -2) { ?>
        <div class="alert alert-warning">
            <h3>Етап 2. Тепер необхідно вказати додаткову інформацію: повний та короткий опис, додаткові характеристики, фото, відео</h3>
            <p>Коли всю інформація заповнено, натисніть кнопку "Надіслати товар на перевірку". Адміністрація сайту перевірить вказану інформацію та активує товар або ж надішле повідомлення щодо відмови вказавжи причину.</p>
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
    <?php } ?>

    <form action="<?=SITE_URL.$_SESSION['alias']->alias?>/save" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?=$product->id?>">

    <label>Назва товару</label>
    <input type="text" name="name" value="<?=$product->name?>" required placeholder="Назва товару">

    <label>Короткий опис</label>
    <textarea name="list"><?=$product->list?></textarea>
    
    <div class="d-flex m-wrap">
        <div class="w33-5 m-w100">
            <div class="bordered">
                <h4>Додайте реальні фото товару</h4>
                <input type="file" name="photo[]" accept="image/jpg,image/jpeg,image/png" multiple id="add-images" onchange="imagesPreview(this, '.add-gallery')">
                <br>
                <div class="add-gallery"></div>
            </div>
            <div class="bordered">
                <h4>Наявні фото товару</h4>
                <?php if(!empty($_SESSION['alias']->images)) {
                    echo '<div class="product-gallery d-flex wrap">';
                    foreach ($_SESSION['alias']->images as $i => $image) {
                        $path = 'images/'.$image->path;
                        if (!file_exists($path))
                            continue; ?>
                    
                        <figure class="w25-5 text-center">
                            <a href="<?=IMG_PATH.$image->path?>" class="w100">
                                <img src="<?=IMG_PATH.$image->thumb_path?>" alt="<?=$image->title ?? $product->name?>" class="w-auto"/>
                            </a>
                            <a href="<?=SITE_URL.$_SESSION['alias']->alias?>/delete_image?id=<?=$image->id?>" class='trash'><i class="far fa-trash-alt"></i> Видалити</a>
                        </figure>

                    <?php }
                    for ($a=0; $a < (4 - $i%4); $a++) { 
                        echo '<figure class="w25-5"></figure>';
                    }
                    echo "</div>";
                } ?>
            </div>
        </div>
        <div class="w33-5 m-w100">
            <div class="bordered">
                <h4>Характеристики</h4>
                <?php if ($options) {
                    foreach ($options as $option) {
                        if($option->type_name == 'select' && $option->id != 8) {
                            $selected_id = $product->options[$option->alias]->value->id ?? 0; ?>
                            <div class="d-flex v-center">
                                <label><?=$option->name?></label>
                                <select name="option-<?=$option->id?>" required <?=$selected_id ? '':'class="required"'?>>
                                    <?php if($selected_id == 0)
                                        echo "<option disabled selected>Оберіть потрібне</option>";
                                    foreach ($option->values as $value) {
                                        $selected = ($selected_id == $value->id) ? 'selected' : '';
                                        echo "<option value={$value->id} {$selected}>{$value->name}</option>";
                                    } ?>
                                </select>
                            </div>
                <?php } } } ?>
            </div>
            <div class="shipping-info bordered">
                <h4>Доставка та оплата</h4>
                <p>Керується з профілю користувача (<a href="/profile/edit" target="_blank"><i class="fas fa-edit"></i> редагувати</a>)</p>
            </div>
        </div>
        <div class="w33-5 m-w100">
            <div class="bordered">
                <h4>Основне</h4>
                <div class="d-flex v-center">
                    <label>Ціна у грн</label>
                    <input type="number" min="1" name="price" value="<?=$product->price?>" required placeholder="Ціна">
                </div>
                <?php if($groups) { ?>
                <div class="d-flex v-center">
                    <label>Група</label>
                    <?php if($_SESSION['option']->ProductMultiGroup && false)
                    {
                        $_SESSION['alias']->js_load[] = 'assets/jstree/jstree.min.js';
                        $_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/init-jstree.js';
                        echo '<link rel="stylesheet" href="'.SITE_URL.'assets/jstree/themes/default/style.min.css" />';
                        echo '<input type="hidden" name="product_groups" id="selected" value="" />';
                        $product_groups = array();
                        require_once '_groupsTree.php';
                    }
                    else
                    {
                        $list = array();
                        $emptyChildsList = array();
                        foreach ($groups as $g) {
                            $list[$g->id] = $g;
                            $list[$g->id]->child = array();
                            if(isset($emptyChildsList[$g->id])) {
                                foreach ($emptyChildsList[$g->id] as $c) {
                                    $list[$g->id]->child[] = $c;
                                }
                            }
                            if($g->parent > 0) {
                                if(isset($list[$g->parent]->child)) $list[$g->parent]->child[] = $g->id;
                                else {
                                    if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
                                    else $emptyChildsList[$g->parent] = array($g->id);
                                }
                            }
                        }

                        echo('<select name="group" required>');
                        echo ('<option value="0" disabled selected>Оберіть кінцеву групу</option>');
                        if(!empty($list))
                        {
                            function showList($active_group, $all, $list, $parent = 0, $level = 0)
                            {
                                $prefix = '';
                                for ($i=0; $i < $level; $i++) { 
                                    $prefix .= '- ';
                                }
                                foreach ($list as $g) if($g->parent == $parent) {
                                    if(empty($g->child)){
                                        $selected = ($active_group == $g->id) ? 'selected' : '';
                                        echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
                                    } else {
                                        echo('<optgroup label="'.$prefix.$g->name.'">');
                                        $l = $level + 1;
                                        $childs = array();
                                        foreach ($g->child as $c) {
                                            $childs[] = $all[$c];
                                        }
                                        showList ($active_group, $all, $childs, $g->id, $l);
                                        echo('</optgroup>');
                                    }
                                }
                                return true;
                            }
                            showList($product->group, $list, $list);
                        }
                        echo('</select>');
                    } ?>
                </div>
                <?php } ?>
                <div class="d-flex v-center">
                    <label>Наявність</label>
                    <?php if($_SESSION['option']->useAvailability)
                        echo '<input type="number" min="0" name="availability" value="'.$product->availability.'" required placeholder="Наявність (одиниць)">';
                    else
                    {
                        $where_availability_name = ['availability' => '#a.id'];
                        if($_SESSION['language']) $where_availability_name['language'] = $_SESSION['language'];
                        $availability = $this->db->select($_SESSION['service']->table.'_availability as a', '*', 1, 'active')
                                                ->join($_SESSION['service']->table.'_availability_name', 'name', $where_availability_name)
                                                ->order('position')
                                                ->get('array');
                        if($availability) { ?>
                            <select name="availability" required>
                                <?php foreach ($availability as $a) {
                                    $selected = $a->id == $product->availability ? 'selected' : '';
                                    echo "<option value='{$a->id}' {$selected}>{$a->name}</option>";
                                } ?>
                            </select>
                        <?php }
                    } ?>
                </div>
                <?php if ($product->active >= 0) { ?>
                <div class="d-flex v-center">
                    <label>Активність (доступність на сайті)</label>
                    <select name="active" <?=$product->active >= 0 ? '' : 'disabled'?>>
                        <option value="1" <?=$product->active ? 'selected':''?>>Активний</option>
                        <option value="0" <?=$product->active ? '':'selected'?>>Відключено</option>
                    </select>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="d-flex wrap">
        <div class="w50-5 m-w100">
            <h4 class="MarckScript">Розширений опис товару</h4>
            <textarea name="text" rows="10" class="w100" placeholder="Розширений опис товару" id="editor"><?=html_entity_decode($product->text, ENT_QUOTES, 'utf-8')?></textarea>
        </div>
        <div class="w50-5 m-w100">
            <div class="bordered">
                <h4>Додати відео товару</h4>
                <input type="text" name="video" placeholder="Адреса відеозапису">
                <p>Підтримуються сервіси youtu.be, youtube.com, vimeo.com</p>
            </div>
            <?php if(!empty($_SESSION['alias']->videos)) { ?>
            <div class="bordered">
                <h4>Наявні відео товару</h4>
                <div class="d-flex wrap video-gallery">
                <?php foreach($_SESSION['alias']->videos as $video) { ?>
                    <div class="w50-5 m100 text-center">
                        <?php if($video->site == 'youtube'){ ?>
                            <a href="https://www.youtube.com/watch?v=<?=$video->link?>" class="video" target="_blank">
                                <img src="https://img.youtube.com/vi/<?=$video->link?>/mqdefault.jpg">
                            </a>
                        <?php } elseif($video->site == 'vimeo'){ 
                            $vimeo = false;
                            @$vimeo = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$video->link.php"));
                            if($vimeo){
                            ?>
                            <a href="https://vimeo.com/<?=$video->link?>" class="video" target="_blank">
                                <img src="<?=$vimeo[0]['thumbnail_large']?>">
                            </a>
                        <?php } } ?>
                        <a href="<?=SITE_URL.$_SESSION['alias']->alias?>/delete_video?id=<?=$video->id?>" class='trash'><i class="far fa-trash-alt"></i> Видалити</a>
                    </div>
                <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <div class="d-flex h-center">
        <button id="showContacts"><i class="far fa-save"></i> Зберегти</button>
    </div>
</main>

<?php $this->load->js(['assets/ckeditor-basic/ckeditor.js',
                        'assets/lightGallery/js/lightgallery.js',
                        'assets/lightGallery/modules/lg-thumbnail.min.js']);
$this->load->js_init("var editor = CKEDITOR.replace( 'editor' ); $(\".product-gallery\").lightGallery({'selector':'a.w100'}); init_edit();");
if(!empty($_SESSION['alias']->videos)) {
    $this->load->js('assets/lightGallery/modules/lg-video.min.js');
    $this->load->js_init("$(\".video-gallery\").lightGallery({'selector':'a.video'});");
} ?>

<style>
    .product-gallery figure, .video-gallery .m100 { margin-bottom: 15px }
    form .d-flex, .gallery { padding: 5px }
    .add-gallery img { height: 120px; width: auto; padding: 5px }
    form .d-flex label { width: 40% }
    @media screen and (max-width: 576px) {
        .add-gallery img { height: 80px }
    }
</style>
<script type="text/javascript">
    var imagesPreview = function(input, placeToInsertImagePreview) {
        $(placeToInsertImagePreview).empty();
        if (input.files) {
            var filesAmount = input.files.length;
            for (i = 0; i < filesAmount; i++) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    $($.parseHTML('<img>')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                }
                reader.readAsDataURL(input.files[i]);
            }
        }
    };
    function init_edit()
    {
        $('.trash').click(function(event) {
            if(!confirm('Ви впевнені, що бажаєте видалити фото/відео?'))
                event.preventDefault();
        });
    }
</script>