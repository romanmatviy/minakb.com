<?php 
if(isset($_SESSION['alias']->alias_from) && $_SESSION['alias']->alias_from != $_SESSION['alias']->id)
    $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load[] = 'js/'.$_SESSION['alias']->alias.'/likes.js';
else
    $_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/likes.js';
?>
<link href="<?=SERVER_URL?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<?php if($userLike == 1) { ?>
    <style type="text/css">
        .fa-heart.like {
            color: red;
        }
    </style>
<?php } ?>
<button type="button" style="border:none; background: rgba(0,0,0,0)" onclick="setLike()" title="<?=$this->text('Вподобати товар')?>">
    <i id="pageLikesFavicon" class="fa fa-lg fa-heart like"></i>
</button><span id="pageLikesCount"> <?=$likes?> </span>
 
<script>
    var LIKE_alias = <?=$alias?>;
    var LIKE_content = <?=$content?>;
    LIKE_URL = '<?=SERVER_URL.$_SESSION['alias']->alias?>';
    LIKE_ERROR_empty_name = "<?=$this->text('Вкажіть Ваше ім\'я')?>";
    LIKE_ERROR_empty_email = "<?=$this->text('Вкажіть Ваш email')?>";
    LIKE_ERROR_empty_emailtel = "<?=$this->text('Вкажіть Ваш email або номер телефону')?>";
    LIKE_ERROR_empty_password = "<?=$this->text('Вкажіть пароль до сайту '.SITE_NAME)?>";
</script>

<div id="like_no_login" tabindex="-1" role="dialog">
    <button type="button" class="close" onclick="$('#like_no_login').slideUp();"><span aria-hidden="true">&times;</span></button>
    <div name="content">
        <h2 class="wishlists-title">Додавання до&nbsp;списку бажань</h2> 
        <p>Будь ласка, авторизуйтесь, щоб додати товар у список бажань</p>

        <?php if(!empty($_SESSION['notify']->error)) { ?>
           <div class="alert alert-danger fade in">
                <span class="close" data-dismiss="alert">×</span>
                <h4><?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : 'Помилка!'?></h4>
                <p><?=$_SESSION['notify']->error?></p>
            </div>
        <?php } ?>

        <div id="like-ajax-error" class="alert alert-danger fade in">
            <span class="close" data-dismiss="alert">×</span>
            <h4><?=$this->text('Помилка!', 0)?></h4>
            <p>some error</p>
        </div>

        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" <?=(isset($_SESSION['notify']->action) && $_SESSION['notify']->action == 'like-login') ? '' : 'class="active"'?> >
                <a href="#like-signup" aria-controls="like-signup" role="tab" data-toggle="tab"><?=$this->text('Реєстрація')?></a>
            </li>
          <li role="presentation" <?=(isset($_SESSION['notify']->action) && $_SESSION['notify']->action == 'like-login') ? 'class="active"' : ''?>><a href="#like-login" aria-controls="like-login" role="tab" data-toggle="tab"><?=$this->text('Увійти')?></a></li>
        </ul>
        <div class="tab-content">
            <div id="like-signup" role="tabpanel" class="tab-pane <?=(isset($_SESSION['notify']->action) && $_SESSION['notify']->action == 'like-login') ? '' : 'active'?>">
                <form onsubmit="return likeSignUp();" action="<?=SITE_URL.$_SESSION['alias']->alias?>/signup" method="POST">
                    <div class="form-group">
                        <label for="like-name"><?=$this->text("Ваше ім'я")?></label>
                        <input type="text" name="name" id="like-name" class="form-control" required placeholder="<?=$this->text('як до Вас звертатися')?>">
                    </div>
                    <div class="form-group">
                        <label for="like-emailtel"><?=$this->text('Ваш email')?></label>
                        <input type="text" name="email" id="like-email-signup" class="form-control" required placeholder="<?=$this->text('для авторизації у Вашому кабінеті')?>" value="<?=$this->data->re_post('email')?>">
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-info pull-right" onclick="$('#like_no_login').slideUp();"><?=$this->text('Закрити')?></button>
                        <button type="submit" class="btn btn-warning"><?=$this->text('Зареєструватися')?></button>
                    </div>
                </form>
            </div>
            <div id="like-login" role="tabpanel" class="tab-pane <?=(isset($_SESSION['notify']->action) && $_SESSION['notify']->action == 'like-login') ? 'active' : ''?>">
                <form onsubmit="return likeLogin();" action="<?=SITE_URL.$_SESSION['alias']->alias?>/login" method="POST">
                    <div class="form-group">
                        <label for="like-email"><?=$this->text('email або телефон')?></label>
                        <input type="text" name="email" id="like-email-login" class="form-control" required placeholder="<?=$this->text('email або телефон')?>*" value="<?=$this->data->re_post('email')?>">
                    </div>
                    <div class="form-group">
                        <label for="like-password"><?=$this->text('Пароль')?></label>
                        <input type="password" name="password" id="like-password" class="form-control" required placeholder="<?=$this->text('Пароль')?>*">
                    </div>
                    <div class="form-group">
                        <a href="<?=SITE_URL?>reset" class="effect pull-right" target="_blank"><?=$this->text('Забув пароль?')?></a>
                        <button type="submit" class="btn btn-warning"><?=$this->text('Увійти')?></button>
                    </div>
                </form>
            </div>
        </div>

        <?php $this->load->library('facebook'); 
        if($_SESSION['option']->facebook_initialise) { ?>
            <p class="text-center"><?=$this->text('Швидкий вхід:')?></p>
            <div class="form-group text-center">
                <button type="button" onclick="facebookSignUp()" class="btn btn-success btn-lg"><i class="fa fa-facebook"></i> facebook</button>
            </div>

            <script>
                window.fbAsyncInit = function() {
                    
                    FB.init({
                      appId      : '<?=$this->facebook->getAppId()?>',
                      cookie     : true,
                      xfbml      : true,
                      version    : 'v2.6'
                    });
                };

                (function(d, s, id){
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) {return;}
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/en_US/sdk.js";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            </script>
        <?php } ?>
    </div>
</div>

<div id="like_confirm" tabindex="-1" role="dialog">
    <button type="button" class="close" onclick="$('#like_confirm').slideUp();"><span aria-hidden="true">&times;</span></button>
    <div name="content">
        <h2 class="wishlists-title">Список бажань</h2> 

        <p>Ви дійсно бажаєте забрати даний товар з Вашого списку бажань?</p>
        
        <button type="button" class="btn btn-warning pull-right" onclick="$('#like_confirm').slideUp();"><?=$this->text('Скасувати')?></button>
        <button type="button" class="btn btn-success" onclick="setCancel()"><?=$this->text('Підтвердити')?></button>
    </div>
</div>

<div id="like_set_success" tabindex="-1" role="dialog">
    <button type="button" class="close closeSuccess"><span aria-hidden="true">&times;</span></button>
    <div name="content">
        <h2 class="wishlists-title">Список бажань</h2> 

        <p id="like_success_signup">Дякуємо за реєстрацію. Тепер Ви можете заходити на сайт за допомогою Вашого email/тел та паролю, що вислано Вам на пошту</p>

        <p id="like-set-ok">Товар успішно додано до списку</p>
        <p id="like-set-cancel">Товар скасовано зі списку бажань. Якщо хочете залишити дану позицію, додайте повторно (клікніть по сердечку)</p>

        <div class="page-info">
            <?php $name = $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->name;
            if(!empty($page['name']))
                $name = $page['name'];
            if(!empty($page['link'])) { ?>
                <a href="<?=SITE_URL.$page['link']?>">
            <?php } if(!empty($page['image'])) { ?>
                <img src="<?=IMG_PATH.$page['image']->m_path?>" class="img-responsive" alt="<?=$page['image']->title?>">
            <?php } if(!empty($page['link'])) { ?>
                </a>
            <?php } ?>
            <h4><?=$name?></h4>
            <?php if(!empty($page['additionall'])) echo($page['additionall']); ?>
            <div style="clear: both;"></div>
        </div>
        
        <button type="button" class="btn btn-warning closeSuccess pull-right"><?=$this->text('Закрити')?></button>
        <a class="btn btn-success" href="<?=SITE_URL.$_SESSION['alias']->alias?>" target="_blank"><?=$this->text('До всіх вподобань')?></a>
    </div>
</div>


<style type="text/css">
    
    #like_no_login, #like_set_success, #like_confirm {
        position: fixed;
        z-index: 1000;
        top: 10px;
        left: 0;
        width: 100%;
        max-width: 500px;
        padding: 0 20px;
        border: none;
        background: #fff;
        border-radius: 4px!important;
        display: none;
        overflow: hidden;
        -webkit-box-shadow: 0 1px 0 0 rgba(0, 0, 0, .1), 0 0 1px 1px rgba(0, 0, 0, .08);
        box-shadow: 0 1px 0 0 rgba(0, 0, 0, .1), 0 0 1px 1px rgba(0, 0, 0, .08);
    }
    <?php if(isset($_SESSION['notify']->action) && ($_SESSION['notify']->action == 'like-login' || $_SESSION['notify']->action == 'like-signup')) { ?>
        #like_no_login {
            display: block;
        }
    <?php } 
    unset($_SESSION['notify']);
    ?>
    #like_set_success, #like_confirm {
        padding-bottom: 20px;
    }
    #like_success_signup, #like-set-ok, #like-set-cancel {
        display: none;
    }
    #like_no_login .tab-content {
        color: inherit;
        background: none;
        padding: 20px;
    }
    #like_no_login .nav-tabs>li.active>a {
        background: none;
    }
    #like_no_login > .close, #like_set_success > .close {
        float: right;
        margin-top: 10px;
    }
    #like_no_login form {
        margin: 0;
    }
    #like-ajax-error {
        display: none;
    }
    #like_set_success img {
        width: 150px;
        float: left;
        margin-right: 20px;
    }
    #like_set_success .page-info {
        margin: 10px 0;
    }
    h2.wishlists-title {
        float: none;
    }
</style>